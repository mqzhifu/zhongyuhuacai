<?php

namespace Jy;


use Jy\Component\Event\Event as E;

// TODO 事件优先级: 触发层级限制, 全局事件、局部事件
class Event
{

    public static function __callStatic($name, $args)
    {
        if (is_callable(E::getInstance(), $name)) {
            throw new \Exception("method name :  ". $name. " not exists");
        }

        return E::getInstance()->$name(...$args);
    }

    public  function __call($name, $args)
    {
        if (is_callable(E::getInstance(), $name)) {
            throw new \Exception("method name :  ". $name. " not exists");
        }

        return E::getInstance()->$name(...$args);
    }




}
