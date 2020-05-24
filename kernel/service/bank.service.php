<?php
class BankService{
    //获取一个用户的，金币汇总信息
    function getGoldcoinInfo($uid){
        $rs = [];
        $userLib = new UserService();
        $info = $userLib->getUinfoById($uid);

//        LogLib::appWriteFileHash([$info['goldcoin'],$info['goldcoin_today'],$info['goldcoin_sum'],$info['goldcoin_sum_less']]);
        //当前金币总数
        $rs['total'] = $info['goldcoin'];
        //今日 获取金币总数
        $gameS = new GamesService();
        $ToadyUserSumGoldcoin = $gameS->getToadyUserSumGoldcoin($uid);
        if($ToadyUserSumGoldcoin){
            $rs['today'] = $ToadyUserSumGoldcoin;
        }elseif($ToadyUserSumGoldcoin === false){
            //按说不应该再读库~但，此处是后加的，需要兼容一下
            //唯一操蛋的一点就是，如果用户金币数本就为0，就每次都得读库了
            $today = dayStartEndUnixtime();
            $sql = "select sum(num) as total from goldcoin_log where opt = 1 and uid = {$uid} and a_time >=".$today['s_time'];
            $sqlrs2 = GoldcoinLogModel::db()->getRowBySQL($sql);
            if(!$sqlrs2 || !arrKeyIssetAndExist($sqlrs2,'total')){
                $cnt = 0;
            }else{
                $cnt = $sqlrs2['total'];
            }
            $rs['today'] = $cnt;

            $gameS->setToadyUserSumGoldcoin($uid,$cnt);
        }
        //已获取总金币数
        if(0){
//        if(arrKeyIssetAndExist($info,'goldcoin_sum')){
            $rs['sum'] = $info['goldcoin_sum'];
        }else{
            // 玩家累计所得金币数，从查表改成读取缓存中的数据 modify by XiaHB time:2019/07/01 Begin;
            // 保留下方的原代码：
            /*$sql = "select sum(num) as total from goldcoin_log where opt = 1 and uid = {$uid}";
            $rs3 = GoldcoinLogModel::db()->getRowBySQL($sql);
            if(!$rs || !arrKeyIssetAndExist($rs3,'total')){
                $sum = 0;
            }else{
                $sum = $rs3['total'];
            }
            $rs['sum'] = $sum;*/
            // 新逻辑如下：
            $tmpBankService = new BankService();
            $sum = $tmpBankService->getAdditiveSumGold($uid);
            $rs['sum'] = $sum;
            // 玩家累计所得金币数，从查表改成读取缓存中的数据 modify by XiaHB time:2019/07/01   End;
            $userLib->upUserInfo($uid,array('goldcoin_sum'=>$sum));
//            $userLib->upCacheUinfoByField();
        }
        //总 消耗 金币数
//        if(0){
////        if(arrKeyIssetAndExist($info,'goldcoin_sum_less')){
//            $rs['goldcoin_sum_less'] = $info['goldcoin_sum_less'];
//        }else{
//            $sql = "select sum(num) as total from goldcoin_log where opt = 2 and uid = {$uid}";
//            $rs4 = GoldcoinLogModel::db()->getRowBySQL($sql);
//            if(!$rs4 || !arrKeyIssetAndExist($rs4,'total')){
//                $sum = 0;
//            }else{
//                $sum = $rs4['total'];
//            }
//            $rs['sum_less'] = $sum;
//            $userLib->upUserInfo($uid,array('goldcoin_sum_less'=>$sum));
//        }

        $rs['sum_less'] =  0;

        return out_pc(200,$rs);
    }


