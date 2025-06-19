<?php

namespace App\Entity;

use App\Enum\CurrencyType;
use App\Repository\CurrencyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CurrencyRepository::class)]
#[ORM\Table(name: 'currency', uniqueConstraints: [
    new UniqueConstraint('code'),
    new UniqueConstraint('name'),
    new UniqueConstraint('namePlural'),
])]
class Currency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private readonly int $id;

    #[ORM\Column(length: 3)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 3)]
    private string $code;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $name;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $namePlural;

    #[ORM\Column(length: 8)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 8)]
    private string $symbol;

    #[ORM\Column(length: 8)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 8)]
    private string $symbolNative;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $decimalDigits;

    // DECIMAL(10, 6) is used for non-conventional currency and cryptocurrency
    // rounding use cases (e.g. BTC, ETH). Ensures future scalability.
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6)]
    #[Assert\Type('numeric')]
    #[Assert\GreaterThanOrEqual(0)]
    private string $rounding;

    #[ORM\Column(enumType: CurrencyType::class)]
    #[Assert\NotBlank]
    private CurrencyType $type;

    #[ORM\OneToMany(targetEntity: CurrencyPair::class, mappedBy: 'fromCurrency')]
    private Collection $fromPairs;

    #[ORM\OneToMany(targetEntity: CurrencyPair::class, mappedBy: 'toCurrency')]
    private Collection $toPairs;

    public function __construct(
        string       $code,
        string       $name,
        string       $namePlural,
        string       $symbol,
        string       $symbolNative,
        int          $decimalDigits,
        string       $rounding,
        CurrencyType $type,
    )
    {
        $this->code = $code;
        $this->name = $name;
        $this->namePlural = $namePlural;
        $this->symbol = $symbol;
        $this->symbolNative = $symbolNative;
        $this->decimalDigits = $decimalDigits;
        $this->rounding = $rounding;
        $this->type = $type;
        $this->fromPairs = new ArrayCollection();
        $this->toPairs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = strtoupper($code);
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getNamePlural(): string
    {
        return $this->namePlural;
    }

    public function setNamePlural(string $namePlural): static
    {
        $this->namePlural = $namePlural;
        return $this;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): static
    {
        $this->symbol = $symbol;
        return $this;
    }

    public function getSymbolNative(): string
    {
        return $this->symbolNative;
    }

    public function setSymbolNative(string $symbolNative): static
    {
        $this->symbolNative = $symbolNative;
        return $this;
    }

    public function getDecimalDigits(): int
    {
        return $this->decimalDigits;
    }

    public function setDecimalDigits(int $decimalDigits): static
    {
        $this->decimalDigits = $decimalDigits;
        return $this;
    }

    public function getRounding(): string
    {
        return $this->rounding;
    }

    public function setRounding(string $rounding): static
    {
        $this->rounding = $rounding;
        return $this;
    }

    public function getType(): CurrencyType
    {
        return $this->type;
    }

    public function setType(CurrencyType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getFromPairs(): Collection
    {
        return $this->fromPairs;
    }

    public function addFromPair(CurrencyPair $fromPair): static
    {
        if (!$this->fromPairs->contains($fromPair)) {
            $this->fromPairs->add($fromPair);
        }
        return $this;
    }

    public function removeFromPair(CurrencyPair $fromPair): static
    {
        if ($this->fromPairs->contains($fromPair)) {
            $this->fromPairs->removeElement($fromPair);
        }
        return $this;
    }

    public function getToPairs(): Collection
    {
        return $this->toPairs;
    }

    public function addToPair(CurrencyPair $toPair): static
    {
        if (!$this->toPairs->contains($toPair)) {
            $this->toPairs->add($toPair);
        }
        return $this;
    }

    public function removeToPair(CurrencyPair $toPair): static
    {
        if ($this->toPairs->contains($toPair)) {
            $this->toPairs->removeElement($toPair);
        }
        return $this;
    }

}
