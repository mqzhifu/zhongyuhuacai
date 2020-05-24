<?php
$redis  = array(
    'instantplay'=>array(
        'host'=>'127.0.0.1',
        'port'=>6370,
        'ps'=>'',
        'timeout'=>30,
        'database'=>1,
    ),
);

$GLOBALS[APP_NAME]['redis'] = $redis;