    //使用优惠卷
    function useCoupon($uid,$couponId){
        if(!$couponId){
            return out_pc(8043);
        }
        $coupon = UserMoneyCouponModel::db()->getById($couponId);
        if(!$coupon){
            return out_pc(1021);
        }

        if($coupon['uid'] != $uid){
            return out_pc(8277);
        }

        if($coupon['valid_time'] > time()){

            return out_pc(8307);
        }

        if(time() > $coupon['expire_time']){
            return out_pc(8292);
        }

        if($coupon['use_time'] && $coupon['use_time'] > 0 ){
            return out_pc(8308);
        }

        $num = $coupon['money'];
        //查看，当前金币余额
        $bank = new BankService();
        $bankInfo = $bank->getGoldcoinInfo($uid);
        $bankInfo = $bankInfo['msg'];
        if(PCK_AREA != 'en'){
            $totalRmb = $bankInfo['total'] * $GLOBALS['main']['goldcoinExchangeRMB'];// 国内汇率标准;
        }else{
            $totalRmb = $bankInfo['total'] * $GLOBALS['main']['goldcoinExchangeUSD'];// 海外汇率标准;
        }
        if($num > $totalRmb){
            return out_pc(8286);
        }

        $today = dayStartEndUnixtime();
        $isUse = MoneyOrderModel::db()->getRow(" uid = $uid and type = 1 and status = 2 and a_time >= " . $today['s_time']);
        if($isUse){
            return out_pc(8309);
        }

        $lib = new UserService();
        $user = $lib->getUinfoById($uid);
        if(!$user){
            return out_pc(1000);
        }

        if(!$user['wechat_uid']){
            return out_pc(8283);
        }
        //交易流水号
        $tradeNo = $this->getTradeNo();

//        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['getMoneyLock']['key'],$uid);
//        $config = array('nx', 'ex'=>$GLOBALS['rediskey']['getMoneyLock']['expire']);
//        $addLockRs = RedisPHPLib::getServerConnFD()->set($key,time(),$config);
//        if(!$addLockRs) {
//            return out_pc(8287);
//        }


        $WxpayService = $this->getWXPayClass();
        $responseXml = $WxpayService->createJsBizPackage($user['wechat_uid'],$num,$tradeNo);
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);

        //正常用户提现
        $data = array(
            'out_trade_no'=>'','in_trade_no'=>$tradeNo,'num'=>$num,'uid'=>$uid,'status'=>1,'thir_back_info'=>'','a_time'=>time(),
            'thir_back_info'=>$responseXml,
            'element_id'=>$coupon['element_id'],
            'balance'=>$bankInfo['total'],  //金币当前余额
        );


        LogLib::appWriteFileHash($data);
//        var_dump($unifiedOrder);

        $exeption = array('NO_AUTH','AMOUNT_LIMIT','PARAM_ERROR','OPENID_ERROR','SEND_FAILED','SYSTEMERROR','NAME_MISMATCH','SIGN_ERROR','XML_ERROR',
            'FATAL_ERROR','FREQ_LIMIT','MONEY_LIMIT','CA_ERROR','PARAM_IS_NOT_UTF8','SENDNUM_LIMIT','RECV_ACCOUNT_NOT_ALLOWED','PAY_CHANNEL_NOT_ALLOWED');

        if (!$unifiedOrder || $unifiedOrder === false) {
            $data['status'] = 3;
            $data['state'] = 2;
            $aid = MoneyOrderModel::db()->add($data);

//            RedisPHPLib::delLock($key);
            return out_pc(7000);
        }

//        if ($unifiedOrder->return_code != 'SUCCESS') {
//            $data['status'] = 3;
//            $data['state'] = 2;
//            $aid = MoneyOrderModel::db()->add($data);
////            RedisPHPLib::delLock($key);
//            return out_pc(7001);
//        }

        if ($unifiedOrder->result_code != 'SUCCESS') {
            $data['status'] = 3;
            $data['state'] = 2;
            $aid = MoneyOrderModel::db()->add($data);
//            RedisPHPLib::delLock($key);

            if(in_array($unifiedOrder->err_code,$exeption)){
                return out_pc(7002);
            }elseif($unifiedOrder->err_code == 'NOTENOUGH'){
                return out_pc(7003);
            }elseif($unifiedOrder->err_code == 'V2_ACCOUNT_SIMPLE_BAN'){
                return out_pc(7004);
            }else{
                return out_pc(7005);
            }
        }

