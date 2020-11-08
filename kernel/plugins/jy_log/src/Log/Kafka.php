<?php
namespace Jy\Log\Log;

use Jy\Log\Contract\Main;

class Kafka extends Main {

    private $_host = "127.0.0.1";
    private $_port = "3333";

//    推送3方
    function flush($info){

    }

    //调试信息
    function emergency($message ,array $context = array()){
        $this->_level = "emergency";

        $this->initPath("emergency");
        $info =  parent::emergency($message,$context);

        $this->flush($info);
    }
    //框架级日志
    function alert($message ,array $context = array()){
        $this->_level = "alert";

        $this->initPath("alert");
        $info =  parent::alert($message,$context);
        $this->flush($info);
    }
    //日常记录
    function critical($message ,array $context = array()){
        $this->_level = "critical";

        $this->initPath("critical");
        $info =  parent::critical($message,$context);
        $this->flush($info);
    }
    //警告
    function error($message ,array $context = array()){
        $this->_level = "error";

        $this->initPath("error");
        $info =  parent::error($message,$context);
        $this->flush($info);
    }
    //致命
    function warning($message ,array $context = array()){
        $this->_level = "warning";

        $this->initPath("warning");
        $info =  parent::warning($message,$context);
        $this->flush($info);
    }
    //以上均不满足，自定义
    function notice($message ,array $context = array()){
        $this->_level = "notice";

        $this->initPath("notice");
        $info =  parent::notice($message,$context);
        $this->flush($info);
    }

    function info($message ,array $context = array()){
        $this->_level = "info";

        $this->initPath("info");
        $info =  parent::info($message,$context);
        $this->flush($info);
    }

    function debug($message ,array $context = array()){
        $this->_level = "debug";

        $this->initPath("debug");
        $info =  parent::debug($message,$context);
        $this->flush($info);
    }

    function log($level,$message ,array $context = array()){
        $this->_level = "log";

        $this->initPath("log");
        $info =  parent::log($level,$message,$context);
        $this->flush($info);
    }

}