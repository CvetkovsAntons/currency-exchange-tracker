<?php

namespace App\Repository;

use App\Entity\ExchangeRate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template T of object
 */
class AbstractRepository extends ServiceEntityRepository
{
    protected EntityManagerInterface $em;

    public function __construct(
        ManagerRegistry $registry,
        string          $entityClass
    )
    {
        parent::__construct($registry, $entityClass);
        $this->em = $registry->getManagerForClass($entityClass);
    }

    /**
     * @param T $entity
     * @param bool $flush
     * @return void
     */
    public function save(object $entity, bool $flush = true): void
    {
        $this->em->persist($entity);
        if ($flush) {
            $this->em->flush();
        }
    }

    /**
     * @param T $entity
     * @return void
     */
    public function delete(object $entity): void
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

}
