<?php

declare(strict_types=1);

namespace App\Core;

use App\Helper\ArrayHelper;

final class Config
{
    const PRODUCTION_MODE = 'production';
    const DEVELOPMENT_MODE = 'devel';
    public string $appMode;
    private string $domain;
    private string $uriPath;
    private array $database;
    public function __construct(array $config)
    {
        $this->setDatabase(ArrayHelper::searchInArrayByIndexes($config, ['config', 'app', 'database']));
        $this->setAppMode(ArrayHelper::searchInArrayByIndexes($config, ['config', 'mode']));
        $this->setDomain(ArrayHelper::searchInArrayByIndexes($config, ['config', 'app', 'domain']));
        $this->setUriPath(ArrayHelper::searchInArrayByIndexes($config, ['config', 'app', 'path']));
    }

    public function isProduction(): bool
    {
        return $this->appMode == self::PRODUCTION_MODE;
    }

    private function setDatabase(string $connectionString): self
    {
        $this->database = parse_url($connectionString);
        return $this;
    }

    public function getDatabaseArgs(): array
    {
        parse_str($this->database['query'] ?? '', $options);
        return [
            'dsn' => join(
                '',
                [
                    $this->database['scheme'],
                    ':dbname=',
                    ltrim($this->database['path'], '/'),
                    ';host=',
                    $this->database['host'],
                ]
            ),
            'user' => $this->database['user'],
            'pass' => $this->database['pass'],
            'options' => $options,
        ];
    }

    /**
     * @todo enum definition it will be better then switch
     */
    public function setAppMode(string $appMode): self
    {
        switch ($appMode) {
            case 'local':
            case 'devel':
                $this->appMode = self::DEVELOPMENT_MODE;
                break;
            default:
                $this->appMode = self::PRODUCTION_MODE;
                break;
        }
        return $this;
    }

    public function setDomain(?string $domain): self
    {
        $this->domain = $domain ?? $_SERVER['HTTP_HOST'];
        return $this;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setUriPath(?string $uriPath): self
    {
        $this->uriPath = $uriPath ?? $_SERVER['REQUEST_URI'];
        return $this;
    }

    public function getUriPath(): string
    {
        return $this->uriPath;
    }

    public function getUri(): string
    {
        $aUrl = parse_url($this->getUriPath());
        if ($aUrl['path'] == '/') {
            return $aUrl['path'];
        }
        return ltrim($aUrl['path'] ?? '/', '/');
    }
}
