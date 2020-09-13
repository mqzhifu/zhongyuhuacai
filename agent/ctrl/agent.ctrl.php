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

        $this->addJs("/agent/assets/js/area_province.js");

//        $this->printAreaData();

        if(_g("opt")){
            $address = _g('address');
            $sex = _g('sex');
            $title = _g('title');
            $real_name = _g('real_name');
            $area = _g('area');
            $invite_agent_code = _g("invite_agent_code");
            $fee_percent = _g('fee_percent');
            $sub_fee_percent = _g('sub_fee_percent');

            $mobile = _g("mobile");
            $smsCode  = _g("sms_code");
//            $pic = _g("pic");
//            $town_code = _g("town_code");

            if(!$mobile){
                exit("mobile is null");
            }

            $type = _g("type");
            if(!$type){
                exit("type is null");
            }

            if(!$address){
                exit("address is null");
            }

            if(!$sex){
                exit("sex is null");
            }

            if(!$title){
                exit("title is null");
            }

            if(!$real_name){
                exit("real_name is null");
            }

            if(!$area){
                exit("area is null");
            }


            $area = explode(",",$area);

            $province = $area[0];
            $city = $area[1];
            $county = $area[2];

            $data = array(
                "address"=>$address,
                "sex"=>$sex,
                "title"=>$title,
                "real_name"=>$real_name,
                "province_code"=>$province,
                "city_code"=>$city,
                "county_code"=>$county,
                "mobile"=>$mobile,
                'fee_percent'=>$fee_percent,
                "sub_fee_percent"=>$sub_fee_percent,
            );

//            $picClass = new UploadService();
//            if($pic){
//                $rs = $picClass->agent("pic");
//            }

            $rs = $this->agentService->apply($this->uid,$type,$invite_agent_code,$data);
            var_dump($rs);exit;
        }

        $this->setTitle('申请成为代理');

        $this->display("apply.html");

    }

    function printAreaData(){
        set_time_limit(0);

        $provinceDB = AreaProvinceModel::db()->getAll(" 1= 1 ",null,'  code,short_name ');
        $data = array();
        foreach ($provinceDB as $k=>$v){
            echo $v['short_name']."<br/>";

            $row = array("id"=>$v['code'],'name'=>$v['short_name'],'child'=>null);
            $cityDB =  AreaCityModel::db()->getAll(" province_code = {$v['code']} ",null,'  code,short_name ');
            $cityArr = null;
            foreach ($cityDB as $k2=>$v2){
                $cityRow = array("id"=>$v2['code'],'name'=>$v2['short_name'],'child'=>null);
                $countyDB =  AreaCountyModel::db()->getAll(" city_code = {$v2['code']} ",null,'  code,short_name ');
                $countyArr = null;
                foreach ($countyDB as $k3=>$v3){
                    $countyRow  = array("id"=>$v3['code'],'name'=>$v3['short_name']);
                    $countyArr[] = $countyRow;
                }
                //======================处理4级=====上面3行注释直接打开就可以用了========
//                foreach ($countyDB as $k3=>$v3){
//                    $countyRow  = array("id"=>$v3['code'],'name'=>$v3['short_name'],'child'=>null);
//                    $townDB =  AreaTownModel::db()->getAll(" county_code = {$v3['code']} ",null,'  code,short_name ');
//                    $townArr = null;
//                    foreach ($townDB as $k4=>$v4){
//                        $townRow  = array("id"=>$v4['code'],'name'=>$v4['short_name']);
//                        $townArr[] = $townRow;
//                    }
//                    $countyRow['child'] = $townArr;
//                    $countyArr[] = $countyRow;
//                }
                //======================处理4级========end
                $cityRow['child'] = $countyArr;
                $cityArr[] = $cityRow;
            }

            $row['child'] = $cityArr;
            $data[] = $row;

//            var_dump($data);exit;
        }

        echo json_encode($data);exit;
    }

    function editUinfo(){

        $this->addJs("/agent/assets/js/area_province.js");

//        $this->printAreaData();

        $this->setTitle('编辑个人资料');
        $this->setSubTitle('编辑个人资料');


        if(_g("opt")){
            $address = _g('address');
            $sex = _g('sex');
            $title = _g('title');
            $real_name = _g('real_name');
            $area = _g('area');
            $pic = _g("pic");
            if(!$address){
                exit("address is null");
            }

            if(!$sex){
                exit("sex is null");
            }

            if(!$title){
                exit("title is null");
            }

            if(!$real_name){
                exit("real_name is null");
            }

            if(!$area){
                exit("area is null");
            }


            $area = explode(",",$area);

            $province = $area[0];
            $city = $area[1];
            $county = $area[2];

            $data = array(
                "address"=>$address,
                 "sex"=>$sex,
                 "title"=>$title,
                 "real_name"=>$real_name,
                 "province_code"=>$province,
                "city_code"=>$city,
                "county_code"=>$county,
            );

            $picClass = new UploadService();
            if($pic){
                $rs = $picClass->agent("pic");
//                var_dump($rs);exit;
            }

            $this->agentService->editOne($this->uinfo['id'],$data);
            exit("ok");
        }

        $this->assign("info",$this->uinfo);
        $this->assign("uinfo",json_encode($this->uinfo));

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