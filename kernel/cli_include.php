<?php
define("RUN_ENV", "CLI");

include BASE_DIR . '/z.class.php';
try {
    $rs = include_once 'z.class.php';
    Z::init();
} catch (Exception $e) {
    var_dump($e->getMessage());
    exit;
}
 
  
