<?php

/**
 * check the configuration
 */
$options = getopt("f:h");
$option = $options['f'] ?? false;
if ($option == 'check') {
    require_once  __DIR__ . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'check.php';
}

/**
 * load config and composer aitoload, setup spl for app autoload
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'loader.php';

/**
 * show actual SQL dump
 */
if ($option == 'db') {
    require_once  __DIR__ . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'db_structure.php';
}

Tracy\Debugger::$strictMode = true;
Tracy\Debugger::enable(false, _DIR_LOGS);
$config = new App\Core\Config($config);
/**
 * setup logger and tracy
 */
$container = new App\Core\Container($config);
$container->run();
