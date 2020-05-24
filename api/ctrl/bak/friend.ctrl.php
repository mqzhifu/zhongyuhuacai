<?php
class FriendCtrl extends BaseCtrl {
    //添加黑名单
    function addBlack($toUid){
        $friendInfo = $this->friendService->isFriend($this->uid,$toUid);
        if(!$friendInfo){
            return $this->out(8250);
        }
        //判断我是否已经把对方拉黑了
        if($friendInfo['black_time']){
            return $this->out(8252);
        }

        $data = array('black_time'=>time());

        FriendModel::db()->upById($friendInfo['id'],$data);
    }
    //删除黑名单用户
    function delBlack($toUid){
        $friendInfo = $this->friendService->isFriend($this->uid,$toUid);
        if(!$friendInfo){
            return $this->out(8250);
        }

        if(!$friendInfo['black_time']){
            return $this->out(8253);
        }

        $data = array('black_time'=>"0");

        FriendModel::db()->upById($friendInfo['id'],$data);

    }
    //申请添加好友
    function apply($toUid){
        $rs = $this->friendService->apply($this->uid,$toUid);
        return $this->out($rs['code'],$rs['msg']);
    }
    //单方面删除一个好友
    function del($toUid){
        $this->friendService->del($this->uid,$toUid);
    }
    //我的好友列表
    function getList(){
        $this->friendService->allList($this->uid);
    }
    //同意添加
    function agree($fromUid){
        $rs = $this->friendService->agree($fromUid,$this->uid);

        return $this->out($rs['code'],$rs['msg']);
    }
    //拒绝
    function deny($fromUid){
        $this->friendService->deny($fromUid,$this->uid);
    }
    //设置好友备注名
    function setMemoName($toUid,$name){
        $this->friendService->setMemoName($this->uid,$toUid,$name);
    }

    private function isFriend($uid1,$uid2){
        $row = FriendModel::db()->getRow("from_uid = $uid1 and to_uid = $uid2");
        return $row;
    }
    //获取 别人向自己 发起<添加好友>记录数
    function applyListCnt(){
        $list = $this->friendService->applyList($this->uid);
        return count($list);
    }
}