        $data['status'] = 2;
        $data['type'] = 1;
        $data['state'] = 1;

        $aid = MoneyOrderModel::db()->add($data);

        // 提现券重复使用BUG修复 add by XiaHB time:2019/06/01 Begin;
        if($aid){
            UserMoneyCouponModel::db()->upById($couponId, array('use_time'=>time()));
        }
        // 提现券重复使用BUG修复 add by XiaHB time:2019/06/01   End;

        LogLib::appWriteFileHash($aid);

        //扣除金币,1块钱的不走后台，也就没有 预扣金币这个情况，所以直接扣了就可以
        $lessGold = $num * 10000;
        $rs = $lib->lessGoldcoin($uid,"-".$lessGold,GoldcoinLogModel::$_type_use_coupon_gold_exchange_money,$aid);

        $lib = new TaskService();
        $rs = $lib->trigger($uid,3);

//        RedisPHPLib::delLock($key);

        return out_pc(200);

    }

    //提现
    function getMoney($uid,$elementId,$oid = 0,$shareId = 0,$adId = 0){
        if(!$uid){
            return out_pc(8002);
        }

        $lib = new UserService();
        $user = $lib->getUinfoById($uid);
        if(!$user){
            return out_pc(1000);
        }

        if(!$user['wechat_uid']){
            return out_pc(8283);
        }

        if(!$elementId){
            return out_pc(8048);
        }

        $elementId = intval($elementId);
        if(!$elementId){
            return out_pc(8048);
        }

        $elements = $GLOBALS['main']['getMoneyElement'];
        if(!arrKeyIssetAndExist($elements,$elementId)){
            return out_pc(8024);
        }

        $num = $elements[$elementId];
        if($num <=0 && !$num ){
            return out_pc(8024);
        }

        if($elementId == 1 || $num == 1){
            return out_pc(7026);
        }

        if($num > 100){
            return out_pc(8285);
        }
        //获取0.5提现 成功且，审核通过,每人，限制2条

        if($elementId == 7){
            $elements7List = MoneyOrderModel::db()->getCount(" uid =  ".$uid." and element_id = 7 and status = 2 and state = 1 ");
            if($elements7List &&  $elements7List>=2 ){
                return out_pc(8291);
            }
            $today = dayStartEndUnixtime();
            $elements7Today = MoneyOrderModel::db()->getCount(" uid =  ".$uid." and element_id = 7 and status = 2 and state = 1 and a_time >=".$today['s_time']);
            if($elements7Today   ){
                return out_pc(7006);
            }
        }elseif($elementId == 2){//5元只允许提现一次
            $elements2Row = MoneyOrderModel::db()->getRow(" uid =  ".$uid." and element_id = 2 and status = 2 and state = 1 ");
            if($elements2Row){
                return out_pc(7027);
            }
        }
        //单日：提现次数、金额，限定
        $today = dayStartEndUnixtime();
        $getMoneyCnt = MoneyOrderModel::db()->getAll(" a_time>= {$today['s_time']} and status = 2 and state = 1 and uid =  ".$uid);
        if($getMoneyCnt){
            if(count($getMoneyCnt) >= 2){
                return out_pc(7006);
            }
            $todayGetMoneyTotal = 0;
            foreach ($getMoneyCnt as $k=>$v){
                $todayGetMoneyTotal += $v['num'];
            }

            if($todayGetMoneyTotal  > 20){
                return out_pc(7007);
            }
        }


        //未处理状态，如果用户金币足够，一直 提现，后台管理员又没来得及处理。记录就会一直加加加加加
        $unProcessCnt = MoneyOrderModel::db()->getCount(" a_time>= {$today['s_time']} and status = 1 and state = 0 and uid =  ".$uid);
        if($oid){
            //管理员审核的时候，是大于1次
            if($unProcessCnt > 1){
                return out_pc(7023);
            }
        }else{
            if($unProcessCnt >= 1){
                return out_pc(7023);
            }
        }


        if(!$oid){
            if($elementId != 7){
                $clientInfo = get_client_info();
                if($clientInfo['app_version'] < '1.1.0'){
                    $signS = new SignService();
                    $signList = $signS->getJunction7Day($uid);
                    if(!$signList || count($signList) < 7){//第7天是否签到
                        return out_pc(8302);
                    }
                }else{
                    $userS = new UserService();
                    $activeList = $userS->getActiveContinue($uid);

                    if(!$activeList || count($activeList) < 7){
                        return out_pc(8302);
                    }
                }

            }
        }

        if($shareId){
            if($shareId == -1){
                return out_pc(8301);
            }

            $shareRecord = ShareModel::db()->getRow(" id = $shareId and uid = $uid and type = ".GoldcoinLogModel::$_type_share_get_money);
            if(!$shareRecord){
                return out_pc(8301);
            }
        }elseif ($adId){
            if($adId == -2){
                return out_pc(8322);
            }

            $ad = AdLogModel::db()->getById($adId);
            if(!$ad){
                return out_pc(1025);
            }

            if(!$ad['e_time']){
                return out_pc(8304);
            }

            if($ad['type'] != GoldcoinLogModel::$_type_ad_get_money){
                return out_pc(8306);

            }

            if($ad['uid'] != $uid){
                return out_pc(8071);
            }
        }



        if(!$oid){
            //查看，当前金币余额
            $bank = new BankService();
            $bankInfo = $bank->getGoldcoinInfo($uid);
            $bankInfo = $bankInfo['msg'];
            $totalRmb = $bankInfo['total'] * $GLOBALS['main']['goldcoinExchangeRMB'];
            if($num > $totalRmb){
                return out_pc(8286);
            }

        }else{
            $auditOrder = MoneyOrderModel::db()->getById($oid);
            if(!$auditOrder){
                return out_pc(1021);
            }

            if($auditOrder['state'] != 0 ){//只能未审核的状态才能提现，然后，提现成功后，该状态置为  <通过>/<拒绝>
                return out_pc(7020);
            }

            if($auditOrder['status'] != 1){//已提过
                return out_pc(7022);
            }

//            $lessGoldLog = GoldcoinLogModel::db()->getRow(" memon = $oid");
//            if(!$lessGoldLog){
//                return out_pc(7025);
//            }
        }




        //交易流水号
        $tradeNo = $this->getTradeNo();

        if($oid){

        }else{
            LogLib::appWriteFileHash(['in getmoney but num > 1']);
            if($num > 1){
                $data = array(
                    'out_trade_no'=>'','in_trade_no'=>$tradeNo,'num'=>$num,'uid'=>$uid,'status'=>1,'thir_back_info'=>'','a_time'=>time(),
                    'thir_back_info'=>'',
                    'state'=>0,
                    'element_id'=>$elementId,
                    'balance'=>$bankInfo['total'],  //金币当前余额
                );
                $aid = MoneyOrderModel::db()->add($data);
                //预扣金币，后台审核不通过，会返回
                $rs = $lib->lessGoldcoin($uid,"-".$num * 10000,GoldcoinLogModel::$_type_gold_exchange_money,$aid);
                return out_pc(7021);
            }
        }


        if(ENV == 'dev'){
            $num = 0.4;//测试
        }

        //获取锁
        if(APP_NAME == 'instantplayadmin' || APP_NAME == 'instantplayadminnew'){
            $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['getMoneyLock']['key'],$uid,IS_NAME);
        }else{
            $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['getMoneyLock']['key'],$uid);
        }

        LogLib::appWriteFileHash([$key]);

        $config = array('nx', 'ex'=>$GLOBALS['rediskey']['getMoneyLock']['expire']);

        $addLockRs = RedisPHPLib::getServerConnFD()->set($key,time(),$config);
        if(!$addLockRs) {
            return out_pc(8287);
        }


