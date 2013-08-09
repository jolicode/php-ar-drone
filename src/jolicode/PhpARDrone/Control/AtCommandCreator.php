<?php
namespace jolicode\PhpARDrone\Control;

use jolicode\PhpARDrone\Control\AtCommand;

class AtCommandCreator {

    private $sequence;

    public function __construct()
    {
        $this->sequence = 0;
    }

    public function createConfigCommand($name, $value)
    {
        $args = array();
        $config = '"' . $name . '","' . $value . '"';
        array_push($args, $config);

        $this->sequence++;

        return new AtCommand($this->sequence, AtCommand::TYPE_CONFIG, $args);
    }

    public function createRefCommand($options)
    {
        $config = 0;
        $args = array();

        if ($options['fly'] === true) {
            $config = $config | (1 << 9);
        }

        if ($options['emergency'] === true) {
            $config = $config | (1 << 8);
        }

        array_push($args, $config);

        $this->sequence++;

        return new AtCommand($this->sequence, AtCommand::TYPE_REF, $args);
    }

    public function createPcmdCommand()
    {
        $args = array(0, 0, 0, 0, 0);

        $this->sequence++;

        return new AtCommand($this->sequence, AtCommand::TYPE_PCMD, $args);
    }
}
