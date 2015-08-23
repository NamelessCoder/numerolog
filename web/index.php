<?php

define('NUMEROLOG_HOME', rtrim(realpath(__DIR__ . '/../'), '/') . '/');
define('NUMEROLOG_DATABASE_BASEDIR', NUMEROLOG_HOME . 'data/');

require_once NUMEROLOG_HOME . 'vendor/autoload.php';

$server = new \NamelessCoder\Numerolog\Server();
$query = $server->detectQuery();

header('Content-type: text/plain');
echo json_encode($server->query($query), JSON_OBJECT_AS_ARRAY);
