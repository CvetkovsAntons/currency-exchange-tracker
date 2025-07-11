<?php

declare(strict_types=1);

namespace App\Tests\Repository\ExchangeRateHistoryRepository;

use App\Tests\Abstract\Repository\AbstractExchangeRateHistoryRepositoryTest;
use App\Tests\Internal\Factory\CurrencyPairTestFactory;
use App\Tests\Internal\Factory\CurrencyTestFactory;
use App\Tests\Internal\Factory\ExchangeRateHistoryTestFactory;
use DateTimeImmutable;

class ExchangeRateHistoryRepositoryGetLatestBeforeTest extends AbstractExchangeRateHistoryRepositoryTest
{
    public function testSuccess(): void
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

}
