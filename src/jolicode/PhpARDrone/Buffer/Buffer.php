<?php
namespace jolicode\PhpARDrone\Buffer;

class Buffer {

    private $buffer;
    private $offset;

    public function __construct($buffer)
    {
        $this->buffer = $buffer;
        $this->offset = 0;
    }

    public function getUint32LE()
    {
        $value =  unpack('V/', substr($this->buffer, $this->offset, ($this->offset + 4)));
        $this->moveOffset(4);

        return dechex($value[1]);
    }

    public function getUint16LE()
    {
        $value =  unpack('v/', substr($this->buffer, $this->offset, ($this->offset + 2)));
        $this->moveOffset(2);

        return dechex($value[1]);
    }

    public function getBytes($nbBytes)
    {
        $value = dechex(substr($this->buffer, $this->offset, ($this->offset + $nbBytes)));
        $this->moveOffset($nbBytes);

        return $value;
    }

    private function moveOffset($step)
    {
        $this->offset = $this->offset + $step;
    }
}
