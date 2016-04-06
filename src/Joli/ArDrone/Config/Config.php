<?php

namespace Joli\ArDrone\Config;

class Config
{
    const DRONE_IP = '192.168.1.1';
    const NAVDATA_PORT = 5554;
    const CONTROL_PORT = 5556;

    public static $commands = [
        'takeoff',
        'land',
        'clockwise',
        'counterClockwise',
        'front',
        'back',
        'right',
        'left',
        'up',
        'down',
        'stop',
        'exit',
        'ftrim',
        'flip',
    ];
}
