<?php
$redis  = array(
    'sanguo'=>array(
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

    'game_match'=>array(
        'host'=>'127.0.0.1',
        'port'=>6379,
        'ps'=>'',
        'timeout'=>30,
    ),
);

$GLOBALS['redis'] = $redis;