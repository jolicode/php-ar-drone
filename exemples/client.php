<?php
$loader = require_once __DIR__ . "/../vendor/autoload.php";
$loader->add('jolicode\PhpARDrone', __DIR__ . '/../src/');

$loop = React\EventLoop\Factory::create();

$factory = new Datagram\Factory($loop);

$factory->createClient('192.168.1.1', 5554)->then(function (Datagram\Socket $client) use ($loop) {

    // Enable connection with drone
    $client->send('1');

    $client->on('message', function($message) {
        $frame = new jolicode\PhpARDrone\Navdata\Frame($message);
        echo $frame->getHeader();
    });
});

$loop->run();
