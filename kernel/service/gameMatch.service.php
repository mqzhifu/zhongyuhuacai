<?php
class GameMatchService{
    public $matchWait =6;
    public $gameTime = 60;//游戏时间
    function __construct(){
    }
    //用户匹配游戏 - 报名   将用户报名UID  扔到 内存池，等待6秒后，客户端再次请求
    function userMatchSign($uid , $type ,$userSex ){
        $info = ['___ userMatchSign:','signUid'=>$uid,'type'=>$type ];
        LogLib::wsWriteFileHash($info);

        $typeInfo = AppTypeModel::db()->getById($type);
        if(!$typeInfo){
            return out_pc(2000);
        }

        $userMatchStatus = $this->getUinfoByUidByField($uid,'status');
        if($userMatchStatus == UserModel::$_status_matching){
            return out_pc(2001);
        }

        if($userMatchStatus == UserModel::$_status_playing){
            return out_pc(2002);
        }

        if($userMatchStatus == UserModel::$_status_matching_ok){
            return out_pc(2012);
        }
        //更新 状态为  匹配状态
        $this->upUinfoByField($uid,array('status'=>UserModel::$_status_matching));

        $para = array('uid'=>$uid,'type'=>$type,'startTime'=>time(),'workId'=>$GLOBALS['ws_server']->worker_id);
        LogLib::wsWriteFileHash(["start swoole_timer_tick:",$para]);
        //匹配 定时器 每秒执行一次
        $timerId = swoole_timer_tick(1000,function($timeId,$params){
            $this->loopFindMatchUser($timeId,$params);
        },$para);

        //报名信息 写入redis
        $this->addUserSign($uid,$userSex,$timerId,$GLOBALS['ws_server']->worker_id,$type);

        //报名池 队列  写入 redis
        $this->addUserSignPool($type,$uid);
        LogLib::wsWriteFileHash("userMatchSign end!");

        return out_pc(200,"please waiting...match second:".$this->matchWait.".game total time:".$this->gameTime);
    }
    //实时 把比分 同步给另外一个用户
    function realtimeRsyncMsg($uid,$roomId,$score){
        LogLib::wsWriteFileHash(["___realtimeRsyncMsg start:",$uid,$roomId,$score]);

        if(!$uid){
            return out_pc(1001);
        }

        if(!$roomId){
            return out_pc(1006);
        }

        $uinfo = $this->getUinfoByUid($uid);
        LogLib::wsWriteFileHash($uinfo);
        if(!$uinfo){
            return out_pc(1005);
        }
        if($uinfo['status'] != UserModel::$_status_playing){
            return out_pc(2005);
        }
        if(!$uinfo['roomId']){
            return out_pc(1006);
        }

        if( $uinfo['roomId'] != $roomId){
            return out_pc(4004);
        }

//        $room = RoomModel::db()->getAll(" room_id = '{$uinfo['roomId']}'");
        $room = $this->getRoomById($roomId);
        if(!$room){
            return out_pc(3000);
        }
        if($room['fromUser']['uid'] != $uid && $room['toUser']['uid']  != $uid){
            return out_pc(4000);
        }

        if($room['status'] != RoomModel::$_status_start){
            //两边都没有 发起 开始游戏
            return out_pc(4001);
        }

        $user1Status = $this->getUinfoByUidByField($room['fromUser']['uid'],'status');
        if($user1Status != UserModel::$_status_playing){
            return out_pc(2005,$room['fromUser']['uid']."非游戏中状态");
        }

        $user2Status = $this->getUinfoByUidByField($room['toUser']['uid'],'status');
        if($user2Status != UserModel::$_status_playing){
            return out_pc(2005,$room['toUser']['uid']."非游戏中状态");
        }

        //60秒一局
//        if(time() - $room[0]['game_start_time'] >= $this->gameTime){//游戏已结束
//            //这里应该是个异常，60秒，两边都没有发起结束请求
//            return out_pc(4015);
//        }

        if($room['fromUser']['uid'] == $uid){
            $toUid = $room['toUser']['uid'];

//            $data = array('score'=>$score);
//            LogLib::wsWriteFileHash(["if 1",$data]);
//            RoomModel::db()->upById($room[0]['id'],$data);
        }else{
            $toUid = $room['fromUser']['uid'];

//            $data = array('score'=>$score);
//            LogLib::wsWriteFileHash(["if 2",$data]);
//            RoomModel::db()->upById($room[1]['id'],$data);
        }

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['rsyncMsg']['key'],$roomId);
        $data = array($uid,$score);

        $time = microtime_float();
        RedisPHPLib::getServerConnFD()->zAdd($key,$time,json_encode($data));
        //push 给另外一个用户  数据
        $data = array('code'=>7012,'msg'=>array($score));
        sendToUid($toUid,$data,1);

        $msg = array('score'=>$score,'uid'=>$uid ,'room_id'=>$roomId);
        $data = array('ctrl'=>'asynTask','ac'=>'rsyncMsgWriteDB','data'=>$msg);
        $GLOBALS['ws_server']->task($data);

