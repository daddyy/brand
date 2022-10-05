<?php

declare(strict_types=1);

namespace App\DTO;

use Exception;

class LangDTO extends DTO
{
    public int $lang_id;
    public string $alpha_2;

    public function __construct(?array $row)
    {
        if (is_null($row)) {
            throw new Exception('Check the lang name');
        }
        parent::__construct($row);
    }
}
