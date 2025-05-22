<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Customer>
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    /**
     * Finds a paginated list of Customer entities.
     *
     * @param int $page The current page number (1-indexed).
     * @param int $limit The maximum number of results per page.
     * @param string|null $searchQuery The search query string.
     *
     * @return Customer[] Returns an array of Customer objects for the current page.
     */
    public function findPaginated(int $page, int $limit, ?string $searchQuery = null): array
    {
        $offset = ($page - 1) * $limit;

        $queryBuilder = $this->createQueryBuilder('c')
            ->orderBy('c.id', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if ($searchQuery) {
            $queryBuilder->andWhere('c.name LIKE :search OR c.email LIKE :search')
                         ->setParameter('search', '%' . $searchQuery . '%');
        }

        $result = $queryBuilder->getQuery()->getResult();
        return $result ?? [];
    }

    /**
     * Counts the number of Customer entities matching a search query.
     *
     * @param string|null $searchQuery The search query string.
     *
     * @return int The total number of matching customers.
     */
    public function countBySearch(?string $searchQuery = null): int
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->select('count(c.id)');

        if ($searchQuery) {
            $queryBuilder->andWhere('c.name LIKE :search OR c.email LIKE :search')
                         ->setParameter('search', '%' . $searchQuery . '%');
        }

        $result = $queryBuilder->getQuery()->getSingleScalarResult();
        return (int) ($result ?? 0);
    }
}
