<?php
define('_DIR_WWW', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR);
define('_DIR_ROOT', realpath(_DIR_WWW . '..' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
define('_DIR_APP', realpath(_DIR_ROOT . 'src') . DIRECTORY_SEPARATOR);
define('_DIR_APP_CORE', realpath(_DIR_APP . 'App') . DIRECTORY_SEPARATOR);
define('_DIR_CACHE', realpath(_DIR_ROOT . 'cache') . DIRECTORY_SEPARATOR);
define('_DIR_TMP', realpath(_DIR_ROOT . 'tmp') . DIRECTORY_SEPARATOR);
define('_DIR_LOGS', realpath(_DIR_ROOT . 'logs') . DIRECTORY_SEPARATOR);
define('_DIR_VENDOR', realpath(_DIR_ROOT . 'vendor') . DIRECTORY_SEPARATOR);
define('_DIR_CONFIG', realpath(_DIR_APP . 'config') . DIRECTORY_SEPARATOR);
