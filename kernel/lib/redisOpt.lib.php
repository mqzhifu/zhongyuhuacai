<?php

class RedisOptLib{
//    static $_instance = null;
    static $_redis = null;
    static $_kernelRedisKeyConfig = null;
    static $_appRedisKeyConfig = null;

    static $_kernelKeyPre = KERNEL_NAME;
    static $_appKeyPre = APP_NAME;
//    static function inc($redisObj){
//        if(self::$_instance){
//            return self::$_instance;
//        }
//
//        if(!$redisObj){
//            exit("new RedisOptLib,redisObj is null");
//        }
//
//        self::$_instance = new self();
//
//        return self::$_instance;
//    }

    static function init(RedisPHPLib $redisObj){
        self::$_redis = $redisObj;
        self::$_kernelRedisKeyConfig = ConfigCenter::get(KERNEL_NAME,"rediskey");
        self::$_appRedisKeyConfig = ConfigCenter::get(APP_NAME,"rediskey");
    }
    static function plusKernelPre($key){
        return self::$_kernelKeyPre . "_".$key;
    }

    static function plusAppPre($key){
        return self::$_appKeyPre . "_".$key;
    }

    //-----------------kernel
    // token-----------
    static function getToken($uid){
        $key = self::plusKernelPre( self::$_kernelRedisKeyConfig['token']['key'] );
        $key = self::$_redis->getAppKeyById($key,$uid);
        $redisToken = self::$_redis->get($key);
        return $redisToken;
    }

    static function setToken($uid,$token){
        $key = self::plusKernelPre( self::$_kernelRedisKeyConfig['token']['key'] );
        $key = self::$_redis->getAppKeyById($key,$uid);

        $redisToken = self::$_redis->set($key,$token,self::$_kernelRedisKeyConfig['token']['expire']);
        return $redisToken;

//        $kernelRedisObj = new RedisPHPLib($GLOBALS[KERNEL_NAME]['redis']['instantplay']);
//        $key = $kernelRedisObj->getAppKeyById($redusKey['token']['key'],$uid);
//        LogLib::inc()->debug("set token redis:".$key . " ".$redusKey['token']['expire']);
//        $kernelRedisObj->set($key,$token,$redusKey['token']['expire']);
//        $redusKey = ConfigCenter::get(APP_NAME,"rediskey");
    }



    static function delToken($uid){
        $key = RedisPHPLib::getAppKeyById( self::$_kernelRedisKeyConfig['token']['key']  ,$uid);
        $redisToken = RedisPHPLib::del($key);
        return $redisToken;
    }
    // request id-----------
    static function getRequestId(){
        $key = self::plusKernelPre( self::$_kernelRedisKeyConfig['request_id']['key'] );
        $script = "redis.call('incr', KEYS[1]) ; return  redis.call('get', KEYS[1])";
        $execRs = self::$_redis->evalLua($script,array($key),1);
        return $execRs;
    }

//    function getRequestId(){
//        $key = ContainerLib::get("kernelRedisObj")->getAppKeyById($GLOBALS[KERNEL_NAME]['rediskey']['request_id']['key'], "" , KERNEL_NAME);
//        $script = "redis.call('incr', KEYS[1]) ; return  redis.call('get', KEYS[1])";
//        $execRs = ContainerLib::get("kernelRedisObj")->eval($script,array($key),1);
//
//        return $execRs;
//    }

    //-----------------kernel end-----------


    //-----------------在线人数-----------
//    static function getOnlineUserTotal(){
//        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['online_user_total']['key']);
//        return RedisPHPLib::getServerConnFD()->get($key);
//    }
//
//    static function delOnlineUserTotal(){
//        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['online_user_total']['key']);
//        return RedisPHPLib::getServerConnFD()->del($key);
//    }
//
//    static function decrOnlineUserTotal(){
//        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['online_user_total']['key']);
//        return RedisPHPLib::getServerConnFD()->decr($key);
//    }
//
//    static function incrOnlineUserTotal(){
//        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['online_user_total']['key']);
//        return RedisPHPLib::getServerConnFD()->incr($key);
//    }
    //-----------------在线人数----end-------

    static function setBaiduIpParser($ip,$address){
        $key = self::plusAppPre( self::$_appRedisKeyConfig['baiduIPMap']['key'] );
        $key = self::$_redis->getAppKeyById($key,$ip);
        $isJson = 0;
        if(arrKeyIssetAndExist(self::$_appRedisKeyConfig['baiduIPMap'],'isJson')){
            $isJson = 1;
        }
        $rs = self::$_redis->set($key,$address,self::$_appRedisKeyConfig['baiduIPMap']['expire'],$isJson);
        return $rs;
    }

    static function getBaiduIpParser($ip){
        $key = self::plusAppPre( self::$_appRedisKeyConfig['baiduIPMap']['key'] );
        $key = self::$_redis->getAppKeyById($key,$ip);
        $isJson = 0;
        if(arrKeyIssetAndExist(self::$_appRedisKeyConfig['baiduIPMap'],'isJson')){
            $isJson = 1;
        }
        $rs = self::$_redis->get($key,$isJson);
        return $rs;
    }
}