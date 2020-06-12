<?php

namespace Jy\Facade;

use Jy\Config\Facade\Config as CF;

class Config
{

    private static $instances = array();

    public static function getInstance()
    {
        return CF::getInstance();
    }

    public static function __callStatic($name, $args)
    {
        if (is_callable(static::getInstance(), $name)) {
            throw new \Exception("method name :  ". $name. " not exists");
        }

        return static::getInstance()->$name(...$args);
    }

}
