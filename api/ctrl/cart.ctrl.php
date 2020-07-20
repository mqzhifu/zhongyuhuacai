<?php
class CartCtrl extends BaseCtrl  {
    //获取用户购物车内的产品数
    function getUserCartCnt(){
//        $cnt = $list = CartModel::db()->getCount(" uid = {$this->uid}");
        $cnt = $list = $this->cartService->getUserCartNum($this->uid);
        out_ajax(200,$cnt);
    }
    //获取用户购物车内的产品列表
    function getUserCart(){
//        $oid = $agentUid =get_request_one( $this->request,'oid',0);
        $list = $this->cartService->getUserCart($this->uid);
        out_ajax($list['code'],$list['msg']);
    }

    function delUserCart(){
        $ids = get_request_one( $this->request,'ids',"");
        $rs = $this->cartService->delUserCart($ids,$this->uid);
        out_ajax($rs['code'],$rs['msg']);
    }

    function addUserCart(){
        $pid = $agentUid =get_request_one( $this->request,'pid',0);
        $rs = $this->cartService->addUserCart($this->uid,$pid);
        out_ajax($rs['code'],$rs['msg']);
    }
}