<?php

namespace Jy\Contract\Container;


abstract class ContainerAbstract implements ContainerInterface
{

    /*
     * 获取一个对象实例
     *
     */
    abstract public function get($name);

    /*
     *
     * 判断一个类是否已经实例化
     *
     */
    abstract public function has($name);

    //abstract public function set();
    //abstract public function built();
}


interface ContainerInterface
{
     public function get();

     public function has();

     //public function set();
     //public function built();
}
