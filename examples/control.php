<?php
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('Joli\ArDrone', __DIR__ . '/../src/');

$client = new \Joli\ArDrone\Client();

$client->takeoff();

$client
    ->after('4', function() use ($client) {
        $client->stop();
    })
    ->after('4', function() use ($client) {
        $client->clockwise(1);
    })
    ->after('4', function() use ($client) {
        $client->land();
    });

$client->start();
