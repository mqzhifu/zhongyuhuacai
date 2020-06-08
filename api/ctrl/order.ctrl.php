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
    //某一个产品，近期购买记录
    function getNearUserBuyHistory(){
        $pid = $agentUid =get_request_one( $this->request,'pid',0);
        $list = OrderModel::db()->getAll("pid = $pid group by uid order by a_time desc limit 20 ");
        foreach ($list as $k=>$v){
            $list[$k]['nickname'] = '游客'.$k;
            $list[$k]['avatar'] = get_avatar_url("");
            $user = UserModel::db()->getById($v['uid']);
            if($user){
                $list[$k]['nickname'] = $user['nickname'];
                $list[$k]['avatar'] = get_avatar_url( $user['avatar']);
                $list[$k]['dt'] = get_default_date($user['a_time']);
            }
        }

        return out_pc(200,$list);
    }

    function cntUserOrderByType(){
        $list = OrderModel::db()->getAll("uid = {$this->uid} ");
        if(!$list){

        }
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

    function delUserCart(){
        $id = $agentUid =get_request_one( $this->request,'id',0);
        $this->orderService->delUserCart($id,$this->uid);
    }

    function getUserCart(){
//        $oid = $agentUid =get_request_one( $this->request,'oid',0);
        $this->orderService->getUserCart($this->uid);
    }

    function addUserCart(){
        $pid = $agentUid =get_request_one( $this->request,'pid',0);
        $this->orderService->addUserCart($this->uid,$pid);
    }


}