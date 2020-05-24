<?php
//站内信-个人
class MsgService {

    //$type:1 P2P ,2S2P
    function getList($uid,$page = 1,$type = 0,$cate = 0){
        if(!$uid){
            return out_pc(8002);
        }


        $where = " to_uid = $uid ";

        //收件人 未删除
//        $where = " to_del = 2 ";
//        if($cate){
//            $where .= " and `type` IN ( {$cate} ) ";
//        }
//        if($type){
//            if($type == 1){
//                $where .= " and to_del = 1 and to_uid = $uid";
//            }else{
//                $where .= " and from_del = 1 and from_uid = $uid";
//            }
//        }else{
//            $where .= " and to_del = 1 and send_del = 1 and ( to_uid = $uid or from_uid = $uid ) ";
//        }


//        $strangerMsgCateIds =   MsgModel::$_cate_stranger_im_game ." , ".  MsgModel::$_cate_stranger_im_img ." , ". MsgModel::$_cate_stranger_im_text." , ". MsgModel::$_cate_stranger_im_voice;
//        $where .= " cate not in ( $strangerMsgCateIds )";

        $strangerLastMsg = 0;
        $list = MsgModel::db()->getAll($where . " order by a_time desc ");
        if(!$list){
            return out_pc(200);
        }

        $rs = array();
        foreach($list as $k=>$v){
            //陌生人的消息 要聚合，只显示最后一条就够了
            if(MsgModel::keyInCateStranger($v['category'])){
                if($strangerLastMsg){
                    continue;
                }else{
                    $strangerLastMsg = 1;
                }
            }

            $rs[] = $v;
        }

        return out_pc(200,$rs);

    }


    //点到点时，添加用这个
    //type,1:用户之间，3系统对用户
    function PTP($fromUid,$toUid,$cate,$content,$title = ""){
        if(!$fromUid){
            return out_pc(8039);
        }
        if(!$toUid){
            return out_pc(8026);
        }

        if(!$content){
            return out_pc(8040);
        }

        if(!$cate){
            return out_pc(8041);
        }
        if(!MsgModel::keyInCate($cate)){
            return out_pc(8265);
        }

        $userService = new UserService();
        $user = $userService->getUinfoById($toUid);
        if($user['push_status'] == 2){//用户关闭了PUSH
            return out_pc(8265);
        }

        $data = array(
            'from_uid'=>$fromUid,
            'to_uid'=>$toUid,
            'title'=>$title,
            'type'=>MsgModel::$_type_p2p,
            'content'=>$content,
            'a_time'=>time(),
            'category'=>$cate,
        );


        //是否被对方  免打扰了
        $fansBother = FansBotherModel::db()->getById(" uid = $fromUid and to_uid = $toUid");
        //处理已读/未读
        if(!$fansBother){
            $data['is_read'] = 2;
        }else{
            $data['is_read'] = 1;
        }

        $msgId = MsgModel::db()->add($data);
        return out_pc(200,$msgId);

    }

    //点到点时，添加用这个
    //type,1:用户之间，3系统对用户
    function STP($toUid,$cate,$content,$title = ""){
        $fromUid = 100000;
        if(!$toUid){
            return out_pc(8026);
        }

        if(!$content){
            return out_pc(8040);
        }

        if(!$cate){
            return out_pc(8041);
        }

        if(!MsgModel::keyInCate($cate)){
            return out_pc(8265);
        }

        $userService = new UserService();
        $user = $userService->getUinfoById($toUid);
        if($user['push_status'] == 2){//用户关闭了PUSH
            return out_pc(8265);
        }

        $userService = new UserService();
        $user = $userService->getUinfoById($toUid);
        if($user['push_status'] == 2){//用户关闭了PUSH
            return out_pc(8236);
        }

        $data = array(
            'from_uid'=>$fromUid,
            'to_uid'=>$toUid,
            'title'=>$title,
            'type'=>MsgModel::$_type_s2p,
            'content'=>$content,
            'a_time'=>time(),
            'category'=>$cate,
        );
        $data['is_read'] = 2;

        $msgId = MsgModel::db()->add($data);
        return out_pc(200,$msgId);

    }

