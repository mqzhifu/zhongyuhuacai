<?php
class BaseCtrl {
    public $uid = 0;
    public $uinfo = null;
    public $request = null;
    //微服务 类
    public $userService = null;
    public $productService =null;
    public $systemService =null;
    public $orderService = null;
    public $uploadService = null;
    public $msgService = null;
    public $commentService = null;
    public $upService = null;
    public $collectService = null;
    public $payService = null;
    public $userAddressService = null;
    public $agentService = null;
    public $goodsService = null;
    public $cartService = null;
    public $shareService = null;

    function __construct($request){
        LogLib::inc()->debug(['php_server',$_SERVER]);
        $this->request = $request;
//        $this->checkSign();

        //加载 配置文件 信息
        ConfigCenter::get(APP_NAME,"api");
        ConfigCenter::get(APP_NAME,"err_code");
        ConfigCenter::get(APP_NAME,"main");
        ConfigCenter::get(APP_NAME,"rediskey");

//        //实例化 用户 服务 控制器

        $this->initService();
        $this->trace();

        $tokenRs = $this->initUserLoginInfoByToken();
        if($tokenRs['code'] != 200){
            out_ajax ($tokenRs['code'],$tokenRs['msg']);
        }

//        if(arrKeyIssetAndExist($this->uinfo,'id')){
//            if(RUN_ENV != 'WEBSOCKET'){
//                $rs = $this->checkIfUserBlocked($this->uid);
//                if($rs){
//                    return return $this->out(6004);
//                }
//            }
//        }
//
//        if(arrKeyIssetAndExist($this->uinfo,'status')){
//            return return $this->out(4009);
//        }
//
//        if($this->uid){
//            $this->userService->setDayActiveUser($this->uid);
//        }

        //有些接口必须，得登陆后，才能访问
        $isLogin = $this->loginAPIExcept($request['ctrl'],$request['ac']);
        if($isLogin){
            if(!$this->uinfo){
                return out_ajax(5001);
            }
        }

        $ipBaiduParserAddress = $this->initArea();
        $data = array(
            'a_time'=>time(),
            'ctrl'=>$request['ctrl'],
            'ac'=>$request['ac'],
            'uid'=>$this->uid,
            'client_info'=>json_encode(get_client_info()),
            'ip_parser'=>json_encode($ipBaiduParserAddress,JSON_UNESCAPED_UNICODE),
            'request_id'=>TraceLib::getInc()->getRequestId(),
            'trace_id'=>TraceLib::getInc()->getTraceId(),
        );
        UserLogModel::db()->add($data);
//        //每日 任务初始化
//        $this->taskService->addUserDailyTask($this->uid);
    }

    function trace($localEndpoint = 'baseService',$remoteEndpoint = 'userService'){
        TraceLib::getInc()->tracing($localEndpoint,$remoteEndpoint);
        exit;
    }

    function checkSign(){
        $sign = _g('sign');
        if(!$sign){
            return $this->out(3000);
        }

        $checkSign = TokenLib::checkSign($request , $sign,$this->app['apiSecret']);
        if(!$checkSign){
            return $this->out(3001);
        }

    }

    function initArea(){
        $ip = get_client_ip();
        $ipBaiduParserAddress = RedisOptLib::getBaiduIpParser($ip);
        if(!$ipBaiduParserAddress){
            $ipBaiduParserAddress = AreaLib::getByIp($ip);
            LogLib::inc()->debug($ip);
            LogLib::inc()->debug($ipBaiduParserAddress);
            RedisOptLib::setBaiduIpParser($ip,$ipBaiduParserAddress);
        }
        return $ipBaiduParserAddress;
    }

    function initService(){
        $this->userService = new UserService();
        $this->systemService = new SystemService();
        $this->productService = new ProductService();
        $this->orderService = new OrderService();
        $this->uploadService = new UploadService();
        $this->msgService = new MsgService();
        $this->commentService =  new CommentService();
        $this->upService = new UpService();
        $this->collectService =  new CollectService();
        $this->payService = new PayService();
        $this->userAddressService = new UserAddressService();
        $this->agentService = new AgentService();
        $this->goodsService = new GoodsService();
        $this->cartService = new CartService();
        $this->shareService = new ShareService();
    }

    //返回的数据，1检查格式2如果弱类型要转移成前端想要的类型
    function checkDataAndFormat($data){
        $api = ConfigCenter::get(APP_NAME,"api");
        if(!arrKeyIssetAndExist($api,$this->request['ctrl'])){
            return $this->out(7060);
        }

        if(!arrKeyIssetAndExist($api[$this->request['ctrl']],$this->request['ac'])){
            return $this->out(7061);
        }
        if(!arrKeyIssetAndExist($api[$this->request['ctrl']][$this->request['ac']],'return')){
            return $this->out(7062);
        }

        $apiMethodReturn = $api[$this->request['ctrl']][$this->request['ac']]['return'];
        $data = FilterLib::apiReturnDataCheckInit($apiMethodReturn,$data);
        return $data;
    }


    function out($code,$msg = ""){
        if($code == 200){
//            if(!$msg){
//                if($msg === ""){
//                    $msg = $GLOBALS['code'][$code];
//                }elseif($msg === 0){
//                    $msg = 0;
//                }else{
//                    $msg = [];
//                }
//            }
//
//            if(is_string($msg) && $msg == 'space_string'){
//                $msg = "";
//            }
//
//            $apiMethod = null;
//            if(isset($GLOBALS[APP_NAME]['api'][$this->request['ctrl']][$this->request['ac']])){
//                $apiMethod =$GLOBALS[APP_NAME]['api'][$this->request['ctrl']][$this->request['ac']];
//            }
            $msg = $this->checkDataAndFormat($msg);
            if(arrKeyIssetAndExist($msg,'code')){
                $data = $msg;
            }else{
                $data = array('code'=>$code,"msg"=>$msg);
            }

            return $data;
        }else{
            $data = array('code'=>$code,"msg"=>$msg);
            return $data;
        }
    }

    //有些接口，必须是登陆后，才能访问~有些不需要
    function loginAPIExcept($ctrl ,$ac ){
        $arr =  ConfigCenter::get(APP_NAME,"main")['loginAPIExcept'];
        if(!$arr){
            return 1;
        }
        foreach($arr as $k=>$v){
            if($v[0] == $ctrl && $v[1] == $ac){
                return 0;
            }
        }

        return 1;
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

            $this->uinfo = $rs['msg'];
            $this->uid =  $rs['msg']['id'];
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
        $uid = (int) $decodeData['uid'];
        //防止黑客伪造非整形UID,这样后面所有程度在读取的时候，都会错
        if(!$uid || $uid < 0 ){
            return out_pc(8105);
        }
        $redisToken = RedisOptLib::getToken($uid);
        if(!$redisToken){
            return out_pc(8231);
        }

        if($redisToken != $token){
            return out_pc(8232);
        }
        $uinfoRs = $this->userService->getUinfoById($uid);
        if($uinfoRs['code'] != 200){
            return out_pc($uinfoRs['code'],$uinfoRs['msg']);
        }

        return out_pc(200,$uinfoRs['msg']);
    }

}