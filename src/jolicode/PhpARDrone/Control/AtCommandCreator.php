<?php
namespace jolicode\PhpARDrone\Control;

use jolicode\PhpARDrone\Control\AtCommand;

class AtCommandCreator {

    public function __construct()
    {
    }

    public function createConfigCommand($name, $value)
    {
        $args = array();
        $config = '"' . $name . '","' . $value . '"';
        array_push($args, $config);

        return new AtCommand(AtCommand::TYPE_CONFIG, $args);
    }
}
