<?php
class OrderCtrl extends BaseCtrl  {
    function getListByUser(){
        $list = $this->orderService->getUserList($this->uid);
        return out_pc(200,$list);
    }

    function testPay(){
        include PLUGIN ."wxpay/WxPay.Config.php";
        include PLUGIN ."wxpay/WxPay.JsApiPay.php";
        include PLUGIN ."wxpay/WxPay.Api.php";



        $tools = new JsApiPay();
        $openId = $this->userService->getUinfoById($this->uid)['msg']['wx_open_id'];
//        var_dump($openId);

        //②、统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody("test");
        $input->SetAttach("test");
        $input->SetOut_trade_no("sdkphp".date("YmdHis"));
        $input->SetTotal_fee("1");
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        $input->SetNotify_url("http://paysdk.weixin.qq.com/notify.php");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $config = new WxPayConfig();
        $order = WxPayApi::unifiedOrder($config, $input);

        LogLib::inc()->debug($order);
//        var_dump($order);
//        echo "<br/><br/>";
        $jsApiParameters = $tools->GetJsApiParameters($order);
        out_ajax(200,$jsApiParameters);
//        var_dump($jsApiParameters);
//        //获取共享收货地址js函数参数
//        $editAddress = $tools->GetEditAddressParameters();
//        echo "<br/><br/>";
//        var_dump($editAddress);
    }


    function doing(){



        $this->testPay();
        exit;










//        $num = get_request_one($request['num'],0);
//        $gid = get_request_one($request['gid'],0);

        $agentUid =get_request_one( $this->request,'agent_uid',0);
        $couponId = get_request_one( $this->request,'coupon_id',0);
        $num = get_request_one( $this->request,'num',0);
        $gid = get_request_one( $this->request,'gid',0);
        $pid = get_request_one( $this->request,'pid',0);
        $memo = get_request_one( $this->request,'memo','');


        $oid = $this->orderService->doing($this->uid,$pid,$gid,$num,$agentUid,$couponId,$memo);
        return out_ajax(200,$oid);
    }
    //某一个产品，近期购买记录
    function getNearUserBuyHistory(){
        $pid = $agentUid =get_request_one( $this->request,'pid',0);
        $list = OrderModel::db()->getAll("pid = $pid group by uid order by a_time desc limit 20 ");
        foreach ($list as $k=>$v){
            $list[$k]['nickname'] = '游客'.$k;
            $list[$k]['avatar'] = get_avatar_url("");
            $user = UserModel::db()->getById($v['uid']);
            if($user){
                $list[$k]['nickname'] = $user['nickname'];
                $list[$k]['avatar'] = get_avatar_url( $user['avatar']);
                $list[$k]['dt'] = get_default_date($user['a_time']);
            }
        }

        return out_ajax(200,$list);
    }

    function cntUserOrderByType(){
        $list = OrderModel::db()->getAll("uid = {$this->uid} ");
        if(!$list){

        }
    }

    function getOneDetail(){
        $id = $agentUid =get_request_one( $this->request,'id',0);
        $order = $this->orderService->getOneDetail($id);
        return out_pc(200,$order);
    }

    function refund(){
        $id = $agentUid =get_request_one( $this->request,'id',0);
        $this->orderService->refund($id);
    }

    function pay(){
        $oid = $agentUid =get_request_one( $this->request,'oid',0);
        $uid = $this->uid;
        $type = $agentUid =get_request_one( $this->request,'type',0);

    }

    function delUserCart(){
        $id = get_request_one( $this->request,'id',0);
        $this->orderService->delUserCart($id,$this->uid);
    }

    function getUserCart(){
//        $oid = $agentUid =get_request_one( $this->request,'oid',0);
        $list = $this->orderService->getUserCart($this->uid);
        out_ajax(200,$list);
    }

    function confirmOrder(){
        $pid =get_request_one( $this->request,'pid',0);
        $pcap = get_request_one( $this->request,'pcap',"");
        $num =get_request_one( $this->request,'num',0);

        $rs = $this->orderService->confirmOrder($pid,$num,$pcap);
        out_ajax($rs['code'],$rs['msg']);
    }

    function addUserCart(){
        $pid = $agentUid =get_request_one( $this->request,'pid',0);
        $rs = $this->orderService->addUserCart($this->uid,$pid);
        out_ajax($rs['code'],$rs['msg']);
    }


}