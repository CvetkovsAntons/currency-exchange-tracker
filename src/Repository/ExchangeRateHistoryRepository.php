<?php

namespace App\Repository;

use App\Entity\ExchangeRateHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExchangeRateHistory>
 */
class ExchangeRateHistoryRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExchangeRateHistory::class);
    }

    /**
     * @return ExchangeRateHistory[]
     */
    public function getAll(): array
    {
        return $this->createQueryBuilder('h')
            ->join('h.currencyPair', 'p')
            ->join('p.fromCurrency', 'f')
            ->join('p.toCurrency', 't')
            ->addSelect('p', 'f', 't')
            ->orderBy('f.code', 'ASC')
            ->addOrderBy('t.code', 'ASC')
            ->getQuery()
            ->getResult();
    }

}
