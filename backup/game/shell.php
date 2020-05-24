<?php
define ('BASE_DIR' ,dirname (  dirname (   dirname(__FILE__) ) ) );
define('APP_NAME', 'game');
//运行方式：WEB CLI
define("RUN_ENV","CLI");

include BASE_DIR."/config/".APP_NAME."/env.php";
include BASE_DIR . '/z.class.php';

z::init();
z::runConsoleApp();