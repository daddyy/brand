<?php

namespace App\Extension\QueryFactory;

use App\Factory\SimpleQueryFactory;
use Aura\SqlQuery\Common\DeleteInterface;
use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\Common\LimitInterface;
use Aura\SqlQuery\Common\OrderByInterface;
use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\UpdateInterface;
use Aura\SqlQuery\QueryFactory;
use Aura\SqlQuery\QueryInterface;
use Exception;

class QueryFactoryExtension extends QueryFactory implements QueryFactoryExtensionInterface
{
    public function buildFromParams(
        string $statement,
        array $params
    ): DeleteInterface|InsertInterface|UpdateInterface|SelectInterface {
        return match (strtolower($statement)) {
            'select' => $this->buildSelectFromParams($params),
            'insert' => $this->buildInsertFromParams($params),
            'update' => $this->buildUpdateFromParams($params),
            'softdelete' => $this->buildSoftDeleteFromParams($params),
            'delete' => $this->buildDeleteFromParams($params),
            default => throw new Exception("Error Processing Request", 1)
        };
    }

    public function buildSoftDeleteFromParams($params): UpdateInterface
    {
        $params['cols'] = ['deleted' => 1];
        return $this->buildUpdateFromParams($params);
    }

    public function buildUpdateFromParams($params): UpdateInterface
    {
        $queryObject = $this->newUpdate();
        $queryObject->table($params['table'])
            ->cols($params['cols']);

        if ($queryObject instanceof OrderByInterface && isset($params['order'])) {
            $queryObject->orderBy($params['order']);
        }
        if ($queryObject instanceof LimitInterface) {
            if (isset($params['limit'])) {
                $queryObject->limit($params['limit']);
            }
        }
        $this->whereFromParams($queryObject, $params['where'] ?? []);
        return $queryObject;
    }
    public function buildSelectFromParams($params): SelectInterface
    {
        $queryObject = $this->newSelect();
        $queryObject->from($params['table'])
            ->cols($params['cols']);
        if (isset($params['order'])) {
            $queryObject->orderBy($params['order']);
        }
        if (isset($params['groupBy'])) {
            $queryObject->groupBy($params['groupBy']);
        }
        if (isset($params['page'])) {
            $queryObject->page($params['page'])->setPaging($params['limit']);
        } elseif (isset($params['limit'])) {
            $queryObject->limit($params['limit']);
        }
        if (isset($params['joins'])) {
            foreach ($params['joins'] ?? [] as $join) {
                $queryObject->join(
                    $join['type'] ?? 'inner',
                    $join['table'],
                    SimpleQueryFactory::buildConditions($join['where'])
                );
            }
        }
        $this->whereFromParams($queryObject, $params['where'] ?? []);
        return $queryObject;
    }
    public function buildInsertFromParams(array $params): InsertInterface
    {
        $queryObject = $this->newInsert();
        $queryObject->into($params['table'])
            ->cols($params['cols']);
        return $queryObject;
    }

    /**
     * @todo delete, update
     *
     * @param SelectInterface $queryObject
     * @param array $joins
     * @return SelectInterface
     */
    public function joinsFromParams(SelectInterface $queryObject, array $joins): SelectInterface
    {
        foreach ($joins ?? [] as $join) {
            $queryObject->join(
                $join['type'] ?? 'inner',
                $join['table'],
                SimpleQueryFactory::buildConditions($join['where'])
            );
        }
        return $queryObject;
    }

    public function whereFromParams(
        SelectInterface|UpdateInterface|DeleteInterface $queryObject,
        array $conditions
    ): SelectInterface|UpdateInterface|DeleteInterface {
        $where = SimpleQueryFactory::buildConditions($conditions);
        $queryObject->where($where);
        return $queryObject;
    }

    public function buildDeleteFromParams(array $params): DeleteInterface
    {
        $queryObject = $this->newDelete();
        $queryObject->from($params['table']);
        if ($queryObject instanceof LimitInterface && isset($params['limit'])) {
            $queryObject->limit($params['limit']);
        }
        $this->whereFromParams($queryObject, $params['where'] ?? []);
        return $queryObject;
    }
}
