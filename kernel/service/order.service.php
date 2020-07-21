<?php

class OrderService{
    public $timeout = 30 * 60;//订单超时时间

    const REFUND_STATS_APPLY = 1;
    const REFUND_STATS_OK = 2;
    const REFUND_STATS_REJECT = 3;

    const REFUND_STATS = [
        self::REFUND_STATS_APPLY=>"申请退款",
        self::REFUND_STATS_OK=>"退款通过",
        self::REFUND_STATS_REJECT=>"驳回",
    ];

    const REFUND_REASON_WRONG = 1;
    const REFUND_REASON_REPEAT = 2;
    const REFUND_REASON_NO_REASON = 3;

    const REFUND_REASON_DESC = [
        self::REFUND_REASON_WRONG=>"拍错了",
        self::REFUND_REASON_REPEAT=>"拍多了",
        self::REFUND_REASON_NO_REASON=>"不想要了",
    ];


    const REFUND_TYPE_GOODS_PRICE = 1;
    const REFUND_TYPE_PRICE = 2;

    const REFUND_TYPE_DESC = [
        self::REFUND_TYPE_GOODS_PRICE=>"退款退货",
        self::REFUND_TYPE_PRICE=>"退款不退货",
    ];


    function getListByAgentId($agentIds , $status = 0 ,$agent_one_withdraw = 0 ,$agent_two_withdraw = 0){
        $where = " agent_id in ( $agentIds ) ";
        if($status){
            $where .= " and status in ($status)";
        }

        if($agent_one_withdraw){
            $where .= " and agent_one_withdraw in ($agent_one_withdraw)";
        }

        if($agent_two_withdraw){
            $where .= " and agent_two_withdraw in ($agent_two_withdraw)";
        }

        $list = OrderModel::db()->getAll($where);
        return $list;
    }
    //下单入口
    function doing($uid,$gidsNums,$couponId = 0,$memo = '',$share_uid = 0,$userSelAddressId = 0){
        LogLib::inc()->debug([$uid,$gidsNums,$couponId ,$memo ,$share_uid ]);

        if(!$uid){
            return out_pc(8002);
        }

        if(!$gidsNums){
            return out_pc(8982);
        }

        $pidsArr = null;
        $goodsTotalPrice = 0;
        $haulage = 0;
        $gidsArr = null;
        $numsArr = null;

        $gidsNumsArr = explode(",",$gidsNums);

        $userService = new UserService();
        $agentService = new AgentService();
        $addressService = new UserAddressService();
        $goodsService =  new GoodsService();
        $productService = new ProductService();

        foreach ($gidsNumsArr as $k=>$v){
            $arr = explode("-",$v);
            $gid = $arr[0];
            $num = $arr[1];

            if(!$num){
                return out_pc(8021);
            }

            $goods = $goodsService->getOneDetail($gid)['msg'];
            if(!$goods){
                return out_pc(1027);
            }

            if(!$goods['stock'] || $goods['stock'] < 0){
                return out_pc(8336,"库存不足：gid $gid");
            }

            $product =$productService->getOneDetail($goods['pid'],0,$uid,0);
            if(!$product){
                return out_pc(1026);
            }

            $gidsArr[] = $gid;
            $numsArr[] = $num;
            $pidsArr[] = $goods['pid'];

            $goodsTotalPrice += $goods['sale_price'];
            $haulage += $goods['haulage'];

//            $goodsLinkPcap = GoodsLinkCategoryAttrModel::db()->getAll(" gid = $gid ");
//            if(!$goodsLinkPcap){
//                exit("goodsLinkPcap is null");
//            }
//
//            $pcap_desc_str = "";
//            foreach ($goodsLinkPcap as $k=>$v){
//                $attr = ProductCategoryAttrModel::db()->getById($v['pca_id'])['name'];
//                $para = ProductCategoryAttrParaModel::db()->getById($v['pcap_id'])['name'];
//                $pcap_desc_str .= $attr . ":".$para . " ";
//                $goodsAttrParaDesc = array('attr'=>$attr,"part"=>$para);
//            }
//            $product['pcap_desc_str'] = $pcap_desc_str;
//            $product['goodsAttrParaDesc'] = $goodsAttrParaDesc;
//            $product['haulage'] = $goods['haulage'];
//            $productGoods[] = $product;
        }


        $userSelAddress = "";//用户收货地址详细信息
        $shareUser = null;//分享者的用户信息
        $agentShare = null;//分享者为代理，代理的信息
        if($share_uid && $share_uid != $uid ){
            $shareUserRs = $userService->getUinfoById($share_uid);
            if($shareUserRs['code'] != 200){
                return out_pc($shareUserRs['code'] ,$shareUserRs['msg']);
            }
            $shareUser = $shareUserRs['msg'];

            $agentShareRs = $agentService->getOneByUid($share_uid);
            if($agentShareRs['code'] == 200){
                $agentShare = $agentShareRs['msg'];
            }

        }

        if($userSelAddressId){
            $userSelAddress = $addressService->getById($userSelAddressId)['msg'];
            if(!$userSelAddress){
                return out_pc(1035);
            }
        }
        //收货地址
        $agentAddress = "";
        if($agentShare){
            $agentAddress = AgentModel::getAddrStrById($agentShare['id']);
        }

        //优惠卷
        $couponPrice = 0;
        $couponId = 0;
        if($couponId){
            $couponInfo = CouponModel::db()->getById($couponId);
            if($couponInfo){
                $couponPrice = $couponInfo['price'];
            }
        }
        //最终价格 = 商品总价 + 运费总价 - 优惠卷价格
        $totalPrice = $goodsTotalPrice + $haulage  -  $couponPrice;

//        $withDrawService = new WithdrawMoneyService();
        $order = array(
            'no'=>get_order_rand_no(),
            'pids'=>implode(",",$pidsArr),
            'gids'=>implode(",",$gidsArr),
            'nums'=>implode(",",$numsArr),
            'uid'=>$uid,
            'total_price'=>$totalPrice,
            'goods_price'=> $goods['sale_price'] ,
            'coupon_price'=>$couponPrice,
            'allow_pay_type'=>$goods['pay_type'],
            'status'=>OrderModel::STATUS_WAIT_PAY,
            'a_time'=>time(),
            'pay_time'=>0,
            'express_no'=>"",
            'haulage'=>$goods['haulage'],
            'share_uid'=>$share_uid,
            'agent_id'=>$agentShare['id'],
            'coupon_id'=>$couponId,
            'address_agent'=>$agentAddress,
            'agent_one_withdraw'=>WithdrawMoneyService::WITHDRAW_ORDER_STATUS_WAIT,
            'agent_two_withdraw'=>WithdrawMoneyService::WITHDRAW_ORDER_STATUS_WAIT,
            'factory_withdraw'=>WithdrawMoneyService::WITHDRAW_ORDER_STATUS_WAIT,
            'memo'=>$memo,
            'title'=>"好商品的购买~",
            'expire_time'=>time() + $this->timeout,
            'gids_nums'=>$gidsNums,
            'address_id'=>$userSelAddressId,
        );

        $newId = OrderModel::db()->add($order);


        foreach ($gidsNumsArr as $k=>$v){
            $arr = explode("-",$v);
            $gid = $arr[0];
            $num = $arr[1];

            $goods = GoodsModel::db()->getById($gid);
            $data = array(
                'pid'=>$goods['pid'],
                'gid'=>$gid,
                'num'=>$num,
                'sale_price'=>$goods['sale_price'],
                'haulage'=>$goods['haulage'],
                'oid'=>$newId,
                'a_time'=>time(),
                'uid'=>$uid,
            );
            OrderGoodsModel::db()->add($data);

            CartModel::db()->delete(" pid = {$goods['pid']} and gid = $gid and uid = $uid limit 10");
//            $data = array("stock"=>array(-1));
//            GoodsModel::db()->upById($gid,$data);
        }

        foreach ($gidsNumsArr as $k=>$v){
            $arr = explode("-",$v);
            $gid = $arr[0];
            $num = $arr[1];

            $data = array("stock"=>array(-1));
            $upGoodsStock = GoodsModel::db()->upById($gid,$data);
        }

        foreach ($pidsArr as $k=>$v){
            $data = array("user_buy_total"=>array(1));
            ProductModel::db()->upById($v,$data);
        }


        $data = array("order_num"=>array(1),'consume_total'=>array($totalPrice));
        UserModel::db()->upById($uid,$data);

        return out_pc(200,$newId);

    }
    //用户订单列表
    function getUserList($uid,$status){
        $where = " uid = $uid ";
        $order = " order by id desc";
        if($status){
            $where .= " and status = $status";
        }
        $list =  OrderModel::db()->getALL($where . $order);
        if(!$list){
            return out_pc(200,$list);
        }

        $orderList = null;
        $commentService = new CommentService();
        foreach ($list as $k=>$v){
            $orderInfo = OrderModel::db()->getById( $v['id']);
            $orderInfo['goods_total_num'] = count(explode(",",$orderInfo['gids']));


            $orderInfo['total_price'] = ProductService::formatDataPrice(2,$orderInfo,'total_price');
            $orderInfo['goods_price'] = ProductService::formatDataPrice(2,$orderInfo,'goods_price');
            $orderInfo['haulage'] = ProductService::formatDataPrice(2,$orderInfo,'haulage');

            $orderInfo['sigin_time_dt'] = 0;
            if(arrKeyIssetAndExist($orderInfo,'sigin_time')){
                $orderInfo['sigin_time_dt'] = get_default_date($orderInfo['sigin_time']);
            }
            //发货时间
            $orderInfo['ship_time_time_dt'] = 0;
            if(arrKeyIssetAndExist($orderInfo,'ship_time')){
                $orderInfo['ship_time_time_dt'] = get_default_date($orderInfo['ship_time']);
            }

            $existComment = 0;
            //已签收 或 已完成的，可以进行评论
            if($orderInfo['status'] == OrderModel::STATUS_FINISH || $orderInfo['status'] == OrderModel::STATUS_SIGN_IN){
                $existCommentRow = $commentService->getUserByOid($uid,$v['id']);
                if($existCommentRow){
                    $existComment = 1;
                }
            }

            $orderInfo['receive_address'] = $this->getUserAddress($v);

            $orderInfo['exist_comment'] = $existComment;
            $orderInfo['status_desc'] = OrderModel::STATUS_DESC[$v['status']];
            $orderInfo['goods_list'] = $this->getOneDetail($v['id'])['msg'];

            $orderList[] = $orderInfo;
        }

        return out_pc(200,$orderList);
    }
    //获取收货地址
    function getUserAddress($row){
        $default = "--";
        if(!arrKeyIssetAndExist($row,'address_id')){
            return $default ;
        }


        $addressService  =  new UserAddressService();
        $row = $addressService->getById($row['address_id'])['msg'];

        $p_c_c_t = $row['province_cn'] . "-" .  $row['city_cn']. $row['county_cn']. "-" .  $row['town_cn']."_" .$row['address']  ;

        return $p_c_c_t;
    }

