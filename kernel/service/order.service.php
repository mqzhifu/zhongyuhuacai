<?php

class OrderService{
    //下单入口
    function doing($request){
        $num = get_request_one($request['num'],0);
        $gid = get_request_one($request['gid'],0);

        if(!$gid){
            out_ajax("600");
        }

        $goods = GoodsModel::db()->getById($gid);
        if(!$goods){

        }

        if($goods['stock'] - $num <= 0 ){

        }
    }
    //用户订单列表
    function getUserList($request){

    }
    //支付完成 - 通知订单变更状态
    function finish(){

    }
}