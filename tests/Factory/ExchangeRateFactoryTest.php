<?php

namespace App\Tests\Factory;

use App\Entity\CurrencyPair;
use App\Factory\ExchangeRateFactory;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

class ExchangeRateFactoryTest extends TestCase
{
    private ExchangeRateFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new ExchangeRateFactory();
    }

    public function testCreateSuccessWithoutDatetime(): void
    {
        $pair = $this->createMock(CurrencyPair::class);
        $rate = '1.23';

        $entity = $this->factory->create($pair, $rate);

        $this->assertInstanceOf(CurrencyPair::class, $entity->getCurrencyPair());
        $this->assertSame($pair, $entity->getCurrencyPair());
        $this->assertSame($rate, $entity->getRate());
        $this->assertInstanceOf(DateTimeInterface::class, $entity->getCreatedAt());
        $this->assertInstanceOf(DateTimeInterface::class, $entity->getUpdatedAt());
        $this->assertSame($entity->getCreatedAt(), $entity->getUpdatedAt());
    }

    public function testCreateSuccessWithDatetime(): void
    {
        $pair = $this->createMock(CurrencyPair::class);
        $rate = '1.23';
        $datetime = new DateTimeImmutable();

        $entity = $this->factory->create($pair, $rate, $datetime);

        $this->assertInstanceOf(CurrencyPair::class, $entity->getCurrencyPair());
        $this->assertSame($pair, $entity->getCurrencyPair());
        $this->assertSame($rate, $entity->getRate());
        $this->assertInstanceOf(DateTimeInterface::class, $entity->getCreatedAt());
        $this->assertInstanceOf(DateTimeInterface::class, $entity->getUpdatedAt());
        $this->assertSame($datetime, $entity->getCreatedAt());
        $this->assertSame($datetime, $entity->getUpdatedAt());
    }

}
