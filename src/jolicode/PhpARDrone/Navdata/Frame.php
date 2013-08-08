<?php
namespace jolicode\PhpARDrone\Navdata;

use jolicode\PhpARDrone\Buffer\Buffer;
use jolicode\PhpARDrone\Navdata\Util;
use jolicode\PhpARDrone\Navdata\Option;


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

        $this->header = $this->buffer->getUint32LE();

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

    public function getFrameOptions()
    {
        $isChecksum = false;

        while(!$isChecksum) {
            $idOption =  $this->buffer->getUint16LE();
            $nameOption = Option::$optionIds[hexdec($idOption)];
            $sizeOption = $this->buffer->getUint16LE();

            if ($nameOption === 'checksum') {
                $isChecksum = true;
                $expectedChecksum = 0;

                $data = $this->buffer->getData();

                for ($i = 0; $i < $this->buffer->getLength() - $sizeOption; $i++) {
                    $expectedChecksum = $expectedChecksum + hexdec(bin2hex($data[$i]));
                }
                $expectedChecksum = dechex($expectedChecksum);

                $checksum = $this->buffer->getUint32LE();

                if ($checksum !== $expectedChecksum) {
                    throw new \Exception('Invalid checksum');
                }
                $option = new Option($idOption, $this->buffer);

            } else {

                // Debug demo case
                $option = new Option($idOption, $this->buffer);
                var_dump($option->getData());
                die();
            }

            array_push($this->options, $option);
        }
    }

    private function checkHeaderIntegrity()
    {
        return true;
//        return($this->header == Util::NAVDATA_HEADER);
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

    function __toString()
    {
        $toString = '';

        $toString .= 'HEADER: ' . $this->getHeader() . PHP_EOL;
        $toString .= 'DRONE STATE: ' . $this->getDroneState() . PHP_EOL;
        $toString .= 'SEQUENCE NUMBER: ' . $this->getSequenceNumber() . PHP_EOL;
        $toString .= 'VISION FLAG: ' . $this->getVisionFlag() . PHP_EOL;

        foreach($this->getOptions() as $option) {
            $toString .= '------------------------------------------' . PHP_EOL;
            $toString .= 'OPTION: ' . $option->getOptionName() . PHP_EOL;
        }

        $toString .= '==========================================' . PHP_EOL;

        return $toString;
    }


}
