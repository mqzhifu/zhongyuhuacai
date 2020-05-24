<?php
//用户安全  ，如  绑定手机 等
class UsersafeLib{
    //忘记密码，1手机号找回，2邮箱找回
    private $forgetpsType = array(1=>'手机',2=>'邮箱');

    function forgetPs($addr,$type){
        if(!$type){
            return out_pc(8004);
        }

//        if(!$this->keyInType($type)){
//            return out_pc(8103);
//        }

        if($type == 1){
            $user = UserModel::db()->getRow(" cellphone = '$addr'");
        }elseif($type == 2){
            $user = UserModel::db()->getRow(" email = '$addr'");
        }

        if(!$user){
            return out_pc(1007);
        }


        $upCode = md5(time().rand(100000,99999));
        $key =RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['upPScode']['key'],$upCode);
        RedisPHPLib::set($key,$user['id'],$GLOBALS['rediskey']['upPScode']['expire']);

        return $upCode;
    }

    function bindEmail(){

    }

    function bindPhone($phone,$uid){
        if(!$phone){
            return 1;
        }

        if(!FilterLib::regex($phone,'phone')){
            return 1;
        }

        $user = new UserLib();
        $rs = $user->mobileUniq($phone);
        if($rs){
            return -1;
        }
    }



    function resetPS($ps,$confimPS,$code){
        if(!$code){
            out_ajax(8014);
        }

        if(!$ps){
            out_ajax(8010);
        }

        if(!$confimPS){
            out_ajax(8010);
        }

        if(!FilterLib::regex($ps,'md5')){
            out_ajax(8102);
        }

        if(!FilterLib::regex($confimPS,'md5')){
            out_ajax(8102);
        }

        if($ps != $confimPS){
            out_ajax(8108);
        }


        $key =RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['upPScode']['key'],$code);
        $uid = RedisPHPLib::get($key);
        if(!$uid){
            out_ajax(8110);
        }


        $data = array('ps'=>$ps,'u_time'=>time());
        $rs = UserModel::upById($data,$uid);


        $u = new UserLib();
        $u->upCacheUinfoByField($uid,$data);

        return out_pc(200);
    }

    function upPs($oldPS,$newPS,$confirmPs){

    }

    function getSafeLevel(){

    }

    function idCardAuth($realName,$idNum,$topPic,$backPic,$selfPic){

    }
}