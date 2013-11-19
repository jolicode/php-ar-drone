<?php
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('Joli\ArDrone', __DIR__ . '/../src/');

$client = new \Joli\ArDrone\Client();

$client->createRepl();

$client->start();
