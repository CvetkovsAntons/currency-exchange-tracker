<?php

namespace App\Entity;

use App\Repository\CurrencyPairRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CurrencyPairRepository::class)]
#[ORM\Table(name: 'currency_pair')]
#[ORM\UniqueConstraint(
    name: 'idx_currency_pair_from_currency_to_currency',
    columns: ['from_currency_id', 'to_currency_id']
)]
#[ORM\Index(
    name: 'idx_currency_pair_from_currency',
    columns: ['from_currency_id']
)]
class CurrencyPair
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Currency::class, inversedBy: 'fromPairs')]
    #[ORM\JoinColumn(name: 'from_currency_id', referencedColumnName: 'id', nullable: false)]
    private Currency $fromCurrency;

    #[ORM\ManyToOne(targetEntity: Currency::class, inversedBy: 'toPairs')]
    #[ORM\JoinColumn(name: 'to_currency_id', referencedColumnName: 'id', nullable: false)]
    private Currency $toCurrency;

    #[ORM\OneToOne(targetEntity: ExchangeRate::class, mappedBy: 'currencyPair', cascade: ['persist'])]
    private ?ExchangeRate $exchangeRate = null;

    #[ORM\OneToMany(targetEntity: ExchangeRateHistory::class, mappedBy: 'currencyPair', cascade: ['persist'])]
    private ?Collection $exchangeRateHistory = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Assert\NotBlank]
    private bool $isTracked = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromCurrency(): Currency
    {
        return $this->fromCurrency;
    }

    public function setFromCurrency(Currency $currency): static
    {
        $this->fromCurrency = $currency;
        return $this;
    }

    public function getToCurrency(): Currency
    {
        return $this->toCurrency;
    }

    public function setToCurrency(Currency $currency): static
    {
        $this->toCurrency = $currency;
        return $this;
    }

    public function getExchangeRate(): ?ExchangeRate
    {
        return $this->exchangeRate;
    }

    public function setExchangeRate(ExchangeRate $exchangeRate): static
    {
        if ($exchangeRate->getCurrencyPair() !== $this) {
            $exchangeRate->setCurrencyPair($this);
        }

        $this->exchangeRate = $exchangeRate;

        return $this;
    }

    public function getExchangeRateHistory(): ?Collection
    {
        return $this->exchangeRateHistory;
    }

    public function setExchangeRateHistory(?Collection $exchangeRateHistory): static
    {
        $this->exchangeRateHistory = $exchangeRateHistory;
        return $this;
    }

    public function addExchangeRateHistory(ExchangeRateHistory $exchangeRateHistory): static
    {
        if (!$this->exchangeRateHistory->contains($exchangeRateHistory)) {
            $this->exchangeRateHistory->add($exchangeRateHistory);
        }
        return $this;
    }

    public function removeExchangeRateHistory(ExchangeRateHistory $exchangeRateHistory): static
    {
        if ($this->exchangeRateHistory->contains($exchangeRateHistory)) {
            $this->exchangeRateHistory->removeElement($exchangeRateHistory);
        }
        return $this;
    }

    public function getIsTracked(): bool
    {
        return $this->isTracked;
    }

    public function setIsTracked(bool $isTracked): static
    {
        $this->isTracked = $isTracked;
        return $this;
    }

}
