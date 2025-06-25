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

    public function findClosestBefore(CurrencyPair $pair, ?DateTimeInterface $createdAt = null): ?ExchangeRateHistory
    {
        $query = $this->createQueryBuilder('h')
            ->where('h.currencyPair = :pair')
            ->orderBy('h.createdAt', 'DESC')
            ->setParameter('pair', $pair)
            ->setMaxResults(1);

        if (!is_null($createdAt)) {
            $query->andWhere('h.createdAt <= :date')
                ->setParameter('date', $createdAt);
        }

        return $query->getQuery()->getOneOrNullResult();
    }

}
