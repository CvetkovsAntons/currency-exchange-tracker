<?php

namespace App\Repository;

use App\Entity\CurrencyPair;
use App\Entity\ExchangeRateHistory;
use DateTimeInterface;
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

    public function findClosest(CurrencyPair $pair, ?DateTimeInterface $createdAt = null): ?ExchangeRateHistory
    {
        $baseQuery = $this->createQueryBuilder('h')
            ->where('h.currencyPair = :pair')
            ->setParameter('pair', $pair);

        if (is_null($createdAt)) {
            return $baseQuery
                ->orderBy('h.createdAt', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        }

        $beforeQuery = clone $baseQuery;
        $before = $beforeQuery
            ->andWhere('h.createdAt <= :date')
            ->orderBy('h.createdAt', 'DESC')
            ->setParameter('date', $createdAt)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $afterQuery = clone $baseQuery;
        $after = $afterQuery
            ->andWhere('h.createdAt > :date')
            ->orderBy('h.createdAt', 'ASC')
            ->setParameter('date', $createdAt)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$before) {
            return $after;
        }

        if (!$after) {
            return $before;
        }

        $diffBefore = abs($createdAt->getTimestamp() - $before->getCreatedAt()->getTimestamp());
        $diffAfter = abs($after->getCreatedAt()->getTimestamp() - $createdAt->getTimestamp());

        return $diffBefore <= $diffAfter ? $before : $after;
    }

}
