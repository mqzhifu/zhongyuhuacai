<?php
class AgentCtrl extends BaseCtrl  {
    public $request = null;
    public $agent = null;
    //二级代理，佣金10%，写死了
    public $sub_fee_percent = 10;
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
    //申请成为代理 上传店铺图片
    function applyAgentUploadPic(){
//        var_dump($_FILES);
//        out_ajax(200,"ok");

        $rs = $this->orderService->applyAgentUploadPic($this->uid);
        out_ajax($rs['code'],$rs['msg']);
    }

    function applyQrcode($request){
        if(arrKeyIssetAndExist($request,'aid')){
            $aid = $request['aid'];
        }else{
            $aid = $this->uinfo['id'];
        }
        $url = get_domain_url() ."agent/apply/type=2&aid=".$aid ;

        require_once PLUGIN . '/phpqrcode/qrlib.php';

        $value = $url;					//二维码内容

        $errorCorrectionLevel = 'L';	//容错级别
        $matrixPointSize = 5;			//生成图片大小

        $service = new UploadService();
        //生成二维码图片
        $filename = $service->getApplyAgentUploadPath($aid);
        QRcode::png($value,$filename , $errorCorrectionLevel, $matrixPointSize, 2);


        $original_pic_path = get_agent_apply_original_pic_path();
        $finalPic = $this->mergePic($original_pic_path,$filename);


        $url = $service->getApplyAgentUrl($aid);			//已经生成的原始二维码图片文件
        echo "<img src='$url' />";
        exit;
    }

    function mergePic($src,$qrCode){
        $srcImg = imagecreatefromjpeg($src);
        $qrCodeImg = imagecreatefrompng($qrCode);

        imagecopymerge($srcImg, $qrCodeImg, 80, 90, 0,0, imagesx($qrCodeImg), imagesy($qrCodeImg), 100);

        $merge = 'merge.png';
        var_dump(imagepng($srcImg,'./merge.png'));//bool(true)
    }

    function applyByQrcode(){

    }

    //申请成为一个代理
    function apply($request){
        $this->setTitle('申请成为代理');
        $this->setSubTitle('申请成为代理');

        $this->addJs("/agent/assets/js/area_province.js");

        $type = _g("type");
        $this->assign("type",$type);
//        $this->printAreaData();

        $aid = _g("aid");
        if(!$aid){
            exit("aid is null");
        }
        $this->assign("aid",$aid);

        $agent = AgentModel::db()->getById($aid);
        if(!$agent){
            exit("aid not in db.");
        }
        $this->assign("agent",$agent);


        if(_g("opt")){
            $address = _g('address');
            $sex = _g('sex');
            $title = _g('title');
            $real_name = _g('real_name');
            $area = _g('area');
//            $invite_agent_code = _g("invite_agent_code");
            $fee_percent = _g('fee_percent');
//            $sub_fee_percent = _g('sub_fee_percent');
            $sub_fee_percent = $this->sub_fee_percent;
            $type = _g("type");
            $mobile = _g("mobile");
            $smsCode  = _g("sms_code");
            $pic_tmp_path  = _g("pic_tmp_path");

            if(!$smsCode){
                out_ajax(8389);
            }

            $smsCode = (int)$smsCode;
            if(!$smsCode){
                out_ajax(8387);
            }

            if(strlen($smsCode) != 6){
                out_ajax(8388);
            }

            $VerifierCodeLib = new VerifierCodeLib();
            $VerifierCodeLib->authCode(VerifiercodeModel::TYPE_SMS,$mobile,$smsCode,SmsRuleModel::$_type_agent_apply);

//            $pic = _g("pic");
//            $town_code = _g("town_code");

//            if(!isset($_FILES['pic'])){
//                out_ajax(8018);
//            }
//            $picClass = new UploadService();
//            if(!isset($_FILES['pic'])){
//                $upRs  = $picClass->agent("pic");
//                if($upRs['code']!=200){
//                    out_ajax($upRs['code']);
//                }
//                $pic = $upRs['msg'];
//            }


            if(!$area){
                out_ajax(8395);
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
                "town_code"=>"",
                "mobile"=>$mobile,
                'fee_percent'=>$fee_percent,
                "sub_fee_percent"=>$sub_fee_percent,
//                'invite_agent_code'=>$invite_agent_code,
                'invite_agent_code'=>$agent['invite_code'],
                'pic'=>$pic_tmp_path,
//                "pic"=>$upRs['msg'],
            );

//            $rs = $this->agentService->apply($this->uinfo['id'],$type , $data);
            $rs = $this->agentService->apply($aid,$type , $data);
            return out_pc($rs['code'],$rs['msg']);
        }
        //二级代理佣金
        $this->assign("sub_fee_percent",$this->sub_fee_percent);
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
            $pic = _g("pic_tmp_path");

            if(!$area){
                out_ajax(8395);
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
                'pic'=>$pic,
            );

//            $picClass = new UploadService();
//            if($pic){
//                $rs = $picClass->agent("pic");
//            }


            $uinfo = $this->uinfo;
            $uinfo['real_name'] = $real_name;
            $uinfo['pic'] = $pic;
            $uinfo['title'] = $title;
            $uinfo['address'] = $address;
            $uinfo['sex'] = $sex;

            $this->_sess->setValue("uinfo",$uinfo);


            $rs = $this->agentService->editOne($this->uinfo['id'],$data);
            out_ajax($rs['code'],$rs['msg']);
        }

        $areaStr = "";
        $placeholder = "省/市/县";
        if($this->uinfo['province_code'] && $this->uinfo['city_code'] && $this->uinfo['county_code']){
            $areaStr = $this->uinfo['province_code'] .",". $this->uinfo['city_code']  .",". $this->uinfo['county_code'];
            $placeholder = $this->uinfo['province'] ."/". $this->uinfo['city']  ."/". $this->uinfo['county'];
        }

        $this->assign("placeholder",$placeholder);
        $this->assign("areaStr",$areaStr);

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

            $rs = $this->agentService->userBindAgent($uid,$this->uinfo['id'],$mobile,$smsCode);

            out_ajax($rs['code'],$rs['msg']);
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
//            $uinfo = $this->uinfo;
//            $uinfo['uid'] = 0;
//            $this->_sess->setValue("uinfo",$uinfo);
            out_ajax($rs['code'],$rs['msg']);
        }

        $user = UserModel::db()->getById($this->uinfo['uid']);

        $this->assign("user",$user);

        $this->display("unbind.user.html");
    }

    function mysub(){
        $this->setTitle('我的下级');
        $this->setSubTitle('我的下级');

        $this->display("mysub.html");
    }

}