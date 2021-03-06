<?php
class LoginCtrl extends BaseCtrl {
    //手机/邮箱/用户名 + 密码
    function index($username,$ps){
        return $this->userService->login($username,$ps);
    }

    function wxLittleLoginByCode($request){
        $code = get_request_one( $this->request,'code',"");
        if(!$code){
            return $this->out(8074);
        }
        $WxLittleLib = new WxLittleLib();
        $wxCallbackData = $WxLittleLib->getSessionOpenIdByCode($code);

        LogLib::inc()->debug($wxCallbackData);
//        var_dump($wxData);
//        var_dump($request);

        $return = array("token"=>"","session_key"=>"", 'isReg'=>0);

        $sessionKey = $wxCallbackData['session_key'];
        $openId = $wxCallbackData['openid'];

        $loginData = array('thirdId'=>$openId,'type'=>UserModel::$_type_wechat);
        //正常，登陆
        $loginRs = $this->third($loginData);

        $return['session_key'] = $sessionKey;

        LogLib::inc()->debug(["end back:",$loginRs]);
        if($loginRs['code'] == 200){//登陆成功
            //返回token
            $return['token'] = $loginRs['msg'];
            out_ajax(200,$return);
        }
        //正常登陆没问题，但，DB中找不到此用户，默认就要走注册流程了
        if($loginRs['code'] == 1006){
            //注册一个新的用户，使用微信的用户基础信息
            if(arrKeyIssetAndExist($request,"rawData")){
                //language city  province country
                $rawData = json_decode($request['rawData'],true);
                $userInfo = array('nickname'=>$rawData['nickName'],'avatar'=>$rawData['avatarUrl'],'sex'=>$rawData['gender']);
            }else{
                $userInfo = array('nickname'=>'游客'.rand(1000000,999999));
            }

            $newUserInfo = $this->userService->register($openId,"",UserModel::$_type_wechat,$userInfo);
//            var_dump("new uid :",$newUserInfo['id']);
            $token =  $this->userService->createToken($newUserInfo['msg']['id']);
            $return['token'] = $token;
            LogLib::inc()->debug("create token:$token");
            $return['isReg'] = 1;
            out_ajax( 200,$return);
//            out_ajax(200,$return);
        }else{
            out_ajax($loginRs['code'],$loginRs['msg']);
//            out_ajax($loginRs['code'],$loginRs['msg']);
        }

//        $rawData = $request['rawData'];
//        $iv = $request['iv'];
//        $signature = $request['signature'];
//        $encryptedData = $request['encryptedData'];
//
//        $signature2 =  sha1(htmlspecialchars_decode($rawData).$sessionKey);
//        if($signature != $signature2){
//            exit("$signature != $signature2");
//        }
//
//
//        $data = $WxLittleLib->decryptData($encryptedData,$iv,$sessionKey);
//        echo "           ";
//        var_dump($data);
//        return json_encode($data);

    }

    function decodeWxEncryptedData($request){
//        $rawData = $request['rawData'];
//        $signature = $request['signature'];
        if(!arrKeyIssetAndExist($request,'iv')){
            out_ajax(500,"iv is null");
        }

        if(!arrKeyIssetAndExist($request,'encryptedData')){
            out_ajax(500,"encryptedData is null");
        }

        if(!arrKeyIssetAndExist($request,'sessionKey')){
            out_ajax(500,"sessionKey is null");
        }

        $iv = $request['iv'];
        $encryptedData = $request['encryptedData'];
        $sessionKey = $request['sessionKey'];

        $WxLittleLib = new WxLittleLib();
        $data = $WxLittleLib->decryptData($encryptedData,$iv,$sessionKey);
        $data = json_decode($data,true);

        $userService = new UserService();
        $rs = $userService->upUserInfo($this->uid,array('mobile'=>$data['phoneNumber']));
        //string(146) "{"phoneNumber":"13522536459","purePhoneNumber":"13522536459","countryCode":"86","watermark":{"timestamp":1591855857,"appid":"wx9f0bcf9eed8f9bf2"}}"
        out_ajax($rs['code'],array("upinfoRs"=>$rs['msg'],'mobile'=>$data['phoneNumber']));
    }
    //3方登陆,如果登陆成功后，每次都会重新建立一次token
    function third($request){
        $thirdId = $request['thirdId'];
        $type = $request['type'];
        LogLib::inc()->debug(["ctrl third start ~",'thirdId:',$thirdId,"type:",$type]);
        if(!$thirdId){
            return out_pc(8030);
        }

        if(!$type){
            return out_pc(8004);
        }

        if(!UserModel::keyInRegType($type)){
            return out_pc(8210);
        }
        //判断 type 值是否正常
        if($this->userService->getTypeMethod($type) != UserModel::$_type_cate_third){
            return out_pc(8242);
        }

        $rs = $this->userService->thirdLogin($thirdId,$type);
        return out_pc($rs['code'],$rs['msg']);
    }

