<?php
namespace Jy\Util;
class CheckFramework{
    static $inc = null;
    static function getInstance(){
        if(self::$inc){
            return self::$inc;
        }
        $inc = new self();
        self::$inc = $inc;
        return $inc;
    }
    function checkExt(){
        $arr = array('gd','curl','pdo','mbstring',"mysqli",'openssl','redis');
        foreach ($arr as $k=>$v) {
            if(!extension_loaded($v)){
                throw new \Exception("check ext err: no include $v  . include list:". json_encode($arr),1100001);
            }
        }
        return true;
    }

    function checkConst(){
        $constList = array('ROUCHI_ROOT_PATH','ROUCHI_CONF_PATH','ROUCHI_LOG_PATH','ROUCHI_APP_NAME');
        foreach ($constList as $k=>$v) {
            if(!defined($v)){
                throw new \Exception("check const err: no include $v  . include list:". json_encode($constList),1100002);
            }
        }

        return true;
    }

    function checkPHPVersion(){
        $version = substr(PHP_VERSION,0,3);
        if($version < "7.2"){
            throw new \Exception("PHP VERSION last:7.2.0",1100003);
        }
    }

    function check(){
        $this->checkExt();
        $this->checkConst();
        $this->checkPHPVersion();
    }
}