    //累加 消息未读数
    function addUnreadNum($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['msgUnread']['key'],$uid);
        RedisPHPLib::getServerConnFD()->incr($key);
    }

    function lessUnreadNum($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['msgUnread']['key'],$uid);
        RedisPHPLib::getServerConnFD()->decr($key);
    }

    //$type:1接收者删除，2发送者删除
    function delOneById($id,$uid){
        $msg = Table_msg::inst()->noCache()->getInfo($id);
        if(!$msg){

        }

        if($uid == $msg['to_uid']){
            $data = array("to_del"=>1);
            if( $msg['is_read'] == 1 ){//未读
                $this->lessUnreadNum();
            }
        }else{
            $data = array("from_del"=>1);
        }




//        if (5==$msg['type']){
//            $unread_num = Sys_Redis::inst()->get(usr_msg_unread_key($msg['to_uid']));
//            if($unread_num){
//                $unread_num = $unread_num - 1;
//                if($unread_num <= 0)
//                    $unread_num = 0;
//            }else
//                $unread_num = 0;
//            Sys_Redis::inst()->set(usr_msg_unread_key($msg['to_uid']),$unread_num);
//        }elseif (6==$msg['type']){
//            $unread_num = Sys_Redis::inst()->get(usr_msg_part_unread($msg['to_uid']));
//            if($unread_num){
//                $unread_num = $unread_num - 1;
//                if($unread_num <= 0)
//                    $unread_num = 0;
//            }else
//                $unread_num = 0;
//
//            Sys_Redis::inst()->set(usr_msg_part_unread($msg['to_uid']),$unread_num);
//        }elseif ( 4 == $msg['type'] && !empty($msg['to_gid']) ){
//            // lixin@20151125
//            $__key = sys_user_group_msg_unread($msg['to_gid'], $msg['to_uid']) ; //
//            $unread_num = Sys_Redis::inst()->get( $__key );
//            if($unread_num){
//                $unread_num = $unread_num - 1;
//                if($unread_num <= 0)
//                    $unread_num = 0;
//            }else
//                $unread_num = 0;
//
//            Sys_Redis::inst()->set($__key,$unread_num);
//        }


        Table_msg::inst()->addData($data)->edit($id);

        return out(200,'ok',0,'pc');
    }

    /**
     * 为普通用户发一条系统通知
     */
//    function addSystemToPerson($toUid, $title, $content)
//    {



//    	$to_uid = intval($to_uid);
//    	if (!$to_uid)
//    		return out_err('invalid param to_uid '.$to_uid) ;
//
//    	$data = array(
//    			'content'=>$content,
//    			'type'=>self::$_TYPE_NOTIFY,
//    			'uid'=>$uid,
//    	);
//    	Table_msg_text::inst()->addData($data)->add();
//    	$content_id = Table_msg_text::inst()->insertId();
//    	if(!$content_id)
//    		return out(505,"add msg_text in db is failed...",1,'pc');

//        $send_uid = '999';
//    	$data = array(
//    			'from_uid'=>$send_uid,
//    			'to_uid'=>$to_uid,
//    			'title'=>$title,
//    			'type'=>self::$_TYPE_S2P,
//    			'content'=>$content,
//    			'add_time'=>time(),
//    			'is_read'=>1,//未读
//    	);
//
//    	$msg_id = MsgModel::db()->add($data);
//    	if(!$msg_id)
//    		return out(505,"add msg in db is failed...",1);

//    	$key =
//    	$num = RedisPHPLib::get($key);
//    	if(!$num)
//    		$num = 0;
//
//    	Sys_Redis::inst()->set($user_msg_unread_key,++$num);
//
//    	return out(200,$msg_id,0,'pc');


//        $this->addUnreadNum($to_uid);

//    }


//    //redis-持久化-部分群发
//    function addOneByPart($msg_text_id,$uid ){
//        $msg_text = Table_msg_group::inst()->getInfo($msg_text_id);
//        if($msg_text){
//            $data = array(
//                'send_uid'=>$msg_text['send_uid'],
//                'to_uid'=>$uid,
//                'title'=>$msg_text['title'],
//                'type'=>$msg_text['type'],
//                'content_id'=>$msg_text_id,
//                'add_time'=>time(),
//                'is_read'=>1,
//            );
//
//            Table_msg::inst()->addData($data)->add();
//            $id = Table_msg::inst()->insertId();
//            if($id){
//                $key = usr_msg_part_key($uid);
//                Sys_Redis::inst()->sRemove($key,$msg_text_id);
//            }
//        }
//        return out(200,"ok",0,'pc');
//    }



    /**
     * 获取未读消息数
     * @param unknown $uid
     * @param number $type<br>
     * 0:所有<br>
     * 1用户之间私信、系统私信<br>
     * 2商家群发、用户组、系统群发、系统部分发<br>
     * 3用户组发<br>
     * 4用户标签发送<br>
     * @return int 未读消息数
     */
    function getUserUnreadNum($uid,$type = 0){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['msgUnread']['key'],$uid);
        $cnt =  RedisPHPLib::getServerConnFD()->get($key);
        if(!$cnt){
            $cnt= 0;
        }

        return $cnt;

