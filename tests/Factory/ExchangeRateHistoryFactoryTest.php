<?php

namespace App\Tests\Factory;

use App\Entity\CurrencyPair;
use App\Entity\ExchangeRate;
use App\Entity\ExchangeRateHistory;
use App\Factory\ExchangeRateHistoryFactory;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

class ExchangeRateHistoryFactoryTest extends TestCase
{
    private ExchangeRateHistoryFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new ExchangeRateHistoryFactory();
    }

    public function testCreateSuccess(): void
    {
        $pair = $this->createMock(CurrencyPair::class);
        $rate = '1.23';
        $datetime = new DateTimeImmutable();

        $entity = $this->factory->create($pair, $rate, $datetime);

        $this->validate($entity, $pair, $rate, $datetime);
    }

    public function testCreateFromRecordSuccess(): void
    {
        $exchangeRate = $this->createMock(ExchangeRate::class);
        $pair = $this->createMock(CurrencyPair::class);
        $rate = '1.23';
        $datetime = new DateTimeImmutable('2024-01-01 12:00:00');

        $exchangeRate
            ->method('getCurrencyPair')
            ->willReturn($pair);

        $exchangeRate
            ->method('getRate')
            ->willReturn($rate);

        $exchangeRate
            ->method('getUpdatedAt')
            ->willReturn($datetime);

        $entity = $this->factory->createFromRecord($exchangeRate);

        $this->validate($entity, $pair, $rate, $datetime);
    }

    private function validate(
        ExchangeRate|ExchangeRateHistory $entity,
        CurrencyPair                     $pair,
        string                           $rate,
        DateTimeInterface                $datetime
    ): void
    {
        $this->assertInstanceOf(CurrencyPair::class, $entity->getCurrencyPair());
        $this->assertSame($pair, $entity->getCurrencyPair());
        $this->assertSame($rate, $entity->getRate());
        $this->assertInstanceOf(DateTimeInterface::class, $entity->getCreatedAt());
        $this->assertSame($datetime, $entity->getCreatedAt());
    }

}
