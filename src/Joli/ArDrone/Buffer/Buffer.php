<?php
namespace Joli\ArDrone\Buffer;

class Buffer {
    /**
     * @var string
     */
    private $data;

    /**
     * @var int
     */
    private $offset;

    public function __construct($binary)
    {
        $this->data = $binary;
        $this->offset = 0;
    }

    public function getUint32LE()
    {
        $value =  unpack('V/', substr($this->data, $this->offset, ($this->offset + 4)));
        $this->moveOffset(4);
        return dechex($value[1]);
    }

    public function getUint16LE()
    {
        $value =  unpack('v/', substr($this->data, $this->offset, ($this->offset + 2)));
        $this->moveOffset(2);

        return dechex($value[1]);
    }

    public function getFloat32()
    {
        $value =  unpack('f/', substr($this->data, $this->offset, ($this->offset + 4)));
        $this->moveOffset(4);

        return dechex($value[1]);
    }

    public function getUint8()
    {
        $value =  unpack('C/', substr($this->data, $this->offset, ($this->offset + 1)));
        $this->moveOffset(1);

        return dechex($value[1]);
    }

    public function getInt32()
    {
        $value =  unpack('I/', substr($this->data, $this->offset, ($this->offset + 4)));
        $this->moveOffset(4);

        return dechex($value[1]);
    }

    public function getMask32($masks)
    {
        return $this->mask($masks, $this->getUint32LE());
    }

    public function getVector31() {
        return array(
            'x' => $this->getFloat32(),
            'y' => $this->getFloat32(),
            'z' => $this->getFloat32(),
        );
    }

    public function getMatrix33() {
        return array(
            'm11' => $this->getFloat32(),
            'm12' => $this->getFloat32(),
            'm13' => $this->getFloat32(),
            'm21' => $this->getFloat32(),
            'm22' => $this->getFloat32(),
            'm23' => $this->getFloat32(),
            'm31' => $this->getFloat32(),
            'm32' => $this->getFloat32(),
            'm33' => $this->getFloat32(),
        );
    }

    public function getBytes($nbBytes)
    {
        $value = substr($this->data, $this->offset, ($this->offset + $nbBytes));
        $this->moveOffset($nbBytes);

        return $value;
    }

    private function moveOffset($step)
    {
        $this->offset = $this->offset + $step;
    }

    //todo: move this function ?
    private function mask($masks, $value)
    {
        $flags = array();

        foreach($masks as $name => $mask) {
            $flags[$name] = (hexdec($value) & ($mask)) ? 1 : 0;
        }

      return $flags;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    public function getLength() {
        return strlen($this->data);
    }
}
