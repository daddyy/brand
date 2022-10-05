<?php

use App\Core\Config;
use App\Core\Container;

/**
 * load config and composer aitoload, setup spl for app autoload
 */
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'loader.php');

$config = new Config($config);
/**
 * setup logger and tracy
 */
Tracy\Debugger::$strictMode = true;
Tracy\Debugger::enable($config->isProduction(), _DIR_LOGS);
$container = new Container($config);
$container->run();