    /*    //登出
    function logout(){
        $rs = $this->userService->offline($this->uid);
        return $this->out($rs['code'],$rs['msg']);
    }
    //登陆目前就4种：
    //1:手机/邮箱/用户名 + 密码
    //2:手机 - 短信 - 验证码 (图片验证码)
    //3:3方平台登陆
    //4:特殊情况，游客进来，自动给生成一个用户，并登陆


    //游客模式 - 自动生成一个USER
    function guest($clientInfo = null){
        $rs =  $this->userService->loginRegister(null,null,UserModel::$_type_guest,$this->clientInfo);
        return $this->out($rs['code'],$rs['msg']);
    }

        //手机 - 短信 - 验证码 - 登陆
    // 1 API-手机端-登陆：是只需要手机短信验证码的
    // 2 PC端：是需要图片验证码的
    function cellphoneSMS($cellphone,$smsCode){
        $rs = $this->userService->loginRegister($cellphone,null,UserModel::$_type_cellphone,$this->clientInfo,null,$smsCode,$this->uid);
        return $this->out($rs['code'],$rs['msg']);
    }




    //web socket ========================相关============================================================================

    //断开连接
    function onClose($fd){
        $mysqlId = $GLOBALS['mysql_id']->get($this->uid,'mysql_id');
        LogLib::wsWriteFileHash([" get mysql id in memory",$this->uid,$mysqlId]);

        if($mysqlId){
            $rs1 = WsLogModel::upById($mysqlId,array('e_time'=>time()));
            $rs2 = $GLOBALS['mysql_id']->del($this->uid);
            LogLib::wsWriteFileHash(['up mysql rs',$rs1,"del table mysqlid",$rs2]);
        }else{
            LogLib::wsWriteFileHash(["err----====////=====no mysql_id up wslog table ",$this->uid]);
        }

        RedisOptLib::delOnlineUserTotal();

        $rs1 = $GLOBALS['uid_fd_table']->del($this->uid);
        $rs2 = $GLOBALS['fd_uid_table']->del($fd);

        LogLib::wsWriteFileHash([" del uid_fd_table,mysql_id",$rs1,$rs2]);

        return $this->out(200,array('mysqlId'=>$mysqlId,$rs1,$rs2));
    }

    function wsShutdown($server){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['online_user_total']['key']);
        //清空-累加在线人数
        $rs = RedisPHPLib::getServerConnFD()->del($key);
        LogLib::wsWriteFileHash([" clear online_user_total",$rs]);
    }

    function webSocketLogin($token,$clientInfo = null){
        if($this->uid){//不要重复登陆
            return $this->out(8260);
        }

        if(!$token){
            return out_pc(8035);
        }

        $rs = $this->authToken($token);
        LogLib::wsWriteFileHash(["token",$token,$rs]);
        if($rs['code'] != 200){
            return $this->out($rs['code'],$rs['msg']);
        }

        //UID 绑定 FD,注册到全局内存变量中
        $uid = $rs['msg'];
        //多进程并发，可能同时发送2个 连接请求，这里做个容 错吧
        if($GLOBALS['uid_fd_table']->get($uid,'fd')){
            LogLib::wsWriteFileHash([" fd has exist ,do not repeat login",$token,$rs]);
            return $this->out(8260);
        }

        $rs1 = $GLOBALS['uid_fd_table']->set($uid,['fd'=>$this->clientFrame]);
        $rs2 = $GLOBALS['fd_uid_table']->set($this->clientFrame,['uid'=>$uid]);

        LogLib::wsWriteFileHash(["bind fd_uid_table",$uid,$this->clientFrame,$rs1,$rs2]);

        $this->wsWriteMysql($uid,$clientInfo);

        return $this->out(200);
    }
    //ws 连接信息，持久化到mysql
    function wsWriteMysql($uid,$clientInfo = 0 ){
        //先做容错：不排除，有人断线后，没有发出<关闭>包，S端的心跳也没检查到
        $mysqlId = $GLOBALS['mysql_id']->get($uid,'mysql_id');
        if( $mysqlId ){
            LogLib::wsWriteFileHash([" err mysql id in memory,but no close req",$uid,$mysqlId]);

            $rs = WsLogModel::upById($mysqlId,array('e_time'=>time()));
            LogLib::wsWriteFileHash($rs);
            //累减 在线数
            RedisOptLib::decrOnlineUserTotal();
            $rs2 = $GLOBALS['mysql_id']->del($uid);
        }

        $ip = "";
        $device_id = "";
        $app_version = "";
        if($clientInfo){
            $clientInfo = explode("|",$clientInfo);
            if(arrKeyIssetAndExist($clientInfo,'0'))
                $device_id = $clientInfo[0];
            if(arrKeyIssetAndExist($clientInfo,'1'))
                $app_version = $clientInfo[1];

            if(arrKeyIssetAndExist($clientInfo,'2'))
                $ip = $clientInfo[2];
        }
        $data = array('a_time'=>time(),'e_time'=>0,'uid'=>$uid,'fd'=>$this->clientFrame,'ip'=>$ip,'device_id'=>$device_id,'app_version'=>$app_version,'reg_time'=>$this->uinfo['a_time']);
        $id = WsLogModel::add($data);

        $rs = $GLOBALS['mysql_id']->set($uid,['mysql_id'=>$id]);
        LogLib::wsWriteFileHash([" set mysql id in memory",$id,$rs,$uid]);

        RedisOptLib::incrOnlineUserTotal();
    }

    function pcLoginCellphonePs($cellphone,$ps){
        $rs = $this->userService->selfLogin($cellphone,$ps,UserModel::$_type_pc_cellphone_ps);
        var_dump($rs);exit;
    }

    function pcLoginCellphoneSMS($cellphone,$smsCode){
        $rs = $this->userService->selfLogin($cellphone,null,UserModel::$_type_pc_cellphone_sms,$smsCode);
        var_dump($rs);exit;
    }

*/


}