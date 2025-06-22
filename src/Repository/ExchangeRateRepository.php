<?php

namespace App\Repository;

use App\Entity\CurrencyPair;
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

    public function exists(CurrencyPair $currencyPair): bool
    {
        return !is_null($this->findOneBy(['currencyPair' => $currencyPair]));
    }

}
