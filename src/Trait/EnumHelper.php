<?php

namespace App\Trait;

/**
 * Trait has been created to implement common logic for every enum un the project.
 *
 * Code taken from: https://stackoverflow.com/a/71680007
 */
trait EnumHelper
{
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function array(): array
    {
        return array_combine(self::names(), self::values());
    }

}
