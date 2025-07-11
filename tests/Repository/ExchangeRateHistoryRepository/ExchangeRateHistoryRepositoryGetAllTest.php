<?php

declare(strict_types=1);

namespace App\Tests\Repository\ExchangeRateHistoryRepository;

use App\Entity\ExchangeRateHistory;
use App\Tests\Abstract\Repository\AbstractExchangeRateHistoryRepositoryTest;
use App\Tests\Internal\Factory\CurrencyPairTestFactory;
use App\Tests\Internal\Factory\CurrencyTestFactory;
use App\Tests\Internal\Factory\ExchangeRateHistoryTestFactory;

class ExchangeRateHistoryRepositoryGetAllTest extends AbstractExchangeRateHistoryRepositoryTest
{
    public function testSuccess(): void
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

}
