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
//        $gid = get_request_one( $this->request,'gid',0);
        $num = get_request_one( $this->request,'num',0);
        $categoryAttrPara = get_request_one( $this->request,'categoryAttrPara',0);
        $pid = get_request_one( $this->request,'pid',0);

        $oid = $this->orderService->doing($this->uid,$pid,$categoryAttrPara,$num,$agentUid);
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

    function pay(){
        $oid = $agentUid =get_request_one( $this->request,'oid',0);
        $uid = $this->uid;
        $type = $agentUid =get_request_one( $this->request,'type',0);

    }


}