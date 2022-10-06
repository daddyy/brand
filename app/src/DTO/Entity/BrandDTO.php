<?php

declare(strict_types=1);

namespace App\DTO\Entity;

use App\Control\BrandControl;
use App\DTO\DTO;
use App\DTO\EntityDTO;
use App\DTO\IContentDTO;
use App\DTO\RouteDTO;

class BrandDTO extends EntityDTO
{
    public int $brand_id;

    public static function getTableMainIdentifier(): ?string
    {
        return BrandControl::getTableMainIdentifier();
    }
}
