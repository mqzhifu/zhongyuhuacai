<?php
class IndexCtrl extends BaseCtrl  {

    function index(){
        //调取支付

        //代理
        //密码登陆
        //商品，获取二维码
        //短信登陆
        //短信找回密码
        //发送短信
        //退出

        //提现列表
        //发起提现
    }


    function wxPushLocation($request){
        $latitude = $request['latitude'];
        $longitude = $request['longitude'];


        $addr = AreaLib::getByGPS($latitude,$longitude);

        $data = array(
            'latitude'=>$latitude,'longitude'=>$longitude,'uid'=>$this->uid,'gps_parser_addr'=>$addr,'a_time'=>time(),
        );
        $newId = wxLocationModel::db()->add($data);
        out_ajax(200,[$newId,$addr]);
    }

    function getBannerList(){
        $data = BannerModel::getIndexList();
        out_ajax(200,$data);
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