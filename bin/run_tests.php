#!/usr/bin/env php
<?php

require_once( __DIR__ . '/../vendor/autoload.php');

use Symfony\Component\Yaml\Yaml;

$configs = array(
    '/etc/sugarcli_testrc',
    getenv('HOME') . '/.sugarcli_testrc',
    '.sugarcli_testrc'
);

$params = array(
    'DB_USER' => '',
    'DB_PASSWORD' => '',
    'DB_NAME' => '',
    'DB_HOST' => '',
    'DB_PORT' => '',
    'SUGAR_PATH' => '',
    'SUGAR_URL' => '',
);

foreach ($configs as $conf_file) {
    if (is_readable($conf_file)) {
        $params = array_merge($params, Yaml::parse(file_get_contents($conf_file)));
    }
}

$search = array_map(
    function (
        $name
    ) {
        return "@$name@";
    }
    , array_keys($params)
);

$phpunit_contents = file_get_contents(__DIR__ . '/../phpunit.xml.dist');
$phpunit_contents = str_replace($search, $params, $phpunit_contents);
file_put_contents(__DIR__ . '/../phpunit.xml', $phpunit_contents);


PHPUnit_TextUI_Command::main();

