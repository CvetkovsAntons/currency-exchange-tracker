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
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;

class ExchangeRateHistoryRepositoryTest extends KernelTestCase
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
        $from = CurrencyTestFactory::makeEntity('USD');
        $to = CurrencyTestFactory::makeEntity('EUR');

        $pair = CurrencyPairTestFactory::make($from, $to);
        $history = ExchangeRateHistoryTestFactory::make($pair, '1.11');

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

    public function testGetLatest(): void
    {
        $from = CurrencyTestFactory::makeEntity('USD');
        $to = CurrencyTestFactory::makeEntity('EUR');

        $pair = CurrencyPairTestFactory::make($from, $to);

        $rateOldest = ExchangeRateHistoryTestFactory::make(
            pair: $pair,
            rate: '1.10',
            createdAt: new DateTimeImmutable('-2 hours')
        );

        $rateLatest = ExchangeRateHistoryTestFactory::make(
            pair: $pair,
            rate: '1.12',
            createdAt: new DateTimeImmutable('-1 hour')
        );

        $this->em->persist($from);
        $this->em->persist($to);
        $this->em->persist($pair);
        $this->em->persist($rateOldest);
        $this->em->persist($rateLatest);
        $this->em->flush();

        $latest = $this->repository->getLatest($pair);

        $this->assertSame($rateLatest->getRate(), $latest->getRate());
    }

    public function testGetLatestBefore(): void
    {
        $from = CurrencyTestFactory::makeEntity('USD');
        $to = CurrencyTestFactory::makeEntity('EUR');

        $pair = CurrencyPairTestFactory::make($from, $to);

        $rateOldest = ExchangeRateHistoryTestFactory::make(
            pair: $pair,
            rate: '1.10',
            createdAt: new DateTimeImmutable('-2 hours')
        );

        $rateLatest = ExchangeRateHistoryTestFactory::make(
            pair: $pair,
            rate: '1.12',
            createdAt: new DateTimeImmutable('-1 hour')
        );

        $this->em->persist($from);
        $this->em->persist($to);
        $this->em->persist($pair);
        $this->em->persist($rateOldest);
        $this->em->persist($rateLatest);
        $this->em->flush();

        $datetime = new DateTimeImmutable('-90 minutes');

        $latest = $this->repository->getLatestBefore($pair, $datetime);

        $this->assertSame($rateOldest->getRate(), $latest->getRate());
    }

    public function testGetLatestAfter(): void
    {
        $from = CurrencyTestFactory::makeEntity('USD');
        $to = CurrencyTestFactory::makeEntity('EUR');

        $pair = CurrencyPairTestFactory::make($from, $to);

        $rateOldest = ExchangeRateHistoryTestFactory::make(
            pair: $pair,
            rate: '1.10',
            createdAt: new DateTimeImmutable('-2 hours')
        );

        $rateLatest = ExchangeRateHistoryTestFactory::make(
            pair: $pair,
            rate: '1.12',
            createdAt: new DateTimeImmutable('-1 hour')
        );

        $this->em->persist($from);
        $this->em->persist($to);
        $this->em->persist($pair);
        $this->em->persist($rateOldest);
        $this->em->persist($rateLatest);
        $this->em->flush();

        $datetime = new DateTimeImmutable('-90 minutes');

        $latest = $this->repository->getLatestAfter($pair, $datetime);

        $this->assertSame($rateLatest->getRate(), $latest->getRate());
    }

}
