<?php

declare(strict_types=1);

namespace App\DTO;

use ArrayAccess;
use Nette\Forms\Form;
use App\Helper\Helper;
use App\Helper\ArrayHelper;
use DateTime;

abstract class EntityDTO extends DTO
{
    /**
     * @var Form[]
     */
    private array $forms = [];
    private int $entity_id;
    private string $entity_type;
    private DateTime $entity_modify_date;
    protected RouteDTO $route;
    protected ContentDTO $content;
    public bool $deleted;
    public ?array $data;

    public function getForm($type): ?Form
    {
        return $this->forms[$type] ?? null;
    }

    public function addForm(string $type, Form $form): self
    {
        $this->forms[$type] = $form;
        return $this;
    }

    public function createRoute(): RouteDTO
    {
        return new RouteDTO(
            [
                'path' => ($this->entity_type . '/' . $this->entity_id),
                'object_id' => $this->entity_id,
                'object_type' => $this->entity_type,
            ]
        );
    }

    public function createContent(): ContentDTO
    {
        return new ContentDTO(
            [
                'object_id' => $this->entity_id,
                'object_type' => $this->entity_type,
            ]
        );
    }

    public function getRoute(): RouteDTO
    {
        return $this->route;
    }

    public function getUri(): string
    {
        return $this->route->getAbsPath();
    }

    public function getContent(): ContentDTO
    {
        return $this->content;
    }
    public function setContent(ContentDTO $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function setData(?string $jsonString)
    {
        $this->data = $jsonString ? json_decode($jsonString) : null;
    }

    public function setDeleted(int|bool $deleted): void
    {
        $this->deleted = (bool)$deleted;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function getDataByKey(string $key, $indexes)
    {
        $result = null;
        $getter = Helper::getter($key, $this::class);
        if ($getter) {
            $from = $this->{$getter}();
            if ($from) {
                $result = ArrayHelper::searchInArrayByIndexes($from, $indexes);
            }
        }
        return $result;
    }

    public function setEntityType(string $entity_type): self
    {
        $this->entity_type = $entity_type;
        return $this;
    }
    public function setEntityId(int $entity_id): self
    {
        $this->entity_id = $entity_id;
        return $this;
    }

    public function getEntityModifyDate(): DateTime
    {
        return $this->entity_modify_date;
    }

    public function setEntityModifyDate(string $entity_modify_date): self
    {
        $this->entity_modify_date = $this->getDateTimePropValue($entity_modify_date);
        return $this;
    }
}
