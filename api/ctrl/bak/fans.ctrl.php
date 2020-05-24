<?php
class FansCtrl extends BaseCtrl {
    //获取我关注别人的列表
    function getFollowList(){
        $list = $this->fansService->getFollowList($this->uid);
        return $this->out(200,$list);
    }
    //获取关注我的列表
    function getFansList(){
        $list = $this->fansService->getFansList($this->uid);
        return $this->out(200,$list);
    }
    //关注添加
    function add($toUid){
        $rs = $this->fansService->add($this->uid,$toUid);
        return $this->out($rs['code'],$rs['msg']);
    }
    //取消关注
    function cancel($toUid){
        $rs = $this->fansService->cancel($this->uid,$toUid);
        return $this->out($rs['code'],$rs['msg']);
    }
    //推荐 - 关注用户列表
    function recommendList($page = 1){
        $userList = $this->fansService->recommendList($this->uid,20,$page);
        return $this->out($userList['code'],$userList['msg']);
    }

    //推荐 - 关注用户列表
//    function setMemoName($memo){
//        $userList = $this->fansService->setMemoName($this->uid,$memo);
//        return $this->out($userList['code'],$userList['msg']);
//    }
}