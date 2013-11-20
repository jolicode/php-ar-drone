<?php
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('Joli\ArDrone', __DIR__ . '/../src/');

$client = new \Joli\ArDrone\Client();

$client->takeoff();

$client
    ->after(3, function() use ($client) {
        $client->up(0.6);
    })
    ->after(4, function() use ($client) {
        $client->stop();
    })
    ->after(1, function() use ($client) {
        $client->left(0.3);
    })
    ->after(1, function() use ($client) {
        $client->stop();
    })
    ->after(1, function() use ($client) {
        $client->down(0.5);
    })
    ->after(2, function() use ($client) {
        $client->stop();
    })
    ->after(1, function() use ($client) {
        $client->right(0.3);
    })
    ->after(1, function() use ($client) {
        $client->stop();
    })
    ->after(3, function() use ($client) {
        $client->land();
    });

$client->start();
