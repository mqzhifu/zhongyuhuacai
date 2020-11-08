<?php
$key = array(
    //用户基础信息
    'uinfo'=>array('key'=>'uinfo','expire'=>0 ),
    //登陆token
    'token'=>array('key'=>'token','expire'=> 30 * 24 * 60 * 60),
    //记录一场游戏的，房间消息同步数据
    'rsyncMsg'=>array('key'=>'rycMsg','expire'=>0),
    //在线人数
    'onlineUserTotal'=>array('key'=>'onlineUserTotal','expire'=>0),

    //用户报名信息池，与下面 连对 使用
    'userSign'=>array('key'=>'userSign','expire'=>60*60),
    //用户报名匹配池
    'userSignPool'=>array('key'=>'userSignPool'),//这是一个队列结构，没法失效
    //真实用户，已匹配成功，池
    'matchedUserPool'=>array('key'=>'matchedUserPool','expire'=>60*60),
    //守护进程，匹配真实用户，锁
    'matchedUserLock'=>array('key'=>'matchedUserLock','expire'=>10),
    //房间信息
    'room'=>array('key'=>'room','expire'=>60 * 60),//一个小时失效，期间要持久化

//    'AIRobot'=>array('key'=>'AIRobot','expire'=>65),
//    'heartbeat'=>array('key'=>'heartbeat','expire'=>0),
//    'eventMsg'=>array('key'=>'eventMsg','expire'=>0),
//    'gameActionCnt'=>array('key'=>'game_cnt','expire'=>0),
//    'blackip'=>array('key'=>'blackip','expire'=>0),
);
$GLOBALS['rediskey'] = $key;

