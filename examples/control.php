<?php
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('jolicode\PhpARDrone', __DIR__ . '/../src/');

$client = new \jolicode\PhpARDrone\Client();

$client->on('lowBattery', function($frame) {
    // * Houston, we've got a problem *
});

$client->on('hovering', function() {
    // lala
});

$client->on('landed', function() {
    // loulou
});

$client->takeoff();

$client
    ->after('7', function() use ($client) {
        $client->clockwise(1);
    })
    ->after('3', function() use ($client) {
        $client->counterClockwise(0.5);
    })
    ->after('7', function() use ($client) {
        $client->stop();

        // Landing with callback function
        $client->land(function() {
            echo 'Nous sommes arrivés à destination, la température extérieure est de 28°C.
            Merci d\'avoir choisi AR Drone Company.';
        });
    });

$client->start();
