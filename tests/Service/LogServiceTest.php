<?php

namespace App\Tests\Service;

use App\Entity\Log;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class LogServiceTest extends TestCase
{
    private $entityManager;
    private $logService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logService = new LogService($this->entityManager);
    }

    public function testLogActivity(): void
    {
        $message = 'Test activity message';
        $userId = 123;
        $details = ['key' => 'value'];

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (Log $log) use ($message, $userId, $details) {
                return $log->getType() === 'activity' &&
                       $log->getLevel() === 'info' &&
                       $log->getMessage() === $message &&
                       $log->getUserId() === $userId &&
                       $log->getDetails() === json_encode($details) &&
                       $log->getTimestamp() instanceof DateTimeImmutable;
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->logService->logActivity($message, $userId, $details);
    }

    public function testLogActivityWithoutOptionalParams(): void
    {
        $message = 'Test activity message';

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (Log $log) use ($message) {
                return $log->getType() === 'activity' &&
                       $log->getLevel() === 'info' &&
                       $log->getMessage() === $message &&
                       $log->getUserId() === null &&
                       $log->getDetails() === null &&
                       $log->getTimestamp() instanceof DateTimeImmutable;
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->logService->logActivity($message);
    }

    public function testLogError(): void
    {
        $message = 'Test error message';
        $level = 'critical';
        $details = ['error' => 'test error'];

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (Log $log) use ($message, $level, $details) {
                return $log->getType() === 'error' &&
                       $log->getLevel() === $level &&
                       $log->getMessage() === $message &&
                       $log->getUserId() === null &&
                       $log->getDetails() === json_encode($details) &&
                       $log->getTimestamp() instanceof DateTimeImmutable;
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->logService->logError($message, $level, $details);
    }

    public function testLogErrorWithDefaultLevel(): void
    {
        $message = 'Test error message';

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (Log $log) use ($message) {
                return $log->getType() === 'error' &&
                       $log->getLevel() === 'error' &&
                       $log->getMessage() === $message &&
                       $log->getUserId() === null &&
                       $log->getDetails() === null &&
                       $log->getTimestamp() instanceof DateTimeImmutable;
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->logService->logError($message);
    }
} 