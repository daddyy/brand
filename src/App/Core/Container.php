<?php

declare(strict_types=1);

namespace App\Core;

use App\Control\EntityControl;
use App\Control\IControl;
use App\DTO\DomainDTO;
use App\DTO\LangDTO;
use App\DTO\RouteDTO;
use App\Extension\PDO;
use App\Manager\MysqlManager;
use Latte\Essential\TranslatorExtension;
use Exception as DataException;

/**
 * @todo this class is more as engine or core, create core class separate
 */
final class Container
{
    private $control;
    private $route;
    private MysqlManager $manager;
    public function __construct(private Config $config)
    {
        $this->manager = MysqlManager::connect(
            $config->getDatabaseArgs()
        );
        $this->createUser();
    }

    /**
     * create user DTO, get login etc from control
     *
     * @return void
     */
    public function createUser()
    {
        session_start();
    }


    public function run()
    {
        $domainDto = $this->createDomain($this->config->getDomain());
        $this->route = $this->createRoute(
            $domainDto,
            $this->config->getUri()
        );
        $this->control = $this->createEntityControl();
        $this->render($this->control);
    }

    /**
     * @todo create self core controller to get the entity / node
     */
    private function createEntityControl(): IControl
    {
        $namespacePrefix = '\\App\\Control\\';
        $controlName = $namespacePrefix . $this->route->getEntityControl();
        if (!class_exists($controlName)) {
            $controlName = $namespacePrefix . 'EntityControl';
        }
        $control = new $controlName($this->route, $this->manager);
        return $control;
    }

    /**
     * @todo create RouteControl
     * @todo create redirects if the route is older or if is system and the route exists
     */
    private function createRoute(DomainDTO $domain, string $uriPath): RouteDTO
    {
        $aQuery = [
            'table' => 'route',
            'cols' => ['*'],
            'where' => [
                ['route.path = "%s"', [$uriPath]],
                ['(route.domain_id = %s OR route.domain_id IS NULL)', [$domain->domain_id]],
                ['(route.lang_id = %s OR route.lang_id IS NULL)', [$domain->lang_id]],
            ],
            'order' => [
                'route.domain_id, route.lang_id'
            ]
        ];
        $row = $this->manager->query($this->manager->prepareQuery($aQuery), 'row');
        if (empty($row)) {
            $aUri = explode('/', $uriPath, 2);
            $object_type = reset($aUri);
            $object_id = end($aUri);
            $object_id = $object_type != $object_id && ctype_digit(strval($object_id)) ? $object_id : null;

            $aQuery['where'] = [
                ['route.domain_id = %s', [$domain->domain_id]],
                ['route.lang_id = %s', [$domain->lang_id]],
                ['route.object_type = "%s"', [$object_type]],
                $object_id ? ['route.object_id = "%s"', [$object_id]] : null
            ];

            $row = $this->manager->query($this->manager->prepareQuery($aQuery), 'row');
            if ($object_type == false) {
                throw new DataException("Misssing route for the uri path");
            }
            if (empty($row)) {
                $row = [
                    'domain_id' => $domain->domain_id,
                    'lang_id' => $domain->lang_id,
                    'object_id' => $object_id ? intval($object_id) : 0,
                    'object_type' => $object_type,
                ];
            }
        }
        $route = new RouteDTO($row);
        $route->setDomain($domain)->setLang($domain->getLang());
        return $route;
    }

    /**
     * @todo input for create has to be hostname from _config
     * @todo we know the name of column and the name of related table (property)
     *       we can create the auto sql query with underscode annotation __$table__$columnName
     */
    private function createDomain(string $domainName): DomainDTO
    {
        $row = $this->manager->query(
            $this->manager->prepareQueryFromDto(
                DomainDTO::class,
                'select',
                ['where' => [['domain.name = "%s"', [$domainName]]]]
            ),
            'row'
        );
        if (empty($row)) {
            throw new DataException("Missing lang or domain entity please create it");
        }
        $row = EntityControl::prepareValuesFromAutoRow($row, 'domain');
        $domain = new DomainDTO($row);
        return $domain;
    }

    /**
     * @todo config can says what default template engine can be setup
     * @todo create template factory
     */
    private function createTemplateEngine(?string $templeEngine = null)
    {
        $latte = new \Latte\Engine();
        $latte->setTempDirectory(_DIR_CACHE . 'latte');
        $translator = function (string $original): string {
            return $original;
        };
        $latte->addExtension(new TranslatorExtension($translator));
        return $latte;
    }

    public function getControl(): IControl
    {
        return $this->control;
    }

    public function render(IControl $control): void
    {
        $templateEngine = $this->createTemplateEngine();
        $suffix = 'latte';
        $assigns = [
            'template' => 'content' . DIRECTORY_SEPARATOR . $control->getTemplate() . '.' . $suffix,
            'domain' => $this->route->getDomain(),
            'lang' => $this->route->getLang(),
            'route' => $this->route,
        ];
        $assigns = array_merge($assigns, $control->getAssigns());
        $control->getManager()->disconnect();
        $templateEngine->render(_DIR_APP_CORE . 'resource' . DIRECTORY_SEPARATOR . 'index.' . $suffix, $assigns);
    }
}