    //支付完成 - 通知订单变更状态
    function finish($wx_callback_data){
        $orderNo = $wx_callback_data['out_trade_no'];
        $order = OrderModel::db()->getRow(" no = '$orderNo'");
        if(!$order){
            LogLib::inc()->debug("out_trade_no :not in db :".$orderNo);
            return out_pc(8349);
        }

        if($order['status'] != OrderModel::STATUS_WAIT_PAY){
            LogLib::inc()->debug("order status err( status = {$order['status']}) , status must be = ".OrderModel::STATUS_WAIT_PAY);
            return out_pc(6350);
        }

        $upData = array(
            'status'=>OrderModel::STATUS_PAYED,
            'pay_time'=>time(),
            'out_trade_no'=> $wx_callback_data['transaction_id'],
        );

        OrderModel::db()->upById($order['id'], $upData );


        LogLib::inc()->debug(" pay callback ,process (up order info0 ok");
        return out_pc(200);
    }
    //申请退款
    function applyRefund($oid,$uid,$type,$content,$reason,$pic,$mobile){
        if(!$oid){
            return out_pc(8981);
        }
        $orders = OrderModel::db()->getById($oid);
        if(!$orders){
            return out_pc(1029);
        }

        $exist = OrderRefundModel::db()->getRow(" oid = $oid");
        if($exist){

        }

        $allowStatus = array(
            OrderModel::STATUS_WAIT_PAY,OrderModel::STATUS_SIGN_IN,OrderModel::STATUS_TRANSPORT
        );
//        if(!in_array($orders['status'],$allowStatus)){
//            return out_pc(8353);
//        }

        if(!in_array($type,array_flip(self::REFUND_TYPE_DESC))){

        }

        if(!in_array($reason,array_flip(self::REFUND_REASON_DESC))){

        }

        $data = array(
            'type'=>$type,
            'reason'=>$reason,
            'content'=>$content,
            'pic'=>$pic,
            'status'=>self::REFUND_STATS_APPLY,
            'a_time'=>time(),
            'uid'=>$uid,
            'oid'=>$oid,
            'price'=>$orders['total_price'],
            "mobile"=>$mobile,
        );

        $orderRefundId = OrderRefundModel::db()->add($data);

        $rs = $this->upStatus($oid,OrderModel::STATUS_REFUND,array("refund_id"=>$orderRefundId));
        return out_pc(200,$orderRefundId);
    }
    //获取用户 订单申请的记录列表
    function getUserRefundList($uid){
        $list = OrderRefundModel::db()->getAll(" uid = $uid");
        if(!$list){
            return out_pc(200,$list);
        }
        foreach ($list as $k=>$v){
            $list[$k] = $this->formatRefundRow($v);
        }

        return out_pc(200,$list);
    }
    //获取一条退款记录
    function getUserRefundById($id , $uid){
        $row = OrderRefundModel::db()->getRowById($id);
        if(!$row){
            return out_pc(200,$row);
        }
        $row = $this->formatRefundRow($row);
        return out_pc(200,$row);
    }

