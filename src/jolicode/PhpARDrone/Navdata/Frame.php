<?php
namespace jolicode\PhpARDrone\Navdata;

class Frame {

    private $binaryFrame;

    private $header;
    private $droneState;
    private $sequenceNumber;
    private $visionTag;

    public function __construct($binaryFrame)
    {
        $this->binaryFrame = $binaryFrame;

        $header =  unpack('V/', substr($this->binaryFrame, 0, 4));

        $this->header = intval(dechex($header[1]), 16);


        if ($this->checkHeaderIntegrity()) {
            $this->getData();
        } else {
            throw new \Exception('Invalid frame');
        }

    }

    private function getData() {

    }

    private function checkHeaderIntegrity()
    {
        // todo move to constant
        return ($this->header === 0x55667788);
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function getDroneState()
    {
        return $this->droneState;
    }

    public function getSequenceNumber()
    {
        return $this->sequenceNumber;
    }

    public function getVisionTag()
    {
        return $this->visionTag;

    }
}
