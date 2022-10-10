<?php

namespace App\Extension\QueryFactory;

use Aura\SqlQuery\Common\DeleteInterface;
use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\UpdateInterface;

interface QueryFactoryExtensionInterface
{
    public function buildFromParams(string $statement, array $params): DeleteInterface|InsertInterface|UpdateInterface|SelectInterface;
    public function buildSoftDeleteFromParams($params): UpdateInterface;
    public function buildUpdateFromParams($params): UpdateInterface;
    public function buildSelectFromParams($params): SelectInterface;
    public function buildInsertFromParams(array $params): InsertInterface;
    public function joinsFromParams(SelectInterface $queryObject, array $joins): SelectInterface;
    public function whereFromParams(
        SelectInterface|UpdateInterface|DeleteInterface $queryObject,
        array $wheres
    ): SelectInterface|UpdateInterface|DeleteInterface;
    public function buildDeleteFromParams(array $params): DeleteInterface;
}
