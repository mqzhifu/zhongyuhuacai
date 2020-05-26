<?php
class FriendService{
    function apply($uid,$toUid){
        if($toUid == $uid){
            return out_pc(8255);
        }

        $isFriend = $this->isFriend($uid,$toUid);
        if($isFriend){
            return out_pc(8248);
        }
        //1个小时内，只能对同一用户，发一次请求
        $applyList = FriendRelationModel::db()->getRow(" from_uid = $uid and to_uid = $toUid");
        $hour = 60 * 60;
        if($applyList){
            if(time() < $applyList['a_time'] + $hour){
                return out_pc(8249);
            }
        }


        $fail_time = 30 * 24 * 60 *60;
        $data = array(
            'from_uid'=>$uid,
            'to_uid'=>$toUid,
            'a_time'=>time(),
            'status'=>1,
            'fail_time'=>time() + $fail_time,
        );
        $aid = FriendRelationModel::db()->add($data);
        return out_pc(200,$aid);
        //发送邀请
//        sendInvite
    }

    function getFriendTwoRecord($fromUid,$toUid){
        $friendList = FriendModel::db()->getAll(" ( from_uid = $fromUid and to_uid = $toUid ) or ( from_uid = $toUid and to_uid = $fromUid ) ");
        if($friendList){
            if(count($friendList) == 2){
                return out_pc(200,$friendList);
            }else{
                return out_pc(8251);
            }

        }else{
            return out_pc(8251);
        }
    }

    //单方面删除一个好友
    function del($fromUid,$toUid){
        $isFriend = $this->isFriend($fromUid,$toUid);
        if(!$isFriend){
            return out_pc(8248);
        }

        $id1 =FriendRelationModel::db()->delete("( from_uid = $fromUid and to_uid = $toUid ) or ( from_uid = $toUid and to_uid = $fromUid ) limit 1 ");

        $id2 = FriendModel::db()->delete("( from_uid = $fromUid and to_uid = $toUid ) or ( from_uid = $toUid and to_uid = $fromUid ) limit 2 ");

        return out_pc(200,$id1. "-".$id2);
//        $relation = FriendRelationModel::db()->getById($freind['relation_id']);
//        if(!$relation){
//
//        }
//
//        if($relation['status']){
//
//        }
//
//        if($relation['from_uid'] == $this->uid){
//            $data = array('from_del'=>time());
//        }else{
//            $data = array('to_del'=>time());
//        }
//
//        FriendRelationModel::db()->upById($relation['id'],$data);
//
//        FriendModel::db()->delete(" from_uid = {$this->uid} and to_uid = $toUid");

    }
    //我的好友列表
    function allList($uid){
        $list = FriendModel::db()->getAll("from_uid =".$uid);
        if($list){
            $list = $this->formatList($list);
        }

        return out_pc(200,$list);
    }
    private function formatList($list){
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

    //同意添加
    function agree($fromUid,$toUid){
        $relation = FriendRelationModel::db()->getRow(" from_uid = $fromUid and to_uid = $toUid ");
        if(!$relation){
            return out_pc(8256);
        }

        if($relation['status'] != 1){
            return out_pc(8257);
        }

        $arr = array( 'status'=>2);
        $upId = FriendRelationModel::db()->upById($relation['id'],$arr);

        $data = array(
            'from_uid'=>$fromUid,'to_uid'=>$toUid,'a_time'=>time(),'relation_id'=>$relation['id'],
        );

        $aid1 = FriendModel::db()->add($data);

        $data = array(
            'from_uid'=>$toUid,'to_uid'=>$fromUid,'a_time'=>time(),'relation_id'=>$relation['id'],
        );

        $aid2 = FriendModel::db()->add($data);

        return out_pc(200,$upId."-".$aid1."-".$aid2);

    }
    //拒绝
    function deny($fromUid,$toUid){
        $relation = FriendRelationModel::db()->getRow(" from_uid = $fromUid and to_uid = $toUid ");
        if(!$relation){
            return out_pc(8256);
        }

        if($relation['status']){
            return out_pc(8257);
        }

        $arr = array('deny_time'=>time(),'status'=>2);
        $upRS = FriendRelationModel::db()->upById($relation['id'],$arr);
        return out_pc(200,$upRS);

    }

    function getOneFriendInfo($fromUid,$toUid){
        return FriendModel::db()->getRow(" from_uid = $toUid and to_uid = $fromUid ");
    }

    //设置好友备注名
    function setMemoName($fromUid,$toUid,$name){
        $isFriend = $this->isFriend($fromUid,$toUid);
        if($isFriend){
            return out_pc(8248);
        }

        $data = array('memo_name'=>$name);
        $upRS = FriendModel::db()->upById($isFriend['id'],$data);
        return out_pc(200,$upRS);
    }
    //
    function recommendFriend(){
        $userListSql = "SELECT uid,nickname,avatar FROM USER WHERE sex = 1  AND id not  IN ( SELECT from_uid FROM friend WHERE from_uid = 1 ) order robot limit 100";
    }

    function isFriend($uid1,$uid2){
        $row = FriendModel::db()->getRow("from_uid = $uid1 and to_uid = $uid2");
        return $row;
    }



    //请求 添加 好友 列表
    function applyList($uid){
        //30天以内的，30天之后的不计入
        $list = FriendRelationModel::db()->getAll(" to_uid = $uid and status = ".FriendRelationModel::$_status_apply." and ".time()." <  fail_time");
        if($list){
            $list = $this->formatList($list);
        }

        return out_pc(200,$list);
    }

    function getBlackList($uid){
        $list =  FriendModel::db()->getAll(" from_uid = $uid and black_time > 0");
        return $list;
    }

}