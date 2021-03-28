<?php
define("DEBUG",1);

define ('BASE_DIR' , dirname (   dirname(__FILE__) ) )  ;
define('APP_NAME', 'house');
define("RUN_ENV","CLI");

define("IS_ADMIN",0);
define("OUT_TYPE",'null');

define("STATIC_DIR",BASE_DIR."/static");

define("DB_CONN_DEFAULT",'house');

include BASE_DIR . '/kernel/z.class.php';

z::init();
z::runConsoleApp();

