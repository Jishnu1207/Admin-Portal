<?php

namespace App\Tests\Repository;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class CustomerRepositoryTest extends TestCase
{
    private $entityManager;
    private $managerRegistry;
    private $customerRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->managerRegistry->method('getManagerForClass')
                              ->willReturn($this->entityManager);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->name = Customer::class;
        $this->entityManager->method('getClassMetadata')
                            ->with(Customer::class)
                            ->willReturn($metadata);

        $this->customerRepository = $this->getMockBuilder(CustomerRepository::class)
            ->setConstructorArgs([$this->managerRegistry])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();
    }

    public function testFindPaginatedWithoutFilters(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\Query::class);

        $this->customerRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('c')
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

        $result = $this->customerRepository->findPaginated(1, 10);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFindPaginatedWithSearchQuery(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\Query::class);

        $this->customerRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('c')
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

        $result = $this->customerRepository->findPaginated(1, 10, 'test');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testCountBySearch(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\Query::class);

        $this->customerRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('c')
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

        $result = $this->customerRepository->countBySearch('test');
        $this->assertEquals(5, $result);
    }
} 