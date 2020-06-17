<?php
class PayCallback{
    function wxJsapi(){
        $a = new NotifyCallBack();
        LogLib::inc()->debug("im wxJsapi");
        LogLib::inc()->debug($_REQUEST);


        include_once PLUGIN ."/wxpay/WxPay.Api.php";
        include_once PLUGIN ."/wxpay/WxPay.Config.php";
        include_once PLUGIN ."/wxpay/WxPay.Notify.php";
        include_once PLUGIN ."/wxpay/WxPay.Data.php";
        include_once PLUGIN ."/wxpay/WxPay.NotifyCallBack.php";


        $config = new WxPayConfig();
        $notify = new NotifyCallBack();
        $notify->Handle($config, true);
        $returnData = $notify->GetReturn_msg();

        LogLib::inc()->debug(["============notify->GetReturn_msg========",$returnData]);
        LogLib::inc()->debug(["============wx_callback_data data========",$notify->wx_callback_data]);


        if($returnData && $returnData == 'OK') {//证明验证都没有问题了
            $wx_callback_data = $notify->wx_callback_data;
            $order = GamesGoodsOrderModel::db()->getRow(" in_trade_no = '{$wx_callback_data['out_trade_no']}' ");
        }
    }

    function wxH5(){

    }

    function aliH5(){

    }
}


