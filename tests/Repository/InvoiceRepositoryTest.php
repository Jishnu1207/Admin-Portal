<?php

namespace App\Tests\Repository;

use App\Entity\Invoice;
use App\Entity\Customer;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class InvoiceRepositoryTest extends TestCase
{
    private $entityManager;
    private $managerRegistry;
    private $invoiceRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->managerRegistry->method('getManagerForClass')
                              ->willReturn($this->entityManager);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->name = Invoice::class;
        $this->entityManager->method('getClassMetadata')
                            ->with(Invoice::class)
                            ->willReturn($metadata);

        $this->invoiceRepository = $this->getMockBuilder(InvoiceRepository::class)
            ->setConstructorArgs([$this->managerRegistry])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();
    }

    public function testFindPaginatedWithoutFilters(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\Query::class);

        $this->invoiceRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('i')
            ->willReturn($queryBuilder);

        $queryBuilder->method('select')
            ->willReturnSelf();

        $queryBuilder->method('orderBy')
            ->willReturnSelf();

        $queryBuilder->method('setMaxResults')
            ->willReturnSelf();

        $queryBuilder->method('setFirstResult')
            ->willReturnSelf();

        $queryBuilder->method('leftJoin')
            ->willReturnSelf();

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $query->expects($this->once())
            ->method('getResult')
            ->willReturn([]);

        $result = $this->invoiceRepository->findPaginated(1, 10);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFindPaginatedWithSearchQuery(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\Query::class);

        $this->invoiceRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('i')
            ->willReturn($queryBuilder);

        $queryBuilder->method('select')
            ->willReturnSelf();

        $queryBuilder->method('orderBy')
            ->willReturnSelf();

        $queryBuilder->method('setMaxResults')
            ->willReturnSelf();

        $queryBuilder->method('setFirstResult')
            ->willReturnSelf();

        $queryBuilder->method('leftJoin')
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

        $result = $this->invoiceRepository->findPaginated(1, 10, 'test');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetAllInvoiceCounts(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\Query::class);

        $this->invoiceRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('i')
            ->willReturn($queryBuilder);

        $queryBuilder->method('select')
            ->willReturnSelf();

        $queryBuilder->method('groupBy')
            ->willReturnSelf();

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $query->expects($this->once())
            ->method('getResult')
            ->willReturn([
                ['status' => 0, 'count' => 5],
                ['status' => 1, 'count' => 10],
                ['status' => 2, 'count' => 3]
            ]);

        $result = $this->invoiceRepository->getAllInvoiceCounts();
        $this->assertIsArray($result);
        $this->assertEquals([
            'total' => 18,
            'paid' => 10,
            'unpaid' => 5,
            'cancelled' => 3
        ], $result);
    }

    public function testCountByStatus(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\Query::class);

        $this->invoiceRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('i')
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

        $result = $this->invoiceRepository->countByStatus(0);
        $this->assertEquals(5, $result);
    }
}