//        $unread_num = 0;
//        if(!$type){
//            $point_unread = $this->getUserPointUnreadNum($uid);
//            $part_unread = Table_msg_group::inst()->getPartUnread($uid);
//            $group_msg_unread = Table_msg_group::inst()->getGroupUnread($uid);
//            $ugroup_msg_unread = Table_msg_group::inst()->getSysUserGroupUnread($uid);
//
//            $unread_num = $point_unread + $part_unread + $group_msg_unread + $ugroup_msg_unread;
//        }elseif($type == 1){
//        }elseif($type == 2){
//        }elseif($type == 3){
//        	$ugroup_msg_unread = Table_msg_group::inst()->getSysUserGroupUnread($uid);
//            $unread_num = $ugroup_msg_unread ;
//        }elseif($type == 4){
//        	$point_unread = $this->getMemberPointUnreadNum($uid);
//        	$tag_unread = Table_msg_tagtext::inst()->getSysUserTagUnread($uid);
//        	//$unread_num = Table_msg_tagtext::inst()->getSysUserTagUnread($uid);
//        	$unread_num = $point_unread + $tag_unread ;
//        }
//        return $unread_num;
    }

    public function getMemberPointUnreadNum( $uid ) {
    	$point_unread = Sys_Redis::inst()->get(usr_msg_member_unread_key(  $uid ));
    	if(!$point_unread){
    		if($point_unread === false){
    			$num =  Table_msg::inst()->noCache()->where("  to_uid = $uid and type = ".Table_msg::$_TYPE_NOTIFY." and to_del = 0 and is_read = 0 ")->selectCount();
    			if($num){
    				Sys_Redis::inst()->set(usr_msg_member_unread_key(  $uid ),$num);
    				$point_unread = $num;
    			}else
    				$point_unread = 0;
    		}else
    			$point_unread = 0;
    	}

    	return $point_unread;
    }

	/**
	 * 获取用户私信未读数  type 1/3  用户私信/系统私信
	 * @param int $uid
	 */
    public function getUserPointUnreadNum( $uid ) {
    	$point_unread = Sys_Redis::inst()->get(usr_msg_unread_key(  $uid ));
    	if(!$point_unread){
    		if($point_unread === false){
    			$num =  Table_msg::inst()->noCache()->where("  to_uid = $uid and ( type = 1 or type = 3 ) and to_del = 0 and is_read = 0 ")->selectCount();
    			if($num){
    				Sys_Redis::inst()->set(usr_msg_unread_key(  $uid ),$num);
    				$point_unread = $num;
    			}else
    				$point_unread = 0;
    		}else
    			$point_unread = 0;
    	}

    	return $point_unread;
    }

    //用户已读/已持久化MYSQL-系统群发集合
    function getMsgGroup($uid){
        $key = usr_group_msg_key($uid);
        $sys_msg_redis = Sys_Redis::inst()->sMembers($key);
        if(!$sys_msg_redis){
            $sys_msg_text = Table_msg::inst()->autoClearCache()->where(" type = 5 and to_uid = $uid ")->order(" id desc")->select();
            if($sys_msg_text){
                $rs = array();
                foreach($sys_msg_text as $k=>$v ){
                    Sys_Redis::inst()->sAdd($key,$v['content_id']);
                    $rs[] = $v['content_id'];
                }

                $sys_msg_redis = $rs;
            }
        }

        return $sys_msg_redis;
    }

    /**
     * 用户已读/已持久化MYSQL-系统分标签发集合
     * @param unknown $tid
     * @param unknown $uid
     */
    public function getMsgUserTag($tid, $uid){
    	$key = sys_user_tag_msg_key($tid, $uid);
    	$sys_msg_redis = Sys_Redis::inst()->sMembers($key);
    	if(!$sys_msg_redis){
    		$sys_msg_text = Table_msg::inst()->noCache()->where(" type = 7 and to_tid = {$tid} and to_uid = $uid ")->order(" id desc")->select();
    		if($sys_msg_text){
    			$rs = array();
    			foreach($sys_msg_text as $k=>$v ){
    				Sys_Redis::inst()->sAdd($key,$v['content_id']);
    				$rs[] = $v['content_id'];
    			}

    			$sys_msg_redis = $rs;
    		}
    	}

    	return $sys_msg_redis;
    }

    /**
     * 用户已读/已持久化MYSQL-系统分组发集合
     * @author lixin
     */
    function getMsgUserGroup($gid, $uid){
    	$key = sys_user_group_msg_key($gid, $uid);
    	$sys_msg_redis = Sys_Redis::inst()->sMembers($key);
    	if(!$sys_msg_redis){
    		$sys_msg_text = Table_msg::inst()->noCache()->where(" type = 5 and to_gid = {$gid} and to_uid = $uid ")->order(" id desc")->select();
    		if($sys_msg_text){
    			$rs = array();
    			foreach($sys_msg_text as $k=>$v ){
    				Sys_Redis::inst()->sAdd($key,$v['content_id']);
    				$rs[] = $v['content_id'];
    			}

    			$sys_msg_redis = $rs;
    		}
    	}

    	return $sys_msg_redis;
    }



