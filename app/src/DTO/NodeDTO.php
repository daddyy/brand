<?php

declare(strict_types=1);

namespace App\DTO;

class NodeDTO extends DTO implements IDTO
{
    protected EntityDTO $entity;
    protected ?RouteDTO $route;

    public function __construct(array $params)
    {
        parent::__construct($params);
    }
}
