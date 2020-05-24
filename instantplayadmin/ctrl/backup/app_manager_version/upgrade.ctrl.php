<?php
/**
 * Created by PhpStorm.
 * User: XiaHB
 * Date: 2019/3/17
 * Time: 13:06
 */

/**
 * Class upgradeCtrl
 */
class upgradeCtrl extends BaseCtrl{
    /**
     * APP管理->版本升级管理列表页;
     * 获取最新的一条ID信息;
     */
    public function index(){
        // 可能用到的静态文件;
        $this->addCss("/assets/open/css/massage-set-create.css");
        //$this->addCss("/assets/open/css/wickedpicker.min.css");
        $this->addJs("/assets/open/scripts/jquery.form.min.js");
        $this->addJs("/assets/open/scripts/wickedpicker.min.js");
        $appVersionModel = new AppVersionModel();
        // 获取当前最新的版本信息;
        $result = $appVersionModel->getOneRow();
        $baseUrl = $this->getStaticBaseUrl();
        $result['idcard_img'] = $baseUrl.'/android/android_log.jpg';
        $this->assign("result", $result);
        $this->display("app_manager_version/upgrade/index.html");
    }

    /**
     * APP管理->版本升级管理列表页调用;
     * 更新版本信息;
     */
    public function updateVersionInfo(){
        $v1 = _g("v1");
        $v2 = _g("v2");
        $v3 = _g("v3");
        $summary = trim(_g("summary"));
        $force = _g("force");
        $version_code = (_g("version_code"));
        $api_version = (_g("api_version"));
        $version_name = "$v1.$v2.$v3";
        $appVersionModel = new AppVersionModel();
        // 更新当前最新的版本信息;
        $data = [];
        $data['api_version'] = (!empty($api_version)?$api_version:0);
        $data['summary'] = $summary;
        $data['app_force'] = $force;
        $data['version_code'] = $version_code;
        $data['version_name'] = $version_name;
        $result = $appVersionModel->addInfo($data);
        if(isset($result) && !empty($result)){
            echo json_encode(200);exit();
        }else{
            echo json_encode(201);exit();
        }
    }

    /**
     *
     */
//    public function updateFile(){
//        // 获取文件信息;
//        $arr = $_FILES["file"];
//        // 加限制条件;
//        // 1.文件类型;
//        // 2.文件大小（需要落表）;
//        // 3.保存的文件名不重复;
//        if($arr["size"]<10241000 ) {
//            //临时文件的路径
//            $arr['size'] = round($arr['size']/10241000).'MB';
//            //$filename = iconv("UTF-8","gb2312", $arr["tmp_name"]);
//            $date = date("Ymd/");
//            $path = STATIC_RES."app_install_package/android/$date";
//            if(!file_exists($path)){
//                mkdir($path, 0777, true);
//            }
//            $uploadname = time().mt_rand(100,999).".".'apk';
//            $rs = move_uploaded_file($arr['tmp_name'],$path.$uploadname);
//            if(true === $rs){
//                echo json_encode(0);exit();
//            }
//        }else {
//            echo json_encode(1);exit();
//        }
//    }
}