<?php

namespace App\Repository;

use App\Entity\CurrencyPair;
use App\Entity\ExchangeRate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;

/**
 * @extends ServiceEntityRepository<ExchangeRate>
 */
class ExchangeRateRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $em,
    )
    {
        parent::__construct($registry, ExchangeRate::class);
    }

    public function save(ExchangeRate $exchangeRate, bool $flush = true): void
    {
        $this->em->persist($exchangeRate);

        if ($flush) {
            $this->em->flush();
        }
    }

    public function exists(CurrencyPair $currencyPair): bool
    {
        return !is_null($this->findOneBy(['currencyPair' => $currencyPair]));
    }

}
