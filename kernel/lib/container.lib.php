<?php
class ContainerLib{
    static $_pool = null;
    static function set($key,$value){
        self::$_pool[$key] = $value;
    }

    static function get($key){
        if(arrKeyIssetAndExist(self::$_pool,$key)){
            return self::$_pool[$key] ;
        }

        return false;
    }
}