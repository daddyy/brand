<?php

use App\Core\Config;
use App\Core\Container;

/**
 * check the configuration
 */
// require_once  __DIR__ . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'check.php';

/**
 * load config and composer aitoload, setup spl for app autoload
 */
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'loader.php');

/**
 * show actual SQL dump
 */
// require_once  __DIR__ . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'seed.php';

Tracy\Debugger::$strictMode = true;
Tracy\Debugger::enable(false, _DIR_LOGS);
$config = new Config($config);
/**
 * setup logger and tracy
 */
$container = new Container($config);
$container->run();
