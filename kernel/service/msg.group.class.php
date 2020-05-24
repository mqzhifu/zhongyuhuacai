<?
//站内信-群发
class Table_msg_group extends Table {
    public $_table = "msg_group" ;
    public $_primarykey = "id";

    public static $_static = false;

    public static function inst() {
        if(false == self::$_static) {
            self::$_static = new self();
        }
        return self::$_static;
    }
    //用户访问(读时):检查是否有群发需要写入到数据库
    function writeOnRead($uid){
        $this->processGroupMsg($uid);		//系统群发
        $this->processGroupPartMsg($uid);	//部分群发
        $this->processUserGroupMsg($uid);	//组发
        
        Table_msg_tagtext::inst()->writeOnRead($uid);	//指定标签发
    }
    
    /**
     * 用户组发
     * @author lixin
     * @param $uid 用户id
     */
    function processUserGroupMsg($uid){
    	$gid = Table_User::inst()->noCache()->getinfofield($uid, 'groupid') ;
    	if(empty($gid))	return ;
    	
    	
    	//获取系统群发集合
    	$sys_msg_redis = Table_msg_group::inst()->getUserGroupMsg($gid);
    	if($sys_msg_redis){
    		//用户已读/已持久化MYSQL-系统群发集合
    		$user_msg_redis =  Table_msg::inst()->getMsgUserGroup($gid, $uid);
    		if($user_msg_redis){
    			//两个集合求差集，持久化到MYSQL
    			$diff_msg = Sys_Redis::inst()->sDiff(sys_user_group_msg_key($gid),sys_user_group_msg_key($gid, $uid));
    			if($diff_msg){
    				$_diff_msg = array() ;
    				foreach($diff_msg as $v ){
    					if (Table_msg_group::inst()->addOneByUserGroup($v,$uid,$gid)){ 
    						array_push($_diff_msg, $v);
    					}
    				}
    				$this->setUserGroupUnread($gid, $uid, $_diff_msg);
    			}
    		}else{
    			foreach($sys_msg_redis as $v ){
    				Table_msg_group::inst()->addOneByUserGroup($v,$uid,$gid);
    			}
    			$this->setUserGroupUnread($gid, $uid, $sys_msg_redis);
    		}
    	}    	
    	
    	
    	
    }
    /**
     * 获取分组消息
     * @author lixin
     * @param unknown $gid 组id
     * @param unknown $uid 用户id 非必填，当有值时查询用户已读的该分组的消息
     */
    function getUserGroupMsg($gid){
    	$_key = sys_user_group_msg_key($gid);
    	$sys_msg_redis = Sys_Redis::inst()->sMembers( $_key );
    	if(!$sys_msg_redis){
    		$sys_msg_text = Table_msg_group::inst()->noCache()->where(" type = 4 and to_gid = {$gid} and is_del  = 0  ")->order(" id desc ")->select();
    		if($sys_msg_text){
    			$rs = array();
    			foreach($sys_msg_text as $k=>$v ){
    				Sys_Redis::inst()->sAdd($_key,$v['id']);
    				$rs[] = $v['id'];
    			}
    	
    			$sys_msg_redis = $rs;
    		}
    	}
    	
    	return $sys_msg_redis;    	
    }
    
