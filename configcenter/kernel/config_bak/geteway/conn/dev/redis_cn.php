<?php
$redis  = array(
    'geteway'=>array(
        'host'=>'127.0.0.1',
        'port'=>6379,
        'ps'=>'',
        'timeout'=>30,
    ),

    'sdk'=>array(
        'host'=>'127.0.0.1',
        'port'=>6379,
        'ps'=>'',
        'timeout'=>30,
    ),
);

$GLOBALS['redis'] = $redis;