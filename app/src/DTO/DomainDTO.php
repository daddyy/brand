<?php

declare(strict_types=1);

namespace App\DTO;

use Exception;

class DomainDTO extends DTO
{
    private RouteDTO $route;
    protected LangDTO $lang;
    public int $domain_id;
    public int $lang_id;
    public string $name;

    public function __construct(?array $row)
    {
        if (is_null($row)) {
            throw new Exception('Check the domain name with the database and config');
        }
        parent::__construct($row);
    }

    public function getLang(): LangDTO
    {
        return $this->lang;
    }

    public function setLang(LangDTO $lang): self
    {
        $this->lang = $lang;
        return $this;
    }
}