//        $num = 0.4;//用于测试
        //请求微信，开始付款
//        $WxpayService = new WxpayService('1358633902','111',null,'f313c94d34cae6632d37b2d2eb617fe5');
        $WxpayService = $this->getWXPayClass();
        $responseXml = $WxpayService->createJsBizPackage($user['wechat_uid'],$num,$tradeNo);
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);

        if($oid ){
            $data = $auditOrder;
            $data['thir_back_info'] = $responseXml;
            //后台管理员，审核通过后才提现
        }else{
            //正常用户提现
            $data = array(
                'out_trade_no'=>'','in_trade_no'=>$tradeNo,'num'=>$num,'uid'=>$uid,'status'=>1,'thir_back_info'=>'','a_time'=>time(),
                'thir_back_info'=>$responseXml,
                'element_id'=>$elementId,
                'balance'=>$bankInfo['total'],  //金币当前余额
            );
        }


        LogLib::appWriteFileHash($data);
//        var_dump($unifiedOrder);

        $exeption = array('NO_AUTH','AMOUNT_LIMIT','PARAM_ERROR','OPENID_ERROR','SEND_FAILED','SYSTEMERROR','NAME_MISMATCH','SIGN_ERROR','XML_ERROR',
            'FATAL_ERROR','FREQ_LIMIT','MONEY_LIMIT','CA_ERROR','PARAM_IS_NOT_UTF8','SENDNUM_LIMIT','RECV_ACCOUNT_NOT_ALLOWED','PAY_CHANNEL_NOT_ALLOWED');



        if (!$unifiedOrder || $unifiedOrder === false) {
            $data['status'] = 3;
            if(!$oid){
                $data['state'] = 2;
                $aid = MoneyOrderModel::db()->add($data);
            }else{
                $data['state'] = 2;
                $aid = MoneyOrderModel::db()->upById($oid,$data);
            }

            RedisPHPLib::delLock($key);
            return out_pc(7000);
        }

        if ($unifiedOrder->return_code != 'SUCCESS') {
            $data['status'] = 3;
            if(!$oid){
                $data['state'] = 2;
                $aid = MoneyOrderModel::db()->add($data);
            }else{
                $data['state'] = 2;
                $aid = MoneyOrderModel::db()->upById($oid,$data);
            }
            RedisPHPLib::delLock($key);
//            return out_pc(7001);

            if(in_array($unifiedOrder->err_code,$exeption)){
                return out_pc(7002);
            }elseif($unifiedOrder->err_code == 'NOTENOUGH'){
                return out_pc(7003);
            }elseif($unifiedOrder->err_code == 'V2_ACCOUNT_SIMPLE_BAN'){
                return out_pc(7004);
            }else{
                return out_pc(7005);
            }
        }

        if ($unifiedOrder->result_code != 'SUCCESS') {
            $data['status'] = 3;
            $data['state'] = 2;
            if(!$oid){
                $aid = MoneyOrderModel::db()->add($data);
            }else{
                $aid = MoneyOrderModel::db()->upById($oid,$data);
            }
            RedisPHPLib::delLock($key);

            if(in_array($unifiedOrder->err_code,$exeption)){
                return out_pc(7002);
            }elseif($unifiedOrder->err_code == 'NOTENOUGH'){
                return out_pc(7003);
            }elseif($unifiedOrder->err_code == 'V2_ACCOUNT_SIMPLE_BAN'){
                return out_pc(7004);
            }else{
                return out_pc(7005);
            }
        }

        $data['status'] = 2;
        $data['state'] = 1;

        if(!$oid){
            $aid = MoneyOrderModel::db()->add($data);
        }else{
            $aid = MoneyOrderModel::db()->upById($oid,$data);
        }


        LogLib::appWriteFileHash($aid);

        //扣除金币,1块钱的不走后台，也就没有 预扣金币这个情况，所以直接扣了就可以
        if($elementId == 7 ){
            $lessGold = $num * 10000;
            $rs = $lib->lessGoldcoin($uid,"-".$lessGold,GoldcoinLogModel::$_type_gold_exchange_money,$aid);
        }



        $lib = new TaskService();
        $rs = $lib->trigger($uid,3);
