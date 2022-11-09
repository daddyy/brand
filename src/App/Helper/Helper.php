<?php

declare(strict_types=1);

namespace App\Helper;

class Helper
{
    public static function setter(string $name, string $className, bool $multiple = false): ?string
    {
        $tryNames = self::methodNamesToTry('set', $name, $multiple);

        foreach ($tryNames as $try) {
            if (method_exists($className, $try)) {
                return $try;
            }
        }

        return null;
    }

    public static function getter(string $name, string $className, bool $multiple = false): ?string
    {
        $tryNames = self::methodNamesToTry('get', $name, $multiple);

        foreach ($tryNames as $try) {
            if (method_exists($className, $try)) {
                return $try;
            }
        }

        return null;
    }

    private static function methodNamesToTry(string $suffix, string $name, bool $multiple = false): array
    {
        $tryName = implode('', array_map('ucfirst', explode('_', $name)));

        if ($multiple == false) {
            $tries[] = ucfirst($tryName);
            $tries[] = $tryName;
            $tries[] = $suffix . ucfirst($tryName);
            $tries[] = $suffix . $tryName;
            $tries[] = '_' . $suffix . ucfirst($tryName);
            $tries[] = '_' . $suffix . $tryName;
        } else {
            $tries[] = $suffix . substr(ucfirst($tryName), 0, -1);
            $tries[] = $suffix . substr(ucfirst($tryName), 0, -2);
            $tries[] = $suffix . ucfirst($tryName) . 's';
            $tries[] = $suffix . str_replace('ies', 'y', ucfirst($tryName));
            $tries[] = $suffix . str_replace('es', 'e', ucfirst($tryName));
        }

        return array_unique($tries);
    }

    public static function prepareName($name, $joiner = '', $callbacks = []): string
    {
        $temp   = [];

        if (strpos($name, '\\') !== false) {
            $aName = explode('\\', $name);
        } elseif (strpos($name, '.') !== false) {
            $aName = explode('.', $name);
        } elseif (strpos($name, '-') !== false) {
            $aName = explode('-', $name);
        } elseif (strpos($name, '_') !== false) {
            $aName = explode('_', $name);
        } else {
            $aName = [$name];
        }

        if ($aName) {
            foreach ($aName as $key => $value) {
                if ($key > 0) {
                    $temp[] = ucfirst($value);
                } else {
                    $temp[] = $value;
                }
            }

            if ($callbacks) {
                foreach ($callbacks as $key => $value) {
                    $temp = array_map($value, $temp);
                }
            }

            return join($joiner, $temp);
        }

        return $name;
    }
}
