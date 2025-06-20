<?php

namespace App\Enum;

use App\Trait\EnumHelper;

enum YesNo: string
{
    use EnumHelper;

    case YES = 'Y';
    case NO = 'N';

    public function isYes(): bool { return $this === self::YES; }

    public function isNo(): bool { return $this === self::NO; }

    public static function classifier(): array
    {
        return [
            self::NO->value => 'No',
            self::YES->value => 'Yes',
        ];
    }

}