    function formatRefundRow($row){
        $rs = $row;

        $order = OrderModel::db()->getById( $row['oid']);
        $orderPids = explode(",",$order['pids']);
        $product = ProductModel::db()->getById($orderPids[0]);

        $rs['product_title'] = $product['title'];

        $rs['type_desc'] = self::REFUND_TYPE_DESC[$row['type']];
        $rs['reason_desc'] = self::REFUND_REASON_DESC[$row['reason']];
        $rs['status_desc'] = self::REFUND_STATS[$row['status']];
        $rs['dt'] = get_default_date($row['a_time']);

        return $rs;
    }
    //真的执行 退款  到微信
    function refund($id){
        if(!$id){
            return out_pc(8981);
        }
        $orders = OrderModel::db()->getById($id);
        if(!$orders){
            return out_pc(1029);
        }

        if($orders['status'] != OrderModel::STATUS_REFUND){
            return out_pc(8356);
        }

        $payService = new PayService();
        $rs = $payService->wxPayRefund($id);
        if($rs['code'] != 200){
            return out_pc($rs['code'],$rs['msg']);
        }

        $rs = $this->upStatus($id,OrderModel::STATUS_REFUND_FINISH);
        return out_pc(200,$rs);
    }

    function upStatus($oid,$status,$upData = []){
        LogLib::inc()->debug(['up order status:',$oid,$status]);
        $data = array('status'=>$status,'u_time'=>time());
        if($status == OrderModel::STATUS_SIGN_IN){
            $data['signin_time'] = time();
        }elseif($status == OrderModel::STATUS_REFUND_FINISH   || $status == OrderModel::STATUS_REFUND_REJECT){
            $data['refund_memo'] = $upData['refund_memo'];
        }elseif($status == OrderModel::STATUS_REFUND){
            $data['refund_id'] = $upData['refund_id'];
        }
        return OrderModel::db()->upById($oid,$data);
    }
    //下单未支付，取消一个订单
    function cancel($id){
        if(!$id){
            return out_pc(8981);
        }
        $orders = OrderModel::db()->getById($id);
        if(!$orders){
            return out_pc(1029);
        }

        if($orders['status'] != OrderModel::STATUS_WAIT_PAY){
            return out_pc(8351);
        }

        $rs = $this->upStatus($id,OrderModel::STATUS_CANCEL);
        return out_pc(200,$rs);

    }
    //确认已收货
    function confirmReceive($id){
        if(!$id){
            return out_pc(8981);
        }
        $orders = OrderModel::db()->getById($id);
        if(!$orders){
            return out_pc(1029);
        }

        if($orders['status'] != OrderModel::STATUS_PAYED){
            return out_pc(8352);
        }

        $rs = $this->upStatus($id,OrderModel::STATUS_SIGN_IN);
        return out_pc(200,$rs);
    }

//    function getRowById($id){
//        return OrderModel::db()->getById($id);
//    }


