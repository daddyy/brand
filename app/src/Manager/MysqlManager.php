<?php

declare(strict_types=1);

namespace App\Manager;

use App\DTO\DTO;
use App\DTO\IEntityTypeDTO;
use \PDO as PDO;
use App\Helper\Helper;
use App\Helper\StringHelper;
use App\Manager\IManager;
use Exception;
use PDOException;
use PDOStatement;
use ReflectionClass;
use ReflectionProperty;

/**
 * simple storage manager and simple query factory
 */
class MysqlManager implements IManager
{
    const QUERY_SUCCESS = '00000';
    const FETCH_KEY_PAIR = 'pair';
    const FETCH_KEY_PAIRS = 'pairs';
    const FETCH_COLUMN = 'column';
    const FETCH_COLUMNS = 'columns';
    const FETCH_OBJ = 'object';
    const FETCH_ASSOC = 'row';
    const FETCH_OBJS = 'objects';
    const FETCH_ASSOCS = 'rows';
    private ?PDOStatement $lastSth;
    private PDO $pdo;
    public static array $reflections = [];

    public function __construct(PDO $pdo)
    {
        $this->setPdo($pdo);
    }

    public static function connect(array $params): self
    {
        $pdo = new PDO($params['dsn'], $params['user'], $params['pass'], $params['options'] ?? null);
        return new self($pdo);
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * @todo use fluent (cycle/database, aura/sql, illuminate/database, dibi/dibi etc), for those base queries
     */
    public static function prepareQuery(array $array, string $statement = 'SELECT'): string
    {
        $aSql = [];
        $cols = empty($array['cols']) ? null : $array['cols'];
        $table = empty($array['table']) ? null : $array['table'];
        $where = self::createConditions($array['where'] ?? []);
        $order = empty($array['order']) ? null : ('ORDER BY ' . implode(', ', $array['order']));
        $group = empty($array['group']) ? null : ('GROUP BY ' . implode(', ', $array['group']));
        $limit = self::createPageLimits($array['limit'] ?? 0, $array['page'] ?? null);
        $type = empty($array['type']) ? null : $array['type'];
        $joins = empty($array['join']) ? [] : $array['join'];
        foreach ($joins as $key => $join) {
            $join['type'] = ($join['type'] ? $join['type'] : '') . ' JOIN';
            $joins[$key] = self::prepareQuery($join, 'JOIN');
        }
        $joins = implode(" \n", $joins);
        switch (strtoupper($statement)) {
            case 'JOIN':
                $aSql = [
                    $type,
                    $table,
                    'ON',
                    $where,
                ];
                break;
            case 'DELETE':
                $aSql = [
                    'DELETE',
                    'FROM',
                    $table,
                    $joins,
                    $where ? 'WHERE' : '',
                    $where,
                    $group,
                    $order,
                    $limit,
                ];
                break;
            case 'SELECT':
                $aSql = [
                    'SELECT',
                    implode(',', $cols),
                    'FROM',
                    $table,
                    $joins,
                    $where ? 'WHERE' : '',
                    $where,
                    $group,
                    $order,
                    $limit,
                ];
                break;
            case 'UPDATE':
                $aSql = [
                    'UPDATE',
                    $table,
                    $joins,
                    'SET',
                    $cols = implode(', ', array_map(
                        function ($v, $k) {
                            return sprintf("%s=" . (is_int($v) ? '%s' : "'%s'"), $k, $v);
                        },
                        $cols,
                        array_keys($cols)
                    )),
                    $where ? 'WHERE' : '',
                    $where,
                    $group,
                    $order,
                    $limit,
                ];
                break;
            case 'INSERT':
                $aSql = [
                    'INSERT INTO',
                    $table,
                    '(' . implode(',', array_keys($cols)) . ')',
                    'values',
                    '(' . implode(',', array_map(function ($v) {
                        return (is_int($v) ? $v : "'" . $v . "'");
                    }, $cols)) . ')'
                ];
                break;
            default:
                throw new Exception('Statement type is not defined!');
        }
        $aSql = array_filter($aSql);
        $result = implode(" \n", $aSql);
        return $result;
    }

    public static function createPageLimits(?int $limit = null, ?int $page = null): ?string
    {
        if (is_null($page) && $limit) {
            return 'LIMIT ' . $limit;
        }
        if ($limit && $page) {
            return 'LIMIT ' . ($limit * ($page - 1)) . ', ' . $limit;
        }
        return '';
    }

    public static function createConditions(array $conditions, string $glue = 'AND'): ?string
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
        return join(' ' . $glue . ' ', $where);
    }

