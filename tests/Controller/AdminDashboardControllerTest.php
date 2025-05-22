<?php

namespace App\Tests\Controller;

use App\Controller\AdminDashboardController;
use App\Repository\CustomerRepository;
use App\Repository\InvoiceRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class AdminDashboardControllerTest extends TestCase
{
    private $customerRepository;
    private $invoiceRepository;
    private $twig;
    private $controller;

    protected function setUp(): void
    {
        $this->customerRepository = $this->createMock(CustomerRepository::class);
        $this->invoiceRepository = $this->createMock(InvoiceRepository::class);
        $this->twig = $this->createMock(Environment::class);

        $this->controller = new AdminDashboardController();
        $this->controller->setContainer($this->createContainerMock());
    }

    private function createContainerMock()
    {
        $container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->with('twig')
            ->willReturn($this->twig);
        return $container;
    }

    public function testIndex(): void
    {
        $this->customerRepository->expects($this->once())
            ->method('count')
            ->with([])
            ->willReturn(10);

        $this->invoiceRepository->expects($this->once())
            ->method('getAllInvoiceCounts')
            ->willReturn([
                'total' => 20,
                'paid' => 8,
                'unpaid' => 10,
                'cancelled' => 2
            ]);

        $this->twig->expects($this->once())
            ->method('render')
            ->with(
                'admin_dashboard/index.html.twig',
                [
                    'customerCount' => 10,
                    'invoiceCount' => 20,
                    'paidInvoiceCount' => 8,
                    'unpaidInvoiceCount' => 10,
                    'cancelledInvoiceCount' => 2,
                ]
            )
            ->willReturn('rendered template');

        $response = $this->controller->index($this->customerRepository, $this->invoiceRepository);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered template', $response->getContent());
    }
}
