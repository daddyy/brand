<?php

declare(strict_types=1);

namespace App\DTO;

use App\Helper\ArrayHelper;
use App\Helper\Helper;
use App\Manager\MysqlManager;
use ArrayAccess;
use DateTime;
use Exception;

abstract class DTO implements IDTO
{
    const CONTROL_SUFFIX = 'control';
    public DateTime $modify_date;
    public DateTime $create_date;

    public function __construct(array|ArrayAccess $row)
    {
        $row = $this->prepareValues($row);
        $this->fill($row);
        $this->afterFill($row);
    }

    public function validate(): bool
    {
        return true;
    }

    public function prepareValues(array|ArrayAccess $row): array
    {
        return $row;
    }

    public function afterFill(array|ArrayAccess $row): self
    {
        return $this;
    }

    public function fill(array|ArrayAccess $row, ?DomainDTO $domain = null): self
    {
        foreach ($row as $property => $value) {
            $setter = Helper::setter($property, $this::class);
            $subClass = self::getClosestClassNameByProperty($property, $this::class);
            if ($subClass) {
                $emptyValues = empty(array_filter($value));
                if ($emptyValues) {
                    $value = $this->{'create' . Helper::prepareName($property, '', ['ucfirst'])}();
                } elseif (isset($this->{$property})) {
                    $value = $this->{$property}->fill($value);
                } else {
                    $value = new $subClass($value, $domain);
                }
                if ($domain) {
                    if (property_exists($value, 'domain_id')) {
                        $value->domain_id = $domain->getId();
                    }
                    if (property_exists($value, 'lang_id')) {
                        $value->lang_id = $domain->getLang()->getId();
                    }
                }
            }
            if (is_null($value)) {
                continue;
            }
            if ($setter) {
                $this->{$setter}($value);
            } elseif (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
        return $this;
    }

    public static function getClosestClassNameByProperty(string $property, string $originClass): ?string
    {
        return MysqlManager::getClosestClassNameByProperty($property, $originClass);
    }

    private function getDateTimePropValue(string $date): DateTime
    {
        return new DateTime($date);
    }

    public function setModifyDate($modify_date): void
    {
        $this->modify_date = $this->getDateTimePropValue($modify_date);
    }

    public function setCreateDate($create_date): void
    {
        $this->create_date = $this->getDateTimePropValue($create_date);
    }

    public static function getEntityType(): string
    {
        $class = explode('\\', static::class);
        return  strtolower(Helper::prepareName(rtrim(end($class), 'DTO')));
    }

    public function getEntityControl(): string
    {
        return Helper::prepareName(ucfirst(self::getEntityType()) . '_' . self::CONTROL_SUFFIX);
    }

    public function getId(): ?int
    {
        $propertyId = $this->getTableMainIdentifier();
        return isset($this->{$propertyId}) ? $this->{$propertyId} : null;
    }

    public static function getTableName(): ?string
    {
        if (static::getTableMainIdentifier()) {
            return static::getEntityType();
        }
        return null;
    }

    public static function getTableMainIdentifier(): ?string
    {
        $property = static::getEntityType() . '_id';
        if (property_exists(static::class, $property)) {
            return $property;
        }
        return null;
    }

    public static function getTableUniqueIdentifiers(): array
    {
        return [static::getTableMainIdentifier()];
    }
}
