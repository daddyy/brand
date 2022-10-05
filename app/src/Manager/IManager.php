<?php

declare(strict_types=1);

namespace App\Manager;

use App\DTO\DTO;

interface IManager
{
    public function query(string $sql, ?string $fetchType = null): mixed;
    public function delete(DTO $entityDTO): array;
    public function upsert(DTO $entityDTO): array;
    public static function prepareQuery(array $array, string $statement = 'SELECT'): string;
    public static function prepareQueryFromDto(string $className, string $statement, array $params = []): string;
}
