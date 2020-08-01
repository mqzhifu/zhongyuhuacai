<?php
class OrderCtrl extends BaseCtrl  {
    //下单
    function doing(){
//        $agentUid = get_request_one( $this->request,'agent_uid',0);
//        $pid = get_request_one( $this->request,'pid',0);
//        $gid = get_request_one( $this->request,'gid',0);
//        $couponId = get_request_one( $this->request,'coupon_id',0);

        $share_uid = get_request_one( $this->request,'share_uid',0);
        $userSelAddressId = get_request_one( $this->request,'userSelAddressId',0);
        $gidsNum = get_request_one( $this->request,'gidsNums',0);
        $memo = get_request_one( $this->request,'memo','');


        $rs = $this->orderService->doing($this->uid,$gidsNum,"",$memo,$share_uid,$userSelAddressId);
        return $this->out($rs['code'],$rs['msg']);
    }
    //获取一个用户的所有订单
    function getUserList(){
        $status = get_request_one( $this->request,'status',0);
        $rs = $this->orderService->getUserList($this->uid,$status);
        return out_ajax($rs['code'],$rs['msg']);
    }
    //统计一个用户的所有订单的，各种状态，有多少条记录
    function getUserCnt(){
        $rs = $this->orderService->getUserCntGroupByStatus($this->uid);
        return out_ajax($rs['code'],$rs['msg']);
    }
    //某一个产品，近期购买记录
    function getNearUserBuyHistory(){
        $pid = $agentUid =get_request_one( $this->request,'pid',0);
        $list = $this->orderService->getNearUserBuyHistory($pid);
        return out_ajax(200,$list);
    }
    //获取订单详情
    function getOneDetail(){
        $id = $agentUid =get_request_one( $this->request,'id',0);
        $order = $this->orderService->getOneDetail($id);
        return out_pc($order['code'],$order['msg']);
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

    //--------------退款 相关------------start---------

    //申请退款
    function applyRefund(){
        $id = $agentUid =get_request_one( $this->request,'id',0);
        $type = $agentUid =get_request_one( $this->request,'type',0);
        $reason = $agentUid =get_request_one( $this->request,'reason',0);
        $content = $agentUid =get_request_one( $this->request,'content',0);
        $pic = $agentUid =get_request_one( $this->request,'pic',"");
        $mobile = $agentUid =get_request_one( $this->request,'mobile',"");

        $rs = $this->orderService->applyRefund($id,$this->uid,$type,$content,$reason,$pic,$mobile);
        out_ajax($rs['code'],$rs['msg']);
    }
    //申请退款 上传的图片
    function applyRefundUploadPic(){
        $rs = $this->orderService->applyRefundUploadPic($this->uid);
        out_ajax($rs['code'],$rs['msg']);
    }

    //用户申请退款记录列表
    function getUserRefundList(){
        $rs = $this->orderService->getUserRefundList($this->uid);
        out_ajax($rs['code'],$rs['msg']);
    }
    //获取一条退款记录的详情信息
    function getUserRefundById(){
        $id = $agentUid =get_request_one( $this->request,'id',0);
        $rs = $this->orderService->getUserRefundById($id,$this->uid);
        out_ajax($rs['code'],$rs['msg']);
    }
    function getRefundConst(){
        $arr = array(
            'status'=>OrderService::REFUND_STATS,
            'type'=>OrderService::REFUND_TYPE_DESC,
            'reason'=>OrderService::REFUND_REASON_DESC,
        );

        out_ajax(200,$arr);
    }

    //--------------退款 相关------------end---------

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
    //
    function confirmOrder(){
//        $pid =get_request_one( $this->request,'pid',0);
//        $pcap = get_request_one( $this->request,'pcap',"");
//        $num =get_request_one( $this->request,'num',0);
//
        $gidsNums = get_request_one( $this->request,'gidsNums',"");

        $rs = $this->orderService->confirmOrder($gidsNums,$this->uid);
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