    //一个订单的详情
    function getOneDetail($id){
        if(!$id){
            return out_pc(8981);
        }
        $orders = OrderModel::db()->getById($id);
        if(!$orders){
            return out_pc(1029);
        }

        if(!arrKeyIssetAndExist($orders,'gids_nums')){
            exit("$id : gids_nums is null");
        }

        $gidsNums = explode(",",$orders['gids_nums']);
        $productService = new ProductService();
        $productGoods = null;
        foreach ($gidsNums as $k=>$v){
            $arr = explode("-",$v);
            $gid = $arr[0];
            $num = $arr[1];

            $goods = GoodsModel::db()->getById($gid);
            $product = ProductModel::db()->getById($goods['pid']);
            $product = $productService->formatRow($product,$orders['uid'],0);
            $product = $productService->formatShow(array($product))[0];

            $product['price'] = ProductService::formatDataPrice(2,$goods,'sale_price');
            $product['num'] = $num;
            $product['gid'] = $gid;

            $goodsLinkPcap = GoodsLinkCategoryAttrModel::db()->getAll(" gid = $gid ");
            if(!$goodsLinkPcap){
                exit("goodsLinkPcap is null");
            }

            $pcap_desc_str = "";
            foreach ($goodsLinkPcap as $k2=>$v2){
                $attr = ProductCategoryAttrModel::db()->getById($v2['pca_id'])['name'];
                $para = ProductCategoryAttrParaModel::db()->getById($v2['pcap_id'])['name'];
                $pcap_desc_str .= $attr . ":".$para . " ";
                $goodsAttrParaDesc = array('attr'=>$attr,"part"=>$para);
            }
            $product['pcap_desc_str'] = $pcap_desc_str;
            $product['goodsAttrParaDesc'] = $goodsAttrParaDesc;
            $product['haulage'] = $goods['haulage'];
            $productGoods[] = $product;
        }

        return out_pc(200,$productGoods);
    }

