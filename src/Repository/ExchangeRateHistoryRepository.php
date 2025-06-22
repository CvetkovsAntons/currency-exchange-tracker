<?php

namespace App\Repository;

use App\Entity\ExchangeRateHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExchangeRateHistory>
 */
class ExchangeRateHistoryRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $em,
    )
    {
        parent::__construct($registry, ExchangeRateHistory::class);
    }

    public function save(ExchangeRateHistory $exchangeRate, bool $flush = true): void
    {
        $this->em->persist($exchangeRate);

        if ($flush) {
            $this->em->flush();
        }
    }

}
