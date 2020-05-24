<?php
class PushLib{
    static $_DEVICE_IOS = 1;
    static $_DEVICE_ANDROID = 2;

    private $_sdk =null;
    function getTypeDesc(){
        $arr = array(
            1=>array("desc"=>'微信','fileName'=>'wx.php'),
            2=>array("desc"=>'支付宝','fileName'=>'alipay.php','className'=>'Alipay'),
        );

        return $arr;
    }

    function device(){
        return array(
            1=>'安卓',2=>'IOS'
        );
    }

    function __construct($type){
        if(!$type){
            exit("type is null");
        }
        $typeDesc = $this->getTypeDesc($type);
        include_once PLUGIN . $typeDesc['fileName'];
        $this->_sdk = new $typeDesc['className']();
    }

    function oneAll(){

    }

    function oneIOS($title,$content,$deviceToken){
        $this->push(self::$_DEVICE_IOS,$title,$content,$deviceToken);
    }

    function oneAndroid($title,$content,$deviceToken){
        $this->push(self::$_DEVICE_ANDROID,$title,$content,$deviceToken);
    }
//$deviceType:1 安卓 2IOS
    function push($deviceType,$title,$content,$deviceToken){
        $this->_sdk;
    }
}