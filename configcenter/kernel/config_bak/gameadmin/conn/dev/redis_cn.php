<?php
$redis  = array(
    'gameMatch'=>array(
        'host'=>'127.0.0.1',
        'port'=>6379,
        'ps'=>'',
        'timeout'=>30,
    ),
);

$GLOBALS['redis'] = $redis;