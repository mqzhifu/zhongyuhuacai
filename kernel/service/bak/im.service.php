<?php
//游戏匹配
class ImService{
    //最近聊天的人
    function getNearTalkUser($uid,$limit = 2){
        $rsList =  ImSessionModel::db()->getAll(" ( from_uid = $uid or to_uid = $uid )  order by u_time desc limit  $limit");
        if(!$rsList){
            return $rsList;
        }

        $list = null;
        foreach($rsList as $k=>$v){
            $row = array('last_time'=>$v['u_time']);
            if($v['from_uid'] == $uid){
                $row['to_uid'] = $v['to_uid'];
            }else{
                $row['to_uid'] = $v['from_uid'];
            }
            $list[] = $row;
        }

        return $list;

    }

    function formatList($list){
        $userService = new UserService();
        foreach($list as $k=>$v){
            $user = $userService->getUinfoById($v['to_uid']);
            if($user){
                $list[$k]['nickname'] = $user['nickname'];
                $list[$k]['avatar'] = $user['avatar'];
            }else{
                $list[$k]['nickname'] = "未知";
                $list[$k]['avatar'] = "未知";
            }
        }

        return $list;
    }

    function provideList($uid){
        //最近聊天
        $nearTalkUser = $this->getNearTalkUser($uid,2);
        if($nearTalkUser){
            $nearTalkUser = $this->formatList($nearTalkUser);
            foreach($nearTalkUser as $k=>$v){
                $nearTalkUser[$k]['uid'] = $v['to_uid'];
                unset($nearTalkUser[$k]['to_uid']);
            }
        }
        //好友
//        $friend = $this->allList($uid);
        //关注
        $fansService = new FansService();
        $fans = $fansService->getFansList($uid);

        $rs = array('nearTalkUser'=>$nearTalkUser,'fans'=>$fans,
//            'friend'=>$friend
        );
        return out_pc(200,$rs);
    }

    function contactList($uid){
//        好友
//        $friend = $this->allList($uid);
        //关注
        $fansService = new FansService();
        $fans = $fansService->getFansList($uid);
        $follow = $fansService->getFollowList($uid);
        $friend = $fansService->getEachOther($uid);

        $rs = array('follow'=>$follow,
            'friend'=>$friend,
            'fans'=>$fans);

        return out_pc(200,$rs);
    }

