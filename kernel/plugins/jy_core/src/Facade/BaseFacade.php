<?php

namespace Jy\Facade;


abstract class BaseFacade
{
    private static $instances = array();

    abstract public function getFacadeAccessor();

    public static function getProcesser()
    {
        return static::getFacadeAccessor();
    }


    public static function __callStatic($name, $args)
    {
        if (is_callable(static::getProcesser(), $name)) {
            throw new \Exception("method name :  ". $name. " not exists");
        }

        return static::getProcesser()->$name(...$args);
    }
}
