<?php
include_once PLUGIN ."wxpay/WxPay.Config.php";
include_once PLUGIN ."wxpay/WxPay.JsApiPay.php";
include_once PLUGIN ."wxpay/WxPay.Api.php";

class PayService{

    //在微信外的 手机端 浏览器 ，以浏览器唤醒微信APP的方式，支付
    function wxH5(){

    }
    //在微信内，用微信浏览器打开的网页，也可以是公众号进入的
    function wxJsApi($order,$uid){
        LogLib::inc()->debug(["wxJsApi",$order,$uid]);

        $tools = new JsApiPay();
        $userService = new UserService();
        $openId = $userService->getUinfoById($uid)['msg']['wx_open_id'];


        $totalPrice = $order['total_price'];
        //测试使用的金额
        $totalPrice = 1;

        $config = ConfigCenter::get(APP_NAME,"wx")['pay'];
        $notifyUrl  = get_domain_url() . $config['notify_url'];


        LogLib::inc()->debug(["openid"=>$openId,'notifyUrl'=>$notifyUrl]);

        //②、统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody($order['title']);
        //自己生成的数字订单号，发给3方，最后对账的时候使用
        $input->SetOut_trade_no($order['no']);
        //总价
        $input->SetTotal_fee($totalPrice);
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


//        LogLib::inc()->debug(["pay service wxJsApi para",$openId,$uid,$order['id'],$notifyUrl,$order['title'],$order['no'],$order['total_price'],$s_time,$expire_time]);

        $config = new WxPayConfig();
        //先创建预订单
        $unifiedOrderBack = WxPayApi::unifiedOrder($config, $input);
        LogLib::inc()->debug([' unifiedOrder back::unifiedOrder',$unifiedOrderBack]);
        if($unifiedOrderBack['return_code'] == 'FAIL'){
            return out_pc(8357,"生成预订单失败,".$unifiedOrderBack['return_msg']);
        }

        //再获取前端需要唤起微信支付的参数
        $jsApiParameters = $tools->GetJsApiParameters($unifiedOrderBack);
        LogLib::inc()->debug(["GetJsApiParameters back",$jsApiParameters]);

        return out_pc(200,json_decode($jsApiParameters,true));
//        //获取共享收货地址js函数参数
//        $editAddress = $tools->GetEditAddressParameters();
    }

    function wxApp(){

    }

    function wxLittle(){

    }

    function aliH5(){

    }

    function wxPayRefund($oid,$fee = 0){
        $order = OrderModel::db()->getById($oid);
        //默认是全部退款
        if(!$fee){
            $fee = $order['total_price'];
        }

        $fee = 1;


        if(!arrKeyIssetAndExist($order,'out_trade_no')){
            return out_pc(8354);
        }

        try{
            $input = new WxPayRefund();
            $input->SetTransaction_id($order["out_trade_no"]);
            $input->SetTotal_fee( $order["total_price"]);
            $input->SetRefund_fee($fee);

            $config = new WxPayConfig();
            $input->SetOut_refund_no("sdkphp".date("YmdHis"));
            $input->SetOp_user_id($config->GetMerchantId());

            $backInfo = WxPayApi::refund($config, $input);
            LogLib::inc()->debug([" WxPayApi::refund back:",$backInfo,]);
            $backInfo = json_decode($backInfo,true);
            if($backInfo['err_code']){
                return out_pc(8355,json_encode($backInfo));
            }
            return out_pc(200);
        } catch(Exception $e) {
            LogLib::inc()->debug(["rerund err:".json_encode($e)]);
            return out_pc(8355,$e->getMessage());
        }
    }
}