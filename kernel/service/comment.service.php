<?php

class CommentService{
    function getListByPid($pid,$page = 0,$limit = 0){
        if(!$pid){
            return out_pc(8072);
        }

        $list = UserCommentModel::getListByPid($pid);
        if(!$list){
            return out_pc(200,null);
        }

        $list = $this->format($list);

        return out_pc(200,$list);
    }

    function format($list){
        if(!$list){
            return $list;
        }

        $userService = new UserService();
        $data = null;
        foreach ($list as $k=>$v){
            $row = $v;
            $picsUrl = "";
            if(arrKeyIssetAndExist($v,'pic')){
                $pics = explode(",",$v['pic']);
                $picsUrl = [];
                foreach ($pics as $k2=>$v2){
                    $picsUrl[] = get_comment_url($v2);
                }
            }

            if(arrKeyIssetAndExist($v,'video')){
                $picsUrl[]  = get_comment_url($v['video']);
            }

            $row['pic'] = $picsUrl;

            if(arrKeyIssetAndExist($v,'uid')){
                $userRs =$userService->getUinfoById($v['uid']);
                $row['nickname'] = $userRs['msg']['nickname'];
                $row['avatar'] = $userRs['msg']['avatar'];
            }else{
                $row['nickname']  ="";
                $row['avatar'] = get_avatar_url("");
            }

            $data[] = $row;
        }

        return $data;
    }

    function add($uid,$oid,$title,$content = "",$pic = "",$star){
        if(!$uid){
            return out_pc(8002);
        }

        if(!$oid){
            return out_pc(8981);
        }

        if(!$title){
            return out_pc(8975);
        }

        $orderService = new OrderService();
        $orderDetail = $orderService->getRowById($oid);
        $pids = explode(",",$orderDetail['pids']);

//        $existComment = UserCommentModel::db()->getRow(" oid = $oid and uid = $uid");
//        if($existComment){
//            return out_pc(8384);
//        }

        $newIds = null;
        foreach ($pids as $k=>$v){
            $data = array("user_comment_total"=>array(1));
            ProductModel::db()->upById($v,$data);

            $data = array(
                'title'=>$title,
                'content'=>$content,
                'a_time'=>time(),
                'pid'=>$v,
                'uid'=>$uid,
                'pic'=>$pic,
                'star'=>$star,
                'oid'=>$oid,
            );
            $newId = UserCommentModel::db()->add($data);
            $newIds[] = $newId;
        }
        return out_pc(200,$newIds);
    }

    function getRowById($id){
        return UserCommentModel::db()->getById($id);
    }


}