<?php
$key = array(
    'request_id'=>array('key'=>'r_id','expire'=> 0),
    'black_ip'=>array('key'=>'black_ip','expire'=> 0),
    //ip访问记录(有序集合)
    'ip_access_set'=>array('key'=>'ip_access_set','expire'=> 24*60*60),
    //登陆token
    'token'=>array('key'=>'token','expire'=> 30 * 24 * 60 * 60),
);
$GLOBALS[KERNEL_NAME]['rediskey'] = $key;

