<?php

namespace Jy\Facade;

use Jy\Config\Facade\Config;
use Jy\Redis\Facade\Redis as RD;

class Redis
{

    public static function getInstance($model = '', $name = '')
    {
        return RD::getInstance($model, $name);
    }

    public static function __callStatic($name, $args)
    {
        if (is_callable(static::getInstance(), $name)) {
            throw new \Exception("method name :  ". $name. " not exists");
        }

        return static::getInstance()->$name(...$args);
    }

}
