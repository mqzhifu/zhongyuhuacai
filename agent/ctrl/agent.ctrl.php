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
        $this->setTitle('申请成为代理');
        $this->setSubTitle('申请成为代理');

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
        $this->setSubTitle('编辑个人资料');

        $this->display("editInfo.html");
    }

    function bindUser(){
        $this->setTitle('绑定小程序用户');
        $this->setSubTitle('绑定小程序用户');

        if($this->uinfo['uid']){
            exit("您已经绑定了小程序用户，请先解绑~再来绑定关系~~~");
        }

        if(_g("opt")){
            $uid = _g("uid");
            $mobile = _g("mobile");
            $smsCode = _g("smsCode");

            $this->agentService->userBindAgent($uid,$mobile,$smsCode);

            var_dump(2134234234);exit;
        }



        $this->display("bind.user.html");
    }

    function unbindUser(){
        $this->setTitle('解绑小程序用户');
        $this->setSubTitle('解绑小程序用户');

        if(!$this->uinfo['uid']){
            exit("您并没有绑定小程序用户，请先绑定，再来解除绑定关系~~~");
        }

        if(_g("opt")){
            $rs = $this->agentService->unbind($this->uinfo['id'],$this->uinfo['uid']);
            $uinfo = $this->uinfo;
            $uinfo['uid'] = 0;
            $this->_sess->setValue("uinfo",$uinfo);
            var_dump($rs);exit;
        }


        $this->display("unbind.user.html");
    }

    function mysub(){
        $this->setTitle('我的下级');
        $this->setSubTitle('我的下级');

        $this->display("mysub.html");
    }

}