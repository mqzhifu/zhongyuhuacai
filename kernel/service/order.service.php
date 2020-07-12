<?php

class OrderService{
    public $timeout = 30 * 60;//订单超时时间
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
        foreach ($gidsNumsArr as $k=>$v){
            $arr = explode("-",$v);
            $gid = $arr[0];
            $num = $arr[1];

            if(!$num){
                return out_pc(8021);
            }

            $goods = GoodsModel::db()->getById($gid);
            if(!$goods){
                return out_pc(1027);
            }

            if(!$goods['stock'] || $goods['stock'] < 0){
                return out_pc(8336,"库存不足：gid $gid");
            }

            $product = ProductModel::db()->getById($goods['pid']);
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


//        $goods = GoodsModel::db()->getById($gid);
//        if(!$goods){
//            return out_pc(1027);
//        }

//        $product = ProductModel::db()->getById($pid);
//        if(!$product){
//            return out_pc(1026);
//        }


//        if(!$categoryAttrPara){
//            return out_pc(8977);
//        }

//        if($goods['stock'] - $num <= 0 ){
//            return out_pc(8336);
//        }

        $userService = new UserService();
        $agentService = new AgentService();
        $addressService = new UserAddressService();

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

            $orderInfo['status_desc'] = OrderModel::STATUS_DESC[$v['status']];
            $orderInfo['goods_list'] = $this->getOneDetail($v['id'])['msg'];

            $orderList[] = $orderInfo;
        }

        return out_pc(200,$orderList);
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

    function applyRefund($id){
        if(!$id){
            return out_pc(8981);
        }
        $orders = OrderModel::db()->getById($id);
        if(!$orders){
            return out_pc(1029);
        }

        $allowStatus = array(
            OrderModel::STATUS_WAIT_PAY,OrderModel::STATUS_SIGN_IN,OrderModel::STATUS_TRANSPORT
        );
        if(!in_array($orders['status'],$allowStatus)){
            return out_pc(8353);
        }

        $rs = $this->upStatus($id,OrderModel::STATUS_REFUND);
        return out_pc(200,$rs);


    }

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
        }
        return OrderModel::db()->upById($oid,$data);
    }

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

    function addUserCart($uid,$pid){
        $exist = CartModel::db()->getRow(" uid = $uid and pid = $pid");
        if($exist){
            return out_pc(8339);
        }

        $goods = GoodsModel::db()->getRow(" pid = $pid order by sale_price asc ");
        if(!$goods){
            return out_pc(8979);
        }
        $data = array(
            'uid'=>$uid,
            'pid'=>$pid,
            'a_time'=>time(),
            'gid'=>$goods['id'],
        );
        $newId = CartModel::db()->add($data);
        return out_pc(200,$newId);
    }

    function delUserCart($ids,$uid){
        $newId = CartModel::db()->delete(" id in ($ids) and uid = $uid limit 100");
        return out_pc(200,$newId);
    }

    function confirmOrder($gidsNums = ""){
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


    function getUserCartNum($uid){
        $list = CartModel::db()->getCount(" uid = $uid");
        return out_pc(200,$list);
    }

    function getUserCart($uid){
        $service  =  new ProductService();
        $list = CartModel::db()->getAll(" uid = $uid");

        if(!$list){
            return out_pc(200);
        }
        $rs = null;
        foreach ($list as $k=>$v){
            $product = ProductModel::db()->getById($v['pid']);
            $row = $service->formatRow($product);
            $row = $service->formatShow(array($row))[0];
            $row['gid'] = $v['gid'];
            $goods = GoodsModel::db()->getById($row['gid']);
            $row['goods_price'] = $goods['sale_price'];
            $row['cart_id'] = $v['id'];
            $rs[] = $row;
        }

        return out_pc(200,$rs);

    }
}