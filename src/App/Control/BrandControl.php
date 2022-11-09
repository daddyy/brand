<?php

declare(strict_types=1);

namespace App\Control;

use App\Core\Listing;
use App\DTO\EntityDTO;

class BrandControl extends EntityControl implements IControl
{
    public function getItem(array $params = []): EntityDTO
    {
        return parent::getItem($params);
    }

    /**
     * @return EntityDTO[]
     */
    public function getItems(array $params = []): array
    {
        return parent::getItems($params);
    }

    public function getListing(): Listing
    {
        $listing = parent::getListing();
        foreach ($listing->getItems() as $item) {
            $this->createForms($item);
        }
        return $listing;
    }
}
