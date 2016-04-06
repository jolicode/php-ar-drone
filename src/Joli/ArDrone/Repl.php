<?php

namespace Joli\ArDrone;

use Evenement\EventEmitter;
use Joli\ArDrone\Config\Config;

class Repl extends EventEmitter
{
    /**
     * @var \React\EventLoop\LibEventLoop|\React\EventLoop\StreamSelectLoop
     */
    private $loop;

    /**
     * @var string
     */
    public $prompt;

    public function __construct($loop)
    {
        $this->loop = $loop;
        $this->prompt = 'drone> ';
    }

    public function create()
    {
        $that = $this;

        //todo: speed variable
        $this->loop->addReadStream(STDIN, function ($stdin) use ($that) {
            $input = trim(fgets($stdin));

            if (in_array($input, Config::$commands)) {
                if ($input === 'exit') {
                    exit;
                } else {
                    $that->emit('action', [$input]);
                }
            } else {
                echo 'Unknown command'.PHP_EOL;
            }

            echo $that->prompt;
        });

        echo $this->getAsciiArt();
        echo PHP_EOL;
        echo $this->prompt;
    }

    private function getAsciiArt()
    {
        return "
     _ __ | |__  _ __         __ _ _ __       __| |_ __ ___  _ __   ___
    | '_ \| '_ \| '_ \ _____ / _` | '__|____ / _` | '__/ _ \| '_ \ / _ \
    | |_) | | | | |_) |_____| (_| | | |_____| (_| | | | (_) | | | |  __/
    | .__/|_| |_| .__/       \__,_|_|        \__,_|_|  \___/|_| |_|\___|
    |_|         |_|
    ";
    }
}
