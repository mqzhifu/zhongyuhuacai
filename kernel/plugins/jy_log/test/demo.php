<?php
namespace Jy\Log\Facades;//<br/>
require '../vendor/autoload.php';//<br/>
#注意：<br/>
#<br/>
#(1)日志内容类型，包括：字符串、数组，不支持对象. 日志内容会被统一转换成json<br/>
#(2)文件模式持久化模式 一定要设置path(基目录)，其余值均有默认值不影响正常使用.<br/>
#
#异常//<br/>
#<br/>
#(1)path(基目录)为空，抛异常<br/>
#(2) 路径没有权限，抛异常<br/>
#(3)设置日志格式错误 抛异常<br/>
#(4)写日志时内容为空抛异常<br/>
#(5)设置hashType错抛异常<br/>
#

#test 目录下有可执行 demo.php.  Loop.php是性能测试//<br/>
#性能.windows系统，单进程 单次请求 写入10000次日志在1.5秒左右，多进程10000次写入，每多一个进程时间会多0.2秒左右//<br/>
#
#设置基路径//<br/>
Log::init("_path",'D:/www/rouchi/jy_log/test/tmp');//<br/>

#开启全局日志 && 开启日志输出到显示器上
Log::init("_totalRecord",1)->setShowScreen(1);//<br/>

#写入一条：调试日志//<br/>
Log::debug("im {0} info",array("debug"));//<br/>

#设置，hash文件，根据<天>//<br/>
Log::init("_hashType","day");//<br/>

#写入一条：警告日志//<br/>
Log::alert("im {0} info",array("alert"));//<br/>

#设置项目，模块//<br/>
Log::init("_projectName","project")->init("_module","user");//<br/>

#写入一条：正常/普通 日志//<br/>
Log::info(array("im {0} info.","hey {1} "),array("alert",'tom'));//<br/>

#自定义日志格式（也可以调整格式内子项的位置）//<br/>
#请求ID|日期时间|client-IP|进程ID|追踪回溯|  文本  (rid|dt||cip|pid|tr )//<br/>
Log::setMsgFormat("pid|dt");//日志中只显示 PID 和日期时间，且把PID提到 日志内容行首列//<br/>

#写入一条：  日志//<br/>
Log::emergency(array("im {0} info.","hey {1} "),array("emergency",'peter'));//<br/>

#自定义 文本格式中  日期时间 子项，注：DATE 函数中的首个参数值//<br/>
Log::setMsgFormatDatetime("H:i:s Y-m-d");//<br/>

#写入一条： 提醒日志//<br/>
Log::notice(array("im {0} info.","hey {1} "),array("notice",'z'));//<br/>

#写入一条： 自定义日志//<br/>
Log::log("super","aaaa");//<br/>




//class a{
//    @Log
//    private $a = null;
//}
//
//AOP("a");
//
//new a();