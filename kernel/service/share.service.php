<?php

class ShareService{
    const TYPE_FRIEND = 1;
    const TYPE_ALL_FRIEND = 2;
    const TYPE_DESC = array(
        self::TYPE_FRIEND=>"指定好友/群",
        self::TYPE_ALL_FRIEND=>"朋友圈",
    );
    function add($uid,$data){
        if(!$uid){
            return out_pc(8002);
        }

        $data = array(
            'pid'=>$data['pid'],
            'source'=>$data['source'],
            'goto_page_path'=>$data['goto_page_path'],
            'uid'=>$uid,
            'a_time'=>time(),
            'agent_id'=>0,
        );

        $agentService = new AgentService();
        $agentRs = $agentService->getOneByUid($uid);
        if($agentRs['code'] == 200 && $agentRs['msg'] ){
            $data['agent_id'] =  $agentRs['msg']['id'];
        }

        $newId = ShareModel::db()->add($data);

        return out_pc(200,$newId);
    }

    function getUserCnt($uid){
        return UserLikedModel::db()->getCount(" uid = $uid");
    }

}