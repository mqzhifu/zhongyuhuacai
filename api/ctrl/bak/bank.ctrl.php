<?php

class BankCtrl extends BaseCtrl {
    //获取用户 金币汇总信息
    function getGoldcoinInfo(){
        $rs = $this->bankService->getGoldcoinInfo($this->uid);
        return $this->out($rs['code'],$rs['msg']);
    }
    //近3天用户金币消耗日志
    function getGoldLog(){
        //这里默认为 <查看我的钱包> 触发任务
        $rs = $this->taskService->trigger($this->uid,2);



        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['goldcoin_3_log']['key'],$this->uid);
        //先清除已经失效的日志
        $today = dayStartEndUnixtime();
        //2天以前到30天以前的记录区间
        $startT = $today['s_time'] - 2 * 24 * 60 * 60;
        $delStartTime = $today['s_time'] - 30 * 24 * 60 * 60;

        $rs2 = RedisPHPLib::getServerConnFD()->zRemRangeByScore($key,$delStartTime,$startT);
        //根据时间，获取 金币日志
        $list = RedisPHPLib::getServerConnFD()->zRangeByScore($key,$startT,time());
        if($list){
            $data = array();
            foreach ($list as $k=>$v) {
                $row = explode("##",$v);
//                LogLib::appWriteFileHash($row);

                $info = array('a_time'=>$row[2],'num'=>$row[3]);

                if(PCK_AREA != 'en'){
                    $arr = GoldcoinLogModel::getTypeTitle();
                    $info['title'] = $arr[$row[1]];
                    $arr = GoldcoinLogModel::getTypeDesc();
                    $info['content'] = $arr[$row[1]];
                }else{
                    $arr = GoldcoinLogModel::getForeignTypeTitle();
                    $info['title'] = $arr[$row[1]];
                    $arr = GoldcoinLogModel::getForeignTypeDesc();
                    $info['content'] = $arr[$row[1]];
                }

                $data[] = $info;

//                LogLib::appWriteFileHash($info);
            }

            //倒序
            $rs = [];
            for($i=count($data) - 1;$i>=0 ;$i--){
                $rs[] = $data[$i];
            }

        }else{
            //这里做个容错，之前没有加缓存，现在加，会导致之前的数据为空
            //运行一段时间后，会删除这块代码
            $rs = GoldcoinLogModel::db()->getAll("uid = {$this->uid} and a_time >= $startT order by id desc");
            if($rs){
                foreach ($rs as $k=>$v) {
                    $content = $v['id'] . "##" .$v['type'] . "##".$v['a_time']."##".$v['num'];
                    //往redis里写入
                    RedisPHPLib::getServerConnFD()->zAdd($key,$v['a_time'],$content);
                }
            }
        }

        return $this->out(200,$rs);
    }

