<?php

namespace App\Tests\Repository;

use App\Entity\CurrencyPair;
use App\Repository\CurrencyPairRepository;
use App\Tests\Internal\Traits\PurgeDatabaseTrait;
use App\Tests\Utils\Factory\CurrencyPairTestFactory;
use App\Tests\Utils\Factory\CurrencyTestFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

class CurrencyPairRepositoryTest extends WebTestCase
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
        $from = CurrencyTestFactory::create();

        $to = CurrencyTestFactory::create(
            code: 'EUR',
            name: 'Euro',
            namePlural: 'Euros',
            symbol: '€',
            symbolNative: '€',
        );

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
        $from = CurrencyTestFactory::create(
            code: 'GBP',
            name: 'British Pound Sterling',
            namePlural: 'British pounds sterling',
            symbol: '£',
            symbolNative: '£',
        );

        $to = CurrencyTestFactory::create(
            code: 'JPY',
            name: 'Japanese Yen',
            namePlural: 'Japanese yen',
            symbol: '¥',
            symbolNative: '￥',
        );

        $this->em->persist($from);
        $this->em->persist($to);
        $this->em->flush();

        $result = $this->repository->exists($from, $to);

        $this->assertFalse($result);
    }

}
