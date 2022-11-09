<?php

declare(strict_types=1);

namespace App\DTO;

interface IEntityDTO extends IDTO
{
    public static function getTableName(): string;
    public static function getTableMainIdentifier(): string;
    public function getRoute(): RouteDTO;
    public function getId(): ?int;
}
