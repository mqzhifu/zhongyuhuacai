<?php
if (! function_exists('env')) {
    function env($key, $default = null)
    {
        return false;
    }
}

if(!function_exists("out")){
    function out($msg ,$br = 1){
        if(is_object($msg) || is_array($msg)){
            $msg = json_encode($msg);
        }
        if($br){
            if (preg_match("/cli/i", php_sapi_name())){
                echo $msg . "\n";
            }else{
                echo $msg . "<br/>";
            }
        }else{
            echo $msg;
        }
    }
}

include_once "../../loader.php";

use php_base\MsgQueue\Tools\RabbitmqApi;

$apiQueueInfo = RabbitmqApi::getInstant()->getNode();