        return out_pc(200);

    }
    //游戏结束后，离开房间
    function userLeaveGameRoom($uid,$roomId){
        LogLib::wsWriteFileHash(["___userLeaveGameRoom start",$uid,$roomId]);
        if(!$roomId){
            return out_pc(8036);
        }
//        $room = RoomModel::db()->getAll(" room_id = '$roomId'");
        $room = $this->getRoomById($roomId);
        if(!$room){
            return out_pc(1018);
        }

        if($room['fromUser']['uid'] != $uid && $room['toUser']['uid']  != $uid){
            return out_pc(4013);
        }

        if($room['status'] != RoomModel::$_status_end){
            return out_pc(4008);
        }


        $rsyncMsg = array(
            'ctrl'=>'asynTask',
            'ac'=>'roomUpWriteDb',
            'data'=>array('from_leave_time'=>0 ),
            'roomId'=>$roomId,
            'fromUid'=>0
        );


        if($room['fromUser']['uid'] == $uid){
            $toLevel = $room['toUser']['leave_time'];
            $toUid = $room['toUser']['uid'];

            $rsyncMsg['data']['from_leave_time'] = time();
            $rsyncMsg['fromUid'] = $room['fromUser']['uid'];
            $GLOBALS['ws_server']->task($rsyncMsg);

            $rsyncMsg['data']['to_leave_time'] = time();
            $rsyncMsg['fromUid'] = $room['toUser']['uid'];
            $GLOBALS['ws_server']->task($rsyncMsg);

            $this->upRoomUserById($roomId,'fromUser',array('from_leave_time'=>time()));
            $this->upRoomUserById($roomId,'toUser',array('to_leave_time'=>time()));
        }else{
            $toLevel = $room['fromUser']['leave_time'];
            $toUid = $room['fromUser']['uid'];


            $rsyncMsg['data']['to_leave_time'] = time();
            $rsyncMsg['fromUid'] = $room['toUser']['uid'];
            $GLOBALS['ws_server']->task($rsyncMsg);

            $rsyncMsg['data']['from_leave_time'] = time();
            $rsyncMsg['fromUid'] = $room['fromUser']['uid'];
            $GLOBALS['ws_server']->task($rsyncMsg);

            $this->upRoomUserById($roomId,'fromUser',array('to_leave_time'=>time()));
            $this->upRoomUserById($roomId,'toUser',array('from_leave_time'=>time()));
        }
        //对手还没有离开，需要推送
        if(!$toLevel){
            $data = array('code'=>7014,$toUid);
            sendToUid($toUid,$data,1);
        }

        return out_pc(200);
    }
    //取消匹配 - 还未进入游戏，点击了 取消 按钮
    function cancelSignMatch($uid,$type){
        LogLib::wsWriteFileHash(["___cancelSignMatch"]);
//        if(!RoomModel::keyInType($type)){
//            return out_pc(8210);
//        }

        $userMatchStatus = $this->getUinfoByUidByField($uid,'status');
        if($userMatchStatus != UserModel::$_status_matching){
            return out_pc(2004);
        }
        //获取用户报名信息
        $signInfo = $this->getUserSignInfo($uid,$type);
        LogLib::wsWriteFileHash(["signInfo:",$signInfo]);

        //已匹配成功的队列
//        $matchedRealUser =  $this->getMatchRealUser($uid,$type);
//        LogLib::wsWriteFileHash(["matchedRealUser:",$matchedRealUser]);

        if(!$signInfo){
            return out_pc(2008);
        }
        //清除定时器
        swoole_timer_clear($signInfo['timer_id']);
        //删除报名信息
        $this->delUserSign($uid,$type);
        //删除匹配队列
        $this->delUserSignPoolByUid($type,$uid);
        //容错下  再判断  是不是  匹配进程  已经匹配到了，然后，把报名信息删除了，增加了 匹配成功队列中
        $this->delMatchedRealUser($uid,$type);

        $this->upUinfoByField($uid,array('status'=>UserModel::$_status_normal));

        return out_pc(200);
    }
    //$type:1 断线重连，2断线
    function errorTolerantStatus($uid,$type){
        $uinfo = $this->getUinfoByUid($uid);
        LogLib::wsWriteFileHash(['errorTolerantStatus',$uid,$type,$uinfo]);
        if(!$uinfo || !arrKeyIssetAndExist($uinfo,'status')) {
            $msg = "uinfo is null ,or ,status is null";
            LogLib::wsWriteFileHash($msg);
            return $msg;
        }
        //正常状态不需要处理
        if($uinfo['status'] == UserModel::$_status_normal){
            $msg = "uinfo status is not <normal>,no need process ";
            LogLib::wsWriteFileHash($msg);
            return $msg;
        }elseif($uinfo['status'] == UserModel::$_status_matching){//匹配中
            if($type == 2){//取消掉报名信息

            }elseif($type == 1){//断线重连

            }
        }elseif($uinfo['status'] == UserModel::$_status_matching_ok){//匹配成功，等待游戏开始
            if($type == 2){//取消掉报名信息

            }elseif($type == 1){//断线重连

            }
        }elseif($uinfo['status'] == UserModel::$_status_playing){//游戏中
            if(!arrKeyIssetAndExist($uinfo,'roomId')){
                $msg = "roomId is null,no need process ";
                LogLib::wsWriteFileHash($msg);
                return $msg;
            }

//            $room = RoomModel::db()->getRow("  room_id = '{$uinfo['roomId']}'  order by id desc limit 1");
            $room = $this->getRoomById($uinfo['roomId']);
            if(!$room){
                $msg = "roomId is not in redis ";
                LogLib::wsWriteFileHash($msg);
                return $msg;
            }

            if($room['status'] != RoomModel::$_status_start){
                $msg = "roomInfo status is not start ";
                LogLib::wsWriteFileHash($msg);
                return $msg;
            }

            if($room['fromUser']['uid'] == $uid){
                $toUid = $room['toUser']['uid'];
            }elseif($room['toUser']['uid'] == $uid){
                $toUid = $room['fromUser']['uid'];
            }else{
                $msg = "roomInfo from_uid and to_uid not this uid ";
                LogLib::wsWriteFileHash($msg);
                return $msg;
            }

            if($type == 1){
                //通知对方此人已重连
                $data = array('code'=>7018,'msg'=>$uid);
                sendToUid($toUid,$data,1);
                return array('status'=>$uinfo['status'],'roomInfo'=>$room);
            }else{
                //通知对方此人已掉线
                $data = array('code'=>7016,'msg'=>$uid);
                sendToUid($toUid,$data,1);
            }
        }
    }

    function gameEndTotal($uid,$roomId , $rs  ){
        LogLib::wsWriteFileHash(["___gameEndTotal start:",$uid,$roomId,$rs]);

        if(!$roomId){
            return out_pc(1006);
        }
//        $room = RoomModel::db()->getAll(" room_id = '$roomId'");
        $room = $this->getRoomById($roomId);
        if(!$room){
            return out_pc(3000);
        }

        if($room['status'] != RoomModel::$_status_start){
            return out_pc(4001);
        }

//        if(count($room) != 2){
//            return out_pc(4003);
//        }

        if($room['fromUser']['uid'] != $uid && $room['toUser']['uid'] !=$uid){
            return out_pc(2007);
        }

        $user1Status = $this->getUinfoByUidByField($room['fromUser']['uid'],'status');
        if($user1Status != UserModel::$_status_playing){
            return out_pc(2005,$room['fromUser']['uid']."非游戏中状态");
        }

        $user2Status = $this->getUinfoByUidByField($room['toUser']['uid'],'status');
        if($user2Status != UserModel::$_status_playing){
            return out_pc(2005,$room['toUser']['uid']."非游戏中状态");
        }

        $rsyncMsg = array(
            'ctrl'=>'asynTask',
            'ac'=>'roomUpWriteDb',
            'data'=>array('game_end_time'=>time(),'status'=>RoomModel::$_status_end,'result'=>0),
            'roomId'=>$roomId,
            'fromUid'=>0
        );
        //平局不需要计算输赢ID
        if($rs != RoomModel::$_rs_draw){
            if($rs == RoomModel::$_rs_win){
                $winUid = $uid;
            }else{
                if($room['fromUser']['uid'] == $uid){
                    $winUid = $room['toUser']['uid'];

                    $rsyncMsg['data']['result'] = RoomModel::$_rs_win;
                    $rsyncMsg['fromUid'] = $room['fromUser']['uid'];
                    $GLOBALS['ws_server']->task($rsyncMsg);

                    $rsyncMsg['data']['result'] = RoomModel::$_rs_lose;
                    $rsyncMsg['fromUid'] = $room['toUser']['uid'];
                    $GLOBALS['ws_server']->task($rsyncMsg);
                }else{
                    $winUid = $room['fromUser']['uid'];

                    $rsyncMsg['data']['result'] = RoomModel::$_rs_lose;
                    $rsyncMsg['fromUid'] = $room['fromUser']['uid'];
                    $GLOBALS['ws_server']->task($rsyncMsg);

                    $rsyncMsg['data']['result'] = RoomModel::$_rs_win;
                    $rsyncMsg['fromUid'] = $room['toUser']['uid'];
                    $GLOBALS['ws_server']->task($rsyncMsg);
                }
            }

        }else{
            //更新数据
            $rsyncMsg['data']['result'] = RoomModel::$_rs_draw;
            $rsyncMsg['ac'] = "roomUpWriteDbByRoomId";
            $GLOBALS['ws_server']->task($rsyncMsg);
        }

        $d = array('status'=>RoomModel::$_status_end,'gameEndTime'=>time(),'result'=>$rs);
        $this->upRoomById($roomId,$d);


        //更新 状态
        $this->upUinfoByField($room['fromUser']['uid'],array('status'=>UserModel::$_status_normal,'roomId'=>0));
        $this->upUinfoByField($room['toUser']['uid'],array('status'=>UserModel::$_status_normal,'roomId'=>0));

        //通知两边结束游戏
        $data = array('code'=>7008,'msg'=>array('winUid'=>$winUid));

        sendToUid($room['fromUser']['uid'],$data,1);
        sendToUid($room['toUser']['uid'],$data,1);

        return out_pc(200);

    }
    //前端拿到匹配结果后，通知 S端  开始游戏
    function startGame($uid,$roomId){
        if(!$roomId){
            return out_pc(1006);
        }
//        $room = RoomModel::db()->getAll(" room_id = '$roomId'");
        $room = $this->getRoomById($roomId);
        if(!$room){
            return out_pc(3000);
        }
        LogLib::wsWriteFileHash($room);
        if($room['fromUser']['uid'] != $uid && $room['toUser']['uid'] != $uid){
            return out_pc(4013);
        }

        if($room['status'] != RoomModel::$_status_wait){
            return out_pc(4014);
        }

        $user1 = $this->getUinfoByUid($room['fromUser']['uid']);
        if($user1['status'] != UserModel::$_status_matching_ok){
            return out_pc(2011,$room['fromUser']['uid']." 非 匹配成功 等待开始状态");
        }

        $user2 = $this->getUinfoByUid($room['toUser']['uid']);
        if($user2['status'] != UserModel::$_status_matching_ok){
            return out_pc(2011,$room['toUser']['uid']." 非 匹配成功 等待开始状态");
        }
        //更新状态为：游戏中
        $this->upUinfoByField($room['fromUser']['uid'],array('status'=>UserModel::$_status_playing));
        $this->upUinfoByField($room['toUser']['uid'],array('status'=>UserModel::$_status_playing));


        $rsyncMsg = array(
            'ctrl'=>'asynTask',
            'ac'=>'roomUpWriteDbByRoomId',
            'data'=>array('game_start_time'=>time(),'status'=>RoomModel::$_status_start,),
            'roomId'=>$roomId,
        );
        $GLOBALS['ws_server']->task($rsyncMsg);


        $this->upRoomById($roomId,array('status'=>RoomModel::$_status_start,'gameStartTime'=>time()));

        if($room['fromUser']['uid'] == $uid){
            $this->upRoomUserById($roomId,"fromUser",array('startTime'=>time()));
        }else{
            $this->upRoomUserById($roomId,"toUser",array('startTime'=>time()));
        }

        //通知对方开始游戏
        $data =array('code'=>7006,'msg'=>array());
        sendToUid($room['fromUser']['uid'],$data,1);
        sendToUid($room['toUser']['uid'],$data,1);


        //游戏总时间内，要定时 结算
//        $timerId = swoole_timer_after($this->gameTime * 1000,[$this,"timerEndGame"],$roomId);
//        $data = array("game_start_time"=>time(),'status'=>RoomModel::$_status_start,'end_timer_id'=>$timerId);
//        RoomModel::db()->update($data," id in ({$room[0]['id']},{$room[1]['id']}) limit 2");

//        if($room[0]['robot_level']){
//            $this->setAIRobot($roomId,$uid,$room[0]['from_uid'],$room[0]['robot_level'],$room[0]['game_id']);
//        }elseif( $room[1]['robot_level']){
//            $this->setAIRobot($roomId,$uid,$room[1]['from_uid'],$room[1]['robot_level'],$room[1]['game_id']);
//        }

        return out_pc(200);
    }
    //发起 再来一局
    function againGameApply($uid,$roomId){
        LogLib::wsWriteFileHash(["___againGameApply start",$uid,$roomId]);
        if(!$roomId){
            return out_pc(1006);
        }
//        $room = RoomModel::db()->getAll(" room_id = '$roomId'");
        $room = $this->getRoomById($roomId);
        if(!$room){
            return out_pc(3000);
        }

        if($room['fromUser']['uid'] != $uid && $room['toUser']['uid'] != $uid){
            return out_pc(2007);
        }

        if($room['status'] != RoomModel::$_status_end){
            return out_pc(4005);
        }

        if($room['fromUser']['uid'] == $uid){
            $toUid = $room['toUser']['uid'];
        }else{
            $toUid = $room['fromUser']['uid'];
        }

        $toUidStatus = $this->getUinfoByUidByField($toUid,'status');
        if($toUidStatus == STATUS_MATCHING){
            return out_pc(2010);
        }elseif($toUidStatus == STATUS_PLAYING){
            return out_pc(2009);
        }

        $data =array('code'=>7010,'msg'=>array('roomId'=>$roomId));
        sendToUid($toUid,$data,1);

        return out_pc(200);
    }
    //同意 再来一局
    function agreeAgainGameApply($toUid,$roomId){
        LogLib::wsWriteFileHash(["___agreeAgainGameApply start",$toUid,$roomId]);
        if(!$roomId){
            return out_pc(1006);
        }
//        $room = RoomModel::db()->getAll(" room_id = '$roomId'");
        $room = $this->getRoomById($roomId);
        if(!$room){
            return out_pc(3000);
        }

        if($room['fromUser']['uid'] != $toUid &&$room['toUser']['uid'] != $toUid){
            return out_pc(2007);
        }

        if($room['status'] != RoomModel::$_status_end){
            return out_pc(4005);
        }

        if($room['fromUser']['uid'] == $toUid){
            $fromUid = $room['toUser']['uid'];
        }else{
            $fromUid =$room['fromUser']['uid'];
        }

        $fromStatus = $this->getUinfoByUidByField($fromUid,'status');
        if($fromStatus == STATUS_MATCHING){
            return out_pc(2010);
        }elseif($fromStatus == STATUS_PLAYING){
            return out_pc(2009);
        }

        $this->upUinfoByField($fromUid,array('status'=>UserModel::$_status_matching));

//        $this->upUserMathStatus($fromUid,STATUS_MATCHING);
//        $this->upUserMathStatus($fromUid,STATUS_MATCHING);


        $row = array('uid'=>$fromUid,'a_time'=>time(),'sex'=>1,'failed_time'=>time()+$this->gameTime,'timer_id'=>0,'worker_id'=>0);
        $user1 = $row;
        $row = array('uid'=>$toUid,'a_time'=>time(),'sex'=>1,'failed_time'=>time()+$this->gameTime,'timer_id'=>0,'worker_id'=>0);
        $user2 = $row;
        $roomInfo = $this->mapRoomInfo($user1,$user2,$room[0]['game_id'],2);

        $data = array(
            'roomId'=>$roomInfo['roomId'],
            'fromUid'=>$roomInfo['fromUser']['uid'],
            'toUid'=>$roomInfo['toUser']['uid'],
        );

        $rs = array('code'=>7004,'msg'=>$data);
        sendToUid($fromUid,$rs,1);


        $data = array(
            'roomId'=>$roomInfo['roomId'],
            'fromUid'=>$roomInfo['toUser']['uid'],
            'toUid'=>$roomInfo['fromUser']['uid'],
        );

        $rs = array('code'=>7004,'msg'=>$data);
        sendToUid($toUid,$rs,1);

        return out_pc(200);

    }
    //每1秒，会执行一次此函数，寻找是否有匹配好的用户VS
    //超时X秒，会删除这个定时器，直接给匹配机器人了
    private function loopFindMatchUser($timeId,$para){
        LogLib::wsWriteFileHash(["___loopFindMatchUser start:",$timeId,$para,$this->matchWait, time() - $para['startTime']]);

        $timerWorkId = $para['workId'];
        if(!$GLOBALS['uid_fd_table']->exist($para['uid'])){
            LogLib::wsWriteFileHash([2003,' offline ']);
            $this->delUserSign($para['uid'],$para['type']);
            $this->clearTimer($timeId,$timerWorkId);
            //证明已掉线
            return out_pc(2003);
        }

        $uinfo = $this->getUinfoByUid($para['uid']);
        LogLib::wsWriteFileHash($uinfo);
        if($this->getUinfoByUidByField($para['uid'],'status') != UserModel::$_status_matching){
            LogLib::wsWriteFileHash([2004,' status not matching ']);
            $this->delUserSign($para['uid'],$para['type']);
            $this->clearTimer($timeId,$timerWorkId);
            return out_pc(2004);
        }

        $lastTime = time() - $para['startTime'];
        LogLib::wsWriteFileHash(["lastTime:",$lastTime]);
        if($lastTime > $this->matchWait){
            LogLib::wsWriteFileHash(["timeout", $para['startTime'],$lastTime, $this->matchWait]);
            //已超时，匹配机器人
//            $roomInfo = $this->matchRobot($para['uid'],$para['gameId'],$para['type']);
//            LogLib::wsWriteFileHash(['roomInfo',$roomInfo]);

            $this->cancelSignMatch($para['uid'],$para['type']);

            $data = array('code'=>7002,'msg'=>'匹配超时');
            sendToUid($para['uid'],$data,1);

            return out_pc(1200);
        }else{
            $realUser = $this->getMatchRealUser($para['uid'],$para['type']);
            LogLib::wsWriteFileHash(["matchRealUser rs",$realUser]);
            if($realUser){
                //清除双方 匹配 定时器
                $this->clearTimer($realUser[0]['timer_id'],$realUser[0]['worker_id']);
                $this->clearTimer($realUser[1]['timer_id'],$realUser[1]['worker_id']);
                //清除双方  匹配成功 队列
                $this->delMatchedRealUser($realUser[0]['uid'],$para['type']);
                $this->delMatchedRealUser($realUser[1]['uid'],$para['type']);

                $appId = $this->getAppIdByUid($realUser[0]['uid']);
                //创建房间信息
                $roomInfo = $this->mapRoomInfo($realUser[0],$realUser[1],$appId,$para['type']);
                //更新当前用户状态
                $this->upUinfoByField($realUser[0]['uid'],array('status'=>UserModel::$_status_matching_ok,'roomId'=>$roomInfo['roomId']));
                $this->upUinfoByField($realUser[1]['uid'],array('status'=>UserModel::$_status_matching_ok,'roomId'=>$roomInfo['roomId']));

                //通知两个用户，已准备好，等待开始
                $data = array(
                    'roomId'=>$roomInfo['roomId'],
                    'fromUid'=>$roomInfo['fromUser']['uid'],
                    'toUid'=>$roomInfo['toUser']['uid'],
                );

                $returnInfo = array('code'=>7004,'msg'=>$data);
                sendToUid($realUser[0]['uid'],$returnInfo,1);

                $data = array(
                    'roomId'=>$roomInfo['roomId'],
                    'fromUid'=>$roomInfo['toUser']['uid'],
                    'toUid'=>$roomInfo['fromUser']['uid'],
                );
                $returnInfo = array('code'=>7004,'msg'=>$data);
                sendToUid($realUser[1]['uid'],$returnInfo,1);

                return out_pc(1202);
            }else{
                return out_pc(1201);
            }
        }

        LogLib::wsWriteFileHash("match user,no result.wait next times");
    }

    private function mapRoomInfo($signUser1,$signUser2,$appId,$type,$robotLevel = 0){
        LogLib::wsWriteFileHash(["___mapRoomInfo start - ","signUser1:",$signUser1,"signUser2:",$signUser2,$appId,$type,$robotLevel]);

        $roomId = $this->getRoomId();
        //持久化MYSQL
        $roomInfo = array(
            'room_id'=>$roomId,
            'game_end_time'=>0,//结束时间
            'app_type_id'=>$type,
            'status'=>RoomModel::$_status_wait,
            'app_id'=>$appId,
            'result'=>0,
            'game_start_time'=>0,
            'from_user_s_time'=>0,
            'to_user_s_time'=>0,
        );

        $user1_room_info = $roomInfo;
        $user2_room_info = $roomInfo;
        //报名时间
        $user1_room_info['game_sign_time'] = $signUser1['a_time'];
        $user1_room_info['from_uid'] = $signUser1['uid'];
        $user1_room_info['to_uid'] = $signUser2['uid'];
        $user1_room_info['score'] = 0;

        $user2_room_info['game_sign_time'] = $signUser1['a_time'];
        $user2_room_info['from_uid'] = $signUser2['uid'];
        $user2_room_info['to_uid'] = $signUser1['uid'];
        $user2_room_info['score'] = 0;

        $dd = array('ctrl'=>'asynTask','ac'=>'roomWriteDB','data'=>$user1_room_info);
        $GLOBALS['ws_server']->task($dd);

        $dd = array('ctrl'=>'asynTask','ac'=>'roomWriteDB','data'=>$user2_room_info);
        $GLOBALS['ws_server']->task($dd);



        $redisRoomInfo = $this->formatReturnRoomInfo($roomInfo,$user1_room_info,$user2_room_info);
        //存到 redis 中
        $this->createRoom($redisRoomInfo);

        LogLib::wsWriteFileHash(["roomInfo:",$redisRoomInfo]);
        return $redisRoomInfo;
    }
    //守护进程
    function matchRealUser($appId,$typeId ){
//        $type = $para[0];
//        $appId = $type;
//        $typeId = $para[1];

        //获取锁
        $lockKey = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['matchedUserLock']['key'],$appId."_".$typeId);
        $expireTime = $GLOBALS['rediskey']['matchedUserLock']['expire'];
        $config = array('nx', 'ex'=>$expireTime);

        $addLockRs = RedisPHPLib::getServerConnFD()->set($lockKey,time(),$config);
        LogLib::matchUserWriteFileHash(["getLock",$lockKey,$expireTime]);
        if(!$addLockRs) {
            LogLib::matchUserWriteFileHash(["getLock failed 2100"]);
            return out_pc(2100);
        }

        LogLib::matchUserWriteFileHash(["___start match relay user:",$appId,$typeId]);

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['userSignPool']['key'],$typeId);
        $pushUids = [];
        //取出一个用户，再循环遍历接下来所有的用户
        while ($uid = RedisPHPLib::rPop($key)){
            $signInfo = $this->getUserSignInfo($uid,$typeId);
            LogLib::matchUserWriteFileHash([$uid,$signInfo]);
            if(!$signInfo){
                continue;
            }
            //如果 池子里 用户 已失效，丢弃
            if(time() > $signInfo['failed_time']){
                LogLib::matchUserWriteFileHash("user1 timeout");
                continue;
            }

            $findout = 0;
            while ($uid2 = RedisPHPLib::rPop($key)){
                if($uid2 == $uid){
                    LogLib::matchUserWriteFileHash("self match self....");
                    continue;
                }

                $signInfo2 = $this->getUserSignInfo($uid2,$typeId);
                LogLib::matchUserWriteFileHash([$uid,$signInfo2]);
                if(!$signInfo2){
                    LogLib::matchUserWriteFileHash(" getUserSignInfo is null");
                    continue;
                }
                //如果 池子里 用户 已失效，丢弃
                if(time() > $signInfo2['failed_time']){
                    LogLib::matchUserWriteFileHash("user2 timeout");
                    continue;
                }

                $this->addMatchedRealUser($signInfo,$signInfo2,$uid,$typeId);
                //删除报名信息
                $this->delUserSign($signInfo['uid'],$typeId);
                $this->delUserSign($signInfo2['uid'],$typeId);

                LogLib::matchUserWriteFileHash(["write matched User poll,so del user sign:",$uid,$uid2]);
                $findout = 1;
                break;

            }

            if(!$findout){
                $pushUids[] = $uid;
            }
        }

        LogLib::matchUserWriteFileHash(['push back Uids to redis queue',$pushUids]);
        if($pushUids){
            foreach($pushUids as $k=>$v){
                $this->addUserSignPool($typeId,$v);
            }
        }

        LogLib::matchUserWriteFileHash(" del lock ");
        RedisPHPLib::delLock($lockKey);

        return out_pc(2101);
    }

    //=======================================================================

    //根据UID中的，X，切分出APPID
    function getAppIdByUid($uid){
        $appInfo = explode("X",$uid);
        $appId = $appInfo[0];
        return $appId;
    }
    //清理定时器
    function clearTimer($timerId,$workId){
        if($GLOBALS['ws_server']->worker_id != $workId){
            LogLib::wsWriteFileHash(["clearTimer sendMessage",$timerId,$workId]);
            $GLOBALS['ws_server']->sendMessage($timerId,$workId);
        }else{
            LogLib::wsWriteFileHash(["clearTimer",$timerId,$workId]);
            swoole_timer_clear($timerId);
        }
    }
    //公共输出错误日志
    function echoForArr($block){
        $total = 0;
        foreach($block as $k=>$v){
            $total += $v;
        }

        LogLib::wsWriteFileHash("block total point:".$total);
    }
    //游戏结束后的定时器，为防止前端 不推送结束请求~
