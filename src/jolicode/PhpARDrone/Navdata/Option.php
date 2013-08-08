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

    public function __construct($idOption, $buffer)
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
            case 'time':
                break;
            case 'rawMeasures':
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