    /**
     * @todo single insert has to return last_inserted_id
     * @todo make it protected, instead this metod has to be new metod save, delete, read, readAll
     */
    public function query(string $sql, ?string $fetchType = null): mixed
    {
        $sth = $result = false;
        $pdo = $this->getPdo();
        if ($pdo == false) {
            throw new Exception("There is no PDO connection");
        } elseif (is_string($sql) && !empty($sql)) {
            $statement = $sql;
        } else {
            throw new Exception("Query has to be a string");
        }

        try {
            $sth = $pdo->prepare($statement);
            $sth->execute();
            if ($sth->errorCode() != self::QUERY_SUCCESS) {
                throw new Exception(print_r($sth->errorInfo(), true));
            }
            $result = $sth->rowCount();
        } catch (PDOException $e) {
            $result = false;
            $fetch = false;
            throw new PDOException($e->getMessage());
        }

        if ($fetchType) {
            $result = false;
            $fetch = is_bool($fetchType) && $fetchType ? null : $fetchType;
            switch ($fetch) {
                case PDO::FETCH_KEY_PAIR:
                case self::FETCH_KEY_PAIR:
                    $type = PDO::FETCH_KEY_PAIR;
                    $result = $sth->fetch($type);
                    break;
                case PDO::FETCH_KEY_PAIR:
                case self::FETCH_KEY_PAIRS:
                    $type = PDO::FETCH_KEY_PAIR;
                    $result = $sth->fetchAll($type);
                    break;
                case PDO::FETCH_COLUMN:
                case self::FETCH_COLUMN:
                    $type = PDO::FETCH_COLUMN;
                    $result = $sth->fetch($type);
                    break;
                case PDO::FETCH_COLUMN:
                case self::FETCH_COLUMNS:
                    $type = PDO::FETCH_COLUMN;
                    $result = $sth->fetchAll($type);
                    break;
                case PDO::FETCH_OBJ:
                case self::FETCH_OBJS:
                    $type = PDO::FETCH_OBJ;
                    $result = $sth->fetchAll($type);
                    break;
                case PDO::FETCH_OBJ:
                case self::FETCH_OBJ:
                    $type = PDO::FETCH_OBJ;
                    $result = $sth->fetch($type);
                    break;
                case PDO::FETCH_ASSOC:
                case self::FETCH_ASSOCS:
                    $type = PDO::FETCH_ASSOC;
                    $result = $sth->fetchAll($type);
                    break;
                case PDO::FETCH_ASSOC:
                case self::FETCH_ASSOC:
                    $type = PDO::FETCH_ASSOC;
                    $result = $sth->fetch($type);
                    break;
                default:
                    break;
            }
        }
        $this->lastSth = $sth;
        return $result;
    }

    public function getLasthSth(): ?PDOStatement
    {
        return $this->lastSth;
    }

    public function setPdo(PDO $pdo): self
    {
        $this->pdo = $pdo;

        return $this;
    }
    /**
     * @todo set the last_insert_id  to entity
     */
    public function upsert(DTO $entityDTO): array
    {
        $className = $entityDTO::class;
        $aSql = [
            'table' => $entityDTO::getTableName(),
            'cols' => array_filter(get_object_vars($entityDTO)),
        ];
        unset($aSql['cols']['create_date']);
        unset($aSql['cols']['modify_date']);
        if ($entityDTO->getId()) {
            $aSql['where'] = [
                $entityDTO->getTableMainIdentifier() => $entityDTO->getId(),
            ];
        }
        $sql = self::prepareQuery($aSql, $entityDTO->getId() ? 'update' : 'insert');
        $result[$entityDTO::getTableName()] = $this->query($sql);

        $reflection = self::getReflection($className);
        $protected = $reflection->getProperties(ReflectionProperty::IS_PROTECTED);
        foreach ($protected as $property) {
            $getter = Helper::getter($property->name, $className);
            $subEntityDto = $entityDTO->{$getter}();
            if ($subEntityDto) {
                $subEntityDto->fill([
                    $entityDTO::getTableMainIdentifier() => $entityDTO->getId(),
                    'object_id' => $entityDTO->getId(),
                    'object_type' => $entityDTO::getEntityType(),
                ]);
                $result = array_merge($result, $this->upsert($subEntityDto));
            }
        }
        return $result;
    }