    //确认订单页面，数据列表
    function confirmOrder($gidsNums = "",$uid){
        if(!$gidsNums){
            return out_pc(8982);
        }

        $productService = new ProductService();

        $gidsNums =explode(",",$gidsNums);
        $productGoods = null;
        foreach ($gidsNums as $k=>$v){
            $arr = explode("-",$v);
            $gid = $arr[0];
            $num = $arr[1];

            if(!$num){
                return out_pc(8021);
            }

            $goods = GoodsModel::db()->getById($gid);
            if(!$goods){
                return out_pc(8979);
            }

//            if(!$goods['stock'] || $goods['stock'] < 0){
//                return out_pc(8336);
//            }

            $product = ProductModel::db()->getById($goods['pid']);
            if(!$product){
                return out_pc(1026);
            }
            $product = $productService->formatRow($product,$uid,0);
            $product = $productService->formatShow(array($product))[0];
            $product['price'] = $goods['sale_price'];
            $product['price'] = ProductService::formatDataPrice(2,$product,'price');
            $product['num'] = $num;

            $goodsLinkPcap = GoodsLinkCategoryAttrModel::db()->getAll(" gid = $gid ");
            if(!$goodsLinkPcap){
                exit("goodsLinkPcap is null");
            }

            $pcap_desc_str = "";
            foreach ($goodsLinkPcap as $k2=>$v2){
                $attr = ProductCategoryAttrModel::db()->getById($v2['pca_id'])['name'];
                $para = ProductCategoryAttrParaModel::db()->getById($v2['pcap_id'])['name'];
                $pcap_desc_str .= $attr . ":".$para . " ";
                $goodsAttrParaDesc = array('attr'=>$attr,"part"=>$para);
            }
            $product['pcap_desc_str'] = $pcap_desc_str;
            $product['goodsAttrParaDesc'] = $goodsAttrParaDesc;
            $product['haulage'] = $goods['haulage'];
            $productGoods[] = $product;
        }

//        $data = array(
//            'product'=>$productService->formatShow(array($product))[0],
//            'goods'=>$goods,
//            'goodsAttrParaDesc'=>$goods['goodsAttrParaDesc'] ,
//            'pcap_desc_str'=>$goods['pcap_desc_str'] ,
//        );

        return out_pc(200,$productGoods);

    }

