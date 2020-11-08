<?php
$redis  = array(
    'zhongyuhuacai'=>array(
        'host'=>'127.0.0.1',
        'port'=>6379,
        'ps'=>'',
        'conn_timeout'=>30,
        'database'=>1,
        'conn_persistence'=>1,
        'db_number'=>0,
    ),
);

//$GLOBALS[KERNEL_NAME]['redis'] = $redis;
return $redis;