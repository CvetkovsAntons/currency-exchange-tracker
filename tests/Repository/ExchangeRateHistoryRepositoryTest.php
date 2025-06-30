<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\ExchangeRateHistory;
use App\Repository\ExchangeRateHistoryRepository;
use App\Tests\Internal\Factory\CurrencyPairTestFactory;
use App\Tests\Internal\Factory\CurrencyTestFactory;
use App\Tests\Internal\Factory\ExchangeRateHistoryTestFactory;
use App\Tests\Internal\Traits\PurgeDatabaseTrait;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

class ExchangeRateHistoryRepositoryTest extends WebTestCase
{
    use PurgeDatabaseTrait;

    private EntityManagerInterface $em;
    private ExchangeRateHistoryRepository $repository;

    protected function container(): Container
    {
        return static::getContainer();
    }

    protected function setUp(): void
    {
        static::bootKernel();

        $this->em = $this->container()->get(EntityManagerInterface::class);
        $this->repository = $this->em->getRepository(ExchangeRateHistory::class);
    }

    protected function tearDown(): void
    {
        $this->purgeDatabase();
        parent::tearDown();
    }

    public function testGetAllReturnsEntities(): void
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
        $history = ExchangeRateHistoryTestFactory::create($pair, '1.11');

        $this->em->persist($from);
        $this->em->persist($to);
        $this->em->persist($pair);
        $this->em->persist($history);
        $this->em->flush();

        $result = $this->repository->getAll();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertInstanceOf(ExchangeRateHistory::class, $result[0]);
    }

    public function testFindClosestBefore(): void
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

        $pair = CurrencyPairTestFactory::create($from, $to);
        $history = ExchangeRateHistoryTestFactory::create(
            pair: $pair,
            rate: '155.23',
            createdAt: new DateTimeImmutable('-1 day')
        );

        $this->em->persist($from);
        $this->em->persist($to);
        $this->em->persist($pair);
        $this->em->persist($history);
        $this->em->flush();

        $result = $this->repository->findClosest($pair, new DateTimeImmutable());

        $this->assertInstanceOf(ExchangeRateHistory::class, $result);
        $this->assertSame($history->getRate(), $result->getRate());
    }

    public function testFindClosestBeforeReturnsNull(): void
    {
        $from = CurrencyTestFactory::create(
            code: 'CHF',
            name: 'Swiss Franc',
            namePlural: 'Swiss francs',
            symbol: 'CHF',
            symbolNative: 'CHF',
        );

        $to = CurrencyTestFactory::create(
            code: 'PLN',
            name: 'Polish Zloty',
            namePlural: 'Polish zlotys',
            symbol: 'zł',
            symbolNative: 'zł',
        );

        $pair = CurrencyPairTestFactory::create($from, $to);

        $this->em->persist($from);
        $this->em->persist($to);
        $this->em->persist($pair);
        $this->em->flush();

        $result = $this->repository->findClosest($pair, new DateTimeImmutable());

        $this->assertNull($result);
    }

}
