<?php

class CartService{
//
//    const REFUND_TYPE_GOODS_PRICE = 1;
//    const REFUND_TYPE_PRICE = 2;
//
//    const REFUND_TYPE_DESC = [
//        self::REFUND_TYPE_GOODS_PRICE=>"退款退货",
//        self::REFUND_TYPE_PRICE=>"退款不退货",
//    ];

    //添加一个产品到购物车
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
//            $product = ProductModel::db()->getById($v['pid']);
//            $row = $service->formatRow($product);
//            $row = $service->formatShow(array($row))[0];
//            $row['gid'] = $v['gid'];
//            $goods = GoodsModel::db()->getById($row['gid']);
//            $row['goods_price'] = $goods['sale_price'];

            $row = $service->getOneDetail($v['pid'] , 0 , $uid , 0 );
            $row = $service->formatShowRow($row['msg']);
            $row['cart_id'] = $v['id'];
            $row['gid'] = $v['gid'];
            $rs[] = $row;
        }

        return out_pc(200,$rs);

    }

}