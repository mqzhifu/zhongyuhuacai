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

        return $this->out(200,"ok");
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
        $source =  get_request_one($request,'source',0);  //微信 - 用户直接分享
        $goto_page_path =  get_request_one($request,'goto_page_path',0);


        $data = array(
            'pid'=>$pid,'source'=>$source,'goto_page_path'=>$goto_page_path,
        );

        $rs = $this->shareService->add($this->uid,$data);
        out_ajax(200,$rs['msg']);
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