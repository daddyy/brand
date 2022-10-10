<?php

declare(strict_types=1);

namespace App\DTO;

use App\DTO\Lang\LangVariableDTO;
use Exception;

class LangDTO extends DTO implements IDTO
{
    private array $variables = [];
    public int $lang_id;
    public string $alpha_2;

    public function __construct(?array $row)
    {
        if (is_null($row)) {
            throw new Exception('Check the lang name');
        }
        parent::__construct($row);
    }

    public function addLangVariable($string): LangVariableDTO
    {
        return new LangVariableDTO([
            'title' => $string,
            'lang_id' => $this->getId()
        ]);
    }
}
