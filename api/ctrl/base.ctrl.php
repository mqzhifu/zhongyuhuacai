<?php
class BaseCtrl {
    public $uid = 0;
    public $uinfo = null;

    public $request = null;

    public $userService = null;
    public $imSevice =null;
    public $systemService =null;

    function __construct($request){
        $this->request = $request;
//        $this->checkSign();

        ConfigCenter::get(APP_NAME,"api");
        ConfigCenter::get(APP_NAME,"err_code");
        ConfigCenter::get(APP_NAME,"main");
        ConfigCenter::get(APP_NAME,"rediskey");

//        //实例化 用户 服务 控制器
        $this->userService = new UserService();
//        $this->systemService = new SystemService();
//
        $tokenRs = $this->initUserLoginInfoByToken();
        if($tokenRs['code'] != 200){
            return $this->out($tokenRs['code'],$tokenRs['msg']);
        }

//        if(arrKeyIssetAndExist($this->uinfo,'id')){
//            if(RUN_ENV != 'WEBSOCKET'){
//                $rs = $this->checkIfUserBlocked($this->uid);
//                if($rs){
//                    return $this->out(6004);
//                }
//            }
//        }
//
//        if(arrKeyIssetAndExist($this->uinfo,'status')){
//            return $this->out(4009);
//        }
//
//        if($this->uid){
//            $this->userService->setDayActiveUser($this->uid);
//        }

        //有些接口必须，得登陆后，才能访问
        $isLogin = $this->loginAPIExcept($request['ctrl'],$request['ac']);
        if($isLogin){
            if(!$this->uinfo){
                return $this->out(5001);
            }
        }
//        //每日 任务初始化
//        $this->taskService->addUserDailyTask($this->uid);
    }

    function checkSign(){
        $sign = _g('sign');
        if(!$sign){
            $this->out(3000);
        }

        $checkSign = TokenLib::checkSign($request , $sign,$this->app['apiSecret']);
        if(!$checkSign){
            $this->out(3001);
        }

    }

    function out($code,$msg = ""){
        if($code == 200){
            if(!$msg){
                if($msg === ""){
                    $msg = $GLOBALS['code'][$code];
                }elseif($msg === 0){
                    $msg = 0;
                }else{
                    $msg = [];
                }
            }

            if(is_string($msg) && $msg == 'space_string'){
                $msg = "";
            }

            $apiMethod = null;
            if(isset($GLOBALS[APP_NAME]['api'][$this->request['ctrl']][$this->request['ac']])){
                $apiMethod =$GLOBALS[APP_NAME]['api'][$this->request['ctrl']][$this->request['ac']];
            }

//            if(!$apiMethod){
//                LogLib::appWriteFileHash("apimethod");
//            }
            if($apiMethod && arrKeyIssetAndExist($apiMethod,'return')){
                $msg = FilterLib::apiReturnDataCheckInit($apiMethod['return'],$msg);
            }

            $data = array('code'=>$code,"msg"=>$msg);
//            if(RUN_ENV == 'WEBSOCKET'){
//                return $data;
//            }else{
                return $data;
//            }
        }else{
            $data = array('code'=>$code,"msg"=>$msg);
            return $data;
//            ThrowErr::unknow(APP_NAME,$code,array($msg));
        }


    }

    //有些接口，必须是登陆后，才能访问~有些不需要
    function loginAPIExcept($ctrl ,$ac ){
        $arr = $GLOBALS[APP_NAME]['main']['loginAPIExcept'];
        if(!$arr){
            return 0;
        }
        foreach($arr as $k=>$v){
            if($v[0] == $ctrl && $v[1] == $ac){
                return 1;
            }
        }

        return 0;
    }
    //判断登陆，初始化用户信息
    function initUserLoginInfoByToken(){
//        if(RUN_ENV == "WEB"){
            $token = _g('token');
            if(!$token)
                return out_pc(200,'no token');

            $rs = $this->authToken($token);
            if($rs['code'] != 200){
                return $rs;
            }
            $this->uid = $rs['msg'];
//        }
//        elseif(RUN_ENV == "WEBSOCKET"){
//            if($GLOBALS['uid_fd_table']->exist($this->clientFrame->fd)){
//                $this->uid = $GLOBALS['uid_fd_table']->get($this->clientFrame->fd)['uid'];
//            }
//            if($GLOBALS['fd_uid_table']->exist($this->clientFrame)){
//                $this->uid = $GLOBALS['fd_uid_table']->get($this->clientFrame,'uid');
//                LogLib::wsWriteFileHash(["fd_uid_table get uid=",$this->uid]);
//            }else{
//                LogLib::wsWriteFileHash(["fd_uid_table no exist",$this->clientFrame]);
//            }
//        }


        return out_pc(200);
    }

    function authToken($token){
        $decodeData = TokenLib::getDecode($token);
        if($decodeData['expire'] < time()){
            return out_pc(8230);
        }
        if(!$decodeData['uid']){
            return out_pc(8109);
        }
        $uid = $decodeData['uid'];
        //防止黑客伪造非整形UID,这样后面所有程度在读取的时候，都会错
        if(!$uid || $uid < 0 ){
            return out_pc(8105);
        }
//        $redisToken = RedisOptLib::getToken($uid);
//        if(!$redisToken){
//            return out_pc(8231);
//        }
//
//        if($redisToken != $token){
//            return out_pc(8232);
//        }
        $this->uinfo = $this->userService->getUinfoById($uid);
        if(!$this->uinfo){//TOKEN解出的UID 不在DB中
            return out_pc(1002);
        }

        return out_pc(200,$uid);
    }

}