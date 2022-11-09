<?php

declare(strict_types=1);

namespace App\Core\Listing;

class Order
{
    private array $orders = [];
    public function __construct(array $params)
    {
        $orders = is_array($params) ? $params : [$params => 0];
        foreach ($orders as $column => $direction) {
            $this->addOrder($column, (int)$direction);
        }
    }

    public function addOrder(string $columnName, int $direction): self
    {
        $this->orders[] = $columnName . ' ' . ($direction ? 'DESC' : 'ASC');
        return $this;
    }

    public function getOrders(): array
    {
        return $this->orders;
    }
}
