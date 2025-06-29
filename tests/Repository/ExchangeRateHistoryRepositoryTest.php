<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\ExchangeRateHistory;
use App\Repository\ExchangeRateHistoryRepository;
use App\Tests\Utils\Factory\CurrencyPairTestFactory;
use App\Tests\Utils\Factory\CurrencyTestFactory;
use App\Tests\Utils\Factory\ExchangeRateHistoryTestFactory;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ExchangeRateHistoryRepositoryTest extends WebTestCase
{
    private EntityManagerInterface $em;
    private ExchangeRateHistoryRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->repository = $this->em->getRepository(ExchangeRateHistory::class);
    }

    public function testGetAllReturnsEntities(): void
    {
        $from = CurrencyTestFactory::create();
        $to = CurrencyTestFactory::create('EUR');
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
        $from = CurrencyTestFactory::create('GBP');
        $to = CurrencyTestFactory::create('JPY');
        $pair = CurrencyPairTestFactory::create($from, $to);
        $createdAt = new DateTimeImmutable('-1 day');
        $history = ExchangeRateHistoryTestFactory::create($pair, '155.23', $createdAt);

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
        $from = CurrencyTestFactory::create('CHF');
        $to = CurrencyTestFactory::create('PLN');
        $pair = CurrencyPairTestFactory::create($from, $to);

        $this->em->persist($from);
        $this->em->persist($to);
        $this->em->persist($pair);
        $this->em->flush();

        $result = $this->repository->findClosest($pair, new DateTimeImmutable());

        $this->assertNull($result);
    }

}
