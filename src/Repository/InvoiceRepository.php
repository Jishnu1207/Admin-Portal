<?php

namespace App\Repository;

use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Invoice>
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    /**
     * Finds a paginated list of Invoice entities.
     *
     * @param int $page The current page number (1-indexed).
     * @param int $limit The maximum number of results per page.
     * @param string|null $searchQuery The search query string for customer name.
     * @param string|null $statusFilter The status filter value (e.g., '0', '1', '2', or 'all').
     * @param string|null $startDate The start date for filtering (Y-m-d format).
     * @param string|null $endDate The end date for filtering (Y-m-d format).
     * @param float|null $minAmount The minimum amount for filtering.
     * @param float|null $maxAmount The maximum amount for filtering.
     *
     * @return Invoice[] Returns an array of Invoice objects for the current page.
     */
    public function findPaginated(int $page, int $limit, ?string $searchQuery = null, ?string $statusFilter = null, ?string $startDate = null, ?string $endDate = null, ?float $minAmount = null, ?float $maxAmount = null): array
    {
        $offset = ($page - 1) * $limit;

        $queryBuilder = $this->createQueryBuilder('i')
            ->orderBy('i.id', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->leftJoin('i.customer', 'c'); // Join with Customer entity

        if ($searchQuery) {
            $queryBuilder->andWhere('c.name LIKE :search')
                         ->setParameter('search', '%' . $searchQuery . '%');
        }

        if ($statusFilter !== null && $statusFilter !== 'all') {
            $queryBuilder->andWhere('i.status = :status')
                         ->setParameter('status', (int)$statusFilter);
        }

        if ($startDate) {
            $queryBuilder->andWhere('i.date >= :startDate')
                         ->setParameter('startDate', new \DateTime($startDate));
        }

        if ($endDate) {
            $queryBuilder->andWhere('i.date <= :endDate')
                         ->setParameter('endDate', new \DateTime($endDate));
        }

        if ($minAmount !== null) {
            $queryBuilder->andWhere('i.amount >= :minAmount')
                         ->setParameter('minAmount', $minAmount);
        }

        if ($maxAmount !== null) {
            $queryBuilder->andWhere('i.amount <= :maxAmount')
                         ->setParameter('maxAmount', $maxAmount);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Counts the number of Invoice entities matching a search query by customer name and status filter.
     *
     * @param string|null $searchQuery The search query string for customer name.
     * @param string|null $statusFilter The status filter value (e.g., '0', '1', '2', or 'all').
     * @param string|null $startDate The start date for filtering (Y-m-d format).
     * @param string|null $endDate The end date for filtering (Y-m-d format).
     * @param float|null $minAmount The minimum amount for filtering.
     * @param float|null $maxAmount The maximum amount for filtering.
     *
     * @return int The total number of matching invoices.
     */
    public function countBySearch(?string $searchQuery = null, ?string $statusFilter = null, ?string $startDate = null, ?string $endDate = null, ?float $minAmount = null, ?float $maxAmount = null): int
    {
        $queryBuilder = $this->createQueryBuilder('i')
            ->select('count(i.id)')
            ->leftJoin('i.customer', 'c'); // Join with Customer entity

        if ($searchQuery) {
            $queryBuilder->andWhere('c.name LIKE :search')
                         ->setParameter('search', '%' . $searchQuery . '%');
        }

        if ($statusFilter !== null && $statusFilter !== 'all') {
            $queryBuilder->andWhere('i.status = :status')
                         ->setParameter('status', (int)$statusFilter);
        }

        if ($startDate) {
            $queryBuilder->andWhere('i.date >= :startDate')
                         ->setParameter('startDate', new \DateTime($startDate));
        }

        if ($endDate) {
            $queryBuilder->andWhere('i.date <= :endDate')
                         ->setParameter('endDate', new \DateTime($endDate));
        }

        if ($minAmount !== null) {
            $queryBuilder->andWhere('i.amount >= :minAmount')
                         ->setParameter('minAmount', $minAmount);
        }

        if ($maxAmount !== null) {
            $queryBuilder->andWhere('i.amount <= :maxAmount')
                         ->setParameter('maxAmount', $maxAmount);
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * Counts the number of Invoice entities by status.
     *
     * @param int $status The status to filter by (0:Unpaid, 1:Paid, 2:Cancelled).
     *
     * @return int The total number of matching invoices.
     */
    public function countByStatus(int $status): int
    {
        return $this->createQueryBuilder('i')
            ->select('count(i.id)')
            ->andWhere('i.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getAllInvoiceCounts(): array
    {
        $result = $this->createQueryBuilder('i')
            ->select('i.status, COUNT(i.id) as count')
            ->groupBy('i.status')
            ->getQuery()
            ->getResult();

        $counts = [
            'total' => 0,
            'paid' => 0,
            'unpaid' => 0,
            'cancelled' => 0
        ];

        foreach ($result as $row) {
            $counts['total'] += $row['count'];
            switch ($row['status']) {
                case 1:
                    $counts['paid'] = $row['count'];
                    break;
                case 0:
                    $counts['unpaid'] = $row['count'];
                    break;
                case 2:
                    $counts['cancelled'] = $row['count'];
                    break;
                default:
                    break;
            }
        }

        return $counts;
    }
}
