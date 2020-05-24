<?php
$redis  = array(
    'local_test'=>array(
        'host'=>'127.0.0.1',
        'port'=>6379,
        'ps'=>'',
        'timeout'=>30,
    ),
    'def_conn'=>'local_test',
);

$GLOBALS['redis'] = $redis;