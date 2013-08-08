<?php
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('jolicode\PhpARDrone', __DIR__ . '/../src/');

$loop = React\EventLoop\Factory::create();

$factory = new Datagram\Factory($loop);

$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);

$factory->createClient('192.168.1.1', 5554)->then(function (Datagram\Socket $client) use ($loop) {

    // Enable connection with drone
    $client->send('1');

    $client->on('message', function($message) {
        $frame = new jolicode\PhpARDrone\Navdata\Frame($message);
        echo $frame;

    });
});

$factory->createClient('192.168.1.1', 5556)->then(function (Datagram\Socket $client) use ($loop) {
    for($i = 1; $i < 100; $i++) {
        $client->send('AT*CONFIG='.$i.',"general:navdata_demo","TRUE"'."\r");
    }
});

// Video chanel /////////////////////////
//$connector = new React\SocketClient\Connector($loop, $dns);
//
//$connector->create('192.168.1.1', 5555)->then(function (React\Stream\Stream $stream) {
//    $stream->on('data', function($video) {
//    });
//});


$loop->run();
