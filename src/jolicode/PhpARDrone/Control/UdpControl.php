<?php
namespace jolicode\PhpARDrone\Control;

use Evenement\EventEmitter;
use jolicode\PhpARDrone\Config\Config;
use Datagram\Factory AS UdpFactory;
use Datagram\Socket AS UdpSocket;

class UdpControl extends EventEmitter {

    private $loop;
    private $socket;
    private $port;
    private $ip;

    public function __construct($loop)
    {
        $this->loop           = $loop;
        $this->port           = Config::CONTROL_PORT;
        $this->ip             = Config::DRONE_IP;
        $this->socket         = null;
        $this->commandCreator = new AtCommandCreator();
        $this->speed          = 0.3;
        $this->ref            = array('fly' => false, 'emergency' => false);
        $this->pcmd           = array();

        $this->start();
    }

    private function start()
    {
        $socket     = $this->socket;
        $udpFactory = new UdpFactory($this->loop);
        $loop       = $this->loop;
        $udpControl = $this;

        $udpFactory->createClient($this->ip, $this->port)->then(function (UdpSocket $client) use (&$loop, &$socket, $udpControl) {
            $socket         = $client;
            $commandCreator = $udpControl->commandCreator;
            $ref            = $udpControl->ref;
            $pcmd           = $udpControl->pcmd;
            $speed          = $udpControl->speed;

            for($j = 0; $j < 30; $j++) {
                $command = $commandCreator->createConfigCommand('general:navdata_demo', 'TRUE');
                $client->send($command);
            }

            $loop->addPeriodicTimer(0.03, function() use ($client, $commandCreator, &$ref, &$pcmd) {
                $cmds = array();

                array_push($cmds, $commandCreator->createRefCommand($ref));
                array_push($cmds, $commandCreator->createPcmdCommand($pcmd));

                $cmds = implode('', $cmds);
                $client->send($cmds);
            });

            $udpControl->on('land', function() use (&$ref, &$pcmd) {
                $pcmd = array();
                $ref['fly'] = false;
            });

            $udpControl->on('takeoff', function() use (&$ref, &$pcmd) {
                $pcmd = array();
                $ref['fly'] = true;
            });

            $udpControl->on('clockwise', function() use (&$pcmd, $speed) {
                $pcmd['clockwise'] = $speed;
                unset($pcmd['counterClockwise']);
            });

            $udpControl->on('counterClockwise', function() use (&$pcmd, $speed) {
                $pcmd['counterClockwise'] = $speed;
                unset($pcmd['clockwise']);
            });

            $udpControl->on('stop', function() use (&$pcmd) {
                $pcmd = array();
            });

            $udpControl->on('front', function() use (&$pcmd, $speed) {
                $pcmd['front'] = $speed;
                unset($pcmd['back']);
            });

            $udpControl->on('back', function() use (&$pcmd, $speed) {
                $pcmd['back'] = $speed;
                unset($pcmd['front']);
            });

            $udpControl->on('right', function() use (&$pcmd, $speed) {
                $pcmd['right'] = $speed;
                unset($pcmd['left']);
            });

            $udpControl->on('left', function() use (&$pcmd, $speed) {
                $pcmd['left'] = $speed;
                unset($pcmd['right']);
            });

            $udpControl->on('up', function() use (&$pcmd, $speed) {
                $pcmd['up'] = $speed;
                unset($pcmd['down']);
            });

            $udpControl->on('down', function() use (&$pcmd, $speed) {
                $pcmd['down'] = $speed;
                unset($pcmd['up']);
            });
        });
    }
}