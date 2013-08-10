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
    $commandCreator = new \jolicode\PhpARDrone\Control\AtCommandCreator();

    $i = 0;

    $flyState = 0;
    $spinAngle = 0;
    $speed = 0.3;

    $ref = array('fly' => $flyState, 'emergency' => false);
    $pcmd = array();

    for($j = 0; $j < 30; $j++) {
        $command = $commandCreator->createConfigCommand('general:navdata_demo', 'TRUE');
        $client->send($command);
    }

    $loop->addPeriodicTimer(0.03, function() use ($client, &$flyState, &$i, &$spinAngle, $commandCreator, &$ref, &$pcmd) {
        $cmds = array();

        array_push($cmds, $commandCreator->createRefCommand($ref));
        array_push($cmds, $commandCreator->createPcmdCommand($pcmd));

        $cmds = implode('', $cmds);
        $client->send($cmds);
    });

    $emitter->on('land', function() use (&$ref) {
        $ref['fly'] = false;
    });

    $emitter->on('takeoff', function() use (&$ref) {
        $ref['fly'] = true;
    });

    $emitter->on('clockwise', function() use (&$pcmd, $speed) {
        $pcmd['clockwise'] = $speed;
        unset($pcmd['counterClockwise']);
    });

    $emitter->on('counterClockwise', function() use (&$pcmd, $speed) {
        $pcmd['counterClockwise'] = $speed;
        unset($pcmd['clockwise']);
    });

    $emitter->on('front', function() use (&$pcmd, $speed) {
        $pcmd['front'] = $speed;
        unset($pcmd['back']);
    });

    $emitter->on('back', function() use (&$pcmd, $speed) {
        $pcmd['back'] = $speed;
        unset($pcmd['front']);
    });

    $emitter->on('right', function() use (&$pcmd, $speed) {
        $pcmd['right'] = $speed;
        unset($pcmd['left']);
    });

    $emitter->on('left', function() use (&$pcmd, $speed) {
        $pcmd['left'] = $speed;
        unset($pcmd['right']);
    });

    $emitter->on('up', function() use (&$pcmd, $speed) {
        $pcmd['up'] = $speed;
        unset($pcmd['down']);
    });

    $emitter->on('down', function() use (&$pcmd, $speed) {
        $pcmd['down'] = $speed;
        unset($pcmd['up']);
    });
});

//todo: speed variable


$loop->addReadStream(STDIN, function ($stdin) use ($loop, $emitter) {
    switch (trim(fgets($stdin))) {
        case 'takeoff':
            $emitter->emit('takeoff');
            break;
        case 'land':
            $emitter->emit('land');
            break;
        case 'clockwise':
            $emitter->emit('clockwise');
            break;
        case 'counterClockwise':
            $emitter->emit('counterClockwise');
            break;
        case 'front':
            $emitter->emit('front');
            break;
        case 'back':
            $emitter->emit('back');
            break;
        case 'right':
            $emitter->emit('right');
            break;
        case 'left':
            $emitter->emit('left');
            break;
        case 'up':
            $emitter->emit('up');
            break;
        case 'down':
            $emitter->emit('down');
            break;
        case 'exit':
            exit;
            break;
        default:
            echo 'Unknown command' . PHP_EOL;
            break;
    }

    echo 'drone> ';
});

echo getAsciiArt();
echo PHP_EOL;
echo 'drone> ';
$loop->run();

function getAsciiArt() {
    return "
     _ __ | |__  _ __         __ _ _ __       __| |_ __ ___  _ __   ___
    | '_ \| '_ \| '_ \ _____ / _` | '__|____ / _` | '__/ _ \| '_ \ / _ \
    | |_) | | | | |_) |_____| (_| | | |_____| (_| | | | (_) | | | |  __/
    | .__/|_| |_| .__/       \__,_|_|        \__,_|_|  \___/|_| |_|\___|
    |_|         |_|
    ";
}
