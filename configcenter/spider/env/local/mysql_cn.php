<?php
$db_config =  array(
    'spider'=>array(
        'master'=>array(
            'type'=>'mysql',
            'host'=>'8.142.177.235',
            'user'=>'root_native',
            'pwd'=>'mqzhifu',
            'port'=>'3306',
            'db_name'=>'spider',
            'db_preifx'=>'',
            'char'=>'utf8',
            'conn_persistence'=>true,
            'conn_timeout'=>60,
        ),
        'slave'=>array(
            'type'=>'mysql',
            'host'=>'8.142.177.235',
            'user'=>'root_native',
            'pwd'=>'mqzhifu',
            'port'=>'3306',
            'db_name'=>'spider',
            'db_preifx'=>'',
            'char'=>'utf8',
            'conn_persistence'=>true,
            'conn_timeout'=>60,
        ),
        'master_slave_switch'=>1,
    ),
);
$GLOBALS[APP_NAME]['db_config'] = $db_config;