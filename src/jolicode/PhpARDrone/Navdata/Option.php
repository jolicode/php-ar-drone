<?php
namespace jolicode\PhpARDrone\Option;

class Option {

    private $buffer;
    private $idOption;
    private $data;

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

    public function __construct($idOption, $binary)
    {
        $this->buffer = new Buffer($binary);
        $this->idOption = $idOption;
        $this->data = array();

        $this->processOption();
    }

    private function processOption()
    {
        switch (Option::$optionIds[$this->idOption]) {
            case 'demo':
                break;
            case 'time':
                break;
            case 'rawMeasures':
                break;
        }
    }
}
