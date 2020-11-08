<?php

namespace Jy\Component\Event;


use Jy\Contract\Event\EventAbstract;

class Event extends EventAbstract
{
    private static $instance = null;

    public static function getInstance()
    {
        if(empty(self::$instance)){
            self::$instance = new static();
        }

        return self::$instance;
    }

}