    /**
     * @todo create user account with rule delete and then we can delete the rows, until that just sign as soft delete
     * @return boolean
     */
    public function delete(DTO $entityDTO): array
    {
        $sql = self::prepareQuery(
            [
                'table' => $entityDTO::getTableName(),
                'cols' => ['deleted' => 1],
                'where' => [[$entityDTO::getTableMainIdentifier() . ' = %s', [$entityDTO->getId()]]]
            ],
            'update'
        );
        return [$entityDTO::getTableName() => $this->query($sql)];
    }

    public static function getReflection(string $className): ReflectionClass
    {
        if (!isset(self::$reflections[$className])) {
            self::$reflections[$className] = new ReflectionClass($className);
        }
        return self::$reflections[$className];
    }

    public static function prepareQueryFromDto(string $className, string $statement, array $params = []): string
    {
        $aSql = self::prepareSelectFromDto($className);
        $aSql = array_merge($aSql, $params);
        return self::prepareQuery($aSql, $statement);
    }

    /////////////////////////////// start ///////////////////////////////
    /**
     * @todo CREATE SQL DTOs BUILDER
     */

    public static function prepareSelectFromDto(string $className): ?array
    {
        if (!$className::getTableName()) {
            return null;
        }
        $aSql['table'] = $className::getTableName();
        $aSql['cols'] = [];
        $aSql['join'] = [];
        $reflection = self::getReflection($className);
        $public = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($public as $property) {
            $aSql['cols'][] = $className::getTableName()
                . '.'
                . $property->name
                . ' as _' . $className::getTableName()
                . '__' . $property->name;
        }
        $protected = $reflection->getProperties(ReflectionProperty::IS_PROTECTED);
        foreach ($protected as $property) {
            if ($property->name) {
                $subClassName = self::getClosestClassNameByProperty($property->name, $className);
                $temp = self::prepareSelectFromDto($subClassName);
                if ($temp) {
                    $aSql['cols'] = array_merge($aSql['cols'], $temp['cols']);
                    $join = [
                        'table' => $temp['table'],
                        'type' => 'left',
                    ];
                    $subReflection = self::getReflection($subClassName);
                    if ($subReflection->implementsInterface(IEntityTypeDTO::class)) {
                        $join['where'] = [
                            [$temp['table'] . '.object_type = "%s"', [$aSql['table']]],
                            $temp['table']
                                . '.object_id = '
                                . $aSql['table']
                                . '.'
                                . $className::getTableMainIdentifier()
                        ];
                    } else {
                        $join['where'] = [
                            $aSql['table']
                                . '.'
                                . $subClassName::getTableMainIdentifier()
                                . ' = '
                                .  $temp['table']
                                . '.' . $subClassName::getTableMainIdentifier()
                        ];
                    }
                    $aSql['join'][] = $join;
                }
            }
        }
        return $aSql;
    }

    public static function getClosestClassNameByProperty(
        string $protectedPropertyName,
        string $originClassName
    ): ?string {
        $tries = [
            rtrim($originClassName, 'DTO'),
            rtrim($originClassName, StringHelper::getLastWord($originClassName, '\\')),
            'App\\DTO',
        ];
        foreach ($tries as $try) {
            $tryClassName = $try . '\\' . Helper::prepareName($protectedPropertyName, '', ['ucfirst']) . 'DTO';
            if (class_exists($tryClassName)) {
                return $tryClassName;
            }
        }
        return null;
    }
}
