<?php
class OrderCtrl extends BaseCtrl  {
//    function getListByUser(){
//        $status = get_request_one( $this->request,'status',0);
//        $list = $this->orderService->getUserList($this->uid,$status);
//        return out_pc(200,$list);
//    }

    function doing(){
//        $agentUid = get_request_one( $this->request,'agent_uid',0);
        $couponId = get_request_one( $this->request,'coupon_id',0);

        $share_uid = get_request_one( $this->request,'share_uid',0);

        $userSelAddressId = get_request_one( $this->request,'userSelAddressId',0);
//        $gid = get_request_one( $this->request,'gid',0);
        $gidsNum = get_request_one( $this->request,'gidsNums',0);
//        $pid = get_request_one( $this->request,'pid',0);
        $memo = get_request_one( $this->request,'memo','');


        $rs = $this->orderService->doing($this->uid,$gidsNum,$couponId,$memo,$share_uid,$userSelAddressId);
        return out_ajax($rs['code'],$rs['msg']);
    }

    function getUserList(){
        $status = get_request_one( $this->request,'status',0);
        $rs = $this->orderService->getUserList($this->uid,$status);
        return out_ajax($rs['code'],$rs['msg']);
    }

    //某一个产品，近期购买记录
    function getNearUserBuyHistory(){
        $pid = $agentUid =get_request_one( $this->request,'pid',0);
        $list = OrderGoodsModel::db()->getAll("pid = $pid group by uid order by a_time desc limit 20 ");
        foreach ($list as $k=>$v){
            $list[$k]['nickname'] = '游客'.$k;
            $list[$k]['avatar'] = get_avatar_url("");
            $list[$k]['dt'] = get_default_date($v['a_time']);
            $user = UserModel::db()->getById($v['uid']);
            if($user){
                $list[$k]['nickname'] = $user['nickname'];
                $list[$k]['avatar'] = get_avatar_url( $user['avatar']);
            }
        }

        return out_ajax(200,$list);
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

    function cancel(){
        $id = $agentUid =get_request_one( $this->request,'id',0);
        $rs = $this->orderService->cancel($id);
        out_ajax($rs['code'],$rs['msg']);
    }

    function confirmReceive(){
        $id = $agentUid =get_request_one( $this->request,'id',0);
        $rs = $this->orderService->confirmReceive($id);
        out_ajax($rs['code'],$rs['msg']);
    }

    function applyRefund(){
        $id = $agentUid =get_request_one( $this->request,'id',0);
        $rs = $this->orderService->applyRefund($id);
        out_ajax($rs['code'],$rs['msg']);
    }

    function pay(){
        $oid = $agentUid =get_request_one( $this->request,'oid',0);
        $uid = $this->uid;
        $type = $agentUid =get_request_one( $this->request,'type',0);

        if(!$type){
            out_ajax(8004);
        }


        if(!$oid){
            out_ajax(8981);
        }

        $order = OrderModel::db()->getById($oid);
        if(!$order){
            out_ajax(1029);
        }

        if(time() > $order['expire_time']){
            out_ajax(8358);
        }

        $rs = null;
        $payService = new PayService();
        if($type == OrderModel::PAY_WX_H5_NATIVE){

            $rs = $payService->wxJsApi($order,$uid);
        }

        out_ajax($rs['code'],$rs['msg']);



    }

    function getUserCartCnt(){
        $cnt = $list = CartModel::db()->getCount(" uid = {$this->uid}");
        out_ajax(200,$cnt);
    }

    function getUserCart(){
//        $oid = $agentUid =get_request_one( $this->request,'oid',0);
        $list = $this->orderService->getUserCart($this->uid);
        out_ajax($list['code'],$list['msg']);
    }

    function confirmOrder(){
//        $pid =get_request_one( $this->request,'pid',0);
//        $pcap = get_request_one( $this->request,'pcap',"");
//        $num =get_request_one( $this->request,'num',0);
//
        $gidsNums = get_request_one( $this->request,'gidsNums',"");

        $rs = $this->orderService->confirmOrder($gidsNums);
        out_ajax($rs['code'],$rs['msg']);
    }

    function delUserCart(){
        $ids = get_request_one( $this->request,'ids',"");
        $rs = $this->orderService->delUserCart($ids,$this->uid);
        out_ajax($rs['code'],$rs['msg']);
    }

    function addUserCart(){
        $pid = $agentUid =get_request_one( $this->request,'pid',0);
        $rs = $this->orderService->addUserCart($this->uid,$pid);
        out_ajax($rs['code'],$rs['msg']);
    }


    function getGoodsIdByPcap(){
        $pid =get_request_one( $this->request,'pid',0);
        $pcap = get_request_one( $this->request,'pcap',"");
        $num =get_request_one( $this->request,'num',0);

        $rs = $this->orderService->getGoodsIdByPcap($pid,$pcap,$num);
        out_ajax($rs['code'],$rs['msg']);
    }

}