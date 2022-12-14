<?php

declare(strict_types=1);

namespace App\DTO;

use App\Helper\Helper;

class RouteDTO extends DTO implements IEntityTypeDTO
{
    private DomainDTO $domain;
    private LangDTO $lang;
    public int $route_id;
    public ?int $lang_id = null;
    public ?int $domain_id = null;
    public ?int $object_id = null;
    public string $object_type;
    public string $path;

    public function setLang(LangDTO $lang): self
    {
        $this->lang = $lang;
        return $this;
    }

    public function setDomain(DomainDTO $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    public function getEntityControl(): string
    {
        return ucfirst(Helper::prepareName($this->object_type . '_' . self::CONTROL_SUFFIX));
    }

    public function getDomain(): ?DomainDTO
    {
        return $this->domain;
    }

    public function getLang(): ?LangDTO
    {
        return $this->lang;
    }

    public function getAbsPath()
    {
        return '/' . ltrim($this->path, '/');
    }

    public static function getTableUniqueIdentifiers(): array
    {
        return ['domain_id', 'lang_id', 'object_type', 'object_id'];
    }
}
