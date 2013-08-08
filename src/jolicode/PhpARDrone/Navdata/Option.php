<?php
namespace jolicode\PhpARDrone\Navdata;

use jolicode\PhpARDrone\Buffer\Buffer;

class Option {

    private $buffer;
    private $idOption;
    private $data;

    /**
     * @property array
     */
    public static $optionIds = array(
        0     => 'demo',
        1     => 'time',
        2     => 'rawMeasures',
        3     => 'physMeasures',
        4     => 'gyrosOffsets',
        5     => 'eulerAngles',
        6     => 'references',
        7     => 'trims',
        8     => 'rcReferences',
        9     => 'pwm',
        10    => 'altitude',
        11    => 'visionRaw',
        12    => 'visionOf',
        13    => 'vision',
        14    => 'visionPerf',
        15    => 'trackersSend',
        16    => 'visionDetect',
        17    => 'watchdog',
        18    => 'adcDataFrame',
        19    => 'videoStream',
        20    => 'games',
        21    => 'pressureRaw',
        22    => 'magneto',
        23    => 'windSpeed',
        24    => 'kalmanPressure',
        25    => 'hdvideoStream',
        26    => 'wifi',
        27    => 'zimmu3000',
        65535 => 'checksum'
    );

    public static $controlState = array(
        0 => 'CTRL_DEFAULT',
        1 => 'CTRL_INIT',
        2 => 'CTRL_LANDED',
        3 => 'CTRL_FLYING',
        4 => 'CTRL_HOVERING',
        5 => 'CTRL_TEST',
        6 => 'CTRL_TRANS_TAKEOFF',
        7 => 'CTRL_TRANS_GOTOFIX',
        8 => 'CTRL_TRANS_LANDING',
        9 => 'CTRL_TRANS_LOOPING'
    );

    public static $flyState = array(
        0 => 'FLYING_OK',
        1 => 'FLYING_LOST_ALT',
        2 => 'FLYING_LOST_ALT_GO_DOWN',
        3 => 'FLYING_ALT_OUT_ZONE',
        4 => 'FLYING_COMBINED_YAW',
        5 => 'FLYING_BRAKE',
        6 => 'FLYING_NO_VISION'
    );

    public function __construct($idOption, Buffer $buffer)
    {
        $this->buffer = $buffer;
        $this->idOption = $idOption;
        $this->data = array();

        $this->processOption();
    }

    private function processOption()
    {
        // Structures from navdata_common.h
        switch (Option::$optionIds[hexdec($this->idOption)]) {
            case 'demo':
                $this->data = $this->getDemoOptionData();
                break;
            case 'visionDetect':
                $this->data = $this->getVisionDetectData();
                break;
            case 'pwm':
                $this->data = $this->getPwmData();
                break;
            case 'physMeasures':
                $this->data = $this->getPhysMeasuresData();
                break;
        }
    }

    private function getDemoOptionData() {
        $flyState          = Option::$flyState[$this->buffer->getUint16LE()];
        $controlState      = Option::$controlState[$this->buffer->getUint16LE()];
        $batteryPercentage = $this->buffer->getUint32LE();
        $theta             = $this->buffer->getFloat32() / 1000;  // [mdeg]
        $phi               = $this->buffer->getFloat32() / 1000;  // [mdeg]
        $psi               = $this->buffer->getFloat32() / 1000;  // [mdeg]
        $altitude          = $this->buffer->getUint32LE() / 1000; // [mm]
        $velocity          = $this->buffer->getVector31();        // [mm/s]
        $frameIndex        = $this->buffer->getUint32LE();

        $detection = array(
            'camera' => array(
                'rotation' => $this->buffer->getMatrix33(),
                'translation' => $this->buffer->getVector31()
            ),
            'tagIndex' => $this->buffer->getUint32LE()
        );

        $detection['camera']['type'] = $this->buffer->getUint32LE();

        $drone = array(
            'camera' => array(
                'rotation' => $this->buffer->getMatrix33(),
                'translation' => $this->buffer->getVector31()
            ),
        );

        $rotation = array(
            'frontBack' => $theta,
            'pitch' => $theta,
            'theta' => $theta,
            'y' => $theta,
            'leftRight' => $phi,
            'roll' => $phi,
            'phi' => $phi,
            'x' => $phi,
            'clockwise' => $psi,
            'yaw' => $psi,
            'psi' => $psi,
            'z' => $psi
        );

        $data = array(
            'controlState' => $controlState,
            'flyState' => $flyState,
            'batteryPercentage' => hexdec($batteryPercentage),
            'rotation' => $rotation,
            'frontBackDegrees' => $theta,
            'leftRightDegrees' => $phi,
            'clockwiseDegrees' => $psi,
            'altitude' => $altitude,
            'altitudeMeters' => $altitude,
            'velocity' => $velocity,
            'xVelocity' => $velocity['x'],
            'yVelocity' => $velocity['y'],
            'zVelocity' => $velocity['z'],
            'frameIndex' => $frameIndex,
            'detection' => $detection,
            'drone' => $drone
        );

        return $data;
    }

