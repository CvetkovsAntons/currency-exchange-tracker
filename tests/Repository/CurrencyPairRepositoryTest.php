<?php

namespace App\Tests\Repository;

use App\Entity\CurrencyPair;
use App\Repository\CurrencyPairRepository;
use App\Tests\Internal\Factory\CurrencyPairTestFactory;
use App\Tests\Internal\Factory\CurrencyTestFactory;
use App\Tests\Internal\Traits\PurgeDatabaseTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;

class CurrencyPairRepositoryTest extends KernelTestCase
{
    use PurgeDatabaseTrait;

    private EntityManagerInterface $em;
    private CurrencyPairRepository $repository;

    protected function container(): Container
    {
        return static::getContainer();
    }

    protected function setUp(): void
    {
        static::bootKernel();

        $this->em = $this->container()->get(EntityManagerInterface::class);
        $this->repository = $this->em->getRepository(CurrencyPair::class);
    }

    protected function tearDown(): void
    {
        $this->purgeDatabase();
        parent::tearDown();
    }

    public function testExistsReturnsTrue(): void
    {
        $from = CurrencyTestFactory::makeEntity('USD');
        $to = CurrencyTestFactory::makeEntity('EUR');

        $pair = CurrencyPairTestFactory::make($from, $to);

        $this->em->persist($from);
        $this->em->persist($to);
        $this->em->persist($pair);
        $this->em->flush();

        $result = $this->repository->exists($from, $to);

        $this->assertTrue($result);
    }

    public function testExistsReturnsFalse(): void
    {
        $from = CurrencyTestFactory::makeEntity('GBP');
        $to = CurrencyTestFactory::makeEntity('JPY');

        $this->em->persist($from);
        $this->em->persist($to);
        $this->em->flush();

        $result = $this->repository->exists($from, $to);

        $this->assertFalse($result);
    }

}
