<?php

class AgentService{

    const TYPE_ONE = 1;
    const TYPE_TWO = 2;
    const TYPE_DESC = array(
        self::TYPE_ONE =>"一级代理",
        self::TYPE_ONE =>"二级代理",
    );

    function apply($uid,$type,$invite_agent_code,$data){
        if(!$uid){
            return out_pc(8002);
        }

        if(!in_array($type,self::TYPE_DESC) ){

        }

        $addressService = new UserAddressService();
        $addressService->checkArea($data);

        $addData = array(
            'province'=>$data['province'],
            'city'=>$data['city'],
            'county'=>$data['county'],
            'town'=>$data['town'],
            'mobile'=>$data['mobile'],
            'title'=>$data['title'],
            'villages'=>$data['address'],
            'uid'=>$uid,

            'id_card_num'=>"",
            'real_name'=>"",
            'type'=>$type,
            'status'=>AgentModel::STATUS_AUDITING,
            'sex'=>"",
            'a_time'=>time(),
            'pic'=>"",

        );

        if($type == AgentService::TYPE_ONE){

        }else{
            if(!$invite_agent_code){

            }

            $agent = AgentModel::db()->getRow(" invite_code = '$invite_agent_code' ");
            if(!$agent){
                $addData['invite_agent_uid'] = $agent['id'];
            }
        }

        $newId = AgentModel::db()->add($addData);
        return out_pc(200,$newId);
    }


}