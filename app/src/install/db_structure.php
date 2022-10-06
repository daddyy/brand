<?php

/**
 * very simple script for generete actual db structure
 * @todo use the docblock and read the @db_props
 * @todo create it as an migration class
 */

$dbTables = $errors = [];
$namespace = '\\App\\DTO\\';
$dbcolumnCollation = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin';
$engine = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

$it = new RecursiveTreeIterator(new RecursiveDirectoryIterator(_DIR_APP_CORE . 'DTO' . DIRECTORY_SEPARATOR, RecursiveDirectoryIterator::SKIP_DOTS));
foreach ($it as $filename) {
    $filename = explode(DIRECTORY_SEPARATOR . 'DTO' . DIRECTORY_SEPARATOR, $filename, 2);
    $filename = end($filename);
    $class = rtrim($filename, '.php');
    $className = $namespace . str_replace('/', '\\', $class);
    try {
        $table = $className::getTableName();
        if ($table === 'entity') {
            throw new Exception('Entity is not yet prepared');
        }
        if ($table == false) {
            throw new Exception('Entity has not primary key, it not for creation');
        }
        $reflection = new ReflectionClass($className);
        $publicProps = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        $indexes = $cols = [];
        foreach ($publicProps as $prop) {
            $collate = $defaults = $options = [];
            $type = 'varchar';
            $propertyDoc = $prop->getDocComment();
            if ($prop->name == $className::getTableMainIdentifier()) {
                $indexes[] = "primary key (`" . $prop->name . "`)";
                $options[] = 'NOT NULL AUTO_INCREMENT';
            }
            if (substr($prop->name, -5) == '_date') {
                $type = 'timestamp';
                $options[] = 'NOT NULL';
                $defaults[] = 'current_timestamp()';
                if (substr($prop->name, 0, 6) == 'modify') {
                    $defaults[] = 'on update current_timestamp()';
                }
            }
            if ($prop->name == 'deleted') {
                $type = 'bit(1)';
                $indexes[] = "key `" . $prop->name . "` (`" . $prop->name . "`)";
                $options[] = 'NOT NULL';
            } elseif ($prop->name == 'data') {
                $type = 'varchar';
                $indexes[] = "key `" . $prop->name . "` (`" . $prop->name . "`(4096))";
                $options[] = 'NULL';
            } elseif (substr($prop->name, -3) == '_id') {
                $type = 'int(11)';
                if ($prop->name != $className::getTableMainIdentifier()) {
                    $indexes[] = "key `" . $prop->name . "` (`" . $prop->name . "`)";
                }
            }
            $collation = null;
            if ($type == 'varchar') {
                $type = match ($prop->name) {
                    'text' => 'text',
                    'path' => 'varchar(255)',
                    'description' => 'varchar(4096)',
                    'data' => 'varchar(4096)',
                    default => 'varchar(45)'
                };
                if ($prop->name == 'text') {
                    $type == 'text';
                }
                $collation = $dbcolumnCollation;
            }
            $cols[$prop->name] = [
                'type' => $type,
                'defaults' => $defaults,
                'collation' => $collation,
                'options' => $options,
                'name' => $prop->name
            ];
        }
        $dbTables[] = [
            'table' => $table,
            'cols' => $cols,
            'indexes' => $indexes,
        ];
    } catch (\Throwable $th) {
        $errors[] = $th->getMessage();
    }
}

$sql = [
    "SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
SET NAMES utf8mb4;"
];
try {

    foreach ($dbTables as $key => $table) {
        $sqlColumns = [];
        $sqlKeys = [];
        $table['indexes'] = array_filter($table['indexes']);
        foreach ($table['cols'] as $column) {
            $column['defaults'] = array_filter($column['defaults']);
            $sqlColumn = [
                "`" . $column['name'] . "`",
                $column['type'],
                $column['collation'],
                $column['options'] ? (join(' ', $column['options'])) : null,
                $column['defaults'] ? ('DEFAULT ' . join(' ', $column['defaults'])) : null
            ];
            $sqlColumns[$column['name']] = join(' ', array_filter($sqlColumn));
        }
        $sqlTable = [
            ("DROP TABLE IF EXISTS `" . $table['table'] . "`;"),
            "CREATE TABLE `" . $table['table'] . "` (",
            join(",\n", array_filter($sqlColumns)),
            $table['indexes'] ? (',' . join(",\n", $table['indexes'])) : null,
            ')',
            $engine . ';'
        ];
        $sql[$table['table']] = join("\n", $sqlTable);
    }
} catch (\Throwable $th) {
    print_r($th->getMessage());
    die();
}
$toSave = join("\n\n", $sql);
$tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . date('Y-m-d_h-i-s') . '_structure' . '.sql';
file_put_contents($tmpFile, $toSave);
echo "\n\n============================ start SQL ========================\n";
echo ">>> db structure sql file was saved as: " . $tmpFile;
echo "\n============================  end SQL  ========================\n";
die();
