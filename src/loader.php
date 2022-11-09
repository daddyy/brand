<?php

use Symfony\Component\Yaml\Yaml;

require_once  __DIR__ . DIRECTORY_SEPARATOR . 'constants.php';
require_once  __DIR__ . DIRECTORY_SEPARATOR . 'missing.php';

require_once _DIR_VENDOR . 'autoload.php';

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
