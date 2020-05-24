<?php
define("RUN_ENV","CLI");

define ('BASE_DIR' ,   dirname(__FILE__)  );
define('APP_NAME', 'admin');

define('DEF_DB_CONN', 'local_test');
define('DOMAIN', 'local.assistant.cn');

define('DOMAIN_URL', "http://" .DOMAIN. "/".APP_NAME);

include BASE_DIR.'/z.class.php';

try{
    $rs = include_once 'z.class.php';
	Z::init();
    Z::runConsoleApp();
}catch (Exception $e){
    var_dump($e->getMessage());exit;
}