    function getGoodsIdByPcap($pid,$pcap,$num = 0){
        if(!$pid){
            return out_pc(8072);

        }
        $product = ProductModel::db()->getById($pid);
        if(!$product){
            return out_pc(1026);
        }

        //获取该产品下的所有商品
        $goodsDb = GoodsModel::db()->getAll(" pid = $pid");
        if(!$goodsDb){
            return out_pc(8979);
        }


        $goods = null;
        $goodsAttrParaDesc = "";
        $pcap_desc_str = "";
        //产品是否有属性参数
        if($product['category_attr_null'] == ProductModel::CATE_ATTR_NULL_FALSE){
            //每个商品下的，属性参数pcap
            $category_attr_para = null;
            foreach ($goodsDb as $k=>$v){
                $row = $v;
                //获取每个商品对应的  分类属性参数
                $row = GoodsLinkCategoryAttrModel::db()->getAll(" gid = {$v['id']}");
                if($row){
                    $category_attr_para[] = $row;
                }
            }

            if(!$category_attr_para){
                return out_pc(8341);
            }

            if(!$pcap){
                return out_pc(8342);
            }
            //检查C端传过来的商品PCAP参数是否正确
            $userPcap = explode(",",$pcap);
            foreach ($userPcap as $k=>$v){
                if(!$v){
                    return out_pc(8345);
                }

                if(strpos($v,'-') === false){
                    return out_pc(8343);

                }

                $tmp = explode("-",$v);
                if(count($tmp) != 2){
                    return out_pc(8344);
                }

                $pca_id = (int)$tmp[0];
                $pcap_id = (int)$tmp[1];
                if(!$pca_id || !$pcap_id){
                    return out_pc(8346);
                }
            }

            $goodsAttrPara = null;
            //商品
            foreach ($category_attr_para as $k=>$goodsPCAP){
                $cntGoodsPCAP = 0;
                //商品下的所有参数
                foreach ($goodsPCAP as $k2=>$onePcap){
                    $f = 0;
                    //用户提交的 商品 属性参数
                    foreach ($userPcap as $k=>$userOnePcap){
                        $tmp = explode("-",$userOnePcap);
                        $pca_id = $tmp[0];
                        $pcap_id = $tmp[1];
                        if($onePcap['pca_id'] == $pca_id && $onePcap['pcap_id'] == $pcap_id){
                            $f = 1;
                            break;
                        }
                    }
                    if(!$f){
                        //只要有一个 参数 找不见，就证明，此条商品，不满足，直接停止该层循环
                        break;
                    }
                    $cntGoodsPCAP++;
                }
                //证明上面的循环都走完了，没有提前结束
                if($cntGoodsPCAP == count($goodsPCAP)){
                    $goodsAttrPara = $goodsPCAP;
                    break;
                }

            }

            if(!$goodsAttrPara){
                return out_pc(8340);
            }

            foreach ($goodsDb as $k=>$v){
                if($v['id'] == $goodsAttrPara[0]['gid']){
                    $goods = $v;
                    break;
                }
            }

            foreach ($goodsAttrPara as $k=>$v){
                $attr = ProductCategoryAttrModel::db()->getById($v['pca_id'])['name'];
                $para = ProductCategoryAttrParaModel::db()->getById($v['pcap_id'])['name'];
                $pcap_desc_str .= $attr . ":".$para . " ";
                $goodsAttrParaDesc = array('attr'=>$attr,"part"=>$para);
            }
        }else{
            $goods = $goodsDb[0];
        }

        $goods['pcap_desc_str'] = $pcap_desc_str;
        $goods['goodsAttrParaDesc'] = $goodsAttrParaDesc;

        return out_pc(200,$goods);
    }

    //统计一个用户的所有订单的，各种状态，有多少条记录
    function getUserCntGroupByStatus($uid){
        $orderList = OrderModel::db()->getAll(" uid = {$uid} group by status" , null, " count(status) as cnt,status ");
        if(!$orderList){
            return out_ajax(200,$orderList);
        }

        $list = array(
            "wait_pay"=>0,"wait_transport"=>0,"wait_signin"=>0,"wait_comment"=>0
        );
        foreach ($orderList as $k=>$v){
            if($v['status'] == 1){//待付款
                $list['wait_pay'] = $v['cnt'];
            }elseif($v['status'] == 2){//已付款，待发货
                $list['wait_transport'] = $v['cnt'];
            }elseif($v['status'] == 5){//已发货，待签收
                $list['wait_signin'] = $v['cnt'];
            }elseif($v['status'] == 8){//完成，待评价
                $list['wait_comment'] = $v['cnt'];
            }
        }
        return out_pc(200,$list);
    }

    //某一个产品，近期购买记录
    function getNearUserBuyHistory($pid){

        $list = OrderGoodsModel::db()->getAll("pid = $pid group by uid order by a_time desc limit 20 ");
        if(!$list){
            return $list;
        }
        $usrService = new UserService();
        foreach ($list as $k=>$v){
            $uinfo = $usrService->getUinfoById($v['uid'])['msg'];
            $list[$k]['nickname'] = $uinfo['nickname'];
            $list[$k]['avatar'] = $uinfo['avatar'];
            $list[$k]['dt'] = get_default_date($v['a_time']);
        }

        return $list;
    }
}