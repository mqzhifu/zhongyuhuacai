<?php
namespace Jy\Common\Valid\Facades;

class Valid {
    private static $instance = null;
//    private static $provide = "file";

    public static function getInstance(){
        if(self::$instance){
            return self::$instance;
        }
//        if(self::$provide == 'file'){
//            $self =  new \Jy\Log\Log\File();
//        }
        $self = new \Jy\Common\Valid\Valid\Valid();

        self::$instance = $self;
        return self::$instance;
    }

    public static function __callStatic($name, $args)
    {
        if (is_callable(static::getInstance(), $name)) {
            throw new \Exception("method name :  ". $name. " not exists");
        }

        return static::getInstance()->$name(...$args);
    }

}