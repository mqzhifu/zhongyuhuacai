<?php
class WxLittleCallbackCtrl{
    public $_msgEncodingAESKey = "";
    public $_msgToken = "";

    function __construct(){
        $config = ConfigCenter::get(APP_NAME,"wx");
        $this->_msgEncodingAESKey = $config['little']['msgEncodingAESKey'];
        $this->_msgToken = $config['little']['msgToken'];
    }
    function receive(){
        $rs = $this->checkSignature();
        echo $_GET['echostr'];exit;
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = $this->_msgToken;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if ($tmpStr == $signature ) {
            return true;
        } else {
            return false;
        }
    }
}