//    function getMsgContent($id,$type){
//        if($type == self::$_TYPE_P2P || $type == self::$_TYPE_S2P || $type == self::$_TYPE_NOTIFY)
//            $content = Table_msg_text::inst()->getInfo($id);
//        else if ($type == Table_msg::$_TYPE_S2T)
//        	$content = Table_msg_tagtext::inst()->getInfo($id);
//        else
//            $content = Table_msg_group::inst()->getInfo($id);
//
//        return $content;
//    }

    function upUnread($uid,$ids,$del = 0){
        if(!$ids)
            return 0;
        if(!$uid)
            return 0;

        $msg_ids = explode(",",$ids);
//         $num = count($msg_ids);
		$num = 1;
        foreach($msg_ids as $k=>$v ){
            $msg = Table_msg::inst()->getInfo($v);
            if(!$msg)
                continue;
            if(empty($del)){
                if($msg['is_read'])//已读，无须其它处理
                    continue;
                if($msg['send_uid'] == $uid)//发件者查看，是不能算已读状态
                    continue;
            }
            if(1 == $msg['type'] || 3 == $msg['type']){
                $unread_num = Sys_Redis::inst()->get(usr_msg_unread_key($uid));
                if($unread_num){
                    $unread_num = $unread_num - $num;
                    if($unread_num <= 0)
                        $unread_num = 0;
                }else
                    $unread_num = 0;

                Sys_Redis::inst()->set(usr_msg_unread_key($uid),$unread_num);
            }elseif(8 == $msg['type']){
                $unread_num = Sys_Redis::inst()->get(usr_msg_member_unread_key($uid));
                if($unread_num){
                    $unread_num = $unread_num - $num;
                    if($unread_num <= 0)
                        $unread_num = 0;
                }else
                    $unread_num = 0;

                Sys_Redis::inst()->set(usr_msg_member_unread_key($uid),$unread_num);
            }elseif(5 == $msg['type']){
                $unread_num = Sys_Redis::inst()->get(usr_msg_group_unread($uid));
                if($unread_num){
                    $unread_num = $unread_num - $num;
                    if($unread_num <= 0)
                        $unread_num = 0;
                }else
                    $unread_num = 0;

                Sys_Redis::inst()->set(usr_msg_group_unread($uid),$unread_num);
            }elseif( 3  == $msg['type'] || 6  == $msg['type'] ){
                $unread_num = Sys_Redis::inst()->get(usr_msg_part_unread($uid));
                if($unread_num){
                    $unread_num = $unread_num - $num;
                    if($unread_num <= 0)
                        $unread_num = 0;
                }else
                    $unread_num = 0;

                Sys_Redis::inst()->set(usr_msg_part_unread($uid),$unread_num);
            }elseif ( 4 == $msg['type'] && !empty($msg['to_gid']) ){
            	// lixin@20151125
            	$__key = sys_user_group_msg_unread($msg['to_gid'], $uid) ; //
            	$unread_num = Sys_Redis::inst()->get( $__key );
            	if($unread_num){
            		$unread_num = $unread_num - $num;
            		if($unread_num <= 0)
            			$unread_num = 0;
            	}else
            		$unread_num = 0;

            	Sys_Redis::inst()->set($__key,$unread_num);
            }elseif ( 7 == $msg['type'] && !empty($msg['to_tid']) ){
            	// lixin@20151125
            	$__key = sys_user_tag_msg_unread($msg['to_tid'], $uid) ; //
            	$unread_num = Sys_Redis::inst()->get( $__key );
            	if($unread_num){
            		$unread_num = $unread_num - $num;
            		if($unread_num <= 0)
            			$unread_num = 0;
            	}else
            		$unread_num = 0;

            	Sys_Redis::inst()->set($__key,$unread_num);
            }
            if(empty($del)){
                Table_msg::inst()->addData(array('is_read'=>1))->where(" id = $v ")->edit();
            }else{
                Table_msg::inst()->addData(array('to_del'=>1))->where(" id = $v ")->edit();
            }

        }



        return 1;

    }

//    function getOneDetail($uid,$id ,$return_type = 'ajax'){
//        $msg = Table_msg::inst()->getInfo($id);
//        if(!$msg)
//            return  out(501,'id 错误，不在DB中',1,'pc');
//        if($msg['to_uid'] != $uid && $msg['send_uid'] != $uid)
//            return  out(501,'该信息不属于该UID',1,'pc');
//
//        $content = Table_msg::inst()->getMsgContent($msg['content_id'],$msg['type']);
//        $msg['content'] = $content['content'];
//        return out(200,$msg,0,$return_type);
//    }
}
