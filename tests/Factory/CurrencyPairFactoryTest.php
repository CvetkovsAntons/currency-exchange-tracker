<?php

namespace App\Tests\Factory;

use App\Entity\Currency;
use App\Factory\CurrencyPairFactory;
use PHPUnit\Framework\TestCase;

class CurrencyPairFactoryTest extends TestCase
{
    private CurrencyPairFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new CurrencyPairFactory();
    }

    public function testCreateSuccess(): void
    {
        $from = $this->createMock(Currency::class);
        $to = $this->createMock(Currency::class);

        $entity = $this->factory->create($from, $to);

        $this->assertInstanceOf(Currency::class, $entity->getFromCurrency());
        $this->assertInstanceOf(Currency::class, $entity->getToCurrency());
        $this->assertSame($from, $entity->getFromCurrency());
        $this->assertSame($to, $entity->getToCurrency());
    }

}
