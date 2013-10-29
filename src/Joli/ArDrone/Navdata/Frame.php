<?php
namespace Joli\ArDrone\Navdata;

use Joli\ArDrone\Buffer\Buffer;
use Joli\ArDrone\Navdata\Util;
use Joli\ArDrone\Navdata\Option;


class Frame {

    private $buffer;
    private $header;
    private $droneState;
    private $sequenceNumber;
    private $visionFlag;
    private $options;
    private $droneStateMasks;

    public function __construct($binaryFrame)
    {
        // from ARDrone_SDK_2_0/ARDroneLib/Soft/Common/config.h
        $this->droneStateMasks = array(
            'flying'                     => (1 << 0),  /*!< FLY MASK => (0) ardrone is landed, (1) ardrone is flying */
            'videoEnabled'               => (1 << 1),  /*!< VIDEO MASK => (0) video disable, (1) video enable */
            'visionEnabled'              => (1 << 2),  /*!< VISION MASK => (0) vision disable, (1) vision enable */
            'controlAlgorithm'           => (1 << 3),  /*!< CONTROL ALGO => (0) euler angles control, (1) angular speed control */
            'altitudeControlAlgorithm'   => (1 << 4),  /*!< ALTITUDE CONTROL ALGO => (0) altitude control inactive (1) altitude control active */
            'startButtonState'           => (1 << 5),  /*!< USER feedback => Start button state */
            'controlCommandAck'          => (1 << 6),  /*!< Control command ACK => (0) None, (1) one received */
            'cameraReady'                => (1 << 7),  /*!< CAMERA MASK => (0) camera not ready, (1) Camera ready */
            'travellingEnabled'          => (1 << 8),  /*!< Travelling mask => (0) disable, (1) enable */
            'usbReady'                   => (1 << 9),  /*!< USB key => (0) usb key not ready, (1) usb key ready */
            'navdataDemo'                => (1 << 10), /*!< Navdata demo => (0) All navdata, (1) only navdata demo */
            'navdataBootstrap'           => (1 << 11), /*!< Navdata bootstrap => (0) options sent in all or demo mode, (1) no navdata options sent */
            'motorProblem'               => (1 << 12), /*!< Motors status => (0) Ok, (1) Motors problem */
            'communicationLost'          => (1 << 13), /*!< Communication Lost => (1) com problem, (0) Com is ok */
            'softwareFault'              => (1 << 14), /*!< Software fault detected - user should land as quick as possible (1) */
            'lowBattery'                 => (1 << 15), /*!< VBat low => (1) too low, (0) Ok */
            'userEmergencyLanding'       => (1 << 16), /*!< User Emergency Landing => (1) User EL is ON, (0) User EL is OFF*/
            'timerElapsed'               => (1 << 17), /*!< Timer elapsed => (1) elapsed, (0) not elapsed */
            'MagnometerNeedsCalibration' => (1 << 18), /*!< Magnetometer calibration state => (0) Ok, no calibration needed, (1) not ok, calibration needed */
            'anglesOutOfRange'           => (1 << 19), /*!< Angles => (0) Ok, (1) out of range */
            'tooMuchWind'                => (1 << 20), /*!< WIND MASK=> (0) ok, (1) Too much wind */
            'ultrasonicSensorDeaf'       => (1 << 21), /*!< Ultrasonic sensor => (0) Ok, (1) deaf */
            'cutoutDetected'             => (1 << 22), /*!< Cutout system detection => (0) Not detected, (1) detected */
            'picVersionNumberOk'         => (1 << 23), /*!< PIC Version number OK => (0) a bad version number, (1) version number is OK */
            'atCodecThreadOn'            => (1 << 24), /*!< ATCodec thread ON => (0) thread OFF (1) thread ON */
            'navdataThreadOn'            => (1 << 25), /*!< Navdata thread ON => (0) thread OFF (1) thread ON */
            'videoThreadOn'              => (1 << 26), /*!< Video thread ON => (0) thread OFF (1) thread ON */
            'acquisitionThreadOn'        => (1 << 27), /*!< Acquisition thread ON => (0) thread OFF (1) thread ON */
            'controlWatchdogDelay'       => (1 << 28), /*!< CTRL watchdog => (1) delay in control execution (> 5ms), (0) control is well scheduled */
            'adcWatchdogDelay'           => (1 << 29), /*!< ADC Watchdog => (1) delay in uart2 dsr (> 5ms), (0) uart2 is good */
            'comWatchdogProblem'         => (1 << 30), /*!< Communication Watchdog => (1) com problem, (0) Com is ok */
            'emergencyLanding'           => (1 << 31)  /*!< Emergency landing : (0) no emergency, (1) emergency */
        );

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
        $this->droneState = $this->buffer->getMask32($this->droneStateMasks);

        // Get sequence number
        $this->sequenceNumber = $this->buffer->getUint32LE();

        // Get vision flag
        $this->visionFlag = $this->buffer->getUint32LE();

    }

    public function getFrameOptions()
    {
        $isChecksum = false;

        while(!$isChecksum) {
            $idOption =  hexdec($this->buffer->getUint16LE());
            $nameOption = Option::$optionIds[$idOption];
            $sizeOption = $this->buffer->getUint16LE();

            if ($nameOption === 'checksum') {
                $isChecksum = true;
                $expectedChecksum = 0;
                $checksum = $this->buffer->getUint32LE();


                $data = $this->buffer->getData();

                for ($i = 0; $i < $this->buffer->getLength() - $sizeOption; $i++) {
                    $expectedChecksum = $expectedChecksum + hexdec(bin2hex($data[$i]));
                }
                $expectedChecksum = dechex($expectedChecksum);


                if ($checksum !== $expectedChecksum) {
                    throw new \Exception('Invalid checksum');
                }

            }

            $option = new Option($idOption, $this->buffer);

            $this->options[$option->getName()] = $option;
        }
    }

    private function checkHeaderIntegrity()
    {
        return true;
//        return ($this->header === 55667788);
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
        $toString .= 'DRONE STATE: ' . print_r($this->getDroneState()) . PHP_EOL;
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
