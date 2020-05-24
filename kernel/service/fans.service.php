<?php
class FansService  {
    private $_fans_limit = 200;
    //获取我关注别人的列表
    function getFollowList($uid){
        $black = FansBlackModel::db()->getAll(" uid = $uid  ");
        if($black){
            $blackUids = split_arr_sql($black,"to_uid");
            $list = FansModel::db()->getAll(" uid = $uid and to_uid not in  ($blackUids) ");
        }else{
            $list = FansModel::db()->getAll(" uid = ".$uid);
        }
        if($list){
            $list = $this->formatList($list,1);
        }

        foreach($list as $k=>$v){
            $list[$k]['uid'] = $v['to_uid'];
            unset($list[$k]['id']);
            unset($list[$k]['to_uid']);
            unset($list[$k]['a_time']);
        }
        return $list;
    }
    //获取关注我的列表
    function getFansList($uid){
        $list = FansModel::db()->getAll(" to_uid = ".$uid);
        if($list){
            $list = $this->formatList($list);
        }

        foreach($list as $k=>$v){
//            $list[$k]['uid'] = $v['to_uid'];
            $list[$k]['uid'] =  $v['uid'];
            unset($list[$k]['id']);
            unset($list[$k]['to_uid']);
            unset($list[$k]['a_time']);
        }

        return $list;
    }
    //关注添加
    function add($uid,$toUid){
        if(!$uid){
            return out_pc(8002);
        }

        if(!$toUid){
            return out_pc(8026);
        }

        if($uid == $toUid){
            return out_pc(8254);
        }

        $userService = new UserService();
        $toUserInfo = $userService->getUinfoById($toUid);
        if(!$toUserInfo){
            return out_pc(1020);
        }

        $isFollow = $this->isFollow($uid,$toUid);
        if($isFollow){//已经关注过了
            return out_pc(8247);
        }

        $follow = FansModel::db()->getCount(" uid = $uid");
        if($follow && $follow >= $this->_fans_limit){
            return out_pc(8272);
        }

        $data = array(
            'a_time'=>time(),
            'to_uid'=>$toUid,
            'uid'=>$uid,
        );

        $aid = FansModel::db()->add($data);

        //判断对方是否关注过我
        $isFollowed = $this->isFollow($toUid,$uid);
        if($isFollowed){
            //处理互相关注的情况
            $eachOther = FansEachOtherModel::db()->getRow("   uid = $uid and to_uid = $toUid   ");
            if(!$eachOther){
                $data = array(
                    'a_time'=>time(),
                    'to_uid'=>$toUid,
                    'uid'=>$uid,
                );

                FansEachOtherModel::db()->add($data);

                $data = array(
                    'a_time'=>time(),
                    'to_uid'=>$uid,
                    'uid'=>$toUid,
                );

                FansEachOtherModel::db()->add($data);

            }
        }


        $lib = new TaskService();
        $rs = $lib->trigger($uid,4);
//        var_dump($rs);exit;


//        $content = $toUserInfo['nickname']." 关注了你";
//        $msgService =  new MsgService();
//        $rs = $msgService->PTP($uid,$toUid,MsgModel::$_cate_followed_me,$content);
////        var_dump($rs);
//
//
//        $content = "我 关注了".$toUserInfo['nickname'];
//        $msgService =  new MsgService();
//        $rs = $msgService->STP($uid,MsgModel::$_cate_follow_other,$content);
//        var_dump($rs);
        return out_pc(200,$aid);
    }
    //互相关注过的
    function getEachOther($uid){
        $list = FansEachOtherModel::db()->getAll(" uid = $uid");
        if(!$list){
            return [];
        }

        $list = $this->formatList($list,1);

        foreach($list as $k=>$v){
            $list[$k]['uid'] = $v['to_uid'];
            unset($list[$k]['id']);
            unset($list[$k]['to_uid']);
            unset($list[$k]['a_time']);
        }

        return $list;
    }

    //取消关注
    function cancel($uid,$toUid){
        if(!$uid){
            return out_pc(8002);
        }

        if(!$toUid){
            return out_pc(8026);
        }

        if($uid == $toUid){
            return out_pc(8254);
        }

        $userService = new UserService();
        $toUserInfo = $userService->getUinfoById($toUid);
        if(!$toUserInfo){
            return out_pc(1020);
        }

        $isFollow = $this->isFollow($uid,$toUid);
        if(!$isFollow){
            return out_pc(8266);
        }

        $rs = FansModel::db()->delete("uid = $uid and to_uid = $toUid limit 1" );


        //处理互相关注的情况
        $eachOther = FansEachOtherModel::db()->getRow("   uid = $uid and to_uid = $toUid   ");
        if($eachOther){
            FansEachOtherModel::db()->delById($eachOther['id']);
            $eachOther = FansEachOtherModel::db()->getRow("   uid = $toUid and to_uid = $uid   ");
            FansEachOtherModel::db()->delById($eachOther['id']);
        }



        return out_pc(200,$rs);

    }

