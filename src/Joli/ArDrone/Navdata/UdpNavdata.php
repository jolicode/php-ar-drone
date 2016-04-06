<?php

namespace Joli\ArDrone\Navdata;

use Evenement\EventEmitter;
use Joli\ArDrone\Config\Config;
use React\Datagram\Factory AS UdpFactory;
use React\Datagram\Socket AS UdpSocket;
use Joli\ArDrone\Navdata\Frame;

class UdpNavdata extends EventEmitter
{
    /**
     * @var \React\EventLoop\StreamSelectLoop
     */
    private $loop;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $ip;

    public function __construct($loop)
    {
        $this->port = Config::CONTROL_PORT;
        $this->ip   = Config::DRONE_IP;
        $this->loop = $loop;

        $this->start();
    }

    private function start()
    {

//        $socket     = $this->socket;
        $udpFactory = new UdpFactory($this->loop);
        $udpNavdata = $this;
//var_dump(Config::DRONE_IP); var_dump(Config::NAVDATA_PORT);die();
        // Navdata stream
        $udpFactory->createClient(Config::DRONE_IP.':'.Config::NAVDATA_PORT)->then(function (UdpSocket $client) use (&$udpNavdata) {
            // Start dialog
            $client->send('1');
            $client->send('1');

            $client->on('message',
                function($message) use (&$udpNavdata) {
                $frame = new Frame($message);
                $udpNavdata->emit('navdata', array($frame));
            });
        });
    }
}