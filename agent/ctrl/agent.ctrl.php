<?php
class AgentCtrl extends BaseCtrl  {
    public $request = null;
    public $agent = null;
    function __construct($request)
    {
        parent::__construct($request);

        $agent = $this->agentService->getOneByUid($this->uid);
        if(!$agent){
            out_ajax(8368);
        }

        $this->agent = $agent;
    }
    //获取一个代理，分享出去的连接，所成交的所有订单
    function getOrderList($request){
        $list = $this->agentService->getOrderListByAId($this->agent['id']);
        out_ajax(200,$list['msg']);
    }

    function withDraw(){
        $num =  get_request_one( $this->request,'num',0);
        $oids =  get_request_one( $this->request,'oids',"");
        $this->agentService->withdrawMoney($this->agent['id'],$num,$oids,$this->uid);
    }
    //申请成为一个代理
    function apply($request){
        if(_g("opt")){
            $type =  get_request_one( $this->request,'type',0);
            $invite_agent_code = get_request_one( $this->request,'invite_agent_code','');
            $data = get_request_one( $this->request,'data',[]);

            $data = array('mobile'=>"13511112222","province_code"=>110000,'city_code'=>110100,'county_code'=>'110102',"town_code"=>"110101007",'address'=>"东四十条，银楼大厦B座12层，1204",'title'=>"饰品小生活",'sex'=>1);
            $rs = $this->agentService->apply($this->uid,$type,$invite_agent_code,$data);
            var_dump($rs);exit;
        }

        $this->setTitle('申请成为代理');

        $this->display("apply.html");

    }

    function editUinfo(){
        $this->setTitle('编辑个人资料');

        $this->display("editInfo.html");
    }

    function bindUser(){
        $this->setTitle('绑定小程序用户');

        $this->display("bind.user.html");
    }

    function unbindUser(){
        $this->setTitle('解绑小程序用户');

        $this->display("unbind.user.html");
    }

    function mysub(){
        $this->setTitle('我的下级');

        $this->display("mysub.html");
    }

}