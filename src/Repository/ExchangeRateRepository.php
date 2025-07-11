<?php

namespace App\Repository;

use App\Entity\ExchangeRate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExchangeRate>
 */
class ExchangeRateRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExchangeRate::class);
    }

    public function getAllTracked(): array
    {
        return $this->createQueryBuilder('rate')
            ->join('rate.currencyPair', 'pair')
            ->where('pair.isTracked = :isTracked')
            ->setParameter('isTracked', true)
            ->getQuery()
            ->getResult();
    }

}