//        var_dump($rs);exit;

        RedisPHPLib::delLock($key);


        return out_pc(200);
    }

    function getWXPayClass(){
        if(APP_NAME == 'instantplay' || APP_NAME == 'instantplayadmin'){
            return new WxpayService('1358633902','wx406c54b223a06df0',null,'f313c94d34cae6632d37b2d2eb617fe5');
        }else{
            return new WxpayService('1358633902','wx66a442fbdb47a4fe',null,'f313c94d34cae6632d37b2d2eb617fe5');
        }


    }

    //内部生成的交易流水号
    function getTradeNo(){
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);

        return $msectime . rand(100000,999999);
    }

    /**
     * @param $uid
     * @param $elementId
     * @param int $oid
     * @param int $shareId
     * @return array
     */
    function getMoneyOsInfo($uid, $elementId, $payPalAddr){
        if(!$uid){
            return out_pc(8002);
        }

        if(!$elementId){
            return out_pc(8048);
        }

        if(!$payPalAddr){
            return out_pc(7051);
        }

        if(is_numeric($payPalAddr)){
            $chars = "/^13[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}$|17[0-9]{1}[0-9]{8}$/";
            if(!preg_match($chars, $payPalAddr)) {
                return out_pc(7050);
            }
        }else{
            $chars1 = "/([a-z0-9]*[-_.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[.][a-z]{2,3}([.][a-z]{2})?/i";;
            if (!preg_match($chars1, $payPalAddr)) {
                return out_pc(7050);
            }
        }

        $lib = new UserService();
        $user = $lib->getUinfoById($uid);
        if(!$user){
            return out_pc(1000);
        }

        $userInfo = UserModel::db()->getById($uid);
        if(empty($userInfo['facebook_uid']) && empty($userInfo['google_uid'])){
            return out_pc(7052);
        }

        $elementId = intval($elementId);
        if(!$elementId){
            return out_pc(7053);
        }

        $elements = $GLOBALS['main']['getMoneyElementOs'];
        if(!arrKeyIssetAndExist($elements,$elementId)){
            return out_pc(7054);
        }

        $num = $elements[$elementId];
        if($num <=0 && !$num ){
            return out_pc(7055);
        }

        if($num > 20){
            return out_pc(7056);
        }

        // 兑换限制： 每天最多可成功提现1次,先判断金币是否满足，再判断是否超过每天兑换次数;
        $today = dayStartEndUnixtime();
        // status = 1：未处理，2：成功，3：失败，4：已确认发货;
        $getMoneyCnt = MoneyOrderModel::db()->getAll(" a_time >= {$today['s_time']} AND status = 2 AND uid =  ".$uid);
        if($getMoneyCnt){
            if(10 == $getMoneyCnt[0]['num']){
                if(count($getMoneyCnt) >= 2){
                    return out_pc(7057);
                }
            }elseif (20 == $getMoneyCnt[0]['num']){
                if(count($getMoneyCnt) >= 1){
                    return out_pc(7057);
                }
            }
        }
        // 后台处理肯定存在不同步的情况，故而加上限制;
        $unProcessCnt = MoneyOrderModel::db()->getCount(" a_time>= {$today['s_time']} AND status = 1 AND uid =  ".$uid);
        if($unProcessCnt >= 1){
            return out_pc(7058);
        }

        // 当前金币余额判断;
        $bank = new BankService();
        $bankInfo = $bank->getGoldcoinInfo($uid);
        $bankInfo = $bankInfo['msg'];
        $totalRmb = $bankInfo['total'] * $GLOBALS['main']['goldcoinExchangeUSD'];
        if($num > $totalRmb){
            return out_pc(7059);
        }

        // 获取内部交易流水号;
        $tradeNo = $this->getTradeNo();

        $data = array(
            'out_trade_no'=>'',
            'in_trade_no'=>$tradeNo,
            'num'=>$num,
            'uid'=>$uid,
            'status'=>1,
            'thir_back_info'=>'',
            'a_time'=>time(),
            'state'=>0,
            'element_id'=>$elementId,
            'balance'=>$bankInfo['total'],// 金币当前余额;
            'pay_pal_address'=>$payPalAddr,// 金币当前余额;
        );
        $aid = MoneyOrderModel::db()->add($data);
        // 预扣金币，后台审核不通过，会返回;
        $lib->lessGoldcoin($uid,"-".$num * 100000, GoldcoinLogModel::$_type_gold_exchange_money,$aid);
        return out_pc(200);
    }

    /**
     * 设置redis中玩家累计所得金币数;
     * @param $uid
     * @param $num
     * @return bool
     */
    public function setAdditiveSumGold($uid, $num){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['additive_sum_gold']['key'], $uid,IS_NAME);
        $num = (int)$num;
        if(!$num || $num < 0){
            return false;
        }
        $rs = $this->getAdditiveSumGold($uid);
        if(!$rs){
            $rs = 0;
        }
        $result = RedisPHPLib::getServerConnFD()->set($key, $rs + $num , $GLOBALS['rediskey']['additive_sum_gold']['expire']);
        if(true === $result){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获取redis中玩家累计所得金币数;
     * @param $uid
     * @return int
     */
    public function getAdditiveSumGold($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['additive_sum_gold']['key'], $uid,IS_NAME);
        $res = RedisPHPLib::getServerConnFD()->get($key);
        // 兼容处理，老用户肯定是不存在这个key值的，因此要读表，并将数据写入缓存;
        if(false === $res){
            $info = GoldcoinLogModel::db()->getRow(" uid = {$uid} AND opt = 1 ", 'goldcoin_log', " SUM(num) AS gold_add_sum");
            // 下述条件必须全部满足;
            if(!empty($info) && is_array($info) && isset($info['gold_add_sum']) && $info['gold_add_sum'] > 0){
                $gold_add_sum = (int)$info['gold_add_sum'];
                $result = RedisPHPLib::getServerConnFD()->set($key, $gold_add_sum , $GLOBALS['rediskey']['additive_sum_gold']['expire']);
                if(true === $result){
                    return $gold_add_sum;
                }else{
                    return 0;
                }
            }
        }else{
            return (int)$res;// 金币数;
        }
        return 0;
    }

    /**
     * 删除redis中玩家累计所得金币数;
     * @param $uid
     * @return int
     */
    public function delAdditiveSumGold($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['additive_sum_gold']['key'],$uid .date("Ymd"),IS_NAME);
        // true or false;
        return RedisPHPLib::getServerConnFD()->del($key);
    }

}

