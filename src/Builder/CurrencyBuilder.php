<?php

namespace App\Builder;

use App\Contract\BuilderInterface;
use App\Entity\Currency;
use App\Enum\CurrencyType;
use Doctrine\Common\Collections\ArrayCollection;

class CurrencyBuilder implements BuilderInterface
{
    private Currency $currency;

    public function __construct()
    {
        $this->currency = new Currency();

        $this->currency->setDecimalDigits(2);
        $this->currency->setRounding(0);
        $this->currency->setType(CurrencyType::FIAT);
        $this->currency->setFromPairs(new ArrayCollection());
        $this->currency->setToPairs(new ArrayCollection());
    }

    public function withCode(string $code): self
    {
        $this->currency->setCode($code);
        return $this;
    }

    public function withName(string $name): self
    {
        $this->currency->setName($name);
        return $this;
    }

    public function withNamePlural(string $namePlural): self
    {
        $this->currency->setNamePlural($namePlural);
        return $this;
    }

    public function withSymbol(string $symbol): self
    {
        $this->currency->setSymbol($symbol);
        return $this;
    }

    public function withSymbolNative(string $symbolNative): self
    {
        $this->currency->setSymbolNative($symbolNative);
        return $this;
    }

    public function withDecimalDigits(int $decimalDigits): self
    {
        $this->currency->setDecimalDigits($decimalDigits);
        return $this;
    }

    public function withRounding(string $rounding): self
    {
        $this->currency->setRounding($rounding);
        return $this;
    }

    public function withType(CurrencyType $type): self
    {
        $this->currency->setType($type);
        return $this;
    }

    public function withFromPairs(ArrayCollection $fromPairs): self
    {
        $this->currency->setFromPairs($fromPairs);
        return $this;
    }

    public function withToPairs(ArrayCollection $toPairs): self
    {
        $this->currency->setFromPairs($toPairs);
        return $this;
    }

    public function build(): Currency
    {
        return $this->currency;
    }

    public function reset(): self
    {
        $this->currency = new Currency();
        return $this;
    }

}
