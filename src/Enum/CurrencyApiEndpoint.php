<?php

namespace App\Enum;

enum CurrencyApiEndpoint: string
{
    case STATUS = 'status';
    case CURRENCIES = 'currencies';
    case LATEST_EXCHANGE_RATE = 'latest';

}
