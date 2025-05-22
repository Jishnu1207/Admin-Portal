<?php

namespace App\Tests\Controller;

use App\Controller\LogController;
use App\Repository\LogRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class LogControllerTest extends TestCase
{
    private $logRepository;
    private $twig;
    private $controller;

    protected function setUp(): void
    {
        $this->logRepository = $this->createMock(LogRepository::class);
        $this->twig = $this->createMock(Environment::class);

        $this->controller = new LogController();
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
        $request = new Request([
            'type' => 'error'
        ]);

        $this->logRepository->expects($this->once())
            ->method('findByType')
            ->with('error')
            ->willReturn([]);

        $this->logRepository->expects($this->once())
            ->method('findAllTypes')
            ->willReturn(['error', 'info', 'warning']);

        $this->twig->expects($this->once())
            ->method('render')
            ->with(
                'log/index.html.twig',
                [
                    'logs' => [],
                    'typeFilter' => 'error',
                    'logTypes' => ['error', 'info', 'warning']
                ]
            )
            ->willReturn('rendered template');

        $response = $this->controller->index($request, $this->logRepository);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered template', $response->getContent());
    }
} 