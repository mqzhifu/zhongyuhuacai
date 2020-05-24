<?php
class ImMsgCtrl extends BaseCtrl {
    function receive($toUid,$type,$title= "",$content = ""){
        $rs = $this->imSevice->receive($this->uid,$toUid,$type,$title,$content);
        return $this->out(200,$rs);

    }
    //获取一会话的所有聊天记录
    function getSessionList($type){
        $rs = $this->imSevice->getUserSession($this->uid,$type);
        return $this->out($rs['code'],$rs['msg']);
    }

    function getSessionMsgList($sessionId){
        $rs = $this->imSevice->getMsgBySessionId($this->uid,$sessionId);

        return $this->out($rs['code'],$rs['msg']);
    }

    //指定一个用户，提供选择列表
    function provideList(){
        $rs = $this->imSevice->provideList($this->uid);
        return $this->out($rs['code'],$rs['msg']);
    }
    //联系人 列表
    function contactList(){
        //好友
        //关注
        //粉丝
        $rs = $this->imSevice->contactList($this->uid);
        return $this->out($rs['code'],$rs['msg']);
    }


    function userIMSessionStatus($type){
        $rs = $this->imSevice->userIMSessionStatus($this->uid,$type);
        return $this->out($rs['code'],$rs['msg']);
    }
}