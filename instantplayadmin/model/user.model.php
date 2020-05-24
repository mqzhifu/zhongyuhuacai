<?php
class UserModel {
    static $_table = 'user';
    static $_pk = 'id';
    static $_db = null;
    static $_db_key = "instantplay";

    //注册类型
    static $_type_cellphone = 1;
    static $_type_email = 2;
    static $_type_name = 3;

    static $_type_wechat = 4;
    static $_type_weibo = 5;
    static $_type_facebook = 6;
    static $_type_google = 7;
    static $_type_twitter = 8;
    static $_type_qq = 9;

    static $_type_guest = 10;

//    static $_type_cellphone_ps = 11;
//    static $_type_pc_cellphone_ps = 12;
//    static $_type_pc_cellphone_sms = 13;

    static function getTypeDesc(){
        return array(
            self::$_type_cellphone =>'手机号',
            self::$_type_email =>'邮箱',
            self::$_type_name =>'用户名',

            self::$_type_wechat =>'微信',
            self::$_type_weibo =>'微博',
            self::$_type_facebook =>'脸书',
            self::$_type_google =>'谷歌',
            self::$_type_twitter =>'推特',
            self::$_type_qq =>'QQ',
            self::$_type_guest =>'游客',
//            self::$_type_cellphone_ps =>'手机密码登陆',
//            self::$_type_pc_cellphone_ps =>'pc端手机密码登陆',
//            self::$_type_pc_cellphone_sms =>'pc端手机验证码登陆',
//            self::$_type_sdk_share_app =>'SDK内分享',
        );
    }

    static $_type_cate_third = 1;
    static $_type_cate_self = 2;
    static $_type_cate_guest = 3;


    static  function getTypeCateDesc(){
        return $_type_cate_desc = [
            self::$_type_cate_guest => '游客',
            self::$_type_cate_third => '3方平台',
            self::$_type_cate_self => '自己平台登陆',
        ];
    }

    static  function getTypeCateSelfDesc(){
        return $_type_cate_desc = [
            self::$_type_wechat ,
            self::$_type_weibo ,
            self::$_type_facebook ,
            self::$_type_google ,
            self::$_type_twitter ,
            self::$_type_qq ,
        ];
    }

    static  function getTypeGuestDesc(){
        return $_type_cate_desc = [
            self::$_type_guest ,
        ];
    }

    static  function getTypeCateThirdDesc(){
        return $_type_cate_desc = [
            self::$_type_cellphone ,
            self::$_type_email ,
            self::$_type_name ,
        ];
    }


    const LOGIN_TYPE_THIRD = 1;
    const LOGIN_TYPE_NAME_PS = 2;
    const LOGIN_TYPE_MOBILE_SMS = 3;

    const LOGIN_TYPE_DESC = [
        self::LOGIN_TYPE_THIRD=>'3方平台登陆',
        self::LOGIN_TYPE_NAME_PS=>'用户名密码登陆',
        self::LOGIN_TYPE_MOBILE_SMS=>'手机验证码登陆',
    ];




    static $_online_true = 1;
    static $_online_false = 2;

    static $_sex_male = 1;//男
    static $_sex_female = 2;//女

    const STATUS_NORMAL = 1;
    const STATUS_DISABLE = 2;

    const STATUS_DESC = [
        self::STATUS_NORMAL=>'正常',
        self::STATUS_DISABLE=>'已禁用',
    ];

    const INNER_TYPE_HUMAN = 1;
    const INNER_TYPE_ROBOT = 2;
    const INNER_TYPE_DESC = [
        self::INNER_TYPE_HUMAN =>'正常用户',
        self::INNER_TYPE_ROBOT =>'机器人',
    ];

    static function getTypeOptions(){
        $html = "";
        foreach (self::getTypeDesc() as $k=>$v) {
            $html .= "<option value={$k}>{$v}</option>";
        }
        return $html;
    }

    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = new DbLib(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    public static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }


    static function getOnlineDesc(){
        return array(self::$_online_true=>'在线',self::$_online_false=>'离线');
    }

    static function getSexDesc(){
        return array(self::$_sex_male=>'男',self::$_sex_female=>'女');
    }

    static function getSexOptions(){
        $html = "";
        foreach (self::getSexDesc() as $k=>$v) {
            $html .= "<option value={$k}>{$v}</option>";
        }
        return $html;
    }

    static function keyInSex($key){
        return in_array($key,array_flip(self::getSexDesc()));
    }
    static function keyInRegType($key){
        return in_array($key,array_flip(self::getTypeDesc()));
    }

    static function keyInOnline($key){
        return in_array($key,array_flip(self::getOnlineDesc()));
    }


    static function getTypeDescByKey($key){
        if(!self::keyInRegType($key)){
            return "未知";
        }
        $arr = self::getTypeDesc();
        return $arr[$key];
    }

    static function getSexDescByKey($key){
        if(!self::keyInSex($key)){
            return "未知";
        }
        $arr = self::getSexDesc();
        return $arr[$key];
    }

    static function getOnlineDescByKey($key){
        if(!self::keyInOnline($key)){
            return "未知";
        }
        $arr = self::getOnlineDesc();
        return $arr[$key];
    }


    static function login($uname,$ps){
        return self::db()->getRow(" uname = '$uname' and ps = '$ps'");
    }
    //根据GPS ，获取 KM 附近的人
    static function getNearUserByMysql($lat,$lon,$km = 1){
        $m = $km * 1000;
        $field = mysql_gps_distance_field($lat,$lon,'lat','lon');
        $sql = "select uid,lat,log,$field as distance from ".self::$_table." where distance < $m order by distance asc ";
        return self::db()->getAllBySQL($sql);
    }
    //获取附近的人，GEO-HASH
    static function getNearUserByGeoHash($userGeoCode,$n = 6){
        $likeGeohash = substr($userGeoCode, 0, $n);
        $sql = 'select uid, from '.self::$_table.' where geo_code like "'.$likeGeohash.'%"';
        return self::db()->getAllBySQL($sql);
    }

//    static function upTotal($uid,$orderPrice){
//        $data = array('consume_total'=>array($orderPrice) ,'order_num'=>array(1));
//        return self::db()->upById($uid,$data);
//    }
    //获取一个用户的 所在地信息
    static function getLivePlaceDesc($uid,$default = '--'){
        $user = self::db()->getById($uid);
        if(!$user){
            return $default;
        }

        return self::getPlace($user,$default);
    }

    static function getPlace($data,$default){


        if(arrKeyIssetAndExist($data,'province_code')){
            $province = AreaProvinceModel::getNameByCode($data['province_code']);
            $rs = $province;
        }else{
            $rs = $default;
        }

        if(arrKeyIssetAndExist($data,'city_code')){
            $city = AreaCityModel::getNameByCode($data['city_code']);
            $rs .= $city;
        }else{
            $rs .= $default;
        }

        if(arrKeyIssetAndExist($data,'county_code')){
            $county = AreaCountyModel::getNameByCode($data['county_code']);
            $rs .= $county;
        }else{
            $rs .= $default;
        }

        if(arrKeyIssetAndExist($data,'town_code')){
            $town = AreaTownModel::getNameByCode($data['town_code']);
            $rs .= $town;
        }else{
            $rs .= $default;
        }

        return $rs;
    }
    //
    static function getAgentLivePlaceDesc($uid,$default = '--'){
        $user = AgentModel::db()->getById($uid);
        if(!$user){
            return $default;
        }

        return self::getPlace($user,$default);
    }
}