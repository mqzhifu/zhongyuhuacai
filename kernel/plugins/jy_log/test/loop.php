<?php
namespace Jy\Log\Facades;//<br/>
require '../vendor/autoload.php';//<br/>

#设置基路径//<br/>
Log::getInstance()->init("_path",'D:/www/rouchi/jy_log/test/tmp');//<br/>

#开启全局日志，开始日志输出到显示器上
//Log::getInstance()->init("_totalRecord",1)->setShowScreen(0);//<br/>

$t1=microtime(true);


#写入一条：调试日志//<br/>
$end = 100;
for ($i=0 ; $i <$end ; $i++) {
    Log::getInstance()->debug("im {0} info",array("debug"));//<br/>
}


$t2=microtime(true);
$eTime = $t2 - $t1;

var_dump($eTime);