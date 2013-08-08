<?php
$loader = require_once __DIR__ . '/../vendor/autoload.php';

$loader->add('jolicode\PhpARDrone', __DIR__ . '/../src/');

$loop = React\EventLoop\Factory::create();

$factory = new Datagram\Factory($loop);

$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);

$emitter = new \Evenement\EventEmitter();

// Navdata stream
$factory->createClient('192.168.1.1', 5554)->then(function (Datagram\Socket $client) use ($loop, $emitter) {
    $client->send('1');

    $client->on('message', function($message) use ($emitter) {
        $frame = new jolicode\PhpARDrone\Navdata\Frame($message);
//        echo $frame;
    });
});

// Control stream
$factory->createClient('192.168.1.1', 5556)->then(function (Datagram\Socket $client) use ($loop, $emitter) {
    $i = 0;

    $flyState = 0;

    $timerLanding = null;
    $timerTakeOff= null;

    for($j = 0; $j < 30; $j++) {
        $client->send('AT*CONFIG=' . $j . ',"general:navdata_demo","TRUE"'."\r");
    }

    $loop->addPeriodicTimer(0.01, function() use ($client, &$flyState, &$i) {
        $client->send('AT*PCMD=' . ++$i . ',0,0,0,0,0'."\r");
        $client->send('AT*REF='.++$i.','.$flyState.''."\r");
    });

    $emitter->on('land', function() use (&$flyState) {
        $flyState = 0;
    });

    $emitter->on('takeoff', function() use (&$flyState) {
        $flyState = 512;
    });

});

$loop->addReadStream(STDIN, function ($stdin) use ($loop, $emitter) {
    switch (trim(fgets($stdin))) {
        case 'takeoff':
            $emitter->emit('takeoff');
            break;
        case 'land':
            $emitter->emit('land');
            break;
        case 'exit':
            exit;
            break;
    }
    echo 'drone> ';
});

echo 'drone> ';
$loop->run();
