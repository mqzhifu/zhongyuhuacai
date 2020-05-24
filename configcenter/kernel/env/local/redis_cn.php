<?php
$redis  = array(
    'instantplay'=>array(
        'host'=>'127.0.0.1',
        'port'=>6379,
        'ps'=>'',
        'timeout'=>30,
        'database'=>1,
    ),
);

$GLOBALS[KERNEL_NAME]['redis'] = $redis;
return $redis;