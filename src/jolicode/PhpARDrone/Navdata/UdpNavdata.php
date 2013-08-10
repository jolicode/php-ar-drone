<?php
namespace jolicode\PhpARDrone\Navdata;

use Evenement\EventEmitter;
use jolicode\PhpARDrone\Config\Config;
use Datagram\Factory AS UdpFactory;
use Datagram\Socket AS UdpSocket;
use jolicode\PhpARDrone\Navdata\Frame;

class UdpNavdata extends EventEmitter {

    private $loop;
    private $socket;
    private $port;
    private $ip;

    public function __construct($loop)
    {
        $this->port           = Config::CONTROL_PORT;
        $this->ip             = Config::DRONE_IP;
        $this->loop           = $loop;
        $this->socket         = null;

        $this->start();
    }

    private function start()
    {
        $socket     = $this->socket;
        $udpFactory = new UdpFactory($this->loop);
        $udpNavdata = $this;

        // Navdata stream
        $udpFactory->createClient(Config::DRONE_IP, Config::NAVDATA_PORT)->then(function (UdpSocket $client) use (&$socket, &$udpNavdata) {
            $socket = $client;
            // Start dialog
            $client->send('1');

            $client->on('message', function($message) use (&$udpNavdata) {
                $frame = new Frame($message);
                $udpNavdata->emit('navdata', array($frame));
            });
        });
    }
}