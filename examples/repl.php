<?php
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('Joli\ArDrone', __DIR__ . '/../src/');

$client = new \jolicode\PhpARDrone\Client();

$client->on('navdata', function($frame) {
    echo $frame;
});

$client->createRepl();

$client->start();