    private function getVisionDetectData()
    {
        return array(
            'nbDetected'        => $this->buffer->getUint32LE(),
            'type'              => $this->timesMap(4, 'uint32LE'),
            'xc'                => $this->timesMap(4, 'uint32LE'),
            'yc'                => $this->timesMap(4, 'uint32LE'),
            'width'             => $this->timesMap(4, 'uint32LE'),
            'height'            => $this->timesMap(4, 'uint32LE'),
            'dist'              => $this->timesMap(4, 'uint32LE'),
            'orientationAngle'  => $this->timesMap(4, 'float32'),
            'rotation'          => $this->timesMap(4, 'matrix33'),
            'translation'       => $this->timesMap(4, 'vector31'),
            'cameraSource'      => $this->timesMap(4, 'uint32LE')
        );
    }

    private function getPwmData()
    {
        return array(
            'motor'            => $this->timesMap(4, 'uint8'),
            'satMotors'        => $this->timesMap(4, 'uint8'),
            'gazFeedForward'   => $this->buffer->getFloat32(),
            'gazAltitude'      => $this->buffer->getFloat32(),
            'altitudeIntegral' => $this->buffer->getFloat32(),
            'vzRef'            => $this->buffer->getFloat32(),
            'uPitch'           => $this->buffer->getInt32(),
            'uRoll'            => $this->buffer->getInt32(),
            'uYaw'             => $this->buffer->getInt32(),
            'yawUI'            => $this->buffer->getFloat32(),
            'uPitchPlanif'     => $this->buffer->getInt32(),
            'uRollPlanif'      => $this->buffer->getInt32(),
            'uYawPlanif'       => $this->buffer->getInt32(),
            'uGazPlanif'       => $this->buffer->getFloat32(),
            'motorCurrents'    => $this->timesMap(4, 'uint16LE'),
            'altitudeProp'     => $this->buffer->getFloat32(),
            'altitudeDer'      => $this->buffer->getFloat32()
        );
    }

    private function getPhysMeasuresData() {
        return array(
            'temperature'    => array(
                'accelerometer' => $this->buffer->getFloat32(),
                'gyroscope' => $this->buffer->getUint16LE()
            ),
            'accelerometers' => $this->buffer->getVector31(),
            'gyroscopes'     => $this->buffer->getVector31(),
            'alim3V3'        => $this->buffer->getUint32LE(),
            'vrefEpson'      => $this->buffer->getUint32LE(),
            'vrefIDG'        => $this->buffer->getUint32LE()
        );
    }

    private function timesMap($n, $type) {
        $data = array();

        for($i = 0; $i < $n; $i++) {
            $value = null;

            if ($type === 'uint32LE') {
                $value = $this->buffer->getUint32LE();
            } else if ($type === 'uint16LE') {
                $value = $this->buffer->getUint16LE();
            } else if ($type === 'float32') {
                $value = $this->buffer->getFloat32();
            } else if ($type === 'matrix33') {
                $value = $this->buffer->getMatrix33();
            } else if ($type === 'vector31') {
                $value = $this->buffer->getVector31();
            } else if ($type === 'uint8') {
                $value = $this->buffer->getUint8();
            }

            array_push($data, $value);
        }

    }
    public function getOptionName() {
        return Option::$optionIds[hexdec($this->idOption)];
    }

    /**
     * @return \jolicode\PhpARDrone\Buffer\Buffer
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
