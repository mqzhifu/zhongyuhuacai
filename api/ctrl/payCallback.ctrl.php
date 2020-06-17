<?php
class PayCallbackCtrl{
    function wxJsapi(){
        LogLib::inc()->debug("im wxJsapi");
        LogLib::inc()->debug($_REQUEST);


        include_once PLUGIN ."/wxpay/WxPay.Api.php";
        include_once PLUGIN ."/wxpay/WxPay.Config.php";
        include_once PLUGIN ."/wxpay/WxPay.Notify.php";
        include_once PLUGIN ."/wxpay/WxPay.Data.php";
        include_once PLUGIN ."/wxpay/WxPay.NotifyCallBack.php";

        //流程有点绕，既然有现成的SDK，懒得改了
        $config = new WxPayConfig();//初始化配置信息
        $notify = new NotifyCallBack();//获取 处理类
        //执行验证
        $notify->Handle($config, true);
        //验证执行完毕，获取结果
        $returnMsg = $notify->GetReturn_msg();
        $returnCode = $notify->GetReturn_code();

        LogLib::inc()->debug(["============notify->GetReturn_msg========",$returnMsg]);
        LogLib::inc()->debug(["============notify->GetReturn_code========",$returnCode]);
        LogLib::inc()->debug(["============wx_callback_data data========",$notify->wx_callback_data]);


        if($returnCode &&  $returnCode == 'SUCCESS') {//证明验证都没有问题了
            $wx_callback_data = $notify->wx_callback_data;
            LogLib::inc()->debug(["wx back data auth ok.",$wx_callback_data]);
            $orderNo = $wx_callback_data['out_trade_no'];
            $order = OrderModel::db()->getRow(" no = '$orderNo'");
            if(!$order){
                LogLib::inc()->debug("out_trade_no :not in db :".$orderNo);
                out_ajax(8349);
            }

            if($order['status'] != OrderModel::STATUS_WAIT_PAY){
                LogLib::inc()->debug("order status err( status = {$order['status']}) , status must be = ".OrderModel::STATUS_WAIT_PAY);
                out_ajax(6350);
            }

            $upData = array(
                'status'=>OrderModel::STATUS_PAYED,
                'pay_time'=>time(),
                'out_trade_no'=> $wx_callback_data['transaction_id'],
            );

            OrderModel::db()->upById($order['id'], $upData );

            LogLib::inc()->debug(" pay callback ,process (up order info0 ok");

            out_ajax(200);
        }else{
            LogLib::inc()->debug(["wx back data auth err.",$returnMsg]);
            out_ajax(8348,$returnMsg);
        }
    }

    function wxH5(){

    }

    function aliH5(){

    }
}


