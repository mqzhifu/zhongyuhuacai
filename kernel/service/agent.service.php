<?php

class AgentService{
    public $orderService = null;
    public $withdrawMoneyService = null;
    const STATUS_WAIT = 1;
    const STATUS_OK = 2;
    const STATUS_REJECT = 3;

    const STATUS_DESC = [
        self::STATUS_WAIT =>"待审批",
        self::STATUS_OK =>"已通过",
        self::STATUS_REJECT =>"驳回",
    ];

    function __construct(){
        $this->orderService = new OrderService();
        $this->withdrawMoneyService = new WithdrawMoneyService();
    }
    //申请提现
    function withdrawMoney($aid,$num,$oids,$uid){
        if(!$aid){
            return out_pc(8372);
        }

        if(!$num){
            return out_pc(8373);
        }

        if(!$oids){
            return out_pc(8374);
        }

        $num = (int)$num;
        if( $num <= 0 ){
            return out_pc(8375);
        }

        $allowFeeMoney = $this->getFee();
        if($num > $allowFeeMoney){

        }

        $oidsArr = explode(",",$oids);
        foreach ($oidsArr as $k=>$oid){
            $order = OrderModel::db()->getById($oid);
            if(!$order){
                return out_pc(1029,array($oid));
            }

            if($order['status'] != OrderModel::STATUS_FINISH ){
                return out_pc(8376,array($oid));
            }

            if($order['uid'] != $uid){
                return out_pc(8377,array($oid));
            }

            if($order['agent_id'] != $aid){
                return out_pc(8378,array($oid));
            }

            $existOrder = $this->withdrawMoneyService->getRowByOid($oid);
            if($existOrder){
                return out_pc(8379,array($oid));
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
            'status'=>WithdrawMoneyService::WITHDRAW_STATUS_WAIT,
            'type'=>WithdrawMoneyService::TYPE_ONE,
        );

        $newId = WithdrawModel::db()->add($data);

        return out_pc(200,$newId);

    }

    function getRowByMobile($mobile){
        return AgentModel::db()->getRow(" mobile = '$mobile'");
    }

    //获取一个代理，分享出去的连接，所成交的所有订单
    function getOrderListByAId($aid,$status = 0){
        $list =  $this->orderService ->getListByAgentId($aid,$status);
        return $list;
    }
    //申请成为一个代理
    function apply($uid,$type,$invite_agent_code = 0,$data){
        if(!$uid){
            return out_pc(8002);
        }

        if(!$type){
            return out_pc(8004);
        }

        if(!in_array($type,array_flip(WithdrawMoneyService::TYPE_DESC)) ){
            return out_pc(8369);
        }
        //校验一个 地址信息
        $addressService = new UserAddressService();
        $checkAddrRs = $addressService->checkArea($data);
        if($checkAddrRs['code'] != 200){
            return out_pc($checkAddrRs['code'],$checkAddrRs['msg']);
        }

        if(!arrKeyIssetAndExist($data,'mobile')){
            return out_pc(8000);
        }

        $exist = $this->getRowByMobile($data['mobile']);
        if($exist){
            return out_pc(8276);
        }

        $addData = array(
            'province_code'=>$data['province_code'],
            'city_code'=>$data['city_code'],
            'county_code'=>$data['county_code'],
            'towns_code'=>$data['town_code'],
            'villages'=>$data['address'],
            'address'=>$data['address'],

            'mobile'=>$data['mobile'],
            'title'=>$data['title'],

            'uid'=>$uid,

            'id_card_num'=>"",
            'real_name'=>"",
            'type'=>$type,
            'status'=>AgentModel::STATUS_AUDITING,
            'sex'=>$data['sex'],
            'a_time'=>time(),
            'pic'=>"",
        );
        //
        if($type == WithdrawMoneyService::TYPE_ONE){

        }else{
            //二级代理，必须得填写一级代理的邀请码
            if(!$invite_agent_code){
                return out_pc(8370);
            }

            $agent = AgentModel::db()->getRow(" invite_code = '$invite_agent_code' ");
            if(!$agent){
                return out_pc(8371);
            }

            $addData['invite_agent_uid'] = $agent['id'];
        }

        $newId = AgentModel::db()->add($addData);
        return out_pc(200,$newId);
    }
    //代理有一个ID,同时代理还可以是普通用户，还有一个UID
    function getOneByUid($uid){
        $agent = AgentModel::db()->getRow(" uid = $uid");
        return out_pc(200,$agent);
    }
    //获取  一级代理下的，所有二级代理
    function getSubAgentList($aid){
        $list = AgentModel::db()->getAll(" invite_agent_uid = $aid");
        return $list;
    }
    //获取一个 一级代理，下的，所有二级代理的，所有订单
    function getAgentSubOrderList($aid,$type){
        $subAgent = $this->getSubAgentList($aid);
        if($subAgent){
            $subAgentIds = "";
            foreach ($subAgent as $k=>$v){
                $subAgentIds .= $v['id'] .",";
            }
            $subAgentIds = substr($subAgentIds,0,strlen($subAgentIds)-1);
            if($type == 1){
                $orderList =  $this->orderService ->getListByAgentId($subAgentIds);
            }elseif($type == 2){
                $orderList =  $this->orderService ->getListByAgentId($subAgentIds,OrderModel::STATUS_FINISH,WithdrawMoneyService::WITHDRAW_ORDER_STATUS_WAIT,WithdrawMoneyService::WITHDRAW_ORDER_STATUS_WAIT);
            }else{
                exit("type err");
            }

            return $orderList;
        }

        return null;
    }
    //获取一个代理，可提现的，订单列表
    function getAllowWithDrawOrderList($aid){
        $agent = AgentModel::db()->getById($aid);
        $subOrderList = null;
        if($agent == WithdrawMoneyService::TYPE_ONE){//一级代理，还要计算自己邀请的二级代理的收入
            //先获取 下面的，二级代理的订单
            $subOrderList = $this->getAgentSubOrderList($aid,2);
        }
        //再获取当前代理，直接分享成功的订单
        $orderList = $this->orderService->getListByAgentId($aid,OrderModel::STATUS_FINISH,0,WithdrawMoneyService::WITHDRAW_ORDER_STATUS_WAIT);
        if($subOrderList && $orderList){
            $orderList = array_merge($orderList,$subOrderList);
        }
        return $orderList;
    }
    //获取一个代理，相关的所有订单，不区分状态
    function getAllOrderList($aid){
        $agent = AgentModel::db()->getById($aid);
        $subOrderList = null;
        if($agent['type'] == WithdrawMoneyService::TYPE_ONE) {//一级代理，还要计算自己邀请的二级代理的收入
            $subOrderList = $this->getAgentSubOrderList($aid,1);
        }

        $orderList = $this->orderService->getListByAgentId($aid);
        if($subOrderList){
            $orderList = array_merge($orderList,$subOrderList);
        }
        return $orderList;
    }

    //获取，一个代理，可提现余额（包括可能该代理还有子级代理）
    function getFee($aid){
        $agent = AgentModel::db()->getById($aid);
        $orderList = $this->getAllowWithDrawOrderList($aid);
        if(!$orderList){
            return array('fee'=>0,'sub_fee'=>0);
        }

        $fee = 0;
        $subFee = 0;
        foreach ($orderList as $k=>$v){
            if($v['agent_id'] == $aid){
                $fee += $v['total_price'] * (  $agent['fee_percent'] / 100);
            }else{
                //这是二级代理完成的订单，上级也可以获取到一些奖励
                $fee += $v['total_price'] * (  $agent['sub_fee_percent'] / 100);
                $subFee += $v['total_price'] * (  $agent['sub_fee_percent'] / 100);
            }
        }

        return array('fee'=>$fee,'sub_fee'=>$subFee);
    }
    //获取一批用户的ID
    function getSomeAgentFee($uids){
        $orderService = new OrderService();
        $orderList = $orderService->getListByAgentId($uids,OrderModel::STATUS_FINISH,OrderModel::WITHDRAW_MONEY_AGENT_WAIT);
        if(!$orderList){
            return 0;
        }

        $fee = 0;
        foreach ($orderList as $k=>$v){
            $fee += $v['total_price'];
        }

        return $fee;
    }

    //普通用户绑定一个代理
    function userBindAgent($uid,$agentMobile,$mobileCode){
        if(!$uid){

        }

        if(!$agentMobile){

        }

        if(!$mobileCode){

        }

        $user = UserModel::db()->getById($uid);
        if(!$user){

        }

        $hasBind = $this->getOneByUid($uid);
        if($hasBind){

        }

        $agent = AgentModel::db()->getRow(" mobile = '$agentMobile'");
        if(!$agent){

        }

        if($agent['uid']){

        }

        $lib = new VerifierCodeLib();
        $rs = $lib->authCode(1,$agentMobile,$mobileCode,9);
        if($rs['code'] != 200){
            return out_pc($rs['code'],$rs['msg']);
        }

        $data = array("uid"=>$uid,);
        $upRs = AgentModel::db()->upById($agent['id'],$data);

        return out_pc(200,$upRs);

    }


}