//    private function timerEndGame($roomId){
//        LogLib::wsWriteFileHash(["timerEndGame",$roomId]);
//        $rs = $this->gameEndTotal($roomId);
//        LogLib::wsWriteFileHash(["gameEndTotal return ",$rs]);
//    }

    function formatUserInfo($userInfo){
        $data = array(
            'uid'=>$userInfo['from_uid'],
            'signTime'=>$userInfo['game_sign_time'],
            'startTime'=>$userInfo['game_start_time'],
            'score'=>$userInfo['score'],
//            'toAvatar'=>'',
//            'toNickName'=>'',
        );
        return $data;
    }

    private function formatReturnRoomInfo($roomInfo,$fromUser,$toUser){

        $data = array(
            'roomId'=>$roomInfo['room_id'],
            'gameEndTime'=>$roomInfo['game_end_time'],//结束时间
            'appTypeId'=>$roomInfo['app_type_id'],
            'status'=>$roomInfo['status'],
            'appId'=>$roomInfo['app_id'],
            'result'=>$roomInfo['result'],
            'gameStartTime'=>$roomInfo['game_start_time'],
            'fromUser'=>$this->formatUserInfo($fromUser),
            'toUser'=>$this->formatUserInfo($toUser),
        );

        return $data;
    }

    private function getRoomId(){
        return md5(uniqid(md5(microtime(true)), true));
    }

    //==================redis========================================
    //添加一个报名信息
    function addUserSign($uid,$sex,$timerId,$workId,$type){
        $failed = time() + $this->matchWait;//失效时间
        $row = array('uid'=>$uid,'a_time'=>time(),'sex'=>$sex,'failed_time'=>$failed,'timer_id'=>$timerId,'worker_id'=>$workId);
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['userSign']['key'],$type."-".$uid);
        return RedisPHPLib::set($key,$row,$GLOBALS['rediskey']['userSign']['expire'],1);
    }
    //删除用户报名信息
    function delUserSign($uid,$type){
        LogLib::wsWriteFileHash("delUserSign:".$uid);
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['userSign']['key'],$type ."-".$uid);
        RedisPHPLib::getServerConnFD()->del($key);
    }
    //获取用户报名信息
    function getUserSignInfo($uid,$typeId){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['userSign']['key'],$typeId."-".$uid);
        $signInfo = RedisPHPLib::get($key,1);
        return $signInfo;
    }
    //---------------
    //添加报名用户到匹配池
    function addUserSignPool($type,$uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['userSignPool']['key'],$type);
        $rs = RedisPHPLib::lpush($key,$uid);

        return $rs;
    }
    //删除，报名用户，匹配池
    function delUserSignPoolByUid($type,$uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['userSignPool']['key'],$type);
        return RedisPHPLib::getServerConnFD()->lRem($key,$uid);
    }
    //------------------
    //更新用户信息
    function upUinfoByField($uid,$data){
        LogLib::wsWriteFileHash(['upUinfoByField',$uid,$data]);
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['uinfo']['key'],$uid);
        foreach ($data as $k=>$v) {
            $rs = RedisPHPLib::getServerConnFD()->hSet($key,$k,$v);
        }
        return $rs;
    }
    //获取一个用户的所有基础信息
    function getUinfoByUid($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['uinfo']['key'],$uid);
        return RedisPHPLib::getServerConnFD()->hGetAll($key);
    }
    //清除失效缓存，否则内存会被占满
    function clearExpireUInfo(){

    }
    //获取一个用户的，固定某个基础信息
    function getUinfoByUidByField($uid,$key){
        $masterKey = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['uinfo']['key'],$uid);
        return RedisPHPLib::getServerConnFD()->hGet($masterKey,$key);
    }
    //--------------
    //获取，已匹配成功的，真实用户
    private function getMatchRealUser($uid,$type ){
        LogLib::wsWriteFileHash(["getMatchRealUser:",$uid,$type]);
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['matchedUserPool']['key'],$type ."-".$uid);
        $rs = RedisPHPLib::get($key,1);

        return $rs;
    }
    //删除一个真实用户已匹配成功
    function delMatchedRealUser($uid,$type ){
        LogLib::wsWriteFileHash(["delMatchedRealUser:",$uid,$type]);
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['matchedUserPool']['key'],$type ."-".$uid);
        $rs = RedisPHPLib::getServerConnFD()->del($key);

        return $rs;
    }
    //添加
    function addMatchedRealUser($signInfo,$signInfo2,$uid,$typeId){
        $matchedUserKey = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['matchedUserPool']['key'],$typeId."-".$uid);
        $rs = array($signInfo,$signInfo2);
        return RedisPHPLib::set($matchedUserKey,$rs,$GLOBALS['rediskey']['userSign']['expire'],1);
    }
    //--------------
    //创建一个房间，持久化到redis
    function createRoom($data){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['room']['key'],$data['roomId']);
        foreach ($data as $k=>$v) {
            if($k == 'fromUser' || $k == 'toUser'){
                $v = json_encode($v);
            }
            $rs = RedisPHPLib::getServerConnFD()->hSet($key,$k,$v);
        }

        return $rs;
    }
    //获取房间信息
    function getRoomById($roomId){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['room']['key'],$roomId);
        $list =  RedisPHPLib::getServerConnFD()->hGetAll($key);
        if($list){
            foreach ($list as $k=>$v) {
                if($k == 'fromUser' || $k == 'toUser'){
                    $v = json_decode($v,true);
                    $list[$k] = $v;
                }
            }
        }

        return $list;
    }
    //更新房间信息
    function upRoomById($roomId,$data){
        LogLib::wsWriteFileHash(["upRoomById",$roomId,$data]);
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['room']['key'],$roomId);
        foreach ($data as $k=>$v) {
            RedisPHPLib::getServerConnFD()->hSet($key,$k,$v);
        }
        return 1;
    }

    //更新房间信息
    function upRoomUserById($roomId,$userKey,$data){
        LogLib::wsWriteFileHash(["upRoomUserById",$roomId,$userKey,$data]);

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['room']['key'],$roomId);
        $user = RedisPHPLib::getServerConnFD()->hGet($key,$userKey);
        $user = json_decode($user,true);
        LogLib::wsWriteFileHash($user);



        foreach ($user as $k2=>$v2) {
            foreach ($data as $k=>$v) {
                if($k2 == $k){
                    $user[$k] = $v;
                    break;
                }
            }
        }
        $user = json_encode($user);
        LogLib::wsWriteFileHash($user);
        RedisPHPLib::getServerConnFD()->hSet($key,$userKey,$user);
        return 1;
    }

    //获取房间信息
    function getMysqlRoomById($roomId){
        return RoomModel::db()->getAll(" room_id = '$roomId'");
    }
    //-----------------------------

    //累减 在线人数
    function decrOnlineUserTotal(){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['onlineUserTotal']['key']);
        //累减 在线数
        $rs = RedisPHPLib::getServerConnFD()->decr($key);
        LogLib::wsWriteFileHash(['decr onlineUserTotal number',$rs]);
        return $rs;
    }
    //累加在线人数
    function incrOnlineUserTotal(){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['onlineUserTotal']['key']);
        $rs = RedisPHPLib::getServerConnFD()->incr($key);
        LogLib::wsWriteFileHash(['incr onlineUserTotal number',$rs]);
        return $rs;
    }

    function clearOnlineUserTotal(){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['onlineUserTotal']['key']);
        $rs = RedisPHPLib::getServerConnFD()->del($key);
        LogLib::wsWriteFileHash(['clear onlineUserTotal number',$rs]);
        return $rs;
    }

    function getOnlineUserTotal(){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['onlineUserTotal']['key']);
        $rs = RedisPHPLib::getServerConnFD()->get($key);
        LogLib::wsWriteFileHash(['getOnlineUserTotal',$rs]);
        return $rs;
    }

    //给前端容错，获取一局游戏的开始时间
