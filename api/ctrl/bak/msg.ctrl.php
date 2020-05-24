<?php
class MsgCtrl extends BaseCtrl   {

    function getList(){
        $listRs = $this->msgService->getList($this->uid,1,1);
        if($listRs['code'] != 200){
            return $listRs;
        }
        $rs = array('list'=>[],'recommendUser'=>[]);

        $list = $listRs['msg'];
        if(!$list ){
            $recommend = $this->fansService->recommendList($this->uid,5);
            $rs['recommendUser'] = $recommend;
        }elseif( count($list) < 5) {
            $recommend = $this->fansService->recommendList($this->uid, 5 - count($list));
            $rs['recommendUser'] = $recommend;
        }

        if($list){
            foreach($list as $k=>$v){
                $list[$k]['format_time'] = time_format($v['a_time']);
            }
        }

        $rs['list'] = $list;


        return $this->out(200,$rs);

    }
    //获取一个用户的  未读的 总消息数
    function getUnread(){
        $cnt = $this->msgService->getUserUnreadNum($this->uid);
        $this->out(200,$cnt);
    }

}