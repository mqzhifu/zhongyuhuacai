<?php

class WithdrawMoneyService{

    const TYPE_ONE = 1;
    const TYPE_TWO = 2;
    const TYPE_FACTORY = 3;
    const TYPE_DESC = array(
        self::TYPE_ONE =>"一级代理",
        self::TYPE_TWO =>"二级代理",
        self::TYPE_FACTORY =>"工厂",
    );

    const WITHDRAW_ORDER_STATUS_WAIT = 1;
    const WITHDRAW_ORDER_STATUS_OK = 2;
    const WITHDRAW_ORDER_STATUS_APPLY = 3;
    const WITHDRAW_ORDER_STATUS_DESC = [
        self::WITHDRAW_ORDER_STATUS_WAIT =>"未处理",
        self::WITHDRAW_ORDER_STATUS_OK =>"已提现",
        self::WITHDRAW_ORDER_STATUS_APPLY =>"申请中",
    ];


    const WITHDRAW_STATUS_WAIT = 1;
    const WITHDRAW_STATUS_OK = 2;
    const WITHDRAW_STATUS_REJECT = 3;
    const WITHDRAW_STATUS_DESC = [
        self::WITHDRAW_STATUS_WAIT =>"未处理",
        self::WITHDRAW_STATUS_OK =>"已提现",
        self::WITHDRAW_STATUS_REJECT =>"已驳回",

    ];

    function getRowByOid($oid){
        return  WithdrawModel::db()->getRow(" find_in_set ($oid, oids )  ");
    }


    function agentWithdrawMoney($aid,$uid,$num,$oids){
        if(!$aid){

        }

        if(!$num){

        }

        if(!$oids){

        }

        $num = (int)$num;
        if( $num <= 0 ){

        }

        $allowFeeMoney = $this->getFee();
        if($num > $allowFeeMoney){

        }

        $oidsArr = explode(",",$oids);
        foreach ($oidsArr as $k=>$oid){
            $order = OrderModel::db()->getById($oid);
            if(!$order){

            }

            if($order['status'] != OrderModel::STATUS_FINISH ){

            }

            if($order['uid'] != $uid){

            }

            if($order['agent_id'] != $aid){

            }

            $data = array("agent_withdraw_money_status"=>OrderModel::WITHDRAW_MONEY_STATUS_APPLY);
            OrderModel::db()->upById($oid,$data);
        }

        $data = array(
            'a_time'=>time(),
            'price'=>$num,
            'uid'=>$uid,
            'agent_id'=>$aid,
            'orders_ids'=>$oids,
            'status'=>self::WITHDRAW_STATUS_WAIT,
            'type'=>self::TYPE_ONE,

        );
        WithdrawModel::db()->add($data);

        $this->withdrawMoney();
    }

    function factoryWithdrawMoney($factoryId,$num,$oids){

    }

    //申请提现
    function withdrawMoney(){


    }

    //获取一个代理，分享出去的连接，所成交的所有订单
    function getOrderListByAId($aid){

        $orderService = new OrderService();
        $list = $orderService->getListByAgentId($aid,OrderModel::STATUS_FINISH);

        return $list;
    }
}