<?php
$db_config =  array(
    'gameMatch'=>
        array(
            'master'=>array(
                'type'=>'mysql',
                'host'=>'127.0.0.1',
                'user'=>'games',
                'pwd'=>'pu6zMh2CQ55Q',
                'port'=>'3306',
                'db_name'=>'game_match',
                'db_preifx'=>'',
                'char'=>'utf8',
            ),
            'slave'=>array(
                'type'=>'mysql',
                'host'=>'127.0.0.1',
                'user'=>'games',
                'pwd'=>'pu6zMh2CQ55Q',
                'port'=>'3306',
                'db_name'=>'game_match',
                'db_preifx'=>'',
                'char'=>'utf8',
            ),
        ),
);
$GLOBALS['db_config'] = $db_config;
