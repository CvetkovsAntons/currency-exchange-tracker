<?php

namespace App\Entity;

use App\Enum\CurrencyType;
use App\Repository\CurrencyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CurrencyRepository::class)]
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
    private string $name_plural;

    #[ORM\Column(length: 8)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 8)]
    private string $symbol;

    #[ORM\Column(length: 8)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 8)]
    private string $symbol_native;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $decimal_digits;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    #[Assert\Type('numeric')]
    #[Assert\GreaterThanOrEqual(0)]
    private string $rounding;

    #[ORM\Column(enumType: CurrencyType::class)]
    #[Assert\NotBlank]
    private CurrencyType $type;

    public function __construct(
        string $code,
        string $name,
        string $name_plural,
        string $symbol,
        string $symbol_native,
        int $decimal_digits,
        string $rounding,
        CurrencyType $type,
    ) {
        $this->code = $code;
        $this->name = $name;
        $this->name_plural = $name_plural;
        $this->symbol = $symbol;
        $this->symbol_native = $symbol_native;
        $this->decimal_digits = $decimal_digits;
        $this->rounding = $rounding;
        $this->type = $type;
    }

    public function getId(): int
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
        return $this->name_plural;
    }

    public function setNamePlural(string $name_plural): static
    {
        $this->name_plural = $name_plural;

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
        return $this->symbol_native;
    }

    public function setSymbolNative(string $symbol_native): static
    {
        $this->symbol_native = $symbol_native;

        return $this;
    }

    public function getDecimalDigits(): int
    {
        return $this->decimal_digits;
    }

    public function setDecimalDigits(int $decimal_digits): static
    {
        $this->decimal_digits = $decimal_digits;

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
}
