<?php

/**
 * simple chceck of dir structuru
 * @todo create a config checker and than create make config
 */

$alerts = [];
$msg = "the application needs to live this method '%s' for proper functionality to this filename: '%s'";
if (version_compare(PHP_VERSION, '8.1') <= 0) {
    $alerts['php'] = 'The version of php has to be greater than 8.1';
}
$filenames = [
    _DIR_LOGS => ['is_dir', 'is_writeable'],
    _DIR_CACHE => ['is_dir', 'is_writeable'],
    _DIR_CONFIG => ['is_dir', 'is_writeable'],
    _DIR_VENDOR => ['is_dir', 'is_writeable'],
];
foreach ($filenames as $dir => $callbacks) {
    foreach ($callbacks as $callback) {
        if (!$callback($dir)) {
            $alerts[$dir][$callback] = sprintf($msg, $callback, $dir);
        }
    }
}
