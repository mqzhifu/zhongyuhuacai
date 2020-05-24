<?php
define ('BASE_DIR' ,dirname (  dirname (   dirname(__FILE__) ) ) ) ;
define('APP_NAME', 'game');

//运行方式：WEB CLI
define("RUN_ENV","WEBSOCKET");

//define("OUT_TYPE","PROTOBUF");
//define("IN_TYPE","PROTOBUF");

define("OUT_TYPE","JSON");
define("IN_TYPE","JSON");

define("SERVER_CONF_KEY",'gameMatch');

include BASE_DIR . "/config/" . APP_NAME . "/env.php";
include BASE_DIR . '/z.class.php';

z::init();
z::runWebSocket();

