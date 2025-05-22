<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Invoice;
use App\Repository\CustomerRepository;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class ApiController extends AbstractController
{
    #[Route('/list/{entity}', name: 'app_api_list', methods: ['GET'])]
    public function list(string $entity, CustomerRepository $customerRepository, InvoiceRepository $invoiceRepository): JsonResponse
    {
        $data = match($entity) {
            'customer' => array_map(function($customer) {
                return [
                    'id' => $customer->getId(),
                    'name' => $customer->getName(),
                    'phone' => $customer->getPhone(),
                    'email' => $customer->getEmail(),
                    'address' => $customer->getAddress(),
                ];
            }, $customerRepository->findAll()),
            'invoice' => array_map(function($invoice) {
                return [
                    'id' => $invoice->getId(),
                    'customer' => $invoice->getCustomer(),
                    'date' => $invoice->getDate()->format('Y-m-d'),
                    'amount' => $invoice->getAmount(),
                    'status' => $invoice->getStatus(),
                ];
            }, $invoiceRepository->findAll()),
            default => throw $this->createNotFoundException('Entity not found'),
        };

        return $this->json($data);
    }

    #[Route('/create/{entity}', name: 'app_api_create', methods: ['POST'])]
    public function create(string $entity, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $result = match($entity) {
            'customer' => $this->createCustomer($data, $entityManager),
            'invoice' => $this->createInvoice($data, $entityManager),
            default => throw $this->createNotFoundException('Entity not found'),
        };

        return $this->json($result);
    }

    private function createCustomer(array $data, EntityManagerInterface $entityManager): array
    {
        $customer = new Customer();
        $customer->setName($data['name']);
        $customer->setPhone($data['phone'] ?? null);
        $customer->setEmail($data['email']);
        $customer->setAddress($data['address'] ?? null);

        $entityManager->persist($customer);
        $entityManager->flush();

        return [
            'id' => $customer->getId(),
            'message' => 'Customer created successfully'
        ];
    }

    private function createInvoice(array $data, EntityManagerInterface $entityManager): array
    {
        $invoice = new Invoice();
        $invoice->setCustomer($data['customer']);
        $invoice->setDate(new \DateTime($data['date']));
        $invoice->setAmount($data['amount']);
        $invoice->setStatus($data['status']);

        $entityManager->persist($invoice);
        $entityManager->flush();

        return [
            'id' => $invoice->getId(),
            'message' => 'Invoice created successfully'
        ];
    }
}
