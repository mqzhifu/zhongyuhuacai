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

        return out_pc(200,$this->format($list));
    }

    function format($list){
        if(!$list){
            return $list;
        }

        $userService = new UserService();
        $data = null;
        foreach ($list as $k=>$v){
            $row = $v;
            if(arrKeyIssetAndExist($v,'pic')){
                $row['pic'] = get_comment_url($v['pic']);
            }
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
        $orderDetail = $orderService->getOneDetail($oid);


        $data = array("user_comment_total"=>array(1));
        ProductModel::db()->upById($orderDetail['msg'][0]['id'],$data);



        $data = array(
            'title'=>$title,
            'content'=>$content,
            'a_time'=>time(),
            'pid'=>0,
            'uid'=>$uid,
            'pic'=>$pic,
            'star'=>$star,
            'oid'=>$oid,
        );
        $newId = UserCommentModel::db()->add($data);
        return out_pc(200,$newId);
    }


}