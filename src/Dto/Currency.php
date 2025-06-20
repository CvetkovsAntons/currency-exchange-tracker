<?php

namespace App\Dto;

class Currency
{
    public string $code;
    public string $name;
    public string $name_plural;
    public string $symbol;
    public string $symbol_native;
    public int $decimal_digits;
    public string|int $rounding;
    public string $type;

}
