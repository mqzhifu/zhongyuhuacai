<?php
class OrderCtrl extends BaseCtrl  {
    function getListByUser(){
        $list = $this->orderService->getUserList($this->uid);
        return out_pc(200,$list);
    }
    function doing(){
//        $num = get_request_one($request['num'],0);
//        $gid = get_request_one($request['gid'],0);

        $agentUid = $this->request['agent_uid'];
        $gid = $this->request['gid'];
        $num = $this->request['num'];

        $oid = $this->orderService->doing($this->uid,$gid,$num,$agentUid);
        return out_pc(200,$oid);
    }

    function getOneDetail(){
        $id = $this->request['id'];
        $order = $this->orderService->getOneDetail($id);
        return out_pc(200,$order);
    }

    function refund(){
        $id = $this->request['id'];
        $this->orderService->refund($id);
    }


}