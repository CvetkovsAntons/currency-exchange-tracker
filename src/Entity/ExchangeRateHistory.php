<?php

namespace App\Entity;

use App\Repository\ExchangeRateHistoryRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExchangeRateHistoryRepository::class)]
#[ORM\Table(name: 'exchange_rate_history')]
#[ORM\Index(name: 'idx_exchange_rate_history_currency_pair', columns: ['currency_pair_id'])]
class ExchangeRateHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: CurrencyPair::class, cascade: ['persist', 'remove'], inversedBy: 'exchangeRateHistory')]
    #[ORM\JoinColumn(name: 'currency_pair_id', referencedColumnName: 'id', nullable: false)]
    private CurrencyPair $currencyPair;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 10)]
    private string $rate;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private DateTimeInterface $createdAt;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCurrencyPair(): CurrencyPair
    {
        return $this->currencyPair;
    }

    public function setCurrencyPair(CurrencyPair $currencyPair): static
    {
        $this->currencyPair = $currencyPair;
        return $this;
    }

    public function getRate(): string
    {
        return $this->rate;
    }

    public function setRate(string $rate): static
    {
        $this->rate = $rate;
        return $this;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

}
