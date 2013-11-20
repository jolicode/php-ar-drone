<?php
namespace Joli\ArDrone\Control;

use Evenement\EventEmitter;
use Joli\ArDrone\Config\Config;
use Datagram\Factory AS UdpFactory;
use Datagram\Socket AS UdpSocket;

class UdpControl extends EventEmitter {
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
        $this->loop           = $loop;
        $this->port           = Config::CONTROL_PORT;
        $this->ip             = Config::DRONE_IP;
        $this->commandCreator = new AtCommandCreator();
        $this->speed          = 0.3;
        $this->ref            = array('fly' => false, 'emergency' => false);
        $this->pcmd           = array();
        $this->anim           = array();

        $this->start();
    }

    private function start()
    {
        $udpFactory = new UdpFactory($this->loop);
        $loop       = $this->loop;
        $udpControl = $this;

        $udpFactory->createClient($this->ip, $this->port)->then(function (UdpSocket $client) use (&$loop, $udpControl) {
            $commandCreator = $udpControl->commandCreator;
            $ref            = $udpControl->ref;
            $pcmd           = $udpControl->pcmd;
            $anim           = $udpControl->anim;

            // Start dialog
            $client->send('1');
            $client->send('1');

            for($j = 0; $j < 5; $j++) {
                $cmds = array();

                array_push($cmds, $commandCreator->createConfigCommand('general:navdata_demo', 'TRUE'));
                array_push($cmds, $commandCreator->createPcmdCommand($pcmd));
                array_push($cmds, $commandCreator->createRefCommand($ref));

                $cmds = implode('', $cmds);
                $client->send($cmds);
                sleep(0.03);
            }

            // According to tests, a satisfying control of the AR.Drone 2.0 is reached
            // by sending the AT-commands every 30 ms for smooth drone movements.
            $loop->addPeriodicTimer(0.03, function() use ($client, $commandCreator, &$ref, &$pcmd, &$anim) {
                $cmds = array();

                array_push($cmds, $commandCreator->createRefCommand($ref));
                array_push($cmds, $commandCreator->createPcmdCommand($pcmd));

                if(count($anim) > 0) {
                    for($i = 0; $i <= 10; $i++) {
                        foreach($anim as $name => $duration) {
                            array_push($cmds, $commandCreator->createConfigCommand($name, $duration));
                        }
                    }

                    $anim = array();
                }

                $cmds = implode('', $cmds);
                $client->send($cmds);
            });


            $udpControl->on('land', function() use (&$ref, &$pcmd) {
                $pcmd = array();
                $ref['fly'] = false;
            });

            $udpControl->on('ftrim', function() use (&$client, &$commandCreator) {
                $client->send($commandCreator->createFtrimCommand());
            });

            $udpControl->on('takeoff', function() use (&$ref, &$pcmd) {
                $pcmd = array();
                $ref['fly'] = true;
            });

            $udpControl->on('clockwise', function($speed = 0.5) use (&$pcmd) {
                $pcmd['clockwise'] = $speed;
                unset($pcmd['counterClockwise']);
            });

            $udpControl->on('counterClockwise', function($speed = 0.5) use (&$pcmd) {
                $pcmd['counterClockwise'] = $speed;
                unset($pcmd['clockwise']);
            });

            $udpControl->on('stop', function() use (&$pcmd) {
                $pcmd = array();
            });

            $udpControl->on('front', function($speed = 0.3) use (&$pcmd) {
                $pcmd['front'] = $speed;
                unset($pcmd['back']);
            });

            $udpControl->on('back', function($speed = 0.3) use (&$pcmd) {
                $pcmd['back'] = $speed;
                unset($pcmd['front']);
            });

            $udpControl->on('right', function($speed = 0.3) use (&$pcmd) {
                $pcmd['right'] = $speed;
                unset($pcmd['left']);
            });

            $udpControl->on('left', function($speed = 0.3) use (&$pcmd) {
                $pcmd['left'] = $speed;
                unset($pcmd['right']);
            });

            $udpControl->on('up', function($speed = 0.6) use (&$pcmd) {
                $pcmd['up'] = $speed;
                unset($pcmd['down']);
            });

            $udpControl->on('down', function($speed = 0.6) use (&$pcmd) {
                $pcmd['down'] = $speed;
                unset($pcmd['up']);
            });

            $udpControl->on('flip', function() use (&$anim) {
                $anim['control:flight_anim'] = '16,5';
            });
        });
    }
}
