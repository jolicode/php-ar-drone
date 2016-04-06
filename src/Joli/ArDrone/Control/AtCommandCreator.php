<?php

namespace Joli\ArDrone\Control;

class AtCommandCreator
{
    /**
     * @var int
     */
    private $sequence;

    /**
     * @var array
     */
    private $pcmdAlias = [
        'left' => ['index' => 1, 'invert' => true],
        'right' => ['index' => 1, 'invert' => false],
        'front' => ['index' => 2, 'invert' => true],
        'back' => ['index' => 2, 'invert' => false],
        'up' => ['index' => 3, 'invert' => false],
        'down' => ['index' => 3, 'invert' => true],
        'clockwise' => ['index' => 4, 'invert' => false],
        'counterClockwise' => ['index' => 4, 'invert' => true],
    ];

    public function __construct()
    {
        $this->sequence = 0;
    }

    public function createConfigCommand($name, $value)
    {
        $args = [];
        $config = '"'.$name.'","'.$value.'"';
        array_push($args, $config);

        ++$this->sequence;

        return new AtCommand($this->sequence, AtCommand::TYPE_CONFIG, $args);
    }

    public function createRefCommand($options)
    {
        $config = 0;
        $args = [];

        if ($options['fly'] === true) {
            $config = $config | (1 << 9);
        }

        if ($options['emergency'] === true) {
            $config = $config | (1 << 8);
        }

        array_push($args, $config);

        ++$this->sequence;

        return new AtCommand($this->sequence, AtCommand::TYPE_REF, $args);
    }

    public function createPcmdCommand($options)
    {
        $args = [0, 0, 0, 0, 0];

        foreach ($options as $key => $value) {
            $alias = $this->pcmdAlias[$key];

            if ($alias['invert']) {
                $value = -$value;
            }

            $args[$alias['index']] = $this->floatToIEEE($value);
        }

        if ($args[1] != 0 || $args[2] != 0) {
            $args[0] = 1;
        }

        ++$this->sequence;

        return new AtCommand($this->sequence, AtCommand::TYPE_PCMD, $args);
    }

    public function createFtrimCommand()
    {
        return new AtCommand($this->sequence, AtCommand::TYPE_FTRIM, []);
    }

    public function createAnimCommand()
    {
        $args = [17, 1];

        return new AtCommand($this->sequence, AtCommand::TYPE_ANIM, $args);
    }

    private function floatToIEEE($floatInt)
    {
        $floatInt = (float) $floatInt;
        $binInt = pack('f', $floatInt);

        $hexInt = '';
        for ($i = 0; $i < strlen($binInt); ++$i) {
            $c = ord($binInt{$i});
            $hexInt = sprintf('%02X', $c).$hexInt;
        }

        if ($floatInt < 0) {
            $binIntString = decbin(hexdec($hexInt));
            $twoComplement = '';

            for ($i = 0; $i < strlen($binIntString); ++$i) {
                if ($binIntString[$i] == '0') {
                    $twoComplement .= '1';
                } else {
                    $twoComplement .= '0';
                }
            }

            return -(bindec($twoComplement) + 1);
        } else {
            return hexdec($hexInt);
        }
    }
}
