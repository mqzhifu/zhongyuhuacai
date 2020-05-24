<?php
class LoginCtrl extends BaseCtrl {


    function getToken($appId,$uid,$ps){
        if(!$appId){
            return $this->out(1000);
        }
        if(!$uid){
            return $this->out(1001);
        }
        if(!$ps){
            return $this->out(1002);
        }

        $app = AppModel::db()->getById($appId);
        if(!$app){
            return $this->out(1003);
        }

        if($ps != $app['ps']){
            return $this->out(1004);
        }

        $str = $appId."X".$uid;
//        var_dump($str);
        $token = $this->userService->createToken($str);
//        var_dump($token);
//        $uidInfo = TokenLib::getDecode($token);
//        var_dump($uidInfo);
//        exit;
        return $this->out(200,$token);
    }
    //type:1正常首次连接，2断线重连
    function webSocketLogin($token,$type = 1){
        if($this->uid){//不要重复登陆
            return $this->out(1101);
        }

        if(!$token){
            return out_pc(1102);
        }

        $appIdUidRs = TokenLib::getDecode($token);
        LogLib::wsWriteFileHash(["token",$token,$appIdUidRs]);
        if(!$appIdUidRs){
            return $this->out(1100);
        }

        //UID 绑定 FD,注册到全局内存变量中
        $uid = $appIdUidRs['uid'];
        LogLib::wsWriteFileHash(['uid',$uid, 'fd' ,$this->clientFrame]);
        $GLOBALS['uid_fd_table']->set($uid,['fd'=>$this->clientFrame]);
        LogLib::wsWriteFileHash("bind uid_fd_table");
        $GLOBALS['fd_uid_table']->set($this->clientFrame,['uid'=>$uid]);
        LogLib::wsWriteFileHash("bind fd_uid_table");

        $uinfo = $this->matchService->getUinfoByUid($uid);
        if(!$uinfo){
            $data = array('loginTime'=>time(),'fd'=>$this->clientFrame,'roomId'=>'','status'=>UserModel::$_status_normal);
            $this->matchService->upUinfoByField($uid,$data);
        }else{
            $data = array('loginTime'=>time(),'fd'=>$this->clientFrame);
            $this->matchService->upUinfoByField($uid,$data);
        }

        $this->loginLog($uid);

        return $this->out(200,$uinfo);
    }

    function wsShutdown(){
        foreach ($GLOBALS['mysql_id'] as $k=>$v) {
            $rs = LoginModel::upById($v['mysql_id'],array('e_time'=>time(),'close_status'=>3 ) );
            LogLib::wsWriteFileHash(['close this socket ,up mysql e_time close_status',$rs,'memory info:',$k,$v]);
        }

        $this->matchService->clearOnlineUserTotal();
    }

    function loginLog($uid){
        $uinfo = $this->matchService->getUinfoByUid($uid);
        $mysqlId = $GLOBALS['mysql_id']->get($uid,'mysql_id');
        //这里做个容错，不排除，有人连续发了两次请求，或者第一请求发出的关闭包 S端没收到
        //或者，干脆上一次连接就没收到关闭请求包，导致该变量一直存在着
        if( $mysqlId ){
            LogLib::wsWriteFileHash([" err mysql id in memory,but no close req",$uid,$mysqlId]);

            $rs = LoginModel::upById($mysqlId,array('e_time'=>time(),'close_status'=>2 ) );
            LogLib::wsWriteFileHash(['close this socket ,up mysql e_time close_status',$rs]);

            $this->matchService->decrOnlineUserTotal();
            $GLOBALS['mysql_id']->del($uid);

        }

        $clientInfo = $GLOBALS['ws_server']->getClientInfo($this->clientFrame);
        LogLib::wsWriteFileHash(["client info:",$clientInfo]);

        $appId = $this->matchService->getAppIdByUid($uid);
        $insertData = array('uid'=>$uid,'ip'=>$clientInfo['remote_ip'],'fd'=>$this->clientFrame,'room_id'=>$uinfo['roomId'],'status'=>$uinfo['status'],'a_time'=>time(),'e_time'=>0,'app_id'=>$appId);
        $id = LoginModel::db()->add($insertData);
        $rs = $GLOBALS['mysql_id']->set($uid,['mysql_id'=>$id]);
        LogLib::wsWriteFileHash(["login mysql insert ID:",$id,'set memory rs',$rs]);

        $this->matchService->incrOnlineUserTotal();
    }

    //手机验证码 - 登陆
    // 1 API-手机端-登陆：是只需要手机短信验证码的
    // 2 PC端：是需要图片验证码的
    function cellphoneSMS($cellphoneNumber,$smsCode){
        $rs = $this->userService->loginRegister($cellphoneNumber,null,UserModel::$_type_cellphone,$this->clientInfo,null,$smsCode);

        return $this->out($rs['code'],$rs['msg']);
//        //图片验证码
//        $authImgClass = new ImageAuthCodeLib();
//        $authImgCode = $authImgClass->authCode($uniqueCode,$imgCode);
//        if($authImgCode['code']!= 200){
//            out_ajax($authImgCode['code']);
//        }
    }

    //断开连接
    function onClose(){
        LogLib::wsWriteFileHash(["login.onClose"]);
        if($GLOBALS['uid_fd_table']->exist($this->uid) && $GLOBALS['fd_uid_table']->exist($this->clientFrame)){
            $matchService = new GameMatchService();
            $matchService->errorTolerantStatus($this->uid,2);

            LogLib::wsWriteFileHash(["del uid_fd_table",$this->uid]);
            $GLOBALS['uid_fd_table']->del($this->uid);

            LogLib::wsWriteFileHash(["del fd_uid_table",$this->clientFrame]);
            $GLOBALS['fd_uid_table']->del($this->clientFrame);

            $mysqlId = $GLOBALS['mysql_id']->get($this->uid,'mysql_id');
            LogLib::wsWriteFileHash([" get mysql id in memory",$this->uid,$mysqlId]);
            if($mysqlId){
                $rs = LoginModel::upById($mysqlId,array('e_time'=>time(),'close_status'=>1));
                LogLib::wsWriteFileHash($rs);

                $this->matchService->decrOnlineUserTotal();

                $GLOBALS['mysql_id']->del($this->uid);

            }else{
                LogLib::wsWriteFileHash(["err----====////=====no mysql_id up login table ",$this->uid]);
            }

        }else{
            LogLib::wsWriteFileHash(["no bind uid fd|no bind fd uid"]);
        }

        return $this->out(200);

    }
    //断线重连
    function reConnect($token){
        $rs = $this->webSocketLogin($token,2);
        if($rs['code'] != 200){
            return $rs;
        }

        $this->matchService->errorTolerantStatus($this->uid,1);
    }

}