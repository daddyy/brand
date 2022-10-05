<?php

declare(strict_types=1);

namespace App\Core\Listing\DTO;

class PageDTO
{
    public bool $active;
    public string $title;
    public bool $disabled;
    public int $page;
    public string $uri;

    public function __construct(array $values)
    {
        $this->active = $values['active'] ?? false;
        $this->title = $values['title'] ?? (string)$values['page'];
        $this->disabled = $values['disabled'] ?? false;
        $this->page = $values['page'];
        $this->uri = $values['uri'] ?? ('?page=' . $this->page);
    }

    public function setUri(string $uri): self
    {
        $this->uri = $uri;
        return $this;
    }
}
