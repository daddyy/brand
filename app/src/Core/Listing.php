<?php

declare(strict_types=1);

namespace App\Core;

use App\Control\IControl;
use App\Core\Listing\DTO\PageDTO;
use App\Core\Listing\Filter;
use App\Core\Listing\Order;
use App\Core\Listing\Pager;
use App\DTO\DomainDTO;
use App\Helper\ArrayHelper;
use App\Manager\IManager;

class Listing
{
    private string $url;
    private array $urlParts;
    private int $count;
    private array $items;
    protected Pager $pager;
    protected Filter $filter;
    protected Order $order;

    public function __construct(private IControl $control)
    {
        // @todo
        $this->setUrl($_SERVER['REQUEST_URI']);
        $this->pager = $this->createPager(
            $this->getValueOfQuery('page'),
            $this->getValueOfQuery('limit')
        );
        $this->order = $this->createOrder(
            $this->getValueOfQuery('order')
        );
        $this->filter = $this->createFilter(
            $this->getValueOfQuery('filter')
        );
    }

    private function getValueOfQuery(string $name)
    {
        return ArrayHelper::searchInArrayByIndexes($this->urlParts, ['query', 'list', $name]);
    }

    public function getItems(): array
    {
        if (!isset($this->items)) {
            $count = $this->getCount($this->getParameters());
            $this->items = $count ? $this->control->getItems($this->getParameters()) : [];
        }
        return $this->items;
    }

    private function getCount(array $params = []): int
    {
        if (!isset($this->count)) {
            $params['limit'] = null;
            $params['cols'] = ['count(*) as count'];
            $sql = $this->getManager()::prepareQueryFromDto($this->control->getEntityDtoName(), 'select', $params);
            $this->count = $this->getManager()->query($sql, 'column');
        }
        return $this->count;
    }

    public function getParameters()
    {
        return [
            'limit' => $this->pager->getLimit(),
            'page' => $this->pager->getPage(),
            'order' => $this->order->getOrders(),
            'where' => $this->filter->getConditions(),
        ];
    }

    protected function createPager($page = null, $limit = null): Pager
    {
        $params = [
            'page' => $page > 0 ? intval($page) : 1,
            'limit' => $limit > 0 ? intval($limit) : null
        ];
        return new Pager($params);
    }

    protected function createOrder(?array $params = []): Order
    {
        if ($params) {
            foreach ($params as $key => $value) {
                $keys = explode('.', $key);
                if (count($keys) == 1) {
                    unset($params[$key]);
                    $params[$this->control::getTableName() . '.' . $key] = $value;
                }
            }
        }
        return new Order($params ?? [$this->control::getTableName() . '.' . $this->control::getTableMainIdentifier() => 0]);
    }

    protected function createFilter(?array $params = []): Filter
    {
        return new Filter($params ?? []);
    }

    private function getManager(): IManager
    {
        return $this->control->getManager();
    }

    /**
     * @return PageDTO[]
     */
    public function getPages(?int $nearbyCount = null): array
    {
        $number = intval(ceil($this->getCount() / $this->pager->getLimit()));
        $pages = $this->pager->getPages($number, $nearbyCount);
        foreach ($pages as $page) {
            $page->setUri($this->getUriForPage($page->page));
        }
        return $pages;
    }

    private function getUriForPage(int $page): string
    {
        $urlParts = $this->urlParts;
        $urlParts['query']['list']['page'] = $page;
        $uri = $this->getUrlFromParts($urlParts);
        return $uri;
    }

    public function getUriByOrderColumn(string $columnName, $multiple = false): string
    {
        $orderParams = $this->getValueOfQuery('order');
        $value = $orderParams[$columnName] ?? 0;
        if ($multiple) {
            $orderParams[$columnName] = $value ? 0 : 1;
        } else {
            $orderParams = [$columnName => ($value ? 0 : 1)];
        }
        $urlParts = $this->urlParts;
        $urlParts['query']['list']['order'] = $orderParams;
        $uri = $this->getUrlFromParts($urlParts);
        return $uri;
    }

    private function setUrl(string $url)
    {
        $this->url = $url;
        $this->urlParts = parse_url($url);
        if (!empty($this->urlParts['query'])) {
            parse_str($this->urlParts['query'], $this->urlParts['query']);
        }
    }

    private function getUrlFromParts(array $urlParts): string
    {
        $urlParts['query'] = $urlParts['query'] ? urldecode(http_build_query($urlParts['query'])) : '';
        return http_build_url($urlParts);
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
