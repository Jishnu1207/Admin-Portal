<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CustomerRepository;
use App\Repository\InvoiceRepository;

class AdminDashboardController extends AbstractController
{
    #[Route('/admin', name: 'app_admin_dashboard')]
    public function index(CustomerRepository $customerRepository, InvoiceRepository $invoiceRepository): Response
    {
        $customerCount = $customerRepository->count([]);
        $invoiceCounts = $invoiceRepository->getAllInvoiceCounts();

        return $this->render('admin_dashboard/index.html.twig', [
            'customerCount' => $customerCount,
            'invoiceCount' => $invoiceCounts['total'],
            'paidInvoiceCount' => $invoiceCounts['paid'],
            'unpaidInvoiceCount' => $invoiceCounts['unpaid'],
            'cancelledInvoiceCount' => $invoiceCounts['cancelled'],
        ]);
    }
}
