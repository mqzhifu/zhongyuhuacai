<?php
$memConfig = array(
    array('h'=>'127.0.0.1','p'=>11211,'weight'=>null),
    array('h'=>'127.0.0.1','p'=>11212,'weight'=>null),
    array('h'=>'127.0.0.1','p'=>11213,'weight'=>null),
    array('h'=>'127.0.0.1','p'=>11214,'weight'=>null),
    array('h'=>'127.0.0.1','p'=>11215,'weight'=>null),
    array('h'=>'127.0.0.1','p'=>11216,'weight'=>null),
);


$GLOBALS[KERNEL_NAME]['memcache'] = $memConfig;