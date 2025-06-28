<?php

namespace App\Enum;

enum CurrencyType: string
{
    case FIAT = 'fiat';
    case CRYPTO = 'crypto';

}
