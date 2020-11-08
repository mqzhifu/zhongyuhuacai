<?php
define ('BASE_DIR' , dirname (   dirname(__FILE__) ) )  ;
define('APP_NAME', 'instantplay');
//运行方式：WEB CLI
define("RUN_ENV","CLI");


include BASE_DIR . '/kernel/z.class.php';

z::init();
z::runConsoleApp();
