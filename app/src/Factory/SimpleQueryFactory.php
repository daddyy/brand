<?php

namespace App\Factory;

use Exception;

/**
 * @todo for each queryType create separeted builder {$driver}\Select extends CommonSelect etc.
 * @todo assign values has to be prepared as statement
 */
class SimpleQueryFactory
{
    private static $_instance;

    public function __construct(private string $driver)
    {
    }

    public static function getInstance(string $driver): self
    {
        if (!isset(static::$_instance[$driver])) {
            self::$_instance[$driver] = new self($driver);
        }
        return self::$_instance[$driver];
    }

    public static function createQuery(string $typeQuery, array $array, string $driver): string
    {
        $instance = self::getInstance($driver);
        $aQuery = $instance->buildQuery($typeQuery, $array);
        $sQuery = $instance->buildQueryString($typeQuery, $aQuery);
        return $sQuery;
    }

    public static function prepareQuery(string $typeQuery, array $array, string $driver): array
    {
        $sQuery = self::createQuery($typeQuery, $array, $driver);
        $result = [
            'queryString' => $sQuery,
        ];
        return $result;
    }

    /**
     * @todo create validation for each type
     */
    private function buildQuery(string $typeQuery, array $array): array
    {
        $array = $this->buildCommon($array);
        return match (strtolower($typeQuery)) {
            'delete' => $this->buildDelete($array),
            'select' => $this->buildSelect($array),
            'update' => $this->buildUpdate($array),
            'insert' => $this->buildInstert($array),
            default => throw new Exception('Statement ' . $typeQuery . ' type is not defined!')
        };
    }

    private function buildCommon($array): array
    {
        $common = [
            'joins' => $this->buildJoins($array['joins'] ?? []),
            'where' => self::buildConditions($array['where'] ?? []),
            'order' => $this->buildOrder($array['order'] ?? []),
            'group' => $this->buildGroup($array['group'] ?? []),
            'limit' => $this->buildPageLimits($array['limit'] ?? null, $array['page'] ?? null),
        ];
        return array_merge($array, $common);
    }

    private function buildJoins(array $joins): array
    {
        $result = [];
        foreach ($joins as $key => $join) {
            $join['type'] = ($join['type'] ? $join['type'] : '') . ' JOIN';
            $result[] = $this->buildJoin($join);
        }
        return $result;
    }

    private function buildJoin(array $join): string
    {
        return join("\n\t", [
            join(' ', [$join['type'], $join['table'], 'ON']),
            self::buildConditions($join['where']),
        ]);
    }

    private function buildDelete(array $array): array
    {
        return [
            'delete_from' => $this->buildFrom('delete', $array['table']),
            '_joins' => implode("\n", $array['joins'] ?? []),
            'where' => $array['where'] ?? '',
            'group_by' => $array['group'] ?? '',
            'order_by' => $array['order'] ?? '',
            'limit' => $array['limit'] ?? '',
        ];
    }

    private function buildUpdate(array $array): array
    {
        return [
            'update' => $this->buildFrom('update', $array['table']),
            '_joins' => implode("\n", $array['joins'] ?? []),
            'set' => $this->buildCols('update', $array['cols']),
            'where' => $array['where'] ?? '',
            'group_by' => $array['group'] ?? '',
            'order_by' => $array['order'] ?? '',
            'limit' => $array['limit'] ?? '',
        ];
    }

    private function buildInstert(array $array): array
    {
        return [
            'insert_into' => $this->buildFrom('select', $array['table']),
            '_values' => $this->buildCols('insert', $array['cols']),
        ];;
    }

    private function buildSelect(array $array): array
    {
        return [
            'select' => $this->buildCols('select', $array['cols'] ?? ['*']),
            'from' => $this->buildFrom('select', $array['table']),
            '_joins' => implode("\n", $array['joins'] ?? []),
            'where' => $array['where'] ?? '',
            'group_by' => $array['group'] ?? '',
            'order_by' => $array['order'] ?? '',
            'limit' => $array['limit'] ?? '',
        ];
    }

    private function buildFrom(string $typeQuery, string $table): string
    {
        return match ($typeQuery) {
            'select' => $table,
            'update' => $table,
            'insert' => $table,
            default => throw new Exception('TypeQuery is not defined!')
        };
    }

    private function buildCols(string $typeQuery, array $cols): string
    {
        return match ($typeQuery) {
            'select' => implode(",\n\t", $cols),
            'update' => implode(', ', array_map(
                function ($v, $k) {
                    return sprintf("%s=" . (is_int($v) ? '%s' : "'%s'"), $k, $v);
                },
                $cols,
                array_keys($cols)
            )),
            'insert' => '(' . implode(',', array_keys($cols)) . ')'
                . ' values '
                . ' (' . implode(',', array_map(function ($v) {
                    return (is_int($v) ? $v : "'" . $v . "'");
                }, $cols)) . ')',
            default => throw new Exception('TypeQuery is not defined!')
        };
    }

    public static function buildConditions(array $conditions, string $glue = 'AND'): string
    {
        $where = [];
        foreach ($conditions as $key => $mixed) {
            if (is_string($key) && (is_string($mixed) || is_numeric($mixed))) {
                $where[] = $key . ' = ' . (is_int($mixed) ? ($mixed) : ('"' . $mixed . '"'));
            } elseif (is_bool($mixed)) {
                $where[] = $mixed ? "1 = 1" : "1 = 0";
            } elseif (is_string($mixed)) {
                $where[] = $mixed;
            } elseif (!is_null($mixed)) {
                $aReverse = array_reverse($mixed);
                $where[] = vsprintf(reset($mixed), array_shift($aReverse));
            }
        }
        return join("\n\t" . trim($glue) . " ", $where);
    }

    private function buildPageLimits(?int $limit = null, ?int $page = null): ?string
    {
        if (is_null($page) && $limit) {
            return $limit;
        }
        if ($limit && $page) {
            return ($limit * ($page - 1)) . ', ' . $limit;
        }
        return null;
    }

    private function buildOrder(array $orders): string
    {
        return implode(', ', $orders);
    }

    private function buildGroup(array $groups): string
    {
        return implode(', ', $groups);
    }

    /**
     * @todo validate the input against typeQuery
     *
     * @param string $typeQuery
     * @param array $array
     * @return string
     */
    public function buildQueryString(string $typeQuery, array $array): string
    {
        $strings = [];
        foreach ($array as $prefix => $value) {
            if (empty($value)) {
                continue;
            }
            if (substr($prefix, 0, 1) == '_') {
                $prefix = '';
            }
            $strings[] = str_replace('_', ' ', strtoupper($prefix)) . ' ' . $value;
        }
        return join("\n", $strings);
    }
}
