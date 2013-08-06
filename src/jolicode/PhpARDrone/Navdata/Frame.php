<?php
namespace jolicode\PhpARDrone\Navdata;

use jolicode\PhpARDrone\Buffer\Buffer;
use jolicode\PhpARDrone\Navdata\Util;
use jolicode\PhpARDrone\Option\Option;

class Frame {

    private $buffer;
    private $header;
    private $droneState;
    private $sequenceNumber;
    private $visionFlag;
    private $options;

    public function __construct($binaryFrame)
    {
        $this->buffer = new Buffer($binaryFrame);
        $this->options = array();

        $header = $this->buffer->getUint32LE();

        $this->header = intval(dechex($header), 16);

        if ($this->checkHeaderIntegrity()) {
            $this->getFrameConfig();

            $this->getFrameOptions();
            //Get option
            // 1. check checksum
        } else {
            throw new \Exception('Invalid frame');
        }
    }

    private function getFrameConfig()
    {
        // Get drone state
        $this->droneState = $this->buffer->getUint32LE();

        // Get sequence number
        $this->sequenceNumber = $this->buffer->getUint32LE();

        // Get vision flag
        $this->visionFlag = $this->buffer->getUint32LE();

    }

    private function getFrameOptions()
    {
        while(true) {
            $idOption =  $this->buffer->getUint16LE();
            $nameOption = Option::$optionIds[$idOption];

            $sizeOption = $this->buffer->getUint16LE();

            if ($nameOption === 'checksum') {
                //Todo: check checksum
                break;
            } else {
                $option = new Option($idOption, $this->buffer->getBytes($sizeOption - 4));

                array_push($this->options, $option);
            }
        }

    }

    private function checkHeaderIntegrity()
    {
        return ($this->header === Util::NAVDATA_HEADER);
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

    public function getVisionFlag()
    {
        return $this->visionFlag;

    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
