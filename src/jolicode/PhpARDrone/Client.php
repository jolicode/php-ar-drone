<?php
namespace jolicode\PhpARDrone;

use Evenement\EventEmitter;
use jolicode\PhpARDrone\Control\UdpControl;
use jolicode\PhpARDrone\Navdata\UdpNavdata;
use React\EventLoop\Factory AS LoopFactory;
use Datagram\Factory AS UdpFactory;
use jolicode\PhpARDrone\Config\Config;

class Client extends EventEmitter {

    private $udpFactory;
    private $udpControl;
    private $udpNavdata;
    private $timerOffset;
    private $loop;

    public function __construct()
    {
        $this->loop        = LoopFactory::create();

        $udpFactory        = new UdpFactory($this->loop);
        $this->udpFactory  = $udpFactory;
        $this->socket      = null;
        $this->timerOffset = 0;

        $this->startUdpNavdata();
        $this->startUdpControl();
    }

    private function startUdpNavdata()
    {
        $this->udpNavdata = new UdpNavdata($this->loop);
        $that = $this;

        $this->udpNavdata->on('navdata', function($navdata) use ($that) {
            $that->emit('navdata', array($navdata));
        });
    }

    private function startUdpControl()
    {
        $this->udpControl = new UdpControl($this->loop);
    }

    public function createRepl()
    {
        $repl = new Repl($this->loop);
        $repl->create();

        $udpControl = $this->udpControl;

        $repl->on('action', function($action) use (&$udpControl) {
            $udpControl->emit($action);
        });
    }

    public function after($duration, $fn)
    {
        $this->loop->addTimer(($this->timerOffset + $duration), $fn);
        $this->timerOffset += $duration;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUdpNavdata()
    {
        return $this->udpNavdata;
    }

    /**
     * @return \React\EventLoop\LibEventLoop|\React\EventLoop\StreamSelectLoop
     */
    public function getLoop()
    {
        return $this->loop;
    }

    public function start()
    {
        $this->loop->run();
    }

    public function __call($name, $arguments)
    {
        if(in_array($name, Config::$commands)) {
            if ($name === 'takeoff' || $name === 'land' || $name === 'stop') {
                $this->udpControl->emit($name);
            } else {
                if (count($arguments) > 1) {
                    new \Exception('There are too many arguments');
                }
                $this->udpControl->emit($name, array($arguments[0]));
            }
        } else {
            new \Exception('Invalid function');
        }
    }

}