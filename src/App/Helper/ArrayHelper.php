<?php

declare(strict_types=1);

namespace App\Helper;

class ArrayHelper
{
    public static function searchInArrayByIndexes(array $value, array $indexes, $return = null)
    {
        foreach ($indexes as $index) {
            if (isset($value[$index])) {
                $value = $value[$index];
            } else {
                $value = null;
                break;
            }
        }
        if ($return && is_null($value)) {
            $value = $return;
        }

        return $value;
    }

    public static function isMultidimensional(array $array): bool
    {
        $result = array_filter($array, 'is_array');
        $result = count($result) > 0;
        return $result;
    }
}
