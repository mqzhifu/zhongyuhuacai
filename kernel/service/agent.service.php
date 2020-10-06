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
    function getAreaStr($aid,$default = "",$explode = ','){
        $agent = $this->getById($aid)['msg'];
        $str = $agent['province_cn'] .$explode .  $agent['city_cn'] .$explode  .  $agent['county_cn'] .$explode  .  $agent['town_cn'];
        return $str;
    }

    function getById($aid){
        if(!$aid){
            return out_pc(8372);
        }

        $agent = AgentModel::db()->getById($aid);
        if(!$agent){
            return out_pc(1040);
        }

        $addrService = new UserAddressService();
        $agent = $addrService->formatRow($agent);

        return out_pc(200,$agent);
    }


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
    //根据手机号，获取一个代理
    function getRowByMobile($mobile){
        return AgentModel::db()->getRow(" mobile = '$mobile'");
    }
    //获取一个代理，分享出去的连接，所成交的所有订单
    function getOrderListByAId($aid,$status = 0){
        $list =  $this->orderService ->getListByAgentId($aid,$status);
        return $list;
    }
    //申请成为一个代理
    //$aid:当前登陆agent端，发起 邀请成为代码的 人
    function apply($aid,$type,$data){
        if(!$aid){
            return out_pc(8390);
        }

        if(!$type){
            return out_pc(8004);
        }

        if(!in_array($type,array_flip(WithdrawMoneyService::TYPE_DESC)) ){
            return out_pc(8369);
        }
        //验证
        if(!arrKeyIssetAndExist($data,'address')){
            return out_pc(8391);
        }
        //验证
        if(!arrKeyIssetAndExist($data,'sex')){
            return out_pc(8392);
        }
        if(!in_array($data['sex'],array_flip(UserModel::getSexDesc())) ){
            return out_pc(8401);
        }

        //验证
        if(!arrKeyIssetAndExist($data,'title')){
            return out_pc(8393);
        }
        //验证
        if(!arrKeyIssetAndExist($data,'real_name')){
            return out_pc(8394);
        }
        //验证 二级代理佣金
        if(!arrKeyIssetAndExist($data,'sub_fee_percent')){
            return out_pc(8396);
        }
        $data['sub_fee_percent'] = (int)$data['sub_fee_percent'];
        if(!$data['sub_fee_percent']){
            return out_pc(8396);
        }
        if( $data['sub_fee_percent'] <= 0 ||  $data['sub_fee_percent'] >= 50){
            return out_pc(8396);
        }
        //验证手机
        if(!arrKeyIssetAndExist($data,'mobile')){
            return out_pc(8000);
        }

        if(!FilterLib::preg($data['mobile'],'phone') ){
            out_ajax(8003);
        }

        $exist = $this->getRowByMobile($data['mobile']);
        if($exist){
            return out_pc(8276);
        }
        //校验一个 地址信息
        $addressService = new UserAddressService();
        $checkAddrRs = $addressService->checkArea($data);
        if($checkAddrRs['code'] != 200){
            return out_pc($checkAddrRs['code'],$checkAddrRs['msg']);
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

            'aid'=>$aid,

            'id_card_num'=>"",
            'real_name'=>"",
            'type'=>$type,
            'status'=>AgentModel::STATUS_AUDITING,
            'sex'=>$data['sex'],
            'a_time'=>time(),
            'pic'=>$data['pic'],
        );
        //
        if($type == WithdrawMoneyService::TYPE_ONE){
            if(!arrKeyIssetAndExist($data,'fee_percent')){
                return out_pc(8397);
            }
            $data['fee_percent'] = (int)$data['fee_percent'];
            if(!$data['fee_percent']){
                return out_pc(8399);
            }
            if( $data['fee_percent'] <= 0 ||  $data['fee_percent'] >= 50){
                return out_pc(8400);
            }

        }else{
            //二级代理，必须得填写一级代理的邀请码
            if(!$data['invite_agent_code']){
                return out_pc(8370);
            }

            $agent = AgentModel::db()->getRow(" invite_code = '{$data['invite_agent_code']}' ");
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
    //获取  一级代理下的，所有二级代理数
    function getSubAgentCnt($aid){
        $list = AgentModel::db()->getCount(" invite_agent_uid = $aid");
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
        $orderList = $this->orderService->getListByAgentId($aid,OrderModel::STATUS_PAYED,WithdrawMoneyService::WITHDRAW_ORDER_STATUS_WAIT,WithdrawMoneyService::WITHDRAW_ORDER_STATUS_WAIT);
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
    //获取一个代码已提过的钱
    function getHasFee($aid){
        $totalDB = WithdrawModel::db()->getRow("agent_id = $aid and status = ".WithdrawMoneyService::WITHDRAW_STATUS_FINISH ,null ,  " sum(price) as total");
        $total = 0;
        if($totalDB){
            $total = $totalDB['total'];
            $total = $total / 100;
        }

        return $total;
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
                $fee += $v['total_price'] / 100 * (  $agent['fee_percent'] / 100);
            }else{
                //这是二级代理完成的订单，上级也可以获取到一些奖励
                $fee += $v['total_price'] / 100 * (  $agent['sub_fee_percent'] / 100);
                $subFee += $v['total_price'] / 100 * (  $agent['sub_fee_percent'] / 100);
            }
        }

        return array('fee'=>$fee,'sub_fee'=>$subFee);
    }

    //获取一个代理，分享产品数
    function getShareProductCnt($aid){
        return ShareProductModel::db()->getCount(" agent_id = $aid");
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
    function unbind($aid,$uid){
        if(!$aid){
            return out_pc(8403);
        }

        if(!$uid){
            return out_pc(8402);
        }

        $user = UserModel::db()->getById($uid);
        if(!$user){
            out_ajax(1000);
        }

        $agent = AgentModel::db()->getById($aid);
        if(!$agent){
            out_ajax(1040);
        }

        if($agent['uid'] != $uid){
            out_ajax(8406);
        }

        $agent['uid'] = '';
        $sess = new SessionLib();
        $sess->setValue('uinfo',$agent);

        $rs =  AgentModel::db()->upById($aid,array("uid"=>""));
        return out_pc(200);
    }
    //普通用户绑定一个代理
    function userBindAgent($uid,$aid,$userMobile,$smsCode){
        if(!$uid){
            return out_pc(8402);
        }

        if(!$aid){
            return out_pc(8403);
        }

        if(!$userMobile){
            out_ajax(8000);
        }

        if(!FilterLib::preg($userMobile,'phone')){
            out_ajax(8003);
        }

        if(!$smsCode){
            out_ajax(8389);
        }

        $smsCode = (int)$smsCode;
        if(!$smsCode){
            out_ajax(8387);
        }

        if(strlen($smsCode) != 6){
            out_ajax(8388);
        }


        $lib = new VerifierCodeLib();
        $rs = $lib->authCode(VerifiercodeModel::TYPE_SMS,$userMobile,$smsCode,2);
        if($rs['code'] != 200){
            return out_pc($rs['code'],$rs['msg']);
        }


        $user = UserModel::db()->getById($uid);
        if(!$user){
            out_ajax(1000);
        }

        if($user['mobile'] != $userMobile){
            out_ajax(8404);
        }

        $agent = AgentModel::db()->getById($aid);
        if(!$agent){
            out_ajax(1040);
        }

        if($agent['uid']){
            out_ajax(8405);
        }

        $data = array("uid"=>$uid,);
        $upRs = AgentModel::db()->upById($aid,$data);

        $agent['uid'] = $uid;
        $sess = new SessionLib();
        $sess->setValue('uinfo',$agent);

//        UserModel::db()->upById($id,array());

        return out_pc(200,$upRs);

    }
    //编辑信息
    function editOne($aid,$data){
        if(!$aid){
            return out_pc(8390);
        }

        //验证
        if(!arrKeyIssetAndExist($data,'address')){
            return out_pc(8391);
        }
        //验证
        if(!arrKeyIssetAndExist($data,'sex')){
            return out_pc(8392);
        }
        if(!in_array($data['sex'],array_flip(UserModel::getSexDesc())) ){
            return out_pc(8401);
        }
        //验证
        if(!arrKeyIssetAndExist($data,'title')){
            return out_pc(8393);
        }
        //验证
        if(!arrKeyIssetAndExist($data,'real_name')){
            return out_pc(8394);
        }
        $addressService = new UserAddressService();
        $checkAddrRs = $addressService->checkArea($data);
        if($checkAddrRs['code'] != 200){
            return out_pc($checkAddrRs['code'],$checkAddrRs['msg']);
        }

        $data = array(
            'province_code'=>$data['province_code'],
            'city_code'=>$data['city_code'],
            'county_code'=>$data['county_code'],
//            'town_code'=>$data['town_code'],
//            'villages'=>$data['address'],
            'address'=>$data['address'],

            'title'=>$data['title'],
            'real_name'=>"",
            'sex'=>$data['sex'],
            'a_time'=>time(),
//            'pic'=>$data['pic'],
        );


        $rs =  AgentModel::db()->upById($aid,$data);
        return out_pc(200,$rs);
    }

    function getProvinceByCode($code){
        return AreaProvinceModel::db()->getRow(" code = '$code'");
    }

    function getCityByCode($code){
        return AreaCityModel::db()->getRow(" code = '$code'");
    }

    function getCountyByCode($code){
        return AreaCountyModel::db()->getRow(" code = '$code'");
    }

    function getTownByCode($code){
        return AreaTownModel::db()->getRow(" code = '$code'");
    }

    //后台，要根据 一个名字，来搜索用户，并返回id值，合并列表
    function searchUidsByKeywordUseDbWhere($keyword ,$whereFiles = 'aid'){
        $where = "";

        $productWhere = " title like '%$keyword%' or real_name like '%$keyword%'   ";
        $productList = AgentModel::db()->getAll($productWhere,null, " id ");
        if(!$productList){
            $where .= " and 0";
        }else{
            $pids = "";
            foreach ($productList as $k=>$v){
                $pids .= $v['id'] . " ,";
            }
            $pids = substr($pids,0,strlen($pids)-1);
            $where .= " and $whereFiles in ( $pids ) ";
        }

        return $where;
    }


}