    //处理系统群发全部
    function processGroupMsg($uid){
        //获取系统群发集合
        $sys_msg_redis = Table_msg_group::inst()->getMsgTextGroup();
        if($sys_msg_redis){
            //用户已读/已持久化MYSQL-系统群发集合
            $user_msg_redis =  Table_msg::inst()->getMsgGroup($uid);
            if($user_msg_redis){
                //两个集合求差集，持久化到MYSQL
                $diff_msg = Sys_Redis::inst()->sDiff(sys_group_msg_key(),usr_group_msg_key($uid));
                if($diff_msg){
                    $this->setGroupUnread($uid,$diff_msg);
                    foreach($diff_msg as $v ){
                        Table_msg_group::inst()->addOneByGroup($v,$uid);
                    }
                }
            }else{
            	$this->setGroupUnread($uid,$sys_msg_redis);
                foreach($sys_msg_redis as $v ){
                    Table_msg_group::inst()->addOneByGroup($v,$uid);
                }
            }
        }
    }
    //
    /**
     * 组发：更改未读消息数
     * @author lixin
     * @param unknown $gid
     * @param unknown $uid
     * @param unknown $diff_msg
     */
    function setUserGroupUnread($gid, $uid, $diff_msg)
    {
    	$_key = sys_user_group_msg_unread($gid, $uid);
    	$unread_msg = Sys_Redis::inst()->get( $_key );
    	if(!$unread_msg){
    		$cnt = Table_msg::inst()->noCache()->where(" to_uid =  ".$uid."  and type = 4 and to_gid = {$gid} and is_read = 0 and  to_del = 0  ")->selectCount();
    		if($cnt)
    			$unread_msg = $cnt;
    	}else{
	    	$unread_msg += count($diff_msg);
    	}
    	
    	Sys_Redis::inst()->set($_key, $unread_msg);
    }
    //群发：置一条信息的状态(已读未读)
    function setGroupUnread($uid,$diff_msg){
        $unread_msg = Sys_Redis::inst()->get(usr_msg_group_unread($uid));
        if(!$unread_msg){
            if($unread_msg === false){
                $cnt = Table_msg::inst()->noCache()->where(" to_uid =  ".Common::$_userid."  and type = 5 and is_read = 0 and  to_del = 0  ")->selectCount();
                if($cnt)
                    $unread_msg = $cnt;
            }else{
                $unread_msg = 0;
            }
        }

        $unread_msg += count($diff_msg);
        Sys_Redis::inst()->set(usr_msg_group_unread($uid),$unread_msg);
    }
    //部分群发：置一条信息的状态(已读未读)
    function setPartUnread($uid,$num){
        $unread_msg = Sys_Redis::inst()->get(usr_msg_part_unread($uid));
        if(!$unread_msg){
            if($unread_msg === false){
                $cnt = Table_msg::inst()->noCache()->where(" to_uid =  ".Common::$_userid."  and type = 6 and is_read = 0 and  to_del = 0  ")->selectCount();
                if($cnt)
                    $unread_msg = $cnt;
            }else{
                $unread_msg = 0;
            }
        }

        $unread_msg += $num;
        Sys_Redis::inst()->set(usr_msg_part_unread($uid),$unread_msg);
    }
    //获取群发未读总数
    function getGroupUnread($uid){
        $this->processGroupMsg($uid);
        $this->setGroupUnread($uid,array());
        $unread_msg = Sys_Redis::inst()->get(usr_msg_group_unread($uid));
        return $unread_msg;
    }
    //获取部分群发未读总数
    function getPartUnread($uid){
        Table_msg_group::inst()->initPartMsg();
        $this->processGroupPartMsg($uid);
        $unread_msg = Sys_Redis::inst()->get(usr_msg_part_unread($uid));
        return $unread_msg;
    }
    
    /**
     * lixin@20151126 获取用户组未读消息总数
     * @param unknown $uid
     */
    function getSysUserGroupUnread($uid){
    	$gid = Table_User::inst()->noCache()->getinfofield($uid, 'groupid') ;
    	$__key = sys_user_group_msg_unread($gid, $uid) ;
    	$this->processUserGroupMsg($uid); //组发
    	$unread_msg = Sys_Redis::inst()->get( $__key );
    	return $unread_msg;
    }
    
    //处理部分群发及商家群发
    function processGroupPartMsg($uid){
        Table_msg_group::inst()->initPartMsg();
        $key = usr_msg_part_key($uid);
        if (!Sys_Redis::inst()->exists($key)){
        	$list = Table_msg_group::inst()->where(" status = 2 and find_in_set('".$uid."', `to_uid`)")->noCache()->select();
        	if($list){
        		foreach($list as $k=>$v ){
        			$n = $k+1;
        			Table_msg_group::inst()->setPartUnread($uid,0);
        			$msg = Table_msg::inst()->where(" to_uid = $uid and content_id = ".$v['id'])->autoClearCache()->selectOne();
        			if(!$msg)
        				Sys_Redis::inst()->sAdd(usr_msg_part_key($uid),$v['id']);
        		}
        		 
        	}
        }
        
        $list = Sys_Redis::inst()->sMembers($key);
        if($list){
            $this->setPartUnread($uid,count($list));
            foreach($list as $k=>$v ){
                Table_msg::inst()->addOneByPart($v,$uid);
            }
        }
    }
    
    //redis持久化-群发时调用这个
    // lixin@20151125
    function addOneByUserGroup($msg_text_id, $uid, $gid){
    	$msg_text = Table_msg_group::inst()->getInfo($msg_text_id);
    	if($msg_text && $msg_text['to_gid']==$gid){
    		$data = array(
    				'send_uid'=>$msg_text['send_uid'],
    				'to_uid'=>$uid,
    				'to_gid'=>$msg_text['to_gid'],
    				'title'=>$msg_text['title'],
    				'type'=>$msg_text['type'],
    				'content_id'=>$msg_text_id,
    				'add_time'=>time(),
    		);
    
    		Table_msg::inst()->addData($data)->add();
    		$id = Table_msg::inst()->insertId();
    		if($id){
    			$key = sys_user_group_msg_key($gid, $uid) ;
    			Sys_Redis::inst()->sAdd($key,$msg_text_id);
    			return true;
    		}
    
    	}
    
    	return false;
    }
    
