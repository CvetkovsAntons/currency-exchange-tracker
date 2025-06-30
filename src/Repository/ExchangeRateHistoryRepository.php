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
        if (is_null($createdAt)) {
            return $this->createQueryBuilder('h')
                ->where('h.currencyPair = :pair')
                ->setParameter('pair', $pair)
                ->orderBy('h.createdAt', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        }

        $before = $this->findClosestBefore($pair, $createdAt);

        $after = $this->findClosestAfter($pair, $createdAt);

        if (is_null($before)) {
            return $after;
        }

        if (is_null($after)) {
            return $before;
        }

        $diffBefore = abs($createdAt->getTimestamp() - $before->getCreatedAt()->getTimestamp());
        $diffAfter = abs($after->getCreatedAt()->getTimestamp() - $createdAt->getTimestamp());

        return $diffBefore <= $diffAfter ? $before : $after;
    }

    public function findClosestBefore(CurrencyPair $pair, DateTimeInterface $createdAt): ?ExchangeRateHistory
    {
        return $this->createQueryBuilder('h')
            ->where('h.currencyPair = :pair')
            ->andWhere('h.createdAt <= :date')
            ->orderBy('h.createdAt', 'DESC')
            ->setParameter('pair', $pair)
            ->setParameter('date', $createdAt)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findClosestAfter(CurrencyPair $pair, DateTimeInterface $createdAt): ?ExchangeRateHistory
    {
        return $this->createQueryBuilder('h')
            ->where('h.currencyPair = :pair')
            ->andWhere('h.createdAt >= :date')
            ->orderBy('h.createdAt', 'ASC')
            ->setParameter('pair', $pair)
            ->setParameter('date', $createdAt)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

}
