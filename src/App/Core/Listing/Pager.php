<?php

declare(strict_types=1);

namespace App\Core\Listing;

use App\Core\Listing\DTO\PageDTO;

class Pager
{
    const LIMIT = 2;
    const PAGE = 1;
    private int $limit;
    private int $page;
    public function __construct(array $params)
    {
        $this->limit = $params['limit'] ?? self::LIMIT;
        $this->page = $params['page'] ?? self::PAGE;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPages(int $count, ?int $nearbyCount = null): array
    {
        $pages = [];
        $pages[] = new PageDTO([
            'active' => false,
            'disabled' => $this->getPage() == 1,
            'page' => $this->getPage() - 1,
        ]);
        for ($i = 1; $i <= $count; $i++) {
            $pages[] = new PageDTO([
                'active' => $this->getPage() == $i,
                'page' => $i,
                'disabled' => false,
            ]);
        }
        $pages[] = new PageDTO([
            'active' => false,
            'disabled' => $this->getPage() == $count,
            'page' => $this->getPage() + 1,
        ]);
        return $pages;
    }
}
