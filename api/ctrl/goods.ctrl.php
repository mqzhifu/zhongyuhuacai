<?php
class GoodsCtrl extends BaseCtrl  {
    //获取订单详情
    function getOneDetail(){
        $id = $agentUid =get_request_one( $this->request,'id',0);
        $includePCAP = $agentUid =get_request_one( $this->request,'include_pcap',0);


        $goods = $this->goodsService->getOneDetail($id,$includePCAP,$this->uid);
        return out_ajax($goods['code'],$goods['msg']);
    }
}