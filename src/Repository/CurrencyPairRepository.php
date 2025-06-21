<?php

namespace App\Repository;

use App\Entity\Currency;
use App\Entity\CurrencyPair;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CurrencyPair>
 */
class CurrencyPairRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $em
    )
    {
        parent::__construct($registry, CurrencyPair::class);
    }

    public function save(CurrencyPair $currency, bool $flush = true): void
    {
        $this->em->persist($currency);

        if ($flush) {
            $this->em->flush();
        }
    }

    public function exists(Currency $from, Currency $to): bool
    {
        return (bool) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.fromCurrency = :from')
            ->andWhere('c.toCurrency = :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getSingleScalarResult();
    }

}
