<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\LogService;
use Symfony\Bundle\SecurityBundle\Security;

#[Route('/admin/customer')]
class CustomerController extends AbstractController
{
    private LogService $logService;
    private Security $security;

    public function __construct(LogService $logService, Security $security)
    {
        $this->logService = $logService;
        $this->security = $security;
    }

    #[Route('/', name: 'app_customer_index', methods: ['GET'])]
    public function index(Request $request, CustomerRepository $customerRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $searchQuery = $request->query->get('q');

        $validLimits = [10, 20, 50];

        if (!in_array($limit, $validLimits)) {
            $limit = 10; // Default to 10 if invalid limit is provided
        }

        $customers = $customerRepository->findPaginated($page, $limit, $searchQuery);

        return $this->render('customer/index.html.twig', [
            'customers' => $customers,
            'currentPage' => $page,
            'limit' => $limit,
            'totalCustomers' => $customerRepository->countBySearch($searchQuery),
            'validLimits' => $validLimits,
            'searchQuery' => $searchQuery,
        ]);
    }

    #[Route('/api/list', name: 'app_customer_api_list', methods: ['GET'])]
    public function apiList(CustomerRepository $customerRepository): JsonResponse
    {
        $customers = $customerRepository->findAll();
        $data = array_map(function($customer) {
            return [
                'id' => $customer->getId(),
                'name' => $customer->getName(),
                'phone' => $customer->getPhone(),
                'email' => $customer->getEmail(),
                'address' => $customer->getAddress(),
            ];
        }, $customers);

        return $this->json($data);
    }

    #[Route('/new', name: 'app_customer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $customer = new Customer();
        $form = $this->createFormBuilder($customer)
            ->add('name')
            ->add('phone')
            ->add('email')
            ->add('address')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($customer);
            $entityManager->flush();

            // Log customer creation
            $user = $this->security->getUser();
            $userId = $user ? $user->getId() : null;
            $this->logService->logActivity(
                'Customer created: ' . $customer->getName(),
                $userId,
                ['customer_id' => $customer->getId()]
            );

            return $this->redirectToRoute('app_customer_index');
        }

        return $this->render('customer/new.html.twig', [
            'customer' => $customer,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/api/create', name: 'app_customer_api_create', methods: ['POST'])]
    public function apiCreate(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $customer = new Customer();
        $customer->setName($data['name']);
        $customer->setPhone($data['phone'] ?? null);
        $customer->setEmail($data['email']);
        $customer->setAddress($data['address'] ?? null);

        $entityManager->persist($customer);
        $entityManager->flush();

        // Log customer creation via API
        $user = $this->security->getUser();
        $userId = $user ? $user->getId() : null;
        $this->logService->logActivity(
            'Customer created via API: ' . $customer->getName(),
            $userId,
            ['customer_id' => $customer->getId()]
        );

        return $this->json([
            'id' => $customer->getId(),
            'message' => 'Customer created successfully'
        ]);
    }

    #[Route('/{id}/edit', name: 'app_customer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Customer $customer, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder($customer)
            ->add('name')
            ->add('phone')
            ->add('email')
            ->add('address')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Log customer update
            $user = $this->security->getUser();
            $userId = $user ? $user->getId() : null;
            $this->logService->logActivity(
                'Customer updated: ' . $customer->getName(),
                $userId,
                ['customer_id' => $customer->getId()]
            );

            return $this->redirectToRoute('app_customer_index');
        }

        return $this->render('customer/edit.html.twig', [
            'customer' => $customer,
            'form' => $form->createView(),
        ]);
    }
}