    function formatList($list,$type = 0){
        $userService = new UserService();
        foreach($list as $k=>$v){
            if($type){
                $user = $userService->getUinfoById($v['to_uid']);
            }else{
                $user = $userService->getUinfoById($v['uid']);
            }

            if($user){
                $list[$k]['nickname'] = isset($user['nickname']) ? $user['nickname'] : "未知";
                $list[$k]['avatar'] = $user['avatar'];
                $list[$k]['sex'] = isset($user['sex']) ? $user['sex'] : "未知";
            }else{
                $list[$k]['nickname'] = "未知";
                $list[$k]['avatar'] = "未知";
                $list[$k]['sex'] = "未知";
            }
        }

        return $list;
    }
    //是否已关注
    function isFollow($fromUid,$toUid){
        $row = FansModel::db()->getRow("uid = $fromUid and to_uid = $toUid");
        return $row;
    }

    //推荐 - 关注用户列表
    // 异性、非关注、非游客
    function recommendList($uid,$limit = 5,$page = 1){
        if(!$uid){
            return out_pc(8001);
        }
        $sexWhere = "";
        $userService = new UserService();
        //获取当前用户的性别
        $uSex = $userService->getFieldById($uid,'sex');
        if($uSex){
            if($uSex == 1){
                $sex = 2;
            }else{
                $sex = 1;
            }
            $sexWhere = " and sex = $sex ";
        }

        $rs = array(
            'pageInfo' => array('start'=>0,'end'=>0,'nextPage'=>0,'totalPage'=>0),
            'list'=>[],
        );


        //过滤掉黑名单
        $blackUids = "";
        $black = FansBlackModel::db()->getAll(" uid = $uid  ");
        if($black){
            $blackUids = split_arr_sql($black,"to_uid");
        }

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['recomm_rand_order']['key'],"" ,IS_NAME);
        $rand = RedisPHPLib::getServerConnFD()->get($key);
        if(!$rand){
            //算法 每10分钟得变动一次，不然永远是同样的一个人，出现在固定的位置上
            $randOrder = array("id",'robot','type','goldcoin','goldcoin_sum_less');

            $randNum = rand(0,4);
            $rand = $randOrder[$randNum];
            RedisPHPLib::getServerConnFD()->set($key,$rand, $GLOBALS['rediskey']['recomm_rand_order']['expire']);
        }

        //取出已关注列表
        $follow = $this->getFollowList($uid);
        if($follow){
            $followUids = split_arr_sql($follow,'uid');
            //将黑名单与 已关注的UID合并
            if($blackUids){
                $followUids .= ",".$blackUids;
            }

            $userListSql = "SELECT id,nickname,avatar,sex FROM user WHERE type != 10 and avatar is not null  and id != $uid $sexWhere  AND id not  IN ( $followUids ) order by $rand desc limit 100";
        }else{
            if($blackUids){
                $userListSql = "SELECT id,nickname,avatar,sex FROM user WHERE type != 10 and avatar is not null and  id != $uid $sexWhere AND id not  IN ( $blackUids )  order by $rand desc limit 100";
            }else{
                $userListSql = "SELECT id,nickname,avatar,sex FROM user WHERE type != 10 and avatar is not null and  id != $uid $sexWhere  order by $rand desc limit 100";
            }
        }
        $userList = UserModel::db()->getAllBySQL($userListSql);
        if(!$userList){
            return out_pc(200,$rs);
        }
        $cnt = count($userList);
        $pageInfo = PageLib::getPageInfo($cnt,$limit,$page);

        $finalUserList = [];
        if( $cnt > $limit){//这里需要随机了，保证每次刷新获取新的推荐人，都是不同的
            $end = $pageInfo['end'];
            $start = $pageInfo['start'];

            for($i=$start;$i<=$end;$i++){
                $userList[$i]['uid'] = $userList[$i]['id'];
                unset($userList[$i]['id']);
                $finalUserList[] = $userList[$i];
            }
        }else{
            for($i=0;$i<$cnt;$i++){
                $userList[$i]['uid'] = $userList[$i]['id'];
                unset($userList[$i]['id']);
                $finalUserList[] = $userList[$i];
            }
        }

        if($finalUserList){
            foreach ($finalUserList as $k=>$v) {
                $user = $userService->getUinfoById($v['uid']);
                $finalUserList[$k]['avatar'] = $user['avatar'];
            }
        }

//        LogLib::appWriteFileHash($finalUserList);

        $rs['pageInfo'] = $pageInfo;
        $rs['list'] = $finalUserList;

        return out_pc(200,$rs);

    }
}