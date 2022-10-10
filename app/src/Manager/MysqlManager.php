<?php

declare(strict_types=1);

namespace App\Manager;

use App\DTO\DTO;
use App\DTO\IEntityTypeDTO;
use App\Extension\QueryFactory\QueryFactoryExtension;
use App\Factory\SimpleQueryFactory;
use App\Helper\Helper;
use App\Helper\StringHelper;
use App\Manager\IManager;
use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\QueryInterface;
use Exception;
use \PDO as PDO;
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
    private ExtendedPdo $pdo;
    public static array $reflections = [];
    public static string $driver;
    public static QueryFactoryExtension $qf;

    public function __construct(ExtendedPdo $pdo)
    {
        $this->setPdo($pdo);
    }

    public function disconnect(): void
    {
        $this->pdo->disconnect();
    }
    public static function connect(array $params): self
    {
        $pdo = new ExtendedPdo($params['dsn'], $params['user'], $params['pass'], $params['options'] ?? null);
        self::$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        self::$qf = new QueryFactoryExtension(self::$driver);
        return new self($pdo);
    }

    public function getPdo(): ExtendedPdo
    {
        return $this->pdo;
    }

    /**
     * @todo use fluent (cycle/database, aura/sql, illuminate/database, dibi/dibi etc), for those base queries
     */
    public static function prepareQuery(array $params, string $statement = 'SELECT'): QueryInterface
    {
        return static::$qf->buildFromParams($statement, $params);
    }

    /**
     * @todo single insert has to return last_inserted_id
     * @todo make it protected, instead this metod has to be new metod save, delete, read, readAll
     */
    public function query(QueryInterface|string $sql, ?string $fetchType = null): mixed
    {
        $sth = $result = false;
        $bindValues = [];
        if ($this->getPdo() == false) {
            throw new Exception("There is no PDO connection");
        } elseif (is_string($sql) && !empty($sql)) {
            $statement = $sql;
        } elseif ($sql instanceof QueryInterface) {
            $statement = $sql->getStatement();
            $bindValues = $sql->getBindValues();
        } else {
            throw new Exception("Query has to be a string");
        }

        try {
            $sth = $this->getPdo()->prepare($statement);
            $sth->execute($bindValues);
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
                    $result = $sth->fetch(PDO::FETCH_KEY_PAIR);
                    break;
                case PDO::FETCH_KEY_PAIR:
                case self::FETCH_KEY_PAIRS:
                    $result = $sth->fetchAll(PDO::FETCH_KEY_PAIR);
                    break;
                case PDO::FETCH_COLUMN:
                case self::FETCH_COLUMN:
                    $result = $sth->fetch(PDO::FETCH_COLUMN);
                    break;
                case PDO::FETCH_COLUMN:
                case self::FETCH_COLUMNS:
                    $result = $sth->fetchAll(PDO::FETCH_COLUMN);
                    break;
                case PDO::FETCH_OBJ:
                case self::FETCH_OBJS:
                    $result = $sth->fetchAll(PDO::FETCH_OBJ);
                    break;
                case PDO::FETCH_OBJ:
                case self::FETCH_OBJ:
                    $result = $sth->fetch(PDO::FETCH_OBJ);
                    break;
                case PDO::FETCH_ASSOC:
                case self::FETCH_ASSOCS:
                    $result = $sth->fetchAll(PDO::FETCH_ASSOC);
                    break;
                case PDO::FETCH_ASSOC:
                case self::FETCH_ASSOC:
                    $result = $sth->fetch(PDO::FETCH_ASSOC);
                    break;
                default:
                    break;
            }
        }
        $this->lastSth = $sth;
        return $result;
    }

    public function getLastSth(): ?PDOStatement
    {
        return $this->lastSth;
    }

    public function setPdo(ExtendedPdo $pdo): self
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

    public static function prepareQueryFromDto(string $className, string $statement, array $params = []): QueryInterface
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
        $mainIdentifier = $className::getTableMainIdentifier();
        $tableName = $className::getTableName();
        if (!$mainIdentifier || !$tableName) {
            return null;
        }
        $aSql['table'] = $tableName;
        $aSql['cols'] = [];
        $aSql['joins'] = [];
        $reflection = self::getReflection($className);
        $public = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($public as $property) {
            $aSql['cols'][] = $tableName
                . '.'
                . $property->name
                . ' as _' . $tableName
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
                                . $mainIdentifier
                        ];
                    } else {
                        $subMainIdentifier = $subClassName::getTableMainIdentifier();
                        $join['where'] = [
                            $aSql['table']
                                . '.'
                                . $subMainIdentifier
                                . ' = '
                                .  $temp['table']
                                . '.' . $subMainIdentifier
                        ];
                    }
                    $aSql['joins'][] = $join;
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
