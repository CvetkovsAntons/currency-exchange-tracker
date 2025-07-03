<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;

/**
 * @template T of object
 */
class AbstractRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(
        ManagerRegistry $registry,
        string          $entityClass
    )
    {
        parent::__construct($registry, $entityClass);

        $manager = $registry->getManagerForClass($entityClass);

        if (!$manager instanceof EntityManagerInterface) {
            throw new LogicException(sprintf(
                'Entity manager for class %s is not an instance of EntityManagerInterface.',
                $entityClass
            ));
        }

        $this->em = $manager;
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
