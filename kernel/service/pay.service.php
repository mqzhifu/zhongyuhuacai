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
    function wxJsApi($order,$uid){
        include PLUGIN ."wxpay/WxPay.Config.php";
        include PLUGIN ."wxpay/WxPay.JsApiPay.php";
        include PLUGIN ."wxpay/WxPay.Api.php";


        $tools = new JsApiPay();
        $openId = $this->userService->getUinfoById($uid)['msg']['wx_open_id'];


        $config = ConfigCenter::get(APP_NAME,"wx")['pay'];
        $notifyUrl  = get_domain_url() . $config['notify_url'];

        //②、统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody($order['title']);
        //自己生成的数字订单号，发给3方，最后对账的时候使用
        $input->SetOut_trade_no($order['no']);
        //总价
        $input->SetTotal_fee($order['total_price']);
        //订单创建时间
        $s_time = date("YmdHis",$order['a_time']);
        $input->SetTime_start($s_time);
        $expire_time = date("YmdHis", $order['expire_time']);
        //失效时间
        $input->SetTime_expire($expire_time);
        //回调地址
        $input->SetNotify_url($notifyUrl);
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);

//        $input->SetGoods_tag("test");
//        $input->SetAttach($Attach);


        LogLib::inc()->debug(["pay service wxJsApi para",$openId,$uid,$order['id'],$notifyUrl,$order['title'],$order['no'],$order['total_price'],$s_time,$expire_time]);

        $config = new WxPayConfig();
        //先创建预订单
        $order = WxPayApi::unifiedOrder($config, $input);

        LogLib::inc()->debug([' WxPayApi::unifiedOrder',$order]);
        //再获取前端需要唤起微信支付的参数
        $jsApiParameters = $tools->GetJsApiParameters($order);
        LogLib::inc()->debug(["GetJsApiParameters",$jsApiParameters]);

        return out_pc(200,$jsApiParameters);
//        //获取共享收货地址js函数参数
//        $editAddress = $tools->GetEditAddressParameters();
    }

    function wxApp(){

    }

    function wxLittle(){

    }

    function aliH5(){

    }
}