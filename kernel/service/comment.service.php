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

    function add($uid,$pid,$title,$content = "",$pic = "",$star){
        if(!$uid){
            return out_pc(8002);
        }

        if(!$pid){
            return out_pc(8072);
        }

        if(!$title){
            return out_pc(8975);
        }

        $data = array(
            'title'=>$title,
            'content'=>$content,
            'a_time'=>time(),
            'pid'=>$pid,
            'uid'=>$uid,
            'pic'=>$pic,
            'star'=>$star,
        );
        $newId = UserCommentModel::db()->add($data);

        $data = array("user_comment_total"=>array(1));
        ProductModel::db()->upById($pid,$data);
        return out_pc(200,$newId);
    }


}