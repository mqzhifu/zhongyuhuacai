<?php

class PayService{
    //下单入口
    function doing($request){
        $oid = get_request_one($request['oid'],0);
        $type = get_request_one($request['type'],0);
        $order = OrderModel::db()->getById($oid);
        if(!$order){

        }

        if(!$type){

        }
    }

    function wxH5(){

    }

    function wxJsApi(){

    }

    function wxApp(){

    }

    function wxLittle(){

    }
}