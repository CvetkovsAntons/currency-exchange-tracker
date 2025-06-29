<?php

namespace App\Tests\Repository;

use App\Entity\CurrencyPair;
use App\Repository\CurrencyPairRepository;
use App\Tests\Utils\Factory\CurrencyPairTestFactory;
use App\Tests\Utils\Factory\CurrencyTestFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CurrencyPairRepositoryTest extends WebTestCase
{
    private EntityManagerInterface $em;
    private CurrencyPairRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->repository = $this->em->getRepository(CurrencyPair::class);
    }

    public function testExistsReturnsTrue(): void
    {
        $from = CurrencyTestFactory::create();
        $to = CurrencyTestFactory::create('EUR');
        $pair = CurrencyPairTestFactory::create($from, $to);

        $this->em->persist($from);
        $this->em->persist($to);
        $this->em->persist($pair);
        $this->em->flush();

        $result = $this->repository->exists($from, $to);
        $this->assertTrue($result);
    }

    public function testExistsReturnsFalse(): void
    {
        $from = CurrencyTestFactory::create('GBP');
        $to = CurrencyTestFactory::create('JPY');

        $this->em->persist($from);
        $this->em->persist($to);
        $this->em->flush();

        $result = $this->repository->exists($from, $to);
        $this->assertFalse($result);
    }

}