    //用户离开了会话
    //$type:1进入 2离开
    function userIMSessionStatus($uid,$sessionId,$type){
        if(!$uid){
            return out_pc(8002);
        }

        if(!$sessionId){
            return out_pc(8038);
        }

        $session = ImSessionModel::db()->getById($sessionId);
        if(!$session){
            return out_pc(1017);
        }

        if($session['from_uid'] != $uid || $session['to_uid'] != $uid){
            return out_pc(8259);
        }

        if($uid == $session['to_uid']){
            $data = array('to_uid_unread'=>0);
            ImSessionModel::db()->upById($session['id'],$data);
        }else{
            $data = array('from_uid_unread'=>0);
            ImSessionModel::db()->upById($session['id'],$data);
        }
    }
    //IM接收所有消息
    function receive($fromUid,$toUid,$type,$title = '',$content = ''){
        if(!$fromUid){
            return out_pc(8039);
        }

        if(!$toUid){
            return out_pc(8026);
        }

        if(!$type){
            return out_pc(8004);
        }

//        if(ENV == 'release'){
//            $adminId = 200000;
//        }else{
//            $adminId = 100000;
//        }

        $useService =  new UserService();
        $user = $useService->getUinfoById($toUid);

        $online = 2;
        if(arrKeyIssetAndExist($user,'online') && $user['online'] == 1){
            $online = 1;
        }
        LogLib::appWriteFileHash([$user,$online,'user websocket status online']);
        $rs = 0;
        if($online == 2){//离线状态
            $push = new PushXinGeLib();
            $rs = $push->pushAndroidNotifyOneMsgByToken($toUid,$title,$content);
            LogLib::appWriteFileHash($rs);
            return $rs;
        }
//        $lib = new ImTencentLib();
//        $userStatus = $lib->getUserStatus($user['im_tencent_sign'],$adminId,$toUid);
//        if($userStatus['QueryResult'][0]['State'] != 'Online'){
//            $push = new PushXinGeLib();
//            $rs = $push->pushAndroidNotifyOneMsgByToken($toUid,$title,$content);
//            return $rs;
//        }

        return $rs;

//        if(!ImMsgModel::keyInType($type)){
//            return out_pc(8210);
//        }
//
//        if($type == ImMsgModel::$_type_text || $type == ImMsgModel::$_type_game){
//            if(!$content){
//                return out_pc(8040);
//            }
//        }elseif($type == ImMsgModel::$_type_voice ){
//            $content = "[voice]";
//        }elseif($type == ImMsgModel::$_type_img){
//            $content = "[photo]";
//        }
//
//        if($fromUid == $toUid){
//            return out_pc(8258);
//        }
//        $fansBlack = FansBlackModel::getRow(" uid = $toUid and to_uid = $fromUid");
//        //被拉黑
//        if($fansBlack){
//            return out_pc(8237);
//        }
//
//        $isBlack = FansBlackModel::getRow(" uid = $fromUid and to_uid = $toUid");
//        if($isBlack){
//            return out_pc(8271);
//        }
//
//        $session = ImSessionModel::db()->getRow(" ( from_uid = $toUid and to_uid = $fromUid ) or  ( from_uid = $fromUid and to_uid = $toUid ) ");
//        if($session){
//            $sid = $session['id'];
//            //这里要更新最后时间 ，给取 最近聊天的人用
//            $arr = array('u_time'=>time(),'from_del'=>2,'to_del'=>2);
//            ImSessionModel::db()->upById($session['id'],$arr);
//        }else{
//            $data = array(
//                'from_uid'=>$fromUid,
//                'to_uid'=>$toUid,
//                'a_time'=>time(),
//                'u_time'=>time(),
//                'from_del'=>2,
//                'to_del'=>2,
//            );
//            $sid = ImSessionModel::db()->add($data);
//        }
//
//        $data  = array(
//            'session_id'=>$sid,
//            'from_uid'=>$fromUid,
//            'to_uid'=>$toUid,
//            'a_time'=>time(),
//            'content'=>$content,
//            'type'=>$type,
//            'to_uid_del_time'=>0,
//            'to_uid_del_time'=>0,
//        );
//        $aid = ImMsgModel::db()->add($data);
//
//        //是否被对方  免打扰了
//        $fansBother = FansBotherModel::getRow(" uid = $toUid and to_uid = $fromUid");
//        if($fansBother){
//            $hasBother = 1;
//        }else{
//            $hasBother = 2;
//        }
//        //更新 消息未读数
//        //免打扰，要更新对方的未读数
//        //是否被对方免打扰了
//        if(!$hasBother){
//            if($toUid == $session['to_uid']){
//                $data = array('to_uid_unread'=>array(1));
//                ImSessionModel::db()->upById($session['id'],$data);
//            }else{
//                $data = array('from_uid_unread'=>array(1));
//                ImSessionModel::db()->upById($session['id'],$data);
//            }
//        }
//
//        $cate = $this->imTypeMapMsgCategory($fromUid,$toUid,$type);
//        //同时还要增加一条 PUSH消息
//        $msgService = new MsgService();
//        $rs = $msgService->PTP($fromUid,$toUid,$cate,$content);

//        return out_pc(200,$aid);
    }
    //IM的消息类型，要转换成 PUSH消息的  类型
    function imTypeMapMsgCategory($fromUid,$toUid,$type){
        $fansService = new FansService();
        $isStranger =  $fansService->isFollow($toUid,$fromUid);
        if($isStranger){//不是陌生人
            $first = 5;
        }else{
            $first = 4;
        }

        if($type == ImMsgModel::$_type_text){
            $cate = $first.ImMsgModel::$_type_text;
        }elseif($type == ImMsgModel::$_type_img){
            $cate = $first.ImMsgModel::$_type_img;
//            $content = "[photo]";
        }elseif($type == ImMsgModel::$_type_voice){
            $cate = $first.ImMsgModel::$_type_voice;
//            $content = "[voice]";
        }elseif($type == ImMsgModel::$_type_game){
            $cate = $first.ImMsgModel::$_type_game;
        }

        return $cate;
    }
    //$type:1 单边关注/陌生人 ，2已互相关注
    function getUserSession($uid,$type){
        if(!$type){
            return out_pc(8004);
        }
        $list = ImSessionModel::db()->getAll("   from_uid = $uid   or to_uid = $uid   order by  u_time desc ");
        if(!$list){
            return null;
        }

        $rs = null;
        if($type == 1){//陌生人
            $fansService = new FansService();
            foreach($list as $k=>$v){
                if($v['from_uid'] == $uid){
                    $follow = $v['to_uid'];
                }else{
                    $follow = $v['from_uid'];
                }
                //存在证明，我关注了对方，否则就是陌生人
                $isStranger =  $fansService->isFollow($uid,$follow);
                if(!$isStranger){
                    $rs[] = array('to_uid'=>$follow);
                }
            }
            if($rs){
                $rs = $this->formatList($rs);
                foreach($rs as $k=>$v){
                    $rs[$k]['uid'] = $v['to_uid'];
                    unset($rs[$k]['to_uid']);
                }
            }


        }
        return out_pc(200,$rs);
    }

    function getMsgBySessionId($uid,$sessionId){
        if(!$uid){
            return out_pc(8002);
        }

        if(!$sessionId){
            return out_pc(8038);
        }

        $session = ImSessionModel::db()->getById($sessionId);
        if(!$session){
            return out_pc(1017);
        }

        if($session['from_uid'] != $uid &&  $session['to_uid'] != $uid){
            return out_pc(8259);
        }


        $list =  ImMsgModel::db()->getAll("session_id = $sessionId order by a_time desc ");
        if($list){
            foreach($list as $k=>$v){
                unset($list[$k]['to_uid_del_time']);
                unset($list[$k]['from_uid_del_time']);
                unset($list[$k]['type']);
                unset($list[$k]['session_id']);
            }
        }
        return out_pc(200,$list);
    }
}