<?php

/**
 * Class UserAgent
 */
class UserAgent
{


    const DEVICE_TYPE_IPHONE = 'iPhone';
    const DEVICE_TYPE_IPAD = 'iPad';
    const DEVICE_TYPE_ANDROID = 'android';
    const DEVICE_TYPE_PC = 'pc';


    private $_ip;
    private $_city;
    private $_cityID;
    private $_device;

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->_ip;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->_city;
    }

    /**
     * @return mixed
     */
    public function getCityID()
    {
        return $this->_cityID;
    }


    /**
     * @return mixed
     */
    public function getDevice()
    {
        return $this->_device;
    }

    /**
     * DownloadQR constructor.
     */
    public function __construct()
    {
        $this->_setIPArea();
        $this->_setDeviceType();

    }

    private function _setIP()
    {
        if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        else if (!empty($_SERVER["HTTP_CLIENT_IP"])) $ip = $_SERVER["HTTP_CLIENT_IP"];
        else if ($_SERVER["REMOTE_ADDR"]) $ip = $_SERVER["REMOTE_ADDR"];
        else if (getenv("HTTP_X_FORWARDED_FOR")) $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("HTTP_CLIENT_IP")) $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("REMOTE_ADDR")) $ip = getenv("REMOTE_ADDR");
        else throw new Exception("IP Unknown", 1);
        $this->_ip = $ip;

        if (isset($_GET['ip'])) {
            $this->_ip = trim($_GET['ip']);
        }
    }

    private function _setIPArea()
    {
        $this->_setIP();
        $response = file_get_contents('http://ip.taobao.com/service/getIpInfo.php?ip=' . $this->_ip);
        $result = json_decode($response, true);
        if ($result['code'] != 0) {
            throw new Exception($result['data'], 2);
        }
        $this->_city = $result['data']['city'];
        $this->_cityID = $result['data']['city_id'];
    }

    private function _setDeviceType()
    {
        $this->_device = self::DEVICE_TYPE_PC;
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match("/iPhone/", $userAgent)) {
            $this->_device = self::DEVICE_TYPE_IPHONE;
        } elseif (preg_match("/iPad/i", $userAgent)) {
            $this->_device = self::DEVICE_TYPE_IPAD;
        } elseif (preg_match("/android/i", $userAgent)) {
            $this->_device = self::DEVICE_TYPE_ANDROID;
        } else {
            $this->_device = self::DEVICE_TYPE_PC;
        }
    }

}


//Set json format and utf-8 encoding
header('Content-Type: application/json; charset=utf-8');
//Cache control, prevent cache result
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$result = array();
try {
    $instance = new UserAgent;
    $result['cid'] = $instance->getCityID();
    $result['cname'] = $instance->getCity();
    $result['cip'] = $instance->getIp();
    $result['cdevice'] = $instance->getDevice();
} catch (Exception $e) {

}
echo "var returnCitySN = " . json_encode($result) . ";";


