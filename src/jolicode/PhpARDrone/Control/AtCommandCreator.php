<?php
namespace jolicode\PhpARDrone\Control;

use jolicode\PhpARDrone\Control\AtCommand;

class AtCommandCreator {

    private $sequence;

    private $pcmdAlias = array(
        'left'             => array('index' => 1, 'invert' => true),
        'right'            => array('index' => 1, 'invert' => false),
        'front'            => array('index' => 2, 'invert' => true),
        'back'             => array('index' => 2, 'invert' => false),
        'up'               => array('index' => 3, 'invert' => false),
        'down'             => array('index' => 3, 'invert' => true),
        'clockwise'        => array('index' => 4, 'invert' => false),
        'counterClockwise' => array('index' => 4, 'invert' => true)
    );

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

    public function createPcmdCommand($options)
    {
        $args = array(0, 0, 0, 0, 0);

        foreach($options as $key => $value) {
            $alias = $this->pcmdAlias[$key];

            if ($alias['invert']) {
                $value = -$value;
            }

            $args[$alias['index']] = $this->floatToIEEE($value);
        }

        $this->sequence++;

        return new AtCommand($this->sequence, AtCommand::TYPE_PCMD, $args);
    }

    private function floatToIEEE($floatInt) {
        $floatInt = (float) $floatInt;
        $binInt = pack("f", $floatInt);

        $hexInt = "";
        for($i = 0; $i < strlen($binInt); $i++) {
            $c = ord($binInt{$i});
            $hexInt = sprintf("%02X", $c).$hexInt;
        }

        $binIntString = decbin(hexdec($hexInt));
        $twoComplement = '';

        for($i=0; $i < strlen($binIntString); $i++) {
            if ($binIntString[$i] == '0') {
                $twoComplement .= '1';
            } else {
                $twoComplement .= '0';
            }
        }

        return -(bindec($twoComplement) + 1);
    }
}
