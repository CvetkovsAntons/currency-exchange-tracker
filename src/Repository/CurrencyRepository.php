<?php

namespace App\Repository;

use App\Entity\Currency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Currency>
 */
class CurrencyRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $em
    )
    {
        parent::__construct($registry, Currency::class);
    }

    public function save(Currency $currency, bool $flush = true): void
    {
        $this->em->persist($currency);

        if ($flush) {
            $this->em->flush();
        }
    }

    public function getByCode(string $code): ?Currency
    {
        return $this->findOneBy(['code' => $code]);
    }

    public function exists(string $code): bool
    {
        return !is_null($this->getByCode($code));
    }

    public function getAllCodes(): array
    {
        $codes = $this->createQueryBuilder('c')
            ->select('c.code')
            ->getQuery()
            ->getResult();

        return array_column($codes, 'code');
    }

}
