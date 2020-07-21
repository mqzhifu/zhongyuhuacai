<?php

class UpService{
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
        $newId = UserLikedModel::db()->add($data);

        $data = array("user_up_total"=>array(1));
        $rs = ProductModel::db()->upById($pid,$data);

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

        $delRs = UserLikedModel::db()->delete(" pid = $pid and uid = $uid limit 100" );

        $data = array("user_up_total"=>array(-1));
        $rs = ProductModel::db()->upById($pid,$data);

        return out_pc(200,$delRs);
    }


    function exist($pid,$uid){
        $exist = UserLikedModel::db()->getRow(" pid = $pid and uid = $uid");
        return $exist;
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

    function getUserCnt($uid){
        return UserLikedModel::db()->getCount(" uid = $uid");
    }

}