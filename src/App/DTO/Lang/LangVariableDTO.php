<?php

namespace App\DTO\Lang;

use App\DTO\ContentDTO as ContentDTO;
use App\DTO\IContentDTO as IContentDTO;

class LangVariableDTO extends ContentDTO implements IContentDTO
{
    public string $object_type = 'lang';
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
