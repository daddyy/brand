<?php

declare(strict_types=1);

namespace App\Helper;

class StringHelper
{
    public static function isJson(string $string): bool
    {
        $result = false;
        json_decode($string);
        $result = (json_last_error() == JSON_ERROR_NONE);
        return $result;
    }

    public static function getLastWord(string $string, string $glue = ' '): string
    {
        return trim(strrchr($string, $glue));
    }
}
