<?php

namespace Jy;

use Jy\Facade\Log;

class Container
{
    private $_singletons = [];

    function __construct(){

    }

    public function get($name)
    {

        if (isset($this->_singletons[$name])) {
            return $this->_singletons[$name];
        }

        // 依赖注入  todo
        // 别名机制 todo
        // classMap todo
        // 配置文件自动注入 todo
        $className = $name;
        if (!class_exists($className)) {
            throw new \Exception('class '. $name .' not exists');
        }

        $single = \Jy\Di::getInstance()->getClassInstance($className);

        $this->_singletons[$name] =  $single;

        return $single;
    }


    public function has($class)
    {
        return isset($this->_singletons[$class]);
    }

}
