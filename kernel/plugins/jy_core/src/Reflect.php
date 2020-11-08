<?php
namespace Jy;

class Reflect
{

    private $className;
    //..
    public function setClassName(string $className)
    {
        $this->className = $className;
        return $this;
    }


    public function resolveClass(string $className, string $action, string $type = "method")
    {
        $this->className = $className;

        $doc_param = \Jy\App::$app->annotation->resolve($this->className, $action, $type)->getParameters();
        return $doc_param;
    }
}
