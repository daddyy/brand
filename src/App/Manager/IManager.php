<?php

declare(strict_types=1);

namespace App\Manager;

use App\DTO\DTO;
use App\DTO\IEntityDTO;
use Aura\SqlQuery\QueryInterface;

interface IManager
{
    public function disconnect(): void;
    public function query(QueryInterface|string $sql, ?string $fetchType = null): mixed;
    public function delete(DTO $entityDTO): array;
    public function softDelete(IEntityDTO $entityDTO): array;
    public function upsert(DTO $entityDTO): array;
    public static function prepareQuery(array $array, string $statement = 'SELECT'): QueryInterface;
    public static function prepareQueryFromDto(
        string $className,
        string $statement,
        array $params = []
    ): QueryInterface;
}
