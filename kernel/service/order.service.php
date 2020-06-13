<?php

class OrderService{
    public $timeout = 30 * 60;//订单超时时间
    //下单入口
    function doing($uid,$gidsNums,$agentUid = 0,$couponId = 0,$memo = ''){
        LogLib::inc()->debug([$uid,$gidsNums,$agentUid ,$couponId ,$memo ]);

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

//            if(!$goods['stock'] || $goods['stock'] < 0){
//                return out_pc(8336);
//            }

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

        //收货地址
        $agentAddress = "";
        if($agentUid){
            $agent = AgentModel::db()->getById($agentUid);
            if(!$agent){
                return out_pc(1028);
            }
            $agentAddress = AgentModel::getAddrStrById($agentUid);
        }
        //优惠卷
        $couponPrice = 0;
        if($couponId){
            $couponInfo = CouponModel::db()->getById($couponId);
            if($couponInfo){
                $couponPrice = $couponInfo['price'];
            }
        }
        //最终价格 = 商品总价 + 运费总价 - 优惠卷价格
        $totalPrice = $goodsTotalPrice + $haulage  -  $couponPrice;

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
            'agent_uid'=>$agentUid,
            'coupon_id'=>$couponId,
            'address_agent'=>$agentAddress,
            'agent_withdraw_money_status'=>OrderModel::WITHDRAW_MONEY_STATUS_WAIT,
            'factory_withdraw_money_status'=>OrderModel::WITHDRAW_MONEY_FACTORY_WAIT,
            'memo'=>$memo,
            'title'=>"好商品的购买~",
            'expire_time'=>time() + $this->timeout,
            'gids_nums'=>$gidsNums,
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
            );
            OrderGoodsModel::db()->add($data);
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
    function getUserList($uid){
        $list =  OrderModel::getListByUid($uid);
        return out_pc(200,$list);
    }
    //支付完成 - 通知订单变更状态
    function finish($id){

    }

    function refund($id){

    }

    function getOneDetail($id){
        return OrderModel::db()->getById($id);
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

    function delUserCart($id,$uid){
        $newId = CartModel::db()->delById($id);
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
            $product['num'] = $num;

            $goodsLinkPcap = GoodsLinkCategoryAttrModel::db()->getAll(" gid = $gid ");
            if(!$goodsLinkPcap){
                exit("goodsLinkPcap is null");
            }

            $pcap_desc_str = "";
            foreach ($goodsLinkPcap as $k=>$v){
                $attr = ProductCategoryAttrModel::db()->getById($v['pca_id'])['name'];
                $para = ProductCategoryAttrParaModel::db()->getById($v['pcap_id'])['name'];
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
            $rs[] = $row;
        }

        return out_pc(200,$rs);

    }
}