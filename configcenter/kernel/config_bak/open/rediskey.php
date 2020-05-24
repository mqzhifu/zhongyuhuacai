<?php
$key = array(
    'userinfo'=>array('key'=>'uinfo','expire'=>0 ),
    'blackip'=>array('key'=>'blackip','expire'=>600),
    'oauthtoken'=>array('key'=>'oauthtoken','expire'=>6000),
    'verifierimgcode'=>array('key'=>'verifierimgcode','expire'=>6000),
    'upPScode'=>array('key'=>'upPScode','expire'=>600),
    'jsonTotal'=>array('key'=>'json_total','expire'=>0 ),

    'towerMap'=>array('key'=>'tower_map','expire'=>0 ),
    'jsonTotal'=>array('key'=>'json_total','expire'=>0 ),
    'token'=>array('key'=>'token','expire'=> 30 * 24 * 60 * 60),
    'sms'=>array('key'=>'sendsms','expire'=>0),
    'heartbeat'=>array('key'=>'heartbeat','expire'=>0),
    'eventMsg'=>array('key'=>'eventMsg','expire'=>0),
    'gameActionCnt'=>array('key'=>'game_cnt','expire'=>0),

    'serverMatching'=>array('key'=>'serverMatching','expire'=>12),

    // 四期迭代项目新增游戏列缓存数据,暂不设置有效时间;add by XiaHB time:2019/04/12;
    'gameList'=>array('key'=>'gameList','expire'=>0 ),

);
$GLOBALS['rediskey'] = $key;