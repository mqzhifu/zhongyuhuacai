<?php
class OauthCtrl extends BaseCtrl {
    function authorize($token,$appId,$uid){
        $c = new OauthCtrl();
        $c->authorize($token,$appId,$uid);
    }

    function authorizeLogin($token,$appId,$name,$ps){
        $this->authPara($appId,$token);

        $rs = $this->userLib->login($uname,$ps);

        $appInfo = AppModel::db()->getById($appId);

    }
}


//class Authdb{
//
//
//
//
//
//	function getCompanyById($appid){
//        $sql = "select * from company_app where id =  $appid ";
//        $user = $this->_customerDB->get($sql);
//
//        return $user;
//    }
//
//	function getUserInfoByOpenId($openid,$appid){
//        $sql = "select * from company_app_user where openid = '$openid' and appid = $appid ";
//        $user = $this->_customerDB->get($sql);
//        if(!$user){
//            return false;
//        }
//
//        $users = $this->getUserById($user['uid']);
//        return $users;
//    }
//
//	function getUserById($uid){
//        $sql = "select * from users where uid = '$uid' ";
//        return $this->_customerDB->get($sql);
//    }
//
//	function getUserListByAppid($appid,$uid = null){
//        $sql = "select a.uid,a.openid,a.a_time,a.u_time,b.name,b.mobile,b.email,b.reg_time from company_app_user as a left join users as b on a.uid = b.uid where a.appid = $appid order by a.a_time desc";
//        if($uid){
//            $sql .= " and a.uid = '$uid' ";
//        }
//        return $this->_customerDB->mget($sql);
//    }
//
//}

//
//class BaseAuth{
//    //授权成功后，会返回OPENID给请求方，请求方，会根据OPENI拉取用户信息
//    function getUserInfoByOpenId($appid,$openid){
//        $userInfo = MooController::get('Mod_Sso')->getUserInfoByOpenId($openid,$appid);
//        return $userInfo;
//    }
//
//    function getUserOpenId($appId,$uid){
//        $open = MooController::get('Mod_Sso')->getUserOauthOpenId($appId,$uid);
//        if($open && $open['openid']){
//            MooController::get('Mod_Sso')->upUserAuthTime($uid,$open['openid']);
//            return $open['openid'];
//        }
//
//        $openid = $this->makeOpenId($uid,$appId);
//        MooController::get('Mod_Sso')->addUserOauthOpenId($appId,$uid,$openid);
//
//
//        return $openid;
//    }
//}