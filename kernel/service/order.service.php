<?php

class OrderService{
    //下单入口
    function doing($uid,$pid,$categoryAttrPara,$num,$agentUid = 0){
        if(!$uid){
            return out_pc(8002);
        }

//        if(!$gid){
//            return out_pc(8073);
//        }
//
//        $goods = GoodsModel::db()->getById($gid);
//        if(!$goods){
//            return out_pc(1027);
//        }

        $product = ProductModel::db()->getById($pid);
        if(!$product){
            return out_pc(1026);
        }


        if(!$categoryAttrPara){
            return out_pc(8977);
        }


        if($goods['stock'] - $num <= 0 ){
            return out_pc(8336);
        }


        $agentAddress = "";
        if($agentUid){
            $agent = AgentModel::db()->getById($agentUid);
            if(!$agent){
                return out_pc(1028);
            }
            $agentAddress = AgentModel::getAddrStrById($agentUid);
        }

        $totalPrice = $goods['sale_price'] + $goods['haulage'];

        $couponId = _g("coupon_id");
        $couponInfo = CouponModel::db()->getById($couponId);
        $couponPrice = 0;
        if($couponInfo){
            $couponPrice = $couponInfo['price'];
        }
        $totalPrice = $totalPrice  -  $couponPrice;

        $order = array(
            'no'=>get_order_rand_no(),
            'pid'=>$goods['pid'],
            'gid'=>$gid,
            'uid'=>$uid,
            'total_price'=>$totalPrice,
            'goods_price'=> $goods['sale_price'] ,
            'coupon_price'=>$couponPrice,
            'pay_type'=>$goods['pay_type'],
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
        );

        $newId = OrderModel::db()->add($order);

        $data = array("order_num"=>array(1),'consume_total'=>array($goods['sale_price']));
        UserModel::db()->upById($uid,$data);

        $data = array("user_buy_total"=>array(1));
        ProductModel::db()->upById($goods['pid'],$data);

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
        $data = array(
            'uid'=>$uid,
            'pid'=>$pid,
            'a_time'=>time(),
            'gid'=>0,
        );
        $newId = CartModel::db()->add($data);
        return out_pc(200,$newId);
    }

    function delUserCart($id,$uid){
        $newId = CartModel::db()->delById($id);
        return out_pc(200,$newId);
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
            $rs[] = $service->formatRow($product);
        }
        $rs = $service->formatShow($list);
        return out_pc(200,$rs);

    }
}