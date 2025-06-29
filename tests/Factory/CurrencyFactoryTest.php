<?php

namespace App\Tests\Factory;

use App\Enum\CurrencyType;
use App\Factory\CurrencyFactory;
use App\Tests\Utils\Factory\CurrencyTestFactory;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class CurrencyFactoryTest extends TestCase
{
    private CurrencyFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new CurrencyFactory();
    }

    public function testCreateSuccess(): void
    {
        $code = 'USD';
        $dto = CurrencyTestFactory::createDto($code);

        $entity = $this->factory->create($dto);

        $this->assertSame('USD', $entity->getCode());
        $this->assertSame('US Dollar', $entity->getName());
        $this->assertSame('US Dollars', $entity->getNamePlural());
        $this->assertSame('$', $entity->getSymbol());
        $this->assertSame('$', $entity->getSymbolNative());
        $this->assertSame(2, $entity->getDecimalDigits());
        $this->assertSame('0', $entity->getRounding());
        $this->assertSame(CurrencyType::FIAT, $entity->getType());
        $this->assertInstanceOf(ArrayCollection::class, $entity->getFromPairs());
        $this->assertInstanceOf(ArrayCollection::class, $entity->getToPairs());
        $this->assertCount(0, $entity->getFromPairs());
        $this->assertCount(0, $entity->getToPairs());
    }

}
