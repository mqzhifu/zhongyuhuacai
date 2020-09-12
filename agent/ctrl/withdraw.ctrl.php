<?php
class WithdrawCtrl extends BaseCtrl{
    function index(){
        var_dump(333);exit;
    }

    function apply(){
        $this->setTitle('申请提现');

        $this->display("apply.withdraw.html");
    }

    function detail(){
        $this->setTitle('提现详情');

        $this->display("withdraw.detail.html");
    }

    function lists(){
        $this->setTitle('提现列表');

        $this->display("withdraw.list.html");
    }

    function orderList(){
        $this->setTitle('用户订单列表');

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

        $this->assign("orderList",json_encode($orderList));

        $this->display("user.order.list.html");
    }
}