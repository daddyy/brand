<?php

declare(strict_types=1);

namespace App\Control;

use App\Core\Listing;
use App\DTO\DomainDTO;
use App\DTO\DTO;
use App\DTO\EntityDTO;
use App\DTO\LangDTO;
use App\DTO\RouteDTO;
use App\Factory\FormFactory;
use App\Helper\Helper;
use App\Manager\IManager;
use Exception;
use Exception as FormDataException;
use Nette\Forms\Form;

abstract class EntityControl implements IControl
{
    protected EntityDTO $entityDTO;
    /** @var EntityDTO[] */
    protected Listing $listing;
    protected FormFactory $formFactory;

    public function __construct(private RouteDTO $route, private ?IManager $manager = null)
    {
        $this->formFactory = new FormFactory();
    }

    public function createForms(EntityDTO $entityDTO): EntityDTO
    {
        foreach (['delete', 'upsert'] as $actionType) {
            $form = $this->{'createForm' . $actionType}($entityDTO);
            if ($this->actionForm($form, $entityDTO)) {
                if (isset($_SERVER['HTTP_REFERER'])) {
                    $temp = parse_url($_SERVER['HTTP_REFERER']);
                    $queryString = $temp['query'] ?? '';
                }
                Header('Location: /' . $entityDTO->getEntityType() . ($queryString ? ('?' . $queryString) : ''));
                die();
            }
            $entityDTO->addForm($actionType, $form);
        }
        return $entityDTO;
    }

    public function createFormUpsert($entityDTO): Form
    {
        $form = FormFactory::createForm('upsert', $entityDTO);
        $form->addSubmit('save', 'upsert');
        return $form;
    }

    public function createFormDelete($entityDTO): Form
    {
        $form = FormFactory::createForm('softDelete', $entityDTO);
        $form->addSubmit('save', 'softDelete');
        return $form;
    }

    private function actionForm(Form $form, EntityDTO $entityDTO): ?array
    {
        if ($form->isSuccess()) {
            $values = $this->prepareValuesFromAutoRow($form->getValues());
            if ($entityDTO->fill($values, $this->getDomain())->validate()) {
                $result = match ($form->getComponent('save')->value) {
                    'upsert' => $this->getManager()->upsert($entityDTO, $values),
                    'softDelete' => $this->getManager()->softDelete($entityDTO),
                    default => throw new Exception("Error Processing Request")
                };
                return $result;
            } else {
                throw new FormDataException("Error form");
            }
        }
        return null;
    }

    public function getItem(array $params = []): EntityDTO
    {
        $sql = $this->getManager()::prepareQueryFromDto($this->getEntityDtoName(), 'select', $params);
        $row = $this->getManager()->query($sql, 'row');
        return $this->createDtoFromRow($row, true);
    }

    /**
     * @return EntityDTO[]
     */
    public function getItems(array $params = []): array
    {
        $sql = $this->getManager()::prepareQueryFromDto($this->getEntityDtoName(), 'select', $params);
        $rows = $this->getManager()->query($sql, 'rows');
        return $this->createDtosFromRows($rows, true);
    }

    public function getTemplate(): string
    {
        $suffix = 'detail';
        if (empty($this->route->object_id)) {
            $suffix = 'list';
        }
        return strtolower($this->route->object_type . '.' . $suffix);
    }

    public function getManager(): IManager
    {
        return $this->manager;
    }

    public function getEntityDtoName(): string
    {
        $prefix = 'App\\DTO\\Entity\\';
        return $prefix . ucfirst(Helper::prepareName($this->route->object_type . 'DTO'));
    }

    /**
     * @todo remove the hotfix wirt cnd '_id' use reflection
     */
    public static function prepareValuesFromAutoRow($row, string $mainTable = null): array
    {
        $mainTable = $mainTable ?? static::getTableName();
        $mainIdentifier = $mainTable ? $mainTable . '_id' : static::getTableMainIdentifier();
        $tmpRow = [];
        foreach ($row as $property => $value) {
            $keys = explode('__', ltrim($property, '_'));
            if ($mainTable == reset($keys)) {
                if (end($keys) == $mainIdentifier) {
                    $tmpRow['entity_type'] = $mainTable;
                    $tmpRow['entity_id'] = (int) $value;
                }
                if (substr($property, -3) == '_id') {
                    $value = intval($value);
                }
                $tmpRow[end($keys)] = $value;
            } else {
                $tmpRow[reset($keys)][end($keys)] = $value;
            }
        }
        return $tmpRow;
    }

    protected function createDtoFromRow(array $row, bool $dtoAutoRow = false): DTO
    {
        $dtoName = $this->getEntityDtoName();
        if ($dtoAutoRow) {
            $row = $this->prepareValuesFromAutoRow($row);
        }
        return new $dtoName($row);
    }

    protected function createDtosFromRows(array $rows, bool $dtoAutoRow = false): array
    {
        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->createDtoFromRow($row, $dtoAutoRow);
        }
        return $result;
    }

    protected function buildParamsWithQueryParams(array $params): array
    {
        $requestParams = array_merge(
            $_GET['list'] ?? [],
            []
        );
        $requestParams = array_filter($requestParams);
        $params = array_merge($params, $requestParams);
        return $params;
    }

    public function getListing(): Listing
    {
        $listing = new Listing($this);
        return $listing;
    }

    public function getDetail(): EntityDTO
    {
        if (!isset($this->entityDTO)) {
            $entityDTO = $this->getItem(
                [
                    'where' => [
                        (self::getTableMainIdentifier()) => $this->route->object_id,
                        'deleted' => 0,
                    ]
                ],
            );
            $this->entityDTO = $this->createForms($entityDTO);
        }
        return $this->entityDTO;
    }
    public function getLang(): LangDTO
    {
        return $this->route->getLang();
    }

    public function getDomain(): DomainDTO
    {
        return $this->route->getDomain();
    }

    public static function getTableName(): string
    {
        $class = explode('\\', static::class);
        return strtolower(Helper::prepareName(rtrim(end($class), 'Control')));
    }
    public static function getTableMainIdentifier(): string
    {
        return self::getTableName() . '_id';
    }

    public function getAssigns(): array
    {
        if ($this->route->object_id) {
            return ['detail' => $this->getDetail()];
        } elseif ($this->route->object_type) {
            return ['listing' => $this->getListing()];
        }
        return [];
    }
}
