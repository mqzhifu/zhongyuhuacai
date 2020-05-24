<?php

class RedisOptLib{
    //-----------------token-----------
    static function getToken($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['token']['key'],$uid);
        $redisToken = RedisPHPLib::get($key);
        return $redisToken;
    }

    static function delToken($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['token']['key'],$uid);
        $redisToken = RedisPHPLib::del($key);
        return $redisToken;
    }
    //-----------------token end-----------


    //-----------------在线人数-----------
    static function getOnlineUserTotal(){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['online_user_total']['key']);
        return RedisPHPLib::getServerConnFD()->get($key);
    }

    static function delOnlineUserTotal(){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['online_user_total']['key']);
        return RedisPHPLib::getServerConnFD()->del($key);
    }

    static function decrOnlineUserTotal(){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['online_user_total']['key']);
        return RedisPHPLib::getServerConnFD()->decr($key);
    }

    static function incrOnlineUserTotal(){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['online_user_total']['key']);
        return RedisPHPLib::getServerConnFD()->incr($key);
    }
    //-----------------在线人数----end-------
}