<?php
define("DEBUG",1);

define ('BASE_DIR' , dirname (   dirname(__FILE__) ) )  ;
define('APP_NAME', 'api');
define("RUN_ENV","WEB");

define("IS_ADMIN",0);
define("OUT_TYPE",'json');

define("STATIC_DIR",BASE_DIR."/static");

include BASE_DIR . '/kernel/z.class.php';
z::run();

