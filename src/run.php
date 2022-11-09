<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'loader.php';
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

/**
 * show actual SQL dump
 */
if ($option == 'db') {
    require_once  __DIR__ . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'db_structure.php';
}

/**
 * setup logger and tracy
 */
Tracy\Debugger::$strictMode = true;
Tracy\Debugger::enable(false, _DIR_LOGS);
Tracy\Debugger::$maxLength = 500;
Tracy\Debugger::$maxDepth = 10;


$container = new App\Core\Container(
    new App\Core\Config($config)
);
$container->run();