//    function gameStartTime($uid,$roomId){
//        LogLib::wsWriteFileHash(["___playGameTime start",$uid,$roomId]);
//        if(!$roomId){
//            return out_pc(8036);
//        }
//        $room = RoomModel::db()->getAll(" room_id = '$roomId'");
//        if(!$room){
//            return out_pc(1018);
//        }
//
//        if($room[0]['from_uid'] != $uid && $room[1]['from_uid'] != $uid){
//            return out_pc(4013);
//        }
//
//        if($room[0]['status'] != RoomModel::$_status_start){
//            return out_pc(4014);
//        }
//
//        return out_pc(200,$room[0]['game_start_time']);
//    }


    //============================================================


    //双方 用户历史 对战 记录
    function getGameEndTotalInfo($uid,$toUid){
        LogLib::wsWriteFileHash(["___getGameEndTotalInfo start",$uid,$toUid]);

        $pklog = RoomModel::db()->getAll(" from_uid = $uid and to_uid = $toUid ");
        if(!$pklog){
            return array('lost'=>0,'win'=>0,'bestScore'=>0);
        }

        $lost = 0;
        $win = 0;
        foreach($pklog as $k=>$v){
            if($v['result'] == RoomModel::$_rs_win){
                $win++;
            }elseif($v['result'] == RoomModel::$_rs_lose){
                $lost++;
            }
        }

        $best = RoomModel::db()->getRow(" from_uid = $uid order by score desc limit 1");
        if(!$best){
            return array('lost'=>$lost,'win'=>$win,'bestScore'=>0);
        }

        return array('lost'=>$lost,'win'=>$win,'bestScore'=>$best['score']);
    }
    function calcAIRobotPoint($roomId){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['AIRobot']['key'],$roomId);
        $AIRobotInfo = RedisPHPLib::get($key,true);
        $mod = time() - $AIRobotInfo[5];
        if($mod >= $this->gameTime){
            $mod = $this->gameTime;
        }

        $socre = 0;
        foreach($AIRobotInfo[4]['matchDisappear'] as $k=>$v){
            $socre += $v;
            if($k == $mod){
                break;
            }
        }

        foreach($AIRobotInfo[4]['touchBlock'] as $k=>$v){
            $socre += $v[0] + $v[1] + $v[2];
            if($k == $mod){
                break;
            }
        }

        return $socre;

    }
    //匹配机器人
    private function matchRobot($uid,$gameId,$type){
//        LogLib::wsWriteFileHash(["___start matching robot:",$uid]);
//
//        $robotUser = $this->getRobot($uid);
//        if(!$robotUser){
//            return out_pc(4012);
//        }
//
////        LogLib::wsWriteFileHash([" robotUserInfo:",$robotUser]);
//
//        $robotLevel = $this->getRobotLevel();
//        $realUser          = array('uid'=>$uid,'a_time'=>time() - $this->matchWait,'sex'=>1,'failed_time'=>time()+$this->matchWait);
//        $robotUserSignInfo = array('uid'=>$robotUser['id'],'a_time'=>time(),'sex'=>$robotUser['sex'],'failed_time'=>time()+$this->matchWait);
//        $roomInfo = $this->mapRoomInfo($realUser,$robotUserSignInfo,$gameId,$type,$robotLevel);
//
//        $data = $this->formatReturnRoomInfo($roomInfo);
//
//        $rs = array('code'=>6102,'msg'=>$data);
//        sendToUid($uid,$rs,1);
//
//        return out_pc(200,$roomInfo);

    }

    //排行榜
    function getGameRank($uid){
        $sql = "select count(id) as winCnt , from_uid ,game_start_time from room where result = ".RoomModel::$_rs_win." group by from_uid order by  winCnt desc limit 100";
        $totalList = RoomModel::db()->getAllBySQL($sql);

        $rs = array('todayList'=>null,'todayRank'=>0,'todayWinCnt'=>0,'totalList'=>null,'totalRank'=>0,'totalWinCnt'=>0,);
        $today = dayStartEndUnixtime();
        if($totalList){
            foreach($totalList as $k=>$v){
                $uinfo = $this->userService->getUinfoById($v['from_uid']);
                $row = array('rank'=>$k+1,'winCnt'=>$v['winCnt'],'sex'=>$uinfo['sex'],'nickname'=>$uinfo['nickname'],'avatar'=>$uinfo['avatar'],'uid'=>$v['from_uid']);

                if($v['from_uid'] == $uid){
                    $rs['totalRank'] = $k+1;
                    $rs['totalWinCnt'] = $v['winCnt'];
                }

                $rs['totalList'][] = $row;
            }
        }

        $sql = "select count(id) as winCnt , from_uid ,game_start_time from room where result = ".RoomModel::$_rs_win." and game_start_time >= {$today['s_time']} group by from_uid order by  winCnt desc limit 100";
        $todayList = RoomModel::db()->getAllBySQL($sql);
        if($todayList){
            foreach($todayList as $k=>$v){
                $uinfo = $this->userService->getUinfoById($v['from_uid']);
                $row = array('rank'=>$k+1,'winCnt'=>$v['winCnt'],'sex'=>$uinfo['sex'],'nickname'=>$uinfo['nickname'],'avatar'=>$uinfo['avatar'],'uid'=>$v['from_uid']);
                $rs['todayList'][] = $row;

                if($v['from_uid'] == $uid){
                    $rs['todayRank'] = $k + 1;
                    $rs['todayWinCnt'] = $v['winCnt'];
                }
            }
        }


        return out_pc(200,$rs);
    }
    //用户每个用户的，历史汇总信息:战绩、排名等
    function getUserGameTotalInfo($uid,$gameId){
        $cnt = RoomModel::db()->getCount(" from_uid = $uid and game_id = $gameId and result = ".RoomModel::$_rs_win);
        $rs = array('winCnt'=>$cnt,'todayRank'=>0);

        $today = dayStartEndUnixtime();
        $sql = "select count(id) as winCnt , from_uid ,game_start_time from room where result = ".RoomModel::$_rs_win." and game_start_time >= {$today['s_time']} group by from_uid order by  winCnt desc limit 1000";
        $list = RoomModel::db()->getAllBySQL($sql);
        if(!$list){
            return out_pc(200,$rs);
        }

        foreach($list as $k=>$v){
            if($v['from_uid'] == $uid){
                $rs['todayRank'] = $k+1;
                break;
            }
        }

        return out_pc(200,$rs);
    }
    function getRobot($uid = 0){
        $user =$this->userService->getUinfoById($uid);
        if($user['sex'] == 1){
            $sex = 2;
        }else{
            $sex = 1;
        }

        $robot = UserModel::db()->getRowBySQL(" SELECT * FROM `user` where sex = $sex and robot = ".UserModel::$_robot_true." ORDER BY RAND() limit 1");
        return $robot;
    }
    private function getRobotLevel($gameId = 1){
        return rand(1,3);
        //目前是5个级别
        $r = rand(1,10);
        if($r <= 7){
            return rand(1,3);
        }

        if($r == 8 || $r == 9){
            return 4;
        }

        if($r == 100){
            return 5;
        }
    }

    function setAIRobot($roomId,$uid,$robotUid,$robotLevel,$gameId){
        $rs = $this->calcResult($robotLevel);

        $arr = array(
//      roomId=>用户ID 机器人UID 机器人等级 游戏ID 已算好的结果  开始时间
            $uid,$robotUid,$robotLevel,$gameId,$rs,time(),
        );

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['AIRobot']['key'],$roomId);
        RedisPHPLib::set($key,$arr,0,true);


        $para = array('roomId'=>$roomId,'startTime'=>time());
        LogLib::wsWriteFileHash(["setAIRobot swoole_timer_tick:",$para]);
        $timerId = swoole_timer_tick(1000,function($timeId,$params){
            $this->AIRobotExec($timeId,$params);
        },$para);

        RoomModel::db()->update(array("robot_timer"=>$timerId)," room_id = '$roomId' limit 2");
    }

    function AIRobotExec($timeId,$params){
        LogLib::AIRobotWriteFileHash("___setAIRobot start:");
        $mod = time() - $params['startTime'] - 1;
        LogLib::AIRobotWriteFileHash("mod:$mod");
        if($mod >= $this->gameTime){//超时了
            swoole_timer_clear($timeId);
            return false;
        }

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['AIRobot']['key'],$params['roomId']);
        $reobotInfo = RedisPHPLib::get($key,true);
        LogLib::AIRobotWriteFileHash($reobotInfo);
        if(!$reobotInfo){
            LogLib::AIRobotWriteFileHash(out_pc(4017));
            swoole_timer_clear($timeId);
            return false;
        }

        $result = $reobotInfo[4];
        $point = 0;
        foreach($result['matchDisappear'] as $k=>$v){
            if($k == $mod){
                break;
            }
            $point += $v;
        }

        LogLib::AIRobotWriteFileHash(["point1 :",$point]);

        foreach($result['touchBlock'] as $k=>$v){
            if($k == $mod){
                $point += $v[0];

                $data = array('code'=>6106,'msg'=>$point);
                sendToUid($reobotInfo[0],$data,1,'AIRobot');

                $point += $v[1];

                $data = array('code'=>6106,'msg'=>$point);
                sendToUid($reobotInfo[0],$data,1,'AIRobot');

                $point += $v[2];

                $data = array('code'=>6106,'msg'=>$point);
                sendToUid($reobotInfo[0],$data,1,'AIRobot');

                break;
            }else{
                $point += $v[0];
                $point += $v[1];
                $point += $v[2];
            }
        }

        $point += $result['matchDisappear'][$mod];

        LogLib::AIRobotWriteFileHash(["point2 :",$point]);

        $data = array('code'=>6106,'msg'=>$point);
        sendToUid($reobotInfo[0],$data,1 ,'AIRobot');

    }
    //AI
    //1 先确定机器人等级
    //2 根据等级 取得一个 积分，也就是这次比赛的结果
    //3 游戏加分项 一共有2种，1 用户拖个N格的方块到面板上，那就加N分。如果满一行，消除的话，是 10*(1+len)*len/2 ,也就是：10 30 60
    //4 也就是两种加分的总和=第2步的总分数
    function calcResult($robotLevel){
        $config = $this->AIConfig($robotLevel);
        $score = rand($config['minScore'],$config['maxScore']);

        LogLib::wsWriteFileHash("rand score total:".$score);

        $seven =  $score * 0.7 / 10;//7成概率得10分
        $seven = round($seven);
        $second = $score * 0.2 / 20;//2成概率得20分
        $second = round($second);
        $one = $score * 0.1 / 30;//1成概率得30分
        $one = round($one);//这里有坑~ 命中率有点低

        LogLib::wsWriteFileHash("70%:".$seven.",    20%:".$second.",    10%:".$one );

        //生成 10分的格子、20分的格子、30分的格子
        $sevenBlock = $this->fillBlock($seven,10);
        $secondBlock = $this->fillBlock($second,20);
        //合并10 20 分的格子
        $block = array_merge($sevenBlock,$secondBlock);
        //30分的格子，可能会不出现
        if($one){
            $oneBlack = $this->fillBlock($one,30);
            $block = array_merge($block,$oneBlack);
        }else{
            $block[] = 30;//补一个
        }
        //将顺序产生的格子，随机打乱，仿真
        shuffle($block);//将数组随机打乱

        $this->echoForArr($block);
        //游戏时间 - 总格子数 = 余数的，就是空，也就是不加分
        $mod = $this->gameTime - count($block);
        LogLib::wsWriteFileHash( "game time - block =  mod:".$mod);

        $rs = [];//每秒增加的分数
        $j = 0;
        //60个格子都是满的，就不用再随机了
        if($mod <= 0){
            //证明 格子数 大于 60秒，也就是大于60个
            $rs = $block;
        }else{
            for($i=1;$i<=$this->gameTime;$i++){
                //随机 1  - 60 ,假如余数是7，会有60分之7的概率为空
                $rand = rand(1,$this->gameTime);//一定概念 触发
                if( $mod >= $rand){
                    $rs[] = 0;
                    continue;
                }
                if(isset( $block[$j])){
                    $rs[] = $block[$j];
                    $j++;
                }
            }
        }

        $this->echoForArr($rs);

        //每秒钟最多能放3个方块，每一个方块根据数量，增加1分,格子最多9个，最少1个
        $touchBlockArr = [];
        for($i=1;$i<=$this->gameTime;$i++){
            $touchBlockArr[] = array(rand(1,3),0,0);
        }

        return array("matchDisappear"=>$rs,'touchBlock'=>$touchBlockArr);
    }
    function AIConfig($robotLevel){
        $arr = array(
            1=>array('minScore'=>50,'maxScore'=>60,'time'=>$this->gameTime),
            2=>array('minScore'=>60,'maxScore'=>90,'time'=>$this->gameTime),
            3=>array('minScore'=>90,'maxScore'=>150,'time'=>$this->gameTime),
            4=>array('minScore'=>150,'maxScore'=>300,'time'=>$this->gameTime),
            5=>array('minScore'=>600,'maxScore'=>600,'time'=>$this->gameTime),
        );

        return $arr[$robotLevel];
    }

    function fillBlock($end,$num){
        $block = [];
        for($i=0;$i<$end;$i++){
            $block[] = $num;
        }

        return $block;
    }

}