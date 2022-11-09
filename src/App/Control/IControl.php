<?php

declare(strict_types=1);

namespace App\Control;

use App\DTO\DomainDTO;
use App\DTO\IDTO;
use App\Manager\IManager;

interface IControl
{
    public function getItem(array $params = []): IDTO;
    /** @return IDTO[] */
    public function getItems(array $params = []): array;
    public function getTemplate(): string;
    public function getDomain(): DomainDTO;
    public static function getTableName(): string;
    public static function getTableMainIdentifier(): string;
    public function getManager(): IManager;
    public function getAssigns(): array;
    public function getEntityDtoName(): ?string;
}
