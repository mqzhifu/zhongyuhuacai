<?php
namespace php_base\MsgQueue\Exception;
class RetryException extends \Exception{
    private $retry = null;
    function setRetry($retry){
        $this->retry = $retry;
    }

    function getRetry(){
        return $this->retry;
    }
}