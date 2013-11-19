<?php
namespace Joli\ArDrone\Control;

class AtCommand {

    private $type;
    private $args;
    private $sequence;

    const TYPE_REF    = 'REF';
    const TYPE_PCMD   = 'PCMD';
    const TYPE_CALIB  = 'CALIB';
    const TYPE_CONFIG = 'CONFIG';
    const TYPE_FTRIM  = 'FTRIM';
    const TYPE_ANIM   = 'ANIM';

    public function __construct($sequence, $type, $args)
    {
        $this->args     = $args;
        $this->type     = $type;
        $this->sequence = $sequence;
    }

    function __toString()
    {
        $command = 'AT*' . $this->type . '=' . $this->sequence;

        if (count($this->args) > 0) {
            $command .= ','. implode(',', $this->args);
        }

        $command .= "\r";

        return $command;
    }

}
