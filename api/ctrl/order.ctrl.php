<?php
class OrderCtrl extends BaseCtrl  {
//    function getListByUser(){
//        $status = get_request_one( $this->request,'status',0);
//        $list = $this->orderService->getUserList($this->uid,$status);
//        return out_pc(200,$list);
//    }

    //下单
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
    //获取一个用户的所有订单
    function getUserList(){
        $status = get_request_one( $this->request,'status',0);
        $rs = $this->orderService->getUserList($this->uid,$status);
        return out_ajax($rs['code'],$rs['msg']);
    }
    //统计一个用户的所有订单的，各种状态，有多少条记录
    function getUserCnt(){
        $orderList = OrderModel::db()->getAll(" uid = {$this->uid} group by status" , null, " count(status) as cnt,status ");
        if(!$orderList){
            return out_ajax(200,$orderList);
        }

        $list = array(
            "wait_pay"=>0,"wait_transport"=>0,"wait_signin"=>0,"wait_comment"=>0
        );
        foreach ($orderList as $k=>$v){
            if($v['status'] == 1){//待付款
                $list['wait_pay'] = $v['cnt'];
            }elseif($v['status'] == 2){//已付款，待发货
                $list['wait_transport'] = $v['cnt'];
            }elseif($v['status'] == 5){//已发货，待签收
                $list['wait_signin'] = $v['cnt'];
            }elseif($v['status'] == 8){//完成，待评价
                $list['wait_comment'] = $v['cnt'];
            }
        }
        out_ajax(200,$list);
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
    //获取订单详情
    function getOneDetail(){
        $id = $agentUid =get_request_one( $this->request,'id',0);
        $order = $this->orderService->getOneDetail($id);
        return out_pc(200,$order);
    }
    //取消一个订单
    function cancel(){
        $id = $agentUid =get_request_one( $this->request,'id',0);
        $rs = $this->orderService->cancel($id);
        out_ajax($rs['code'],$rs['msg']);
    }
    //确认已收货
    function confirmReceive(){
        $id = $agentUid =get_request_one( $this->request,'id',0);
        $rs = $this->orderService->confirmReceive($id);
        out_ajax($rs['code'],$rs['msg']);
    }
    //申请退款
    function applyRefund(){
        $id = $agentUid =get_request_one( $this->request,'id',0);
        $rs = $this->orderService->applyRefund($id);
        out_ajax($rs['code'],$rs['msg']);
    }
    //唤起支付
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
    function confirmOrder(){
//        $pid =get_request_one( $this->request,'pid',0);
//        $pcap = get_request_one( $this->request,'pcap',"");
//        $num =get_request_one( $this->request,'num',0);
//
        $gidsNums = get_request_one( $this->request,'gidsNums',"");

        $rs = $this->orderService->confirmOrder($gidsNums);
        out_ajax($rs['code'],$rs['msg']);
    }


    //获取用户购物车内的产品数
    function getUserCartCnt(){
        $cnt = $list = CartModel::db()->getCount(" uid = {$this->uid}");
        out_ajax(200,$cnt);
    }
    //获取用户购物车内的产品列表
    function getUserCart(){
//        $oid = $agentUid =get_request_one( $this->request,'oid',0);
        $list = $this->orderService->getUserCart($this->uid);
        out_ajax($list['code'],$list['msg']);
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