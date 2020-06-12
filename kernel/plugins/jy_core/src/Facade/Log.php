<?php

namespace Jy\Facade;

use Jy\Log\Facades\Log as LG;

/**
 * @method static int emergency($message ,array $context = array())
 * @method static int alert($message ,array $context = array())
 * @method static int critical($message ,array $context = array())
 * @method static int error($message ,array $context = array())
 * @method static int warning($message ,array $context = array())
 * @method static int notice($message ,array $context = array())
 * @method static int info($message ,array $context = array())
 * @method static int debug($message ,array $context = array())
 * @method static int log($level,$message ,array $context = array())
 */
class Log
{

    public static function getInstance()
    {
        if (!defined('ROUCHI_LOG_PATH')) throw new \Exception("log path config const : ROUCHI_LOG_PATH  not exists");
        LG::getInstance()->init("_buffMem",1);
        return LG::getInstance()->init('_path', ROUCHI_LOG_PATH);
    }

    public static function __callStatic($name, $args)
    {
        if (is_callable(static::getInstance(), $name)) {
            throw new \Exception("method name :  ". $name. " not exists");
        }

        $arr = debug_backtrace();

        $calledInfo = array("file_name"=>$arr[0]['file'],'function_name'=>$arr[0]['function'],'line'=>$arr[0]['line']);
        static::getInstance()->setCalledInfo($calledInfo);


        $sysInfo = \Jy\Common\RequestContext\RequestContext::get('sys_data');
        if($sysInfo){
            static::getInstance()->setSysBaseInfo($sysInfo);
        }

        return static::getInstance()->$name(...$args);
    }

}
