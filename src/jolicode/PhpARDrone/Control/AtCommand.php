<?php
namespace jolicode\PhpARDrone\Control;

class AtCommand {

    private $type;
    private $args;

    const TYPE_REF    = 'REF';
    const TYPE_PCMD   = 'PCMD';
    const TYPE_CALIB  = 'CALIB';
    const TYPE_CONFIG = 'CONFIG';
    const TYPE_FTRIM  = 'FTRIM';

    public function __construct($type, $args)
    {
        $this->args = $args;
        $this->type = $type;
    }

    function __toString()
    {
        $command = 'AT*' . $this->type . '=' . implode(',', $this->args) . '\\r';
        return $command;
    }

}
