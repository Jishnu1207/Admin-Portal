<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\LogService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Customer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

#[Route('/admin/invoice')]
class InvoiceController extends AbstractController
{
    private LogService $logService;
    private Security $security;

    public function __construct(LogService $logService, Security $security)
    {
        $this->logService = $logService;
        $this->security = $security;
    }

    #[Route('/', name: 'app_invoice_index', methods: ['GET'])]
    public function index(Request $request, InvoiceRepository $invoiceRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $searchQuery = $request->query->get('q');
        $statusFilter = $request->query->get('status');
        $startDate = $request->query->get('start_date');
        $endDate = $request->query->get('end_date');
        $minAmount = $request->query->get('min_amount');
        $maxAmount = $request->query->get('max_amount');

        $validLimits = [10, 20, 50];
        $validStatuses = ['all', '0', '1', '2']; // 'all' for no filter, 0:Unpaid, 1:Paid, 2:Cancelled

        if (!in_array($limit, $validLimits)) {
            $limit = 10; // Default to 10 if invalid limit is provided
        }

        if (!in_array($statusFilter, $validStatuses)) {
            $statusFilter = 'all'; // Default to 'all' if invalid status is provided
        }

        $invoices = $invoiceRepository->findPaginated($page, $limit, $searchQuery, $statusFilter, $startDate, $endDate, $minAmount, $maxAmount);

        $totalInvoices = $invoiceRepository->countBySearch($searchQuery, $statusFilter, $startDate, $endDate, $minAmount, $maxAmount);
        $paidCount = $invoiceRepository->countByStatus(1);
        $unpaidCount = $invoiceRepository->countByStatus(0);
        $cancelledCount = $invoiceRepository->countByStatus(2);

        return $this->render('invoice/index.html.twig', [
            'invoices' => $invoices,
            'currentPage' => $page,
            'limit' => $limit,
            'totalInvoices' => $totalInvoices,
            'validLimits' => $validLimits,
            'searchQuery' => $searchQuery,
            'statusFilter' => $statusFilter,
            'validStatuses' => $validStatuses,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'minAmount' => $minAmount,
            'maxAmount' => $maxAmount,
            'paidCount' => $paidCount,
            'unpaidCount' => $unpaidCount,
            'cancelledCount' => $cancelledCount,
        ]);
    }

    #[Route('/api/list', name: 'app_invoice_api_list', methods: ['GET'])]
    public function apiList(InvoiceRepository $invoiceRepository): JsonResponse
    {
        $invoices = $invoiceRepository->findAll();
        $data = array_map(function($invoice) {
            return [
                'id' => $invoice->getId(),
                // Assuming getCustomer() returns a Customer object and it has a getName() or similar
                'customer' => $invoice->getCustomer() ? $invoice->getCustomer()->getName() : null,
                'date' => $invoice->getDate() ? $invoice->getDate()->format('Y-m-d') : null,
                'amount' => $invoice->getAmount(),
                'status' => $invoice->getStatus(),
            ];
        }, $invoices);

        return $this->json($data);
    }

    #[Route('/new', name: 'app_invoice_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, CustomerRepository $customerRepository): Response
    {
        $invoice = new Invoice();
        $form = $this->createFormBuilder($invoice)
            ->add('customer', EntityType::class, [
                'class' => Customer::class,
                'choice_label' => 'name',
            ])
            ->add('date')
            ->add('amount')
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Unpaid' => 0,
                    'Paid' => 1,
                    'Cancelled' => 2,
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($invoice);
            $entityManager->flush();

            // Log invoice creation
            $user = $this->security->getUser();
            $userId = $user ? $user->getId() : null;
            $this->logService->logActivity(
                'Invoice created for customer: ' . ($invoice->getCustomer() ? $invoice->getCustomer()->getName() : 'N/A'),
                $userId,
                [
                    'invoice_id' => $invoice->getId(),
                    'customer_id' => $invoice->getCustomer() ? $invoice->getCustomer()->getId() : null,
                ]
            );

            return $this->redirectToRoute('app_invoice_index');
        }

        return $this->render('invoice/new.html.twig', [
            'invoice' => $invoice,
            'form' => $form->createView(),
            'customers' => $customerRepository->findAll(),
        ]);
    }

    #[Route('/api/create', name: 'app_invoice_api_create', methods: ['POST'])]
    public function apiCreate(Request $request, EntityManagerInterface $entityManager, CustomerRepository $customerRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Fetch the customer entity based on the provided customer ID
        $customer = $customerRepository->find($data['customer_id']);

        if (!$customer) {
            return $this->json(['message' => 'Customer not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $invoice = new Invoice();
        $invoice->setCustomer($customer);
        $invoice->setDate(new \DateTime($data['date']));
        $invoice->setAmount($data['amount']);
        $invoice->setStatus($data['status']);

        $entityManager->persist($invoice);
        $entityManager->flush();

        // Log invoice creation via API
        $user = $this->security->getUser();
        $userId = $user ? $user->getId() : null;
        $this->logService->logActivity(
            'Invoice created via API for customer: ' . $customer->getName(),
            $userId,
            [
                'invoice_id' => $invoice->getId(),
                'customer_id' => $customer->getId(),
                'status' => $invoice->getStatus()
            ]
        );

        return $this->json([
            'id' => $invoice->getId(),
            'message' => 'Invoice created successfully'
        ]);
    }

    #[Route('/{id}/edit', name: 'app_invoice_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Invoice $invoice, EntityManagerInterface $entityManager, CustomerRepository $customerRepository): Response
    {
        $form = $this->createFormBuilder($invoice)
            ->add('customer', EntityType::class, [
                'class' => Customer::class,
                'choice_label' => 'name',
            ])
            ->add('date')
            ->add('amount')
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Unpaid' => 0,
                    'Paid' => 1,
                    'Cancelled' => 2,
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Log invoice update
            $user = $this->security->getUser();
            $userId = $user ? $user->getId() : null;
            $this->logService->logActivity(
                'Invoice updated for customer: ' . ($invoice->getCustomer() ? $invoice->getCustomer()->getName() : 'N/A'),
                $userId,
                [
                    'invoice_id' => $invoice->getId(),
                    'customer_id' => $invoice->getCustomer() ? $invoice->getCustomer()->getId() : null,
                    'status' => $invoice->getStatus()
                ]
            );

            return $this->redirectToRoute('app_invoice_index');
        }

        return $this->render('invoice/edit.html.twig', [
            'invoice' => $invoice,
            'form' => $form->createView(),
            'customers' => $customerRepository->findAll(),
        ]);
    }
}
