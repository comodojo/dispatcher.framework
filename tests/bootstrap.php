<?php

// A trick to not face httpuri wrath

$_SERVER['HTTP_HOST'] = "http://localhost/";
$_SERVER['SERVER_ADDR'] = "127.0.0.1";
$_SERVER['REMOTE_ADDR'] = "127.0.0.1";
$_SERVER['REMOTE_PORT'] = "51124";
$_SERVER['SERVER_PROTOCOL'] = "HTTP/1.1";
$_SERVER['REQUEST_METHOD'] = "GET";
$_SERVER['REQUEST_URI'] = "/";
// $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);

// Simple bootloader for phpunit using composer autoloader

$loader = require __DIR__ . "/../vendor/autoload.php";

$loader->addPsr4('Comodojo\\Dispatcher\\Tests\\', __DIR__ . "/Dispatcher");
