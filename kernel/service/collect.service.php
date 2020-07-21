<?php

class CollectService{
    function add($uid,$pid){
        if(!$uid){
            return out_pc(8002);
        }

        if(!$pid){
            return out_pc(8072);
        }

        if($this->exist($pid,$uid)){
            return out_pc(8338);
        }

        $data = array(
            'a_time'=>time(),
            'pid'=>$pid,
            'uid'=>$uid,
        );
        $newId = UserCollectionModel::db()->add($data);
        $data = array("user_collect_total"=>array(1));
        ProductModel::db()->upById($pid,$data);

        return out_pc(200,$newId);
    }

    function cancel($uid,$pid){
        if(!$uid){
            return out_pc(8002);
        }

        if(!$pid){
            return out_pc(8072);
        }


        if(!$this->exist($pid,$uid)){
            return out_pc(8347);
        }

        $delRs = UserCollectionModel::db()->delete(" pid = $pid and uid = $uid limit 100" );

        $data = array("user_up_total"=>array(-1));
        $rs = ProductModel::db()->upById($pid,$data);

        return out_pc(200,$delRs);
    }

    function exist($pid,$uid){
        $exist = UserCollectionModel::db()->getRow(" pid = $pid and uid = $uid");
        return $exist;
    }

    function getUserCnt($uid){
        $cnt = CartModel::db()->getCount(" uid = {$uid}");
        return $cnt;
    }

    function getUserList($uid){
        $list = UserCollectionModel::db()->getAll(" uid = $uid");
        if(!$list){
            return out_pc(200,$list);
        }

        $list = $this->formatList($list,$uid);
        return out_pc(200,$list);
    }

    function formatList($list,$uid){
        $cartService = new CartService();
        $cartList = $cartService->getUserCart($uid)['msg'];
        $rs = null;
        foreach ($list as $k=>$v){
            $row = $v;
            $product = ProductModel::db()->getById($v['id']);
            $productList = $this->productService->formatShow(array($product));
            $row['lowest_price'] = fenToYuan( $productList[0]['lowest_price'] );
            $row['title'] = $productList[0]['title'];
            $row['pic'] = $productList[0]['pic'];

//            $row['lowest_price'] = fenToYuan( $product['lowest_price']) ;
//            $row['title'] = $product['title'];
            $hasInCart = 0;
            if($cartList){
                foreach ($cartList as $k2=>$cart){
                    if($cart['id'] == $v['pid']){
                        $hasInCart = 1;
                        break;
                    }
                }
            }
            $row['has_cart'] = $hasInCart;

            $rs[] = $row;
        }

        return $rs;
    }

//    function getListByPid($pid,$page = 0,$limit = 0){
//        if(!$pid){
//            return out_pc(8002);
//        }
//
//        $list = UserCommentModel::getListByPid($pid);
//        return out_pc(200,$this->format($list));
//    }
//
//    function format($list){
//        if(!$list){
//            return $list;
//        }
//
//        $userService = new UserService();
//        $data = null;
//        foreach ($list as $k=>$v){
//            $row = $v;
//            if(arrKeyIssetAndExist($v,'pic')){
//                $row['pic'] = get_comment_url($v['pic']);
//            }
//            if(arrKeyIssetAndExist($v,'uid')){
//                $userRs =$userService->getUinfoById($v['uid']);
//                $row['nickname'] = $userRs['msg']['nickname'];
//            }
//
//            $data[] = $row;
//        }
//
//        return $data;
//    }
}