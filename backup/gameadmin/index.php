<?php
define ('BASE_DIR' ,dirname (  dirname (   dirname(__FILE__) ) ) ) ;
$str = 'gameadmin';
define('APP_NAME', '' . $str . '');

//运行方式：WEB CLI
define("RUN_ENV","WEB");

include BASE_DIR . "/config/" . APP_NAME . "/env.php";
include BASE_DIR . '/z.class.php';

define("MYSQL_MASTER_SALVE",true);
define("USE_OLD_DB",1);

z::init();
z::runWebApp();

