<?php
$db_config =  array(
    'instantplay'=>array(
        'master'=>array(
            'type'=>'mysql',
            'host'=>'127.0.0.1',
            'user'=>'root',
            'pwd'=>'mqzhifu',
            'port'=>'3306',
            'db_name'=>'instantplay',
            'db_preifx'=>'',
            'char'=>'utf8',
            'conn_persistence'=>true,
            'conn_timeout'=>60,
        ),
        'slave'=>array(
            'type'=>'mysql',
            'host'=>'127.0.0.1',
            'user'=>'root',
            'pwd'=>'mqzhifu',
            'port'=>'3306',
            'db_name'=>'instantplay',
            'db_preifx'=>'',
            'char'=>'utf8',
            'conn_persistence'=>true,
            'conn_timeout'=>60,
        ),
        'master_slave_switch'=>1,
    ),
);
$GLOBALS[KERNEL_NAME]['db_config'] = $db_config;
return $db_config;
