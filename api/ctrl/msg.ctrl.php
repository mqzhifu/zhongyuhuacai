<?php
class MsgCtrl extends BaseCtrl  {
    //一个用户下的所有站内信列表
    function getListByUser(){
        $list = $this->msgService->getList($this->uid);
        out_ajax($list['code'],$list['msg']);
    }
    //一条消息的详情
    function detail(){
        $id = $this->request['id'];
        $info = $this->msgService->getOneDetail($id);
        out_ajax($info['code'],$info['msg']);
    }
    //所有未读消息
    function unreadList(){
        $this->msgService->getUserUnreadNum($this->uid);
    }
}