<?php
class IndexCtrl extends BaseCtrl  {

    function index(){
//        return "ok";

//        $data = array(
//            'uid'=>1,
//            'pid'=>1,
//            'gid'=>1,
//            'payList'=>array(
//                array('status'=>1,'time'=>2,'price'=>"4444"),
//                array('status'=>3,'time'=>4,'price'=>"4444"),
//                array('status'=>5,'time'=>6,'price'=>"4444"),
//            )
//        );

//        $this->checkDataAndFormat($data);

        out_ajax(200,"ok");
//        return $this->out(200,"ok");
    }

    //根据用户输入的一段字符串，转换成相关的地址
    function parserAddressByStr($request)
    {
        $str =  get_request_one( $this->request,'address_str','');
        $rs = $this->userAddressService->parserAddressByStr($str);
        return $this->out($rs['code'],$rs['msg']);
    }
    function shareProduct($request){
//        $pid = $request['pid'];
//        $source = $request['source'];//微信 - 用户直接分享
//
//        $data = array(
//            'uid'=>$this->uid,
//            'pid'=>$pid,
//            'a_time'=>time(),
//            'source'=>$source,
//            'agent_id'=>0,
//        );
//
//        $agent = $this->agentService->getOneByUid($this->uid);
//        if($agent){
//            $data['agent_id'] = $agent['id'];
//        }
//
//        $newId = ShareProductModel::db()->add($data);
//        return $this->out(200,$newId);
    }

    function share($request){
        $pid = get_request_one($request,'pid',0);
        $source =  get_request_one($request,'source',"");  //微信 - 用户直接分享
        $goto_page_path =  get_request_one($request,'goto_page_path',"");


        $agentId = 0;
        if(arrKeyIssetAndExist($this->uinfo,'agent')){
            $agentId = $this->uinfo['agent']['id'];
        }

        $data = array(
            'pid'=>$pid,'source'=>$source,'goto_page_path'=>$goto_page_path,'agent_id'=>$agentId,'type'=>ShareService::TYPE_FRIEND,
        );

        $rs = $this->shareService->add($this->uid,$data);
        out_ajax(200,$rs['msg']);
    }
    //分享的时候，合成图，要把头像合进去，按说正常
    //但是，有些头像是直接从微信端获取的URL，该URL的域名，必须得配置到小程序后台，不可能把微信头像的域名配置到我们自己的后台
    //所以，得转换一下
    function getUserWxAvatarBinary(){
        if(!$this->uinfo['avatar_ori']){
            out_ajax(200,204);
        }
        $avatar_ori = $this->uinfo['avatar_ori'];
        //判断 ，是从微信端 直接 获取的URL，还是用户自己上传的图片
        if(substr($avatar_ori,0,"4") != "http"){
            out_ajax(200,205);
        }
        $avatar_url = $this->uinfo['avatar'];
        $avatar_content = file_get_contents($avatar_url);

        echo $avatar_content;
        exit;
    }

    function pageView($request){
        $uid = $this->uinfo['id'];
        $a_time = time();
        $page  = get_request_one( $request,'page',"");
        $entry_type = get_request_one( $request,'entry_type',0);
        $source = get_request_one( $request,'source',"");
        $share_uid = get_request_one( $request,'share_uid',0);


        $data = array(
            'uid'=>$uid,
            'a_time'=>$a_time,
            'page'=>$page,
            'entry_type'=>$entry_type,
            'source'=>$source,
            'share_uid'=>$share_uid,
        );

        $bindInfo = "none";
        if($share_uid){
            $bindRs = $this->agentService->userBindMasterAgent($this->uid,$share_uid);
            $bindInfo = json_encode($bindRs);
        }

        $newId = PageViewModel::db()->add($data);
        $return = array("page_view_new_id"=>$newId,'bind_info'=>$bindInfo);
        out_ajax(200,$return);
    }

    function checkToken(){
        return $this->out(200,"ok");
    }
    //微信获取用户GPS推给小程序，再推到后端
    function wxPushLocation($request){
        $latitude =  get_request_one( $this->request,'latitude','');
        $longitude =  get_request_one( $this->request,'longitude','');

        if(!$latitude){
            return $this->out(8111);
        }

        if(!$longitude){
            return $this->out(8112);
        }

        $addr = AreaLib::getByGPS($latitude,$longitude);

        $data = array(
            'latitude'=>$latitude,'longitude'=>$longitude,'uid'=>$this->uid,'gps_parser_addr'=>$addr,'a_time'=>time(),
        );
        $newId = wxLocationModel::db()->add($data);
        return $this->out(200,$newId);
    }
    //首页轮播图
    function getBannerList(){
        $data = BannerModel::getIndexList();
        return $this->out(200,$data);
    }

//    function getAppVersionInfo($versionCode = 0){
//
//        if($versionCode){
//            $appInfo = AppVersionModel::db()->getRow(" version_code =  $versionCode ");
//            if(!$appInfo){
//                return $this->out(1023);
//            }
//        }else{
//            $appInfo = AppVersionModel::db()->getRow(" 1 order by version_code desc limit 1");
//        }
//        return $this->out(200,$appInfo);
//    }
//
//    function cntLog($category,$type,$memo){
//        if (!in_array($category, CntActionLogModel::getCategories())) {
//            return $this->out(8311,$GLOBALS['code'][8311]);
//        }
//
//        if (!in_array($type, CntActionLogModel::getTypes())) {
//            return $this->out(8312,$GLOBALS['code'][8312]);
//        }
//        $data = array(
//            'category'=>$category,
//            'type'=>$type,
//            'memo'=>$memo,
//            'a_time'=>time(),
//            'uid'=>$this->uid,
//        );
//
//        $rs = CntActionLogModel::db()->add($data);
//        return $this->out(200,$rs);
//    }
//
//
//    //获取APP_UI配置（3级）;
//    public function getUiShowConfig(){
//        $root = AppUiConfigModel::db()->getAll(" pid = 0 ");
//        foreach ($root as $k=>$v) {
//            $sub = AppUiConfigModel::db()->getAll( " pid = {$v['id']} ");
//            foreach ($sub as $k2=>$v2) {
//                $three = AppUiConfigModel::db()->getAll( " pid = {$v2['id']} ");
//                $sub[$k2]['sub'] = $three;
//                if(empty($sub[$k2]['sub'])){
//                    unset($sub[$k2]['sub']);
//                }
//                foreach ($three as $k3=>$v3){
//                    if(arrKeyIssetAndExist($v3,'dir_name')){
//                        $four = AppUiConfigModel::db()->getAll( " pid = {$v3['id']} ");
//                        $sub[$k2][$k3]['sub'] = $four;
//                    }
//                }
//            }
//            $root[$k]['sub'] = $sub;
//        }
//        return $this->out(200, $root);
//    }


}