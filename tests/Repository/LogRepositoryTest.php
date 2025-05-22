<?php

namespace App\Tests\Repository;

use App\Entity\Log;
use App\Repository\LogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class LogRepositoryTest extends TestCase
{
    private $entityManager;
    private $managerRegistry;
    private $logRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->managerRegistry->method('getManagerForClass')
                              ->willReturn($this->entityManager);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->name = Log::class;
        $this->entityManager->method('getClassMetadata')
                            ->with(Log::class)
                            ->willReturn($metadata);

        $this->logRepository = $this->getMockBuilder(LogRepository::class)
            ->setConstructorArgs([$this->managerRegistry])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();
    }

    public function testFindPaginatedWithoutFilters(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\Query::class);

        $this->logRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('l')
            ->willReturn($queryBuilder);

        $queryBuilder->method('select')
            ->willReturnSelf();

        $queryBuilder->method('orderBy')
            ->willReturnSelf();

        $queryBuilder->method('setMaxResults')
            ->willReturnSelf();

        $queryBuilder->method('setFirstResult')
            ->willReturnSelf();

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $query->expects($this->once())
            ->method('getResult')
            ->willReturn([]);

        $result = $this->logRepository->findPaginated(1, 10);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFindPaginatedWithSearchQuery(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\Query::class);

        $this->logRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('l')
            ->willReturn($queryBuilder);

        $queryBuilder->method('select')
            ->willReturnSelf();

        $queryBuilder->method('orderBy')
            ->willReturnSelf();

        $queryBuilder->method('setMaxResults')
            ->willReturnSelf();

        $queryBuilder->method('setFirstResult')
            ->willReturnSelf();

        $queryBuilder->method('andWhere')
            ->willReturnSelf();

        $queryBuilder->method('setParameter')
            ->willReturnSelf();

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $query->expects($this->once())
            ->method('getResult')
            ->willReturn([]);

        $result = $this->logRepository->findPaginated(1, 10, 'test');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFindPaginatedWithTypeFilter(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\Query::class);

        $this->logRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('l')
            ->willReturn($queryBuilder);

        $queryBuilder->method('select')
            ->willReturnSelf();

        $queryBuilder->method('orderBy')
            ->willReturnSelf();

        $queryBuilder->method('setMaxResults')
            ->willReturnSelf();

        $queryBuilder->method('setFirstResult')
            ->willReturnSelf();

        $queryBuilder->method('andWhere')
            ->willReturnSelf();

        $queryBuilder->method('setParameter')
            ->willReturnSelf();

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $query->expects($this->once())
            ->method('getResult')
            ->willReturn([]);

        $result = $this->logRepository->findPaginated(1, 10, null, 'error');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFindPaginatedWithDateRange(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\Query::class);

        $this->logRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('l')
            ->willReturn($queryBuilder);

        $queryBuilder->method('select')
            ->willReturnSelf();

        $queryBuilder->method('orderBy')
            ->willReturnSelf();

        $queryBuilder->method('setMaxResults')
            ->willReturnSelf();

        $queryBuilder->method('setFirstResult')
            ->willReturnSelf();

        $queryBuilder->method('andWhere')
            ->willReturnSelf();

        $queryBuilder->method('setParameter')
            ->willReturnSelf();

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $query->expects($this->once())
            ->method('getResult')
            ->willReturn([]);

        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-01-31');
        $result = $this->logRepository->findPaginated(1, 10, null, null, $startDate, $endDate);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testCountBySearch(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\Query::class);

        $this->logRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('l')
            ->willReturn($queryBuilder);

        $queryBuilder->method('select')
            ->willReturnSelf();

        $queryBuilder->method('andWhere')
            ->willReturnSelf();

        $queryBuilder->method('setParameter')
            ->willReturnSelf();

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn(5);

        $result = $this->logRepository->countBySearch('test');
        $this->assertEquals(5, $result);
    }
}
