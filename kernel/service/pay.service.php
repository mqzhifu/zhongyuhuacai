<?php

class PayService{

    //下单入口
    function doing($uid,$oid,$type){
        $order = OrderModel::db()->getById($oid);
        if(!$order){

        }

        if(!$type){

        }
    }
    //在微信外的 手机端 浏览器 ，以浏览器唤醒微信APP的方式，支付
    function wxH5(){

    }
    //在微信内，用微信浏览器打开的网页，也可以是公众号进入的
    function wxJsApi(){

    }

    function wxApp(){

    }

    function wxLittle(){

    }

    function aliH5(){

    }
}