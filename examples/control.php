<?php
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('jolicode\PhpARDrone', __DIR__ . '/../src/');

$client = new \jolicode\PhpARDrone\Client();

$client->on('navdata', function($frame) {
    // do something with navdata (ie log);
});

$client->takeoff();

$client
    ->after('5', function() use ($client) {
        $client->clockwise(1);
    })
    ->after('5', function() use ($client) {
        $client->counterClockwise(0.5);
    })
    ->after('5', function() use ($client) {
        $client->stop();
        $client->land();
    });

$client->start();
