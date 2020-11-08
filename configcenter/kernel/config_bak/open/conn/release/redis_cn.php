<?php
$redis  = array(
    'sanguo'=>array(
        'host'=>'10.10.7.6',
        'port'=>6379,
        'ps'=>'',
        'timeout'=>30,
    ),

    'sdk'=>array(
        'host'=>'10.10.7.138',
        'port'=>6379,
        'ps'=>'',
        'timeout'=>30,
    ),
);

$GLOBALS['redis'] = $redis;