//    function getGoodsList(){
//        $list = GamesGoodsModel::db()->getAll(" 1 ");
//        return $this->out(200,$list);
//    }

    //使用优惠卷
    function useCoupon($id){
        $rs = $this->bankService->useCoupon($this->uid,$id);
        return $this->out($rs['code'],$rs['msg']);
    }

    //提现的 元素
    function getMoneyElement(){
        $today = dayStartEndUnixtime();
        $elements = $GLOBALS['main']['getMoneyElement'];
        $elements2List = MoneyOrderModel::db()->getRow(" uid =  ".$this->uid." and element_id = 2 and status = 2 and state = 1 ");
        $elements7List = MoneyOrderModel::db()->getCount(" uid =  ".$this->uid." and element_id = 7 and status = 2 and state = 1 ");
        $elements7Today = MoneyOrderModel::db()->getCount(" uid =  ".$this->uid." and element_id = 7 and status = 2 and state = 1 and a_time >=".$today['s_time']);
        $data = array();

        $clientInfo = get_client_info();

        foreach($elements as $k=>$v){
            $allow = 0;
            if($k == 2){
                if($elements2List){
                    $allow = 1;
                }
            }elseif($k == 7 ){
                //这里做个兼容，移动端不支持小数
                if(  $clientInfo['app_version'] < '1.0.9' ){
                    unset($elements[$k]);
                    continue;
                }

                if($elements7List &&  $elements7List>=2 ){
                    $allow = 1;
                }elseif($elements7Today){
                    $allow = 1;
                }

            }

            $data[] = array('element_id'=>$k,'num'=>$v,'allow'=>$allow);
        }

        return $this->out(200,$data);
    }
    //提现日志
    function getMoneyLog(){
        $list = MoneyOrderModel::db()->getAll(" uid =  ".$this->uid . " order by id desc");
        if($list){
            foreach($list as $k=>$v){
                if($v['status'] == 1 && ($v['state'] == 0 || !$v['state'] ) ){//审核中
                    $list[$k]['status'] = 1;
                }elseif( $v['status'] == 2 && $v['state'] == 1){//审核通过了,微信接口也成功了
                    $list[$k]['status'] = 2;
                }elseif($v['status'] == 3 && $v['state'] == 1){//审核通过了,微信接口失败了
                    $list[$k]['status'] = 3;
                }elseif($v['state'] == 2){
                    $list[$k]['status'] = 3;
                }

                unset($list[$k]['id']);
                unset($list[$k]['out_trade_no']);
                unset($list[$k]['in_trade_no']);
                unset($list[$k]['uid']);
                unset($list[$k]['thir_back_info']);
                unset($list[$k]['element_id']);
            }
        }
        return $this->out(200,$list);
    }
    //用户 - 提现
    function getMoney($elementId,$shareId = 0,$adId = 0){
        $rs = $this->bankService->getMoney($this->uid,$elementId,0,$shareId , $adId);
        return $this->out($rs['code'],$rs['msg']);
    }
    //购买游戏内的商品道具
    function payGameGoods($gameId,$feidou_uid,$goodsId,$goldCoin = 0,$type,$developerPayload = ""){
        LogLib::appWriteFileHash(["payGameGoods-----",$this->uid]);

        if(!$gameId){
            return $this->out(8027);
        }

        if(!$goodsId){
            return $this->out(8029);
        }

        if(!$type){
            return $this->out(8004);
        }


        $goldCoin = (int)$goldCoin;

//        if($type == GamesGoodsOrderModel::$_type_wechat){
//            $openId = $this->userService->getFieldById($this->uid,'wechat_uid');
//            if(!$openId){
//                return $this->out(8283);
//            }
//        }

        $goods = PropsPriceModel::db()->getById($goodsId);
        if(!$goods){
            return $this->out(1014);
        }

        if(!arrKeyIssetAndExist($goods,'price')){
            return $this->out(7011);
        }

        $outTradeNo = $gameId.time().rand(100000,99999);
        if($goldCoin){
            if($goldCoin < 1000){
                return $this->out(7013);
            }

            //必须是1000的位数，因为1000金币等于1毛，如果再小的单位，微信那边不接收
            if($goldCoin % 1000 != 0 ){
                return $this->out(7012);
            }

            if(PCK_AREA != 'en'){
                $goodsGoldcoinTrunMoney = round($goldCoin *  $GLOBALS['main']['goldcoinExchangeRMB'],2);// 国内汇率标准;
            }else{
                $goodsGoldcoinTrunMoney = round($goldCoin *  $GLOBALS['main']['goldcoinExchangeUSD'],2);// 海外汇率标准;
            }
            //金币数已超过  商品价
            if($goodsGoldcoinTrunMoney > $goods['price'] ){
                return $this->out(7014);
            }

            $userGoldInfo = $this->bankService->getGoldcoinInfo($this->uid);
            //金币不够
            if($goldCoin > $userGoldInfo['msg']['total']){
                return $this->out(8203);
            }

            //金币就够完全抵扣的，不需要再走，3方支付平台了
            if($goodsGoldcoinTrunMoney == $goods['price']){
                return $this->out(7024);
            }else{
                $money = $goods['price'] - $goodsGoldcoinTrunMoney;
                $category = GamesGoodsOrderModel::$_category_goldcoin_cash;

            }
        }else{
            $money = $goods['price'];
            $category = GamesGoodsOrderModel::$_category_cash;
        }

        include_once PLUGIN ."/wxpay/WxPay.Api.php";
        include_once PLUGIN ."/wxpay/WxPay.Config.php";
        include_once PLUGIN ."/wxpay/WxPay.Notify.php";
        include_once PLUGIN ."/wxpay/WxPay.Data.php";
        include_once PLUGIN ."/wxpay/WxPay.NotifyCallBack.php";

        if($type == GamesGoodsOrderModel::$_type_wechat){
            $order = $this->wxPre($money,$outTradeNo);
        }elseif($type == GamesGoodsOrderModel::$_type_alipay){
            $order = $this->aliPay($money,$outTradeNo);
        }

        $data = array(
            'uid'=>$this->uid,
            'out_trade_no'=>'',
            'in_trade_no'=>$outTradeNo,
            'a_time'=>time(),
            'status'=>GamesGoodsOrderModel::$_status_wait,
            'goldcoin'=>$goldCoin,
            'money'=>$money,
            'pay_type'=>GamesGoodsOrderModel::$_type_wechat,
            'pay_category'=>$category,
            'trade_type'=>GamesGoodsOrderModel::$_trade_type_app,
            'wx_pre_order_back_info'=>json_encode($order),
            'game_id'=>$goods['game_id'],
            'waste_time'=>0,
            'os_type'=>$this->clientInfo['os'],
            'goods_id'=>$goodsId,
            'developerPayload'=>$developerPayload,
            'feidou_uid'=>$feidou_uid,
        );

        if($order['return_code'] == 'FAIL'){
            $data['status'] = GamesGoodsOrderModel::$_status_fail;
            GamesGoodsOrderModel::db()->add($data);
            return $this->out(7015);
        }

        $order['oid'] = $outTradeNo;

//        $order['prepay_id'];//这个参数返回给前端
        $aid = GamesGoodsOrderModel::db()->add($data);

        return $this->out(200,$order) ;
    }
    //获取微信支付后的结果
    function getGamePayOrder($id){
        $order = GamesGoodsOrderModel::db()->getById($id);
        return $this->out(200,$order);
    }

    function WXPayCallback(){
        include_once PLUGIN ."/wxpay/WxPay.Api.php";
        include_once PLUGIN ."/wxpay/WxPay.Config.php";
        include_once PLUGIN ."/wxpay/WxPay.Notify.php";
        include_once PLUGIN ."/wxpay/WxPay.Data.php";
        include_once PLUGIN ."/wxpay/WxPay.NotifyCallBack.php";


//        LogLib::appWriteFileHash([$_REQUEST]);
//        101.226.103.0/25、140.207.54.0/25、103.7.30.0/25、183.3.234.0/25、58.251.80.0/25
        $config = new WxPayConfig();
        $notify = new NotifyCallBack();
        $notify->Handle($config, true);
        $returnData = $notify->GetReturn_msg();

        LogLib::appWriteFileHash(["============notify->GetReturn_msg========",$returnData]);
        LogLib::appWriteFileHash(["============wx_callback_data data========",$notify->wx_callback_data]);


        if($returnData && $returnData == 'OK'){//证明验证都没有问题了
            $wx_callback_data = $notify->wx_callback_data;
            $order = GamesGoodsOrderModel::db()->getRow(" in_trade_no = '{$wx_callback_data['out_trade_no']}' ");
            LogLib::appWriteFileHash($order);
            if(!$order){
                return $this->out(7016);
            }

            if($order['status'] != 1){
                return $this->out(7017);
            }

            $data = array(
                'done_time'=>time(),
                'status'=>GamesGoodsOrderModel::$_status_ok,
                'out_trade_no'=>$wx_callback_data['transaction_id'],
                'wx_final_back_info'=>json_encode($wx_callback_data),
            );

            if( arrKeyIssetAndExist($order,'goldcoin') ){
                $rs = $this->userService->lessGoldcoin($order['uid'],"-" .$order['goldcoin'],GoldcoinLogModel::$_type_pay_game_goods,$wx_callback_data['out_trade_no']);
                LogLib::appWriteFileHash(["====im in les  gold",$rs]);
            }

            $upId = GamesGoodsOrderModel::db()->upById($order['id'],$data);

            // 三期项目新增调用飞豆第三方支付接口 time:20190408 Begin;
            $goodsInfo = GamesGoodsModel::db()->getById($order['goods_id']);
            $requestData = array(
                'userid'=>$order['feidou_uid'],// 用户ID;
                'roleid'=>$order['uid'],// 角色ID;
                'currency'=>'cny',// 第三方充值的原始货币类型，如美元usd，人民币cny;
                'amount'=>$goodsInfo['money'],// 第三方充值的原始货币金额，如0.99;
                'itemid'=>$order['goods_id'],// 第三方充值套餐的ID;
                'itemname'=>$goodsInfo['name'],// 第三方充值套餐的名称;
                'itemprice'=>$order['money'],// 本次充值的人民币金额;
                'coin'=>$order['goldcoin'],// 充值所得游戏币数量;
                'orderid'=>$order['id'],// 第三方充值的订单号（对账用）;
                'device'=>($order['os_type'] == 1)?'android':'ios',// 终端设备分类，全小写。（可选）;
                'channel'=>($order['pay_type'] == 1)?'wx':'alipay',// 终端设备分类，全小写。（可选）;
                //'gameid'=>$order['game_id'],// 表示游戏编号;
                'gameid'=>5740,// 一统;
                'serverid'=>5548,// 表示游戏服务器号【和平台组确定后此值固定，千万不能传1】;
            );
            $feiDouService = new feidouPayService();
            $res = $feiDouService->getIosPay($requestData);
            if(!empty($res)){
                $res_code  =  strstr ( $res ,  ':' ,  true );
                if('ok' == $res_code){
                    $res_meseage  =  strstr ( $res ,  ':' );
                }else{
                    $res_meseage  =  strstr ( $res ,  ':' );
                }
            }
            LogLib::appWriteFileHash(["==============feidou local.iospay.php  end==============",$res_code.'||'.$res_meseage]);
            // 三期项目新增调用飞豆第三方支付接口 time:20190408   End;
            
            // 第一次付费统计 add by xuren 2019-05-30
            FirstPayUserModel::addIfNotExists($order['uid'], $order['game_id'],time());

            return $this->out(200,$upId);
        }else{
            LogLib::appWriteFileHash(["fail",$returnData]);
            return $this->out(7018);
        }
    }

    function appAuthOrderState($oid){
        $order = GamesGoodsOrderModel::db()->getRow(" in_trade_no = '$oid' ");
        LogLib::appWriteFileHash($order);
        if(!$order){
            return $this->out(7016);
        }

        $game = GamesModel::db()->getById($order['game_id']);

//        {"developerPayload": "foobar","productID": "your_product_id","purchaseTime": 1524772796,"purchaseToken": "14245790188"}

        $data = array('developerPayload'=>$order['developerPayload'],'productID'=>$order['goods_id'],'purchaseTime'=>$order['a_time'],'purchaseToken'=>$order['in_trade_no']);
        $data = json_encode($data);

        $order['signedRequest'] = base64_encode( hash_hmac("sha256", $data,$game['app_secret']) ) . "." . base64_encode($data);

        return $this->out(200,$order);
    }

    function queryWxPayOrder(){
        $config = new WxPayConfig();
        $notify = new PayNotifyCallBack();

        $notify->Queryorder();
    }



    function aliPayCallback(){
        include_once PLUGIN ."/alipay/AopSdk.php";
        $aop = new AopClient;
        $aop->alipayrsaPublicKey = '请填写支付宝公钥，一行字符串';
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
    }

    function aliPay($money,$outTradeNo){
        include_once PLUGIN ."/alipay/AopSdk.php";

        $callbackUrl = get_domain_url("https") . "/bank/aliPayCallback/";

        $aop = new AopClient;
//        var_dump($aop);exit;

        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = "2016051301398458";
        $aop->rsaPrivateKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCe8EyACjJHGFpF1ynfyxQjFmjfXYiHJGIbCY1SrmhEATEzV51kHEaJu4SYQTS5hBev5TUtdXeO+LEUIHdIFoitYM9qJU9GdJ3qR4sFesC+8iyqu4wyOc2kTlzc8mwggAnkDulPi6P+p1hJf+miE5Eus3NgDBvCsFsvivDSLvuy/QIDAQAB';
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB';
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new AlipayTradeAppPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = "{\"body\":\"我是测试数据\","
            . "\"subject\": \"App支付测试\","
            . "\"out_trade_no\": \"20170125test01\","
            . "\"timeout_express\": \"30m\","
            . "\"total_amount\": \"0.01\","
            . "\"product_code\":\"QUICK_MSECURITY_PAY\""
            . "}";

        $request->setNotifyUrl($callbackUrl);
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
//        var_dump($response);exit;
        //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        echo htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。
        exit;
    }

    function wxPre($money,$outTradeNo){
        $callbackUrl = get_domain_url("https") . "/bank/WXPayCallback/";
        LogLib::appWriteFileHash(["back url:",$callbackUrl]);
//        $orderExpireTime = time() + 2 * 60 * 60;//2个小时
//        $orderExpireTime = date("Ymdhis",$orderExpireTime);
//        var_dump($orderExpireTime);exit;

        $money = $money * 100;//单位是：分
//        $money = 1;//一分,用于测试

        $input =  new WxPayUnifiedOrder();

//        $input->SetOpenid($openId);
        $input->SetDetail("购买游戏内的道具");
        $input->SetOut_trade_no($outTradeNo);
        $input->SetBody("购买道具");
        $input->SetTotal_fee($money);

        $input->SetTime_start(date("YmdHis"));
//        $input->SetTime_expire(date("YmdHis", $orderExpireTime));
        $input->SetGoods_tag("test");
        $input->SetNotify_url($callbackUrl);
//        $input->SetTrade_type("JSAPI");
        $input->SetTrade_type("APP");
        $config = new WxPayConfig();
        $order = WxPayApi::unifiedOrder($config, $input);

        return $order;
    }

    /**
     *    ___                _
     *   / __|___  ___  __ _| |___
     *  | (_ / _ \/ _ \/ _` |   -_)
     *   \___\___/\___/\__, |_\___|
     *                  |___/
     * 海外用户获取可提现提现的配置列表;
     * @return array
     */
    public function getOsWithdrawal(){
        $data = moneyOsConfigModel::db()->getAll();
        foreach ($data as &$v){
            $v['element_id'] = ($v['gift_card_value'] == 10)?'1':'2';
            $v['allow'] = 0;
            $v['img_url'] = get_static_file_url_by_app('gift', $v['img_url'], 'instantplayadmin');
        }
        return $this->out(200, $data);
    }

    /**
     *    ___                _
     *   / __|___  ___  __ _| |___
     *  | (_ / _ \/ _ \/ _` |   -_)
     *   \___\___/\___/\__, |_\___|
     *                  |___/
     * 海外获取用户提现记录;
     * @return array
     */
    function getOsWithdrawalLog(){
        $list = MoneyOrderModel::db()->getAll(" uid =  ".$this->uid . " order by id desc");
        if($list){
            foreach($list as $k=>$v){
                unset($list[$k]['id']);
                unset($list[$k]['out_trade_no']);
                unset($list[$k]['uid']);
                unset($list[$k]['state']);
                unset($list[$k]['element_id']);
                unset($list[$k]['thir_back_info']);
            }
        }
        foreach ($list as &$value){
            $value['payPal_title'] = (string)"PayPal_Payment";
        }
        return $this->out(200, $list);
    }

    /**
     *    ___                _
     *   / __|___  ___  __ _| |___
     *  | (_ / _ \/ _ \/ _` |   -_)
     *   \___\___/\___/\__, |_\___|
     *                  |___/
     * 海外用户 - 操作提现（PayPal）
     * @param $elementId
     * @param $payPalAddr
     * @return array
     */
    public function withdrawOperation($elementId, $payPalAddr){
        $rs = $this->bankService->getMoneyOsInfo($this->uid, $elementId, $payPalAddr);
        return $this->out($rs['code'],$rs['msg']);
    }

    //      //加减金币
//    function abcd($uid = '', $num = 0){
//        define('AC',$this->ac);// 定义AC常量;
//        $rs = $this->userService->addGoldcoin($uid, $num, GoldcoinLogModel::$_type_play_games,1);// 110710,200000
//        $this->out(200,$rs);
//    }
//
//    function dcba($uid = '', $num = 0){
//        $rs = $this->userService->lessGoldcoin($uid, $num, GoldcoinLogModel::$_type_play_games,'d',"a","b",1);// 110710,200000
//        $this->out(200,$rs);
//    }
//    function goldcoinExchangeRMB(){
//        return $this->out(200,$GLOBALS['main']['goldcoinExchangeRMB']);
//    }


}



