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
    //这个状态，只是订单表冗余出来的属性，方便查询而以
    const WITHDRAW_ORDER_STATUS_WAIT = 1;
    const WITHDRAW_ORDER_STATUS_OK = 2;
    const WITHDRAW_ORDER_STATUS_APPLY = 3;
    const WITHDRAW_ORDER_STATUS_REJECT = 4;
    const WITHDRAW_ORDER_STATUS_FINISH = 5;

    const WITHDRAW_ORDER_STATUS_DESC = [
        self::WITHDRAW_ORDER_STATUS_WAIT =>"未处理",
        self::WITHDRAW_ORDER_STATUS_OK =>"已通过",
        self::WITHDRAW_ORDER_STATUS_APPLY =>"申请中",
        self::WITHDRAW_ORDER_STATUS_REJECT =>"已拒绝",
        self::WITHDRAW_ORDER_STATUS_FINISH =>"已完成",
    ];


    const WITHDRAW_STATUS_WAIT = 1;
    const WITHDRAW_STATUS_OK = 2;
    const WITHDRAW_STATUS_REJECT = 3;
    const WITHDRAW_STATUS_FINISH = 4;
    const WITHDRAW_STATUS_DESC = [
        self::WITHDRAW_STATUS_WAIT =>"未处理",
        self::WITHDRAW_STATUS_OK =>"通过",
        self::WITHDRAW_STATUS_REJECT =>"驳回",
        self::WITHDRAW_STATUS_FINISH =>"已打款/完成",

    ];

    function getRowByOid($oid){
        return  WithdrawModel::db()->getRow(" find_in_set ($oid, oids )  ");
    }
    //获取一个代理的，全部 提现记录
    function getAgentWithdrawApplyList($aid){
        $list = WithdrawModel::db()->getAll(" agent_id = $aid ");
        if(!$list){
            return $list;
        }
        $return = null;
        foreach ($list as $k=>$v){
            $row = $this->formatRow($v);
            $return[] = $row;
        }
        return $return;
    }

    function formatRow($row){
        $row['a_date'] = get_default_date($row['a_time']);
        $row['price_yuan'] = fenToYuan($row['price']);
        $row['status_desc'] =WithdrawMoneyService::WITHDRAW_STATUS_DESC[$row['status']];
        $row['u_date'] = get_default_date($row['u_time']);
        return $row;
    }

    //检查：申请提现的订单状态，是否可以提现
    function checkWithdrawMoneyStatus($orderIds,$agent){
        $orderList = OrderModel::db()->getByIds($orderIds);
        foreach ($orderList as $k=>$order){
            $orderWithdrawStatus = 0;
            if($agent['type'] == AgentModel::ROLE_LEVEL_ONE){
                $orderWithdrawStatus = $order['agent_one_withdraw'];
            }elseif($agent['type'] == AgentModel::ROLE_LEVEL_TWO){
                $orderWithdrawStatus = $order['agent_two_withdraw'];
            }

            if(!$orderWithdrawStatus){
                exit("orderWithdrawStatus type err , is null.");
            }
            //只有 订单为 提现待处理状态才可以
            if($orderWithdrawStatus != WithdrawMoneyService::WITHDRAW_STATUS_WAIT ){
                exit(" oid({$order['id']}) is not  WITHDRAW_STATUS_WAIT , 只有 订单为 提现待处理状态才可以 ");
            }
        }
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