<?php
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('Joli\ArDrone', __DIR__ . '/../src/');

$client = new \jolicode\PhpARDrone\Client();

$client->on('navdata', function($frame) {
    echo $frame;
});

$client->on('hovering', function() {
});

$client->on('landed', function() {
});

//$client->takeoff();
//
//$client
//    ->after('3', function() use ($client) {
//        $client->stop();
//    })
//    ->after('3', function() use ($client) {
//        $client->land();
//    });

$client->start();