class WxpayService
{
    protected $mchid;//商户号
    protected $appid;//商户appId
    protected $apiKey;
    public $data = null;//证书KEY

    public function __construct($mchid, $appid, $appKey,$key)
    {
        $this->mchid = $mchid;
        $this->appid = $appid;
        $this->appKey = $appKey;
        $this->apiKey = $key;
    }

    /**
     * 通过跳转获取用户的openid，跳转流程如下：
     * 1、设置自己需要调回的url及其其他参数，跳转到微信服务器https://open.weixin.qq.com/connect/oauth2/authorize
     * 2、微信服务处理完成之后会跳转回用户redirect_uri地址，此时会带上一些参数，如：code
     * @return 用户的openid
     */
    public function GetOpenid()
    {
        //通过code获得openid
        if (!isset($_GET['code'])){
            //触发微信返回code码
            $scheme = $_SERVER['HTTPS']=='on' ? 'https://' : 'http://';
            $baseUrl = urlencode($scheme.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].$_SERVER['QUERY_STRING']);
            $url = $this->__CreateOauthUrlForCode($baseUrl);
            Header("Location: $url");
            exit();
        } else {
            //获取code码，以获取openid
            $code = $_GET['code'];
            $openid = $this->getOpenidFromMp($code);
            return $openid;
        }
    }

    /**
     * 通过code从工作平台获取openid机器access_token
     * @param string $code 微信跳转回来带上的code
     * @return openid
     */
    public function GetOpenidFromMp($code)
    {
        $url = $this->__CreateOauthUrlForOpenid($code);
        $res = self::curlGet($url);
        //取出openid
        $data = json_decode($res,true);
        $this->data = $data;
        $openid = $data['openid'];
        return $openid;
    }

    /**
     * 构造获取open和access_toke的url地址
     * @param string $code，微信跳转带回的code
     * @return 请求的url
     */
    private function __CreateOauthUrlForOpenid($code)
    {
        $urlObj["appid"] = $this->appid;
        $urlObj["secret"] = $this->appKey;
        $urlObj["code"] = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
    }

    /**
     * 构造获取code的url连接
     * @param string $redirectUrl 微信服务器回跳的url，需要url编码
     * @return 返回构造好的url
     */
    private function __CreateOauthUrlForCode($redirectUrl)
    {
        $urlObj["appid"] = $this->appid;
        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = "snsapi_base";
        $urlObj["state"] = "STATE"."#wechat_redirect";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
    }

    /**
     * 拼接签名字符串
     * @param array $urlObj
     * @return 返回已经拼接好的字符串
     */
    private function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v)
        {
            if($k != "sign") $buff .= $k . "=" . $v . "&";
        }
        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * 企业付款
     * @param string $openid 调用【网页授权获取用户信息】接口获取到用户在该公众号下的Openid
     * @param float $totalFee 收款总费用 单位元
     * @param string $outTradeNo 唯一的订单号
     * @param string $orderName 订单名称
     * @param string $notifyUrl 支付结果通知url 不要有问号
     * @param string $timestamp 支付时间
     * @return string
     */
    public function createJsBizPackage($openid, $totalFee, $outTradeNo,$trueName = null)
    {
        $config = array(
            'mch_id' => $this->mchid,
            'appid' => $this->appid,
            'key' => $this->apiKey,
        );

        $unified = array(
            'mch_appid' => $config['appid'],
            'mchid' => $config['mch_id'],
            'nonce_str' => self::createNonceStr(),
            'openid' => $openid,
            'check_name'=>'NO_CHECK', //校验用户姓名选项。NO_CHECK：不校验真实姓名，FORCE_CHECK：强校验真实姓名
            're_user_name'=>$trueName,   //收款用户真实姓名（不支持给非实名用户打款）
            'partner_trade_no' => $outTradeNo,
            'spbill_create_ip' => '127.0.0.1',
            'amount' => intval($totalFee * 100), //单位 转为分
        );

        //企业付款操作说明信息
        if(APP_NAME == 'instantplay'){
            $unified['desc'] = '开心小游戏提现成功';
        }else{
            $unified['desc'] = '玩赚小游戏提现成功';
        }

        
        $unified['sign'] = self::getSign($unified, $config['key']);
        $responseXml = $this->curlPost('https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers', self::arrayToXml($unified));
        return $responseXml;
    }

    public static function curlGet($url = '', $options = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public function curlPost($url = '', $postData = '', $options = array())
    {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }

        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        //第一种方法，cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLCERT,APP_CONFIG.'/wechat_pay/apiclient_cert.pem');
        //默认格式为PEM，可以注释
        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLKEY,APP_CONFIG.'/wechat_pay/apiclient_key.pem');
        //第二种方式，两个文件合成一个.pem文件
// curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public static function createNonceStr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    public static function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }

    public static function getSign($params, $key)
    {
        ksort($params, SORT_STRING);
        $unSignParaString = self::formatQueryParaMap($params, false);
        $signStr = strtoupper(md5($unSignParaString . "&key=" . $key));
        return $signStr;
    }
    protected static function formatQueryParaMap($paraMap, $urlEncode = false)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if (null != $v && "null" != $v) {
                if ($urlEncode) {
                    $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
}