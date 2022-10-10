<?php

use Symfony\Component\Yaml\Yaml;

require_once _DIR_VENDOR . 'autoload.php';
require_once  __DIR__ . DIRECTORY_SEPARATOR . 'missing.php';

/**
 * mandatory order production, devel, local
 * definition => constants, but it's better use enf or class const for exact use
 * config => database config, etc
 */
$config = [];
foreach (['definition', 'config'] as $type) {
    $extension = 'yml';
    $filepath = _DIR_CONFIG . join('.', [$type, $extension]);
    $config[$type] = [];
    if (file_exists($filepath)) {
        $config[$type] = Yaml::parseFile($filepath);
        $config[$type]['mode'] = 'production';
    }

    foreach (['devel', 'local'] as $prefix) {
        $testPath = _DIR_CONFIG . join('.', [$prefix, $type, $extension]);
        if (file_exists($testPath)) {
            $filepath = $testPath;
            $temp = Yaml::parseFile($filepath);
            $config[$type] = array_merge($config[$type], $temp);
            $config[$type]['mode'] = $prefix;
        }
    }
}

/**
 * load the classes from core and libreries
 * @todo fix the directories structure and use composer
 */
function autoload($className)
{
    $result               = false;
    $filename             = false;
    $namespaceSeparator   = '\\';
    $classExtensionSuffix = '.php';
    $paths                = array(
        'core' => _DIR_APP_CORE
    );
    $temp = explode('\\', $className, 2);
    $target = str_replace($namespaceSeparator, DIRECTORY_SEPARATOR, end($temp)) . $classExtensionSuffix;
    foreach ($paths as $path) {
        $filename = $path . DIRECTORY_SEPARATOR . $target;
        if (file_exists($filename)) {
            $result = $filename;
            break;
        }
    }
    if ($result) {
        require_once $result;
    }
}


if (!function_exists('spl_autoload_register')) {
    throw new \Exception('spl_autoload does not exist in this PHP installation');
}

spl_autoload_register("autoload");
