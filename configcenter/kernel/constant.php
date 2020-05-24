<?php
//框架版本
define('VERSION','1.0');
//路径中的返斜杠:/
define ('DS', "/");
//框架名称
define("KERNEL_NAME","kernel");
//框架目录
define ('KERNEL_DIR', BASE_DIR .DS .KERNEL_NAME);
//框架配置目录
//define ('KERNEL_CONFIG',KERNEL_DIR .DS ."config");
//总（配置）目录
//define("CONFIG_DIR",KERNEL_DIR.DS."config");
define("STORAGE_DIR",BASE_DIR.DS."storage");
define("FUNC_DIR",KERNEL_DIR.DS."functions");


//===========控制器==================
defined('C_EXT') or define('C_EXT', '.ctrl.php');//文件的后缀
defined('C_DIR_NAME') or define('C_DIR_NAME', 'ctrl');//文件夹名
defined('C_CLASS') or define('C_CLASS', 'Ctrl');//文件的类名后缀
//===========控制器==================
//===========模型层==================
defined('M_EXT') or define('M_EXT', '.model.php');
defined('M_DIR_NAME') or define('M_DIR_NAME', 'model');
defined('M_CLASS') or define('M_CLASS', 'Model');
//===========模型层==================
//===========类库==================
defined('LIB_DIR_NAME') or define('LIB_DIR_NAME','lib');
defined('LIB_EXT') or define('LIB_EXT','.lib.php');
defined('LIB_CLASS') or define('LIB_CLASS','Lib');
//===========SERVICE-库==================
defined('S_EXT') or define('S_EXT','.service.php');
defined('S_DIR_NAME') or define('S_DIR_NAME','service');
defined('S_CLASS') or define('S_CLASS','Service');


//调试模式,0:关闭,1:全开，2半开
defined('DEBUG') or define('DEBUG',0);

//默认30秒为超时,shell模式下 不限制
defined('TIME_LIMIT') or define('TIME_LIMIT',30);
//时区
defined('TIME_ZONE') or define('TIME_ZONE', 'Asia/shanghai' ) ;
ini_set("date.timezone",TIME_ZONE);
//定义默认的  控制器名称  与  事件名称
defined('DEF_CTRL') or define('DEF_CTRL','index');
defined('DEF_AC') or define('DEF_AC','index');
//插件
defined('PLUGIN') or define(  'PLUGIN',KERNEL_DIR . '/plugins/');


//总日志目录
define('LOG_PATH', STORAGE_DIR.DS."log");
//文件上传总路径
define('FILE_UPLOAD_DIR', STATIC_DIR . '/upload');

//session存储类型
defined('SESS_TYPE') or define('SESS_TYPE','FILE');
//session 失效时间
defined('SESS_EXPIRE') or define('SESS_EXPIRE',60 * 60 * 3);
//session 存储位置
defined('SESS_STORE_DIR') or define('SESS_STORE_DIR',STORAGE_DIR.DS."session");
//app session 存储位置
defined('APP_SESS_STORE_DIR') or define('APP_SESS_STORE_DIR',SESS_STORE_DIR.DS.APP_NAME);
//语言包
defined('LANG') or define('LANG',"cn");


define("KERNEL_SMARTY_COMPILE_DIR",STORAGE_DIR.DS."view_c".DS.KERNEL_NAME.DS );

//项目目录
define ('APP_DIR', BASE_DIR .DS . APP_NAME);
//项目配置文件目录
//define("APP_CONFIG_DIR",APP_DIR.DS."config");
//APP 生成 模板 编译后的文件位置 s
define("APP_SMARTY_COMPILE_DIR",STORAGE_DIR.DS."view_c".DS.APP_NAME.DS );


//项目-文件上传路径
define('APP_FILE_UPLOAD_DIR', FILE_UPLOAD_DIR . DS .APP_NAME);


//项目-文件上传路径
define('APP_SHELL_DIR', APP_DIR . DS . "shell");
////头像-相对路径 不分应用
//defined('USER_AVATAR_IMG_VIRTUAL') or define('USER_AVATAR_IMG_VIRTUAL', 'avatar/user/');
////头像上传路径
//defined('USER_AVATAR_IMG_UPLOAD') or define('USER_AVATAR_IMG_UPLOAD', BASE_DIR ."/www/" . USER_AVATAR_IMG_VIRTUAL);

return 1;
