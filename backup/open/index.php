<?php
define ('BASE_DIR' ,dirname (  dirname (   dirname(__FILE__) ) ) ) ;
$str = 'open';
define('APP_NAME', '' . $str . '');


//运行方式：WEB CLI
define("RUN_ENV","WEB");

include BASE_DIR . "/config/" . APP_NAME . "/env.php";
include BASE_DIR . '/z.class.php';

define("MYSQL_MASTER_SALVE",false);
define("USE_OLD_DB",1);

define("IS_NAME",'instantplay');

z::init();
z::runWebApp();

