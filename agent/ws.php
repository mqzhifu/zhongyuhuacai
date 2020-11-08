<?php
define ('BASE_DIR' ,dirname (  dirname (   dirname(__FILE__) ) ) ) ;
define('APP_NAME', 'instantplay');

//运行方式：WEB CLI
define("RUN_ENV","WEBSOCKET");

define("OUT_TYPE","JSON");
define("IN_TYPE","JSON");

define("SERVER_CONF_KEY","ws");

include BASE_DIR."/config/".APP_NAME."/env.php";
include BASE_DIR . '/z.class.php';

define("MYSQL_MASTER_SALVE",false);
define("USE_OLD_DB",1);

z::init();
z::runWebSocket();

