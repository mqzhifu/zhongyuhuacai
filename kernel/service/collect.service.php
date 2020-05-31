<?php

class CollectService{
    function add($uid,$pid){
        if(!$uid){
            return out_pc(8002);
        }

        if(!$pid){
            return out_pc(8072);
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