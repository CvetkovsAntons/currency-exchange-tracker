<?php

namespace App\Dto;

class ExchangeRateRequest
{
    public string $from;
    public string $to;
    public ?string $datetime = null;

}
