<?php

namespace App\Tests\Controller;

use App\Controller\CustomerController;
use App\Repository\CustomerRepository;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment;

class CustomerControllerTest extends TestCase
{
    private $customerRepository;
    private $twig;
    private $logService;
    private $security;
    private $entityManager;
    private $controller;

    protected function setUp(): void
    {
        $this->customerRepository = $this->createMock(CustomerRepository::class);
        $this->twig = $this->createMock(Environment::class);
        $this->logService = $this->createMock(LogService::class);
        $this->security = $this->createMock(Security::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->controller = new CustomerController(
            $this->logService,
            $this->security
        );

        $container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturnCallback(function ($id) {
                return match($id) {
                    'twig' => $this->twig,
                    'form.factory' => $this->createMock(\Symfony\Component\Form\FormFactoryInterface::class),
                    default => null
                };
            });
        $this->controller->setContainer($container);
    }

    public function testIndex(): void
    {
        $request = new Request([
            'page' => '1',
            'q' => 'test'
        ]);

        $this->customerRepository->expects($this->once())
            ->method('findPaginated')
            ->with(1, 10, 'test')
            ->willReturn([]);

        $this->customerRepository->expects($this->once())
            ->method('countBySearch')
            ->with('test')
            ->willReturn(5);

        $this->twig->expects($this->once())
            ->method('render')
            ->with(
                'customer/index.html.twig',
                [
                    'customers' => [],
                    'currentPage' => 1,
                    'limit' => 10,
                    'totalCustomers' => 5,
                    'validLimits' => [10, 20, 50],
                    'searchQuery' => 'test'
                ]
            )
            ->willReturn('rendered template');

        $response = $this->controller->index($request, $this->customerRepository);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered template', $response->getContent());
    }

    public function testNew(): void
    {
        $request = new Request();

        $this->twig->expects($this->once())
            ->method('render')
            ->with('customer/new.html.twig')
            ->willReturn('rendered template');

        $response = $this->controller->new($request, $this->entityManager);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered template', $response->getContent());
    }
}
