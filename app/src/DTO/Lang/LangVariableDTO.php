<?php

namespace App\DTO\Lang;

use App\DTO\ContentDTO;
use App\DTO\IContentDTO;

class LangVariableDTO extends ContentDTO implements IContentDTO
{
    public $object_type = 'lang';
    public static function getTableName(): string
    {
        return parent::getTableName();
    }

    public static function getTableMainIdentifier(): string
    {
        return parent::getTableMainIdentifier();
    }
    public function __toString(): string
    {
        return $this->title;
    }
}
