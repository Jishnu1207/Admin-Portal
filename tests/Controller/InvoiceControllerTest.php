<?php

namespace App\Tests\Controller;

use App\Controller\InvoiceController;
use App\Repository\InvoiceRepository;
use App\Repository\CustomerRepository;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment;

class InvoiceControllerTest extends TestCase
{
    private $invoiceRepository;
    private $customerRepository;
    private $twig;
    private $logService;
    private $security;
    private $entityManager;
    private $controller;

    protected function setUp(): void
    {
        $this->invoiceRepository = $this->createMock(InvoiceRepository::class);
        $this->customerRepository = $this->createMock(CustomerRepository::class);
        $this->twig = $this->createMock(Environment::class);
        $this->logService = $this->createMock(LogService::class);
        $this->security = $this->createMock(Security::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->controller = new InvoiceController(
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
            'q' => 'test',
            'status' => 'all',
            'start_date' => '2023-01-01',
            'end_date' => '2023-12-31',
            'min_amount' => '100',
            'max_amount' => '1000'
        ]);

        $this->invoiceRepository->expects($this->once())
            ->method('findPaginated')
            ->with(1, 10, 'test', 'all', '2023-01-01', '2023-12-31', 100.0, 1000.0)
            ->willReturn([]);

        $this->invoiceRepository->expects($this->once())
            ->method('countBySearch')
            ->with('test', 'all', '2023-01-01', '2023-12-31', 100.0, 1000.0)
            ->willReturn(5);

        $this->invoiceRepository->expects($this->exactly(3))
            ->method('countByStatus')
            ->willReturn(2);

        $this->twig->expects($this->once())
            ->method('render')
            ->with(
                'invoice/index.html.twig',
                [
                    'invoices' => [],
                    'currentPage' => 1,
                    'limit' => 10,
                    'totalInvoices' => 5,
                    'validLimits' => [10, 20, 50],
                    'searchQuery' => 'test',
                    'statusFilter' => 'all',
                    'validStatuses' => ['all', '0', '1', '2'],
                    'startDate' => '2023-01-01',
                    'endDate' => '2023-12-31',
                    'minAmount' => '100',
                    'maxAmount' => '1000',
                    'paidCount' => 2,
                    'unpaidCount' => 2,
                    'cancelledCount' => 2
                ]
            )
            ->willReturn('rendered template');

        $response = $this->controller->index($request, $this->invoiceRepository);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered template', $response->getContent());
    }

    public function testNew(): void
    {
        $request = new Request();

        $form = $this->createMock(\Symfony\Component\Form\FormInterface::class);
        $formView = new \Symfony\Component\Form\FormView();

        $form->expects($this->once())
            ->method('handleRequest')
            ->with($request)
            ->willReturnSelf();

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(false);

        $form->expects($this->once())
            ->method('createView')
            ->willReturn($formView);

        $formBuilder = $this->createMock(\Symfony\Component\Form\FormBuilderInterface::class);
        $formBuilder->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $formBuilder->expects($this->exactly(4))
            ->method('add')
            ->with(
                $this->callback(function ($name) {
                    return in_array($name, ['customer', 'date', 'amount', 'status']);
                }),
                $this->callback(function ($type) {
                    return $type === null ||
                           $type === \Symfony\Bridge\Doctrine\Form\Type\EntityType::class ||
                           $type === \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class;
                }),
                $this->callback(function ($options) {
                    if (empty($options)) {
                        return true;
                    }
                    if (isset($options['class']) && $options['class'] === \App\Entity\Customer::class) {
                        return true;
                    }
                    if (isset($options['choices']) && $options['choices'] === [
                        'Unpaid' => 0,
                        'Paid' => 1,
                        'Cancelled' => 2,
                    ]) {
                        return true;
                    }
                    return false;
                })
            )
            ->willReturnSelf();

        $formFactory = $this->createMock(\Symfony\Component\Form\FormFactoryInterface::class);
        $formFactory->expects($this->once())
            ->method('createBuilder')
            ->with(
                $this->callback(function ($type) {
                    return $type === \Symfony\Component\Form\Extension\Core\Type\FormType::class;
                }),
                $this->callback(function ($data) {
                    return $data instanceof \App\Entity\Invoice;
                })
            )
            ->willReturn($formBuilder);

        $this->customerRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturnCallback(function ($id) use ($formFactory) {
                return match($id) {
                    'twig' => $this->twig,
                    'form.factory' => $formFactory,
                    default => null
                };
            });
        $this->controller->setContainer($container);

        $this->twig->expects($this->once())
            ->method('render')
            ->with(
                'invoice/new.html.twig',
                $this->callback(function ($parameters) {
                    return isset($parameters['invoice']) &&
                           $parameters['invoice'] instanceof \App\Entity\Invoice &&
                           isset($parameters['form']) &&
                           $parameters['form'] instanceof \Symfony\Component\Form\FormView &&
                           isset($parameters['customers']) &&
                           is_array($parameters['customers']);
                })
            )
            ->willReturn('rendered template');

        $response = $this->controller->new($request, $this->entityManager, $this->customerRepository);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered template', $response->getContent());
    }
} 