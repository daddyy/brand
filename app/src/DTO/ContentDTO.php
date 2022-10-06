<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * @todo has to implementest interface with required entity fields (object_id, $object_type)
 */
class ContentDTO extends DTO implements IEntityTypeDTO, IContentDTO
{
    public int $content_id;
    public int $lang_id;
    public int $object_id;
    public string $object_type;
    public string $title = '';
    public string $description = '';
    public string $text = '';

    public static function getTableUniqueIdentifiers(): array
    {
        return ['lang_id', 'object_type', 'object_id'];
    }
}
