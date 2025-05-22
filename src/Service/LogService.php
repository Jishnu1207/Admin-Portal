<?php

namespace App\Service;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

class LogService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Logs an activity event.
     */
    public function logActivity(string $message, ?int $userId = null, ?array $details = null): void
    {
        $log = new Log();
        $log->setType('activity');
        $log->setLevel('info'); // Activities are typically info level
        $log->setMessage($message);
        $log->setTimestamp(new DateTimeImmutable());
        $log->setUserId($userId);
        // Store details as JSON string if provided
        $log->setDetails($details ? json_encode($details) : null);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /**
     * Logs an error event.
     */
    public function logError(string $message, string $level = 'error', ?array $details = null): void
    {
        $log = new Log();
        $log->setType('error');
        $log->setLevel($level);
        $log->setMessage($message);
        $log->setTimestamp(new DateTimeImmutable());
        // Error logs might not always be tied to a specific user action
        $log->setUserId(null);
        // Store details as JSON string if provided
        $log->setDetails($details ? json_encode($details) : null);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
