<?php

declare(strict_types=1);

namespace App\Core\Listing;

class Filter
{
    public function __construct(array $params)
    {
    }

    public function getConditions(): array
    {
        return [
            'deleted' => 0
        ];
    }
}