    //redis持久化-群发时调用这个
    // lixin@20151124
    function addOneByGroup($msg_text_id,$uid){
        $msg_text = Table_msg_group::inst()->noCache()->getInfo($msg_text_id);
        if($msg_text){
            $data = array(
                'send_uid'=>$msg_text['send_uid'],
                'to_uid'=>$uid,
				'to_gid'=>$msg_text['to_gid'],
                'title'=>$msg_text['title'],
                'type'=>$msg_text['type'],
                'content_id'=>$msg_text_id,
                'add_time'=>time(),
            );

            Table_msg::inst()->addData($data)->add();
            $id = Table_msg::inst()->insertId();
            if($id){
                $key = usr_group_msg_key(   $uid );
            	if (!empty($msg_text['to_gid'])){
            		$key = sys_user_group_msg_key($msg_text['to_gid'], $uid) ;
            	}
                Sys_Redis::inst()->sAdd($key,$msg_text_id);
                return true;
            }
            
        }
        
        return false;
    }
    
    /**
     * 添加用户分组数据（组id对应user表groupid , 3 为普通用户）
     * @param uid 发信管理员id
     * @param gid 接收用户组id
     * @param title 标题
     * @param content 内容
     * @param link 链接
     * @param return_type 返回值类型 ajax/pc
     * @param src 图片id
     */
    function addOneUserGroupMsg($uid, $gid, $title, $content, $link, $return_type = 'ajax', $src=0)
    {
    	$data = array(
    			'title'=>$title,
    			'content'=>$content,
    			'type'=>4,
    			'link'=>$link,
    			'send_uid'=>$uid,
    			'to_gid'=>$gid,
    			'add_time'=>time(),
    			'img'=>$src,
    	);
    	
    	Table_msg_group::inst()->addData($data)->add();
    	$content_id = Table_msg_group::inst()->insertId();
    	if(!$content_id)
    		return out(505,"add msg_text in db is failed...",1);
    	
    	Sys_Redis::inst()->sAdd(sys_user_group_msg_key($gid),$content_id);
    	
    	return out(200,$content_id,0,$return_type);
    }
    
    //添加一条群发数据
    function addOneGroupMsg($uid,$title,$content,$type,$link,$to_uid = "",$return_type = 'ajax',$src= ""){
        $data = array(
            'title'=>$title,
            'content'=>$content,
            'type'=>$type,
            'link'=>$link,
            'send_uid'=>$uid,
            'to_uid'=>$to_uid,
            'add_time'=>time(),
            'img'=>$src,
        );

        Table_msg_group::inst()->addData($data)->add();
        $content_id = Table_msg_group::inst()->insertId();
        if(!$content_id)
            return out(505,"add msg_text in db is failed...",1);

        Sys_Redis::inst()->sAdd(sys_group_msg_key(),$content_id);

        return out(200,$content_id,0,$return_type);
    }
    //添加一条群发数据
    function addOnePartMsg($uid,$title,$content,$type,$link,$to_uid = "",$return_type = 'ajax',$src= ""){
        $data = array(
            'title'=>$title,
            'content'=>$content,
            'type'=>$type,
            'link'=>$link,
            'send_uid'=>$uid,
            'to_uid'=>$to_uid,
            'add_time'=>time(),
            'img'=>$src,
        );

        Table_msg_group::inst()->addData($data)->add();
        $content_id = Table_msg_group::inst()->insertId();
        if(!$content_id)
            return out(505,"add msg_text in db is failed...",1);
        
        $uidArr = explode(',', $to_uid);
        if (!empty($uidArr)){
        	foreach ($uidArr as $_uid){
	        	$key = usr_msg_part_key($_uid);
	        	Sys_Redis::inst()->sAdd($key,$content_id);
        	}
        }

        return out(200,$content_id,0,$return_type);
    }
    //获取系统群发集合
    function getMsgTextGroup(){
        $sys_msg_redis = Sys_Redis::inst()->sMembers(sys_group_msg_key());
        if(!$sys_msg_redis){
            $sys_msg_text = Table_msg_group::inst()->autoClearCache()->where(" type = 5 and is_del  = 0  ")->order(" id desc ")->select();
            if($sys_msg_text){
                $rs = array();
                foreach($sys_msg_text as $k=>$v ){
                    Sys_Redis::inst()->sAdd(sys_group_msg_key(),$v['id']);
                    $rs[] = $v['id'];
                }

                $sys_msg_redis = $rs;
            }
        }

        return $sys_msg_redis;
    }
    //当清空REDIS后，部分群发是没有持久化的，需要重新读取插入到REDIS中
    function initPartMsg(){
        $exist = Sys_Redis::inst()->get(msg_redis_exist());
        if(!$exist){
            $list = Table_msg_group::inst()->where(" status = 2 ")->noCache()->select();
            if($list){
                foreach($list as $k=>$v ){
                    $n = $k+1;

                    if( !isset($v['to_uid']) || !$v['to_uid']){
                        continue;
                    }

                    $uids = explode(",",$v['to_uid']);
                    foreach($uids as $k2=>$uid ){
                        Table_msg_group::inst()->setPartUnread($uid,0);
                        $msg = Table_msg::inst()->where(" to_uid = $uid and content_id = ".$v['id'])->autoClearCache()->selectOne();
                        if(!$msg)
                            Sys_Redis::inst()->sAdd(usr_msg_part_key($uid),$v['id']);

                    }
                }

                Sys_Redis::inst()->set(msg_redis_exist(),1);
            }
        }
    }
}