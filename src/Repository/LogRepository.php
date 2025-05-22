<?php

namespace App\Repository;

use App\Entity\Log;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Log>
 */
class LogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Log::class);
    }

    /**
     * Finds logs filtered by type and ordered by timestamp.
     *
     * @param string|null $type The type to filter by
     * @return Log[] Returns an array of Log objects
     */
    public function findByType(?string $type = null): array
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->orderBy('l.timestamp', 'DESC');

        if ($type) {
            $queryBuilder->andWhere('l.type = :type')
                        ->setParameter('type', $type);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Finds all unique log types.
     *
     * @return array Returns an array of unique log types
     */
    public function findAllTypes(): array
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->select('DISTINCT l.type')
            ->orderBy('l.type', 'ASC');

        $result = $queryBuilder->getQuery()->getResult();
        return array_column($result, 'type');
    }

    /**
     * Finds logs with pagination and optional filters.
     *
     * @param int $page The page number
     * @param int $limit The number of items per page
     * @param string|null $searchQuery Optional search query
     * @param string|null $typeFilter Optional type filter
     * @param \DateTime|null $startDate Optional start date
     * @param \DateTime|null $endDate Optional end date
     * @return array Returns an array of Log objects
     */
    public function findPaginated(
        int $page,
        int $limit,
        ?string $searchQuery = null,
        ?string $typeFilter = null,
        ?\DateTime $startDate = null,
        ?\DateTime $endDate = null
    ): array {
        $queryBuilder = $this->createQueryBuilder('l')
            ->orderBy('l.timestamp', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit);

        if ($searchQuery) {
            $queryBuilder->andWhere('l.message LIKE :search')
                        ->setParameter('search', '%' . $searchQuery . '%');
        }

        if ($typeFilter) {
            $queryBuilder->andWhere('l.type = :type')
                        ->setParameter('type', $typeFilter);
        }

        if ($startDate) {
            $queryBuilder->andWhere('l.timestamp >= :startDate')
                        ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $queryBuilder->andWhere('l.timestamp <= :endDate')
                        ->setParameter('endDate', $endDate);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Counts logs matching the search query.
     *
     * @param string|null $searchQuery Optional search query
     * @return int Returns the count of matching logs
     */
    public function countBySearch(?string $searchQuery = null): int
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)');

        if ($searchQuery) {
            $queryBuilder->andWhere('l.message LIKE :search')
                        ->setParameter('search', '%' . $searchQuery . '%');
        }

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
