<?php
//oauth 第3方 授权
class oauth{
    function getToken($appId,$timestamp,$authentication){
        if(!$appId){
            out_pc(8030);
        }

        if(!$timestamp){
            out_pc(8031);
        }

        if(!$authentication){
            out_pc(8032);
        }


        $appInfo = AppModel::db()->getById($appId);
        if(!$appInfo){
            out_pc(8033);
        }

        $md5Auth = md5($appId.$timestamp.$appInfo['key']);
        //验证信息来源是否正确
        if($md5Auth != $authentication){
            out_pc(8034);
        }
        //验证来源
//		if($_SERVER['HTTP_REFERER'] != $appInfo['from_url']){
//			$this->errToJson(1006,'from URL is err.');
//		}

        $token = $this->getTokenByAppId($appId);
        if(!$token){
            $token = md5($appId.$appInfo['key']).md5($timestamp.$appInfo['key']);
            $this->setTokenByAppId($appId,$token);
        }

        $data = array('expires'=>$GLOBALS['rediskey']['oauthtoken']['expire'] ,'token'=>$token);
        $this->okToJson($data);
    }
    //已登陆授权
    function authorize($token,$appId,$uid){
        $this->authPara($appId,$token);

        $openid = $this->process($appId,$token,$uid);

        $appInfo = AppModel::db()->getById($appId);

        $time = time();
        $authentication = md5($appId.$time.$appInfo['key']);
        //第3方TOKEN
        $thirdToken = "appid=$appId&timestamp=$time&authentication=".$authentication;


        header("Location:".$appInfo['callback_url']."?openid=".$openid  ."&". $thirdToken);

    }

    function authorizeLogin($token,$appId,$uname,$ps){
        $this->authPara($appId,$token);

        $rs = $this->userLib->login($uname,$ps);

        $appInfo = AppModel::db()->getById($appId);

    }

    //取消授权
    function cancelOauth(){

    }


    function getTokenByAppId($appId){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['oauthtoken']['$key'],$appId);
        return RedisPHPLib::get($key);
    }

    function setTokenByAppId($appId,$token){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['oauthtoken']['$key'],$appId);
        return RedisPHPLib::set($key, $token ,$GLOBALS['rediskey']['oauthtoken']['expire']);
    }

    function process($appId,$token,$uid){
        $openid = $this->getUserOpenId($appId,$uid);
//		$openid = $this->getUserOpenId($appId,'b62af9b35fd795');

        return $openid;

    }

    function getUserOpenId($appId,$uid){
        $open = $this->getUserOauthOpenId($appId,$uid);
        if($open && $open['openid']){
            $this->upUserAuthTime($uid,$open['openid']);
            return $open['openid'];
        }

        $openid = $this->makeOpenId($uid,$appId);
        $this->addUserOauthOpenId($appId,$uid,$openid);


        return $openid;
    }

    function getUserOauthOpenId($appId,$uid){
        $user = AppUserModel::db()->getRow(" uid = '$uid' and appid = $appId ");
        return $user;
    }

    //更新授权时间
    function upUserAuthTime($uid,$openid){
        $dd = array('u_time'=>time());
        return AppUserModel::db()->update($dd," uid = '$uid' and openid = '$openid' ");
    }

    function makeOpenId($uid,$appId){
//		$appinfo = $this->getAppInfoById($appId);

        $rand = rand(0,9);
        $uid_len = strlen($uid);
//		var_dump($rand);
//		$appid = "10001";
        $str = substr($uid,0,$rand);
        if($rand == 0){
            $str = $appId . $uid . $rand;
        }else{
            $n = 0;
            for($i=$rand;$i<$uid_len;$i++){
                if($n == 5){
                    break;
                }
                $tmp = substr($uid,$i-1,1);
                $str .= $tmp . $appId[$n];
                $n++;
            }

            $c =  $rand + 5 ;
            $str .= substr($uid,$c) . $rand;
        }


        $openId = $str;

        return $openId;
    }



    function addUserOauthOpenId($appid,$uid,$openid){
        $d = array('appid'=>$appid,'uid'=>$uid,'openid'=>$openid,'a_time'=>time(),'u_time'=>time());
        $r = AppUserModel::db()->add($d);
        return $r;
    }

    function authPara($appId,$token){
        if(!$appId){
            $this->errToJson(1001,'appid is null');
        }

        if(!$token){
            $this->errToJson(2001,'token is null');
        }

        $redisToken = $this->getTokenByAppId($appId);
        if(!$redisToken){
            $this->errToJson(2002,'app token is expires');
        }

        if($redisToken != $token){
            $this->errToJson(2003,'auth token failed ,server token != third token.');
        }

        $appinfo = $this->getAppInfoById($appId);
        if(!$appinfo){
            $this->errToJson(1004,'appid is error');
        }

        $auth = md5($appId.$appinfo['key']);
//		var_dump($auth);
//		var_dump(substr($token,0,32));
        if(substr($token,0,32) != $auth){
            $this->errToJson(2004,'auth token failed .');
        }

        return true;
    }


}