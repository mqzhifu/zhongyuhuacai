<?php
class OrderCtrl extends BaseCtrl  {
    function getListByUser(){
        $list = $this->orderService->getUserList($this->uid);
        return out_pc(200,$list);
    }
    function doing(){
//        $num = get_request_one($request['num'],0);
//        $gid = get_request_one($request['gid'],0);

        $agentUid =get_request_one( $this->request,'agent_uid',0);
        $gid = get_request_one( $this->request,'gid',0);
        $num = get_request_one( $this->request,'num',0);

        $oid = $this->orderService->doing($this->uid,$gid,$num,$agentUid);
        return out_pc(200,$oid);
    }

    function getOneDetail(){
        $id = $agentUid =get_request_one( $this->request,'id',0);
        $order = $this->orderService->getOneDetail($id);
        return out_pc(200,$order);
    }

    function refund(){
        $id = $agentUid =get_request_one( $this->request,'id',0);
        $this->orderService->refund($id);
    }


}