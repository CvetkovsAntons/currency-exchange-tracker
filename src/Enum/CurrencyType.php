<?php

namespace App\Enum;

enum CurrencyType: string
{
    case FIAT = 'fiat';
    case CRYPTO = 'crypto';

    public function isFiat(): bool { return $this === self::FIAT; }

    public function isCrypto(): bool { return $this === self::CRYPTO; }

}
