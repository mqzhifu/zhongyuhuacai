<?php
class WithdrawCtrl extends BaseCtrl{
    function index(){
        var_dump(333);exit;
    }

    function apply(){
        $this->setTitle('申请提现');
        $this->setSubTitle('申请提现');

        $orderIds = _g("orderIds");
        if(!$orderIds){
            exit("orderIds ids is null");
        }

        $orderList = OrderModel::db()->getByIds($orderIds);
        //验证一下用户提交的订单是否，可以提现
        $this->withdrawMoneyService->checkWithdrawMoneyStatus($orderIds,$this->uinfo);

        $totalPrice =0;
        foreach ($orderList as $k=>$v){
            $totalPrice += $v['total_price'];
        }

        if(_g("opt")){
            $bank = _g("bank");
            $account_num = _g("account_num");
            $account_master = _g("account_master");
            $ali = _g("ali");
            $wx = _g("wx");


            $oids = explode(",",$orderIds);
            $upData = array();
            foreach ($oids as $k=>$v){
                if($this->uinfo['type'] == AgentModel::ROLE_LEVEL_ONE){
                    $upData['agent_one_withdraw'] = WithdrawMoneyService::WITHDRAW_ORDER_STATUS_APPLY;
                }elseif($this->uinfo['type'] == AgentModel::ROLE_LEVEL_TWO){
                    $upData['agent_two_withdraw'] = WithdrawMoneyService::WITHDRAW_ORDER_STATUS_APPLY;
                }else{
                    exit("uinfo type err.");
                }

                $this->orderService->upWithdrawStatus($v,$upData);
            }

            $data = array(
                'a_time'=>time(),
                'u_time'=>time(),
                'orders_ids'=>$orderIds,
                'agent_id'=>$this->uinfo['id'],
                'price'=>$totalPrice,
                'bank'=>$bank,
                'account_num'=>$account_num,
                'account_master'=>$account_master,
                'ali'=>$ali,
                'wx'=>$wx,
                'type'=>$this->uinfo['type'],
                'status'=>WithdrawMoneyService::WITHDRAW_STATUS_WAIT,
            );

            $id = WithdrawModel::db()->add($data);
            var_dump($id);exit;
        }

        $this->assign("totalPrice",fenToYuan($totalPrice));
        $this->assign("orderIds",$orderIds);
        $this->display("apply.withdraw.html");
    }

    function detail(){
        $this->setTitle('提现详情');
        $this->setSubTitle('提现详情');

        $id = _g("id");
        if(!$id){
            exit("id is null");
        }

        $info = WithdrawModel::db()->getById($id);
        if(!$info){
            exit(" id not in db");
        }

        $info['status_desc'] = WithdrawMoneyService::WITHDRAW_STATUS_DESC[$info['status']];
        $info['u_date'] = get_default_date($info['u_time']);
        $info['a_date'] = get_default_date($info['a_time']);
        $info['price'] = fenToYuan($info['price']);
        $this->assign("info",$info);


        $this->display("withdraw.detail.html");
    }

    function lists(){
        $this->setTitle('提现列表');
        $this->setSubTitle('提现列表');


        $list = $this->withdrawMoneyService->getAgentWithdrawApplyList($this->uinfo['id']);
        if($list){
            foreach ($list as $k=>$v){
                $list[$k]['a_date'] = get_default_date($v['a_time']);
                $list[$k]['status_desc'] = WithdrawMoneyService::WITHDRAW_STATUS_DESC[$v['status']];
            }
        }
        $this->assign("list",$list);

        $this->display("withdraw.list.html");
    }

    function orderList(){
        $this->setTitle('用户订单列表');
        $this->setSubTitle('用户订单列表');


        $setType = _g("setType");
        if(!$setType){
            $setType = 1;
        }
        $orderAllList = $this->agentService->getAllOrderList($this->uinfo['id']);
        $orderList = null;
        if($orderAllList){
            //所有未支付的状态
            $noPay = array(OrderModel::STATUS_WAIT_PAY,OrderModel::STATUS_CANCEL,OrderModel::STATUS_TIMEOUT);
            //已支付
            $payed = array(OrderModel::STATUS_PAYED,OrderModel::STATUS_REFUND , OrderModel::STATUS_REFUND_REJECT , OrderModel::STATUS_SIGN_IN, OrderModel::STATUS_TRANSPORT);
            //已完成
            $finish = array(OrderModel::STATUS_FINISH);
            $noPayOrder = null;
            $payedOrder = null;
            $finishOrder = null;
            foreach ($orderAllList as $k=>$v){
                $v['a_date'] = get_default_date($v['a_time']);
                $pids = explode(",",$v['pids']);
                $productNames = "";
                foreach ($pids as $k2=>$v2){
                    $product = ProductModel::db()->getById($v2);
                    if($product){
                        $productNames .= $product['title']."<br/>";
                    }
                }
                $v['productNames'] = $productNames;

                $withdrawStatus = 0;
                if($this->uinfo['type'] == AgentModel::ROLE_LEVEL_TWO){
                    $withdrawStatus = $v['agent_two_withdraw'];
                }elseif($this->uinfo['type'] == AgentModel::ROLE_LEVEL_ONE){
                    $withdrawStatus = $v['agent_one_withdraw'];
                }
                $v['withdraw_status_desc'] = WithdrawMoneyService::WITHDRAW_ORDER_STATUS_DESC[$withdrawStatus];
                $v['withdraw_status'] = $withdrawStatus;
                $v['total_price'] = fenToYuan( $v['total_price']);
                if( in_array($v['status'],$noPay) ){
                    $noPayOrder[] = $v;
                }elseif($v == in_array($v['status'],$payed) ){
                    $payedOrder[] = $v;
                }elseif($v == in_array($v['status'],$finish) ){
                    $finishOrder[] = $finish;
                }else{
                    exit(" order status errr");
                }
            }

//            $orderList = array('noPay'=>$noPayOrder,'payed'=>$payedOrder,'finish'=>$finishOrder);
            $orderList = array(1=>$noPayOrder,2=>$payedOrder,3=>$finishOrder);
        }

//        var_dump($payedOrder);exit;
//        var_dump($orderList);exit;


        $this->assign("setType",$setType);

        $this->assign("orderList",json_encode($orderList));
        $this->display("user.order.list.html");
    }
}