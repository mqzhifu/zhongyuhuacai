<?php
//前端路由器
class RouterLib{

    public $reflection = 1;//使用反射路由
    public $ctrl = null;
    public $ac = null;
    public $app = null;
    public $para = null;
    public $requestId = 0;
    public $ctrlFilePath = "";

    public $clientHeader = null;

	function __construct($frame = null){
		$this->clientFrame = $frame;
	}

	function check(){
	    $checkApp = $this->checkApp();
	    if($checkApp['code'] != 200){
            return out_pc($checkApp['code'],KERNEL_NAME);
        }
        $checkCtrlAc = $this->checkCtrlAc();
        if($checkCtrlAc['code'] != 200){
            return out_pc($checkCtrlAc['code'],KERNEL_NAME);
        }

        //检查IP是否在黑名单中
        $checkIp = FilterLib::checkIPRequest();
        if(!$checkIp){
            exit("IP限制");
        }
        $this->initPara();

        $this->clientHeader = get_client_info();

        return out_pc(200,null,KERNEL_NAME);

    }

    function checkApp(){
        if(!arrKeyIssetAndExist($GLOBALS[KERNEL_NAME]['app'],APP_NAME)){
            z::outError(9207);
        }
        $app = $GLOBALS[KERNEL_NAME]['app'][APP_NAME];
        if($app['status'] != 'open'){
            z::outError(9208);
        }

        $this->app = $app;
        return out_pc(200,null,KERNEL_NAME);
    }

	function checkCtrlAc(){
        $ctrl = "";
        $ac = "";
        $cate = "";
        $sub = "";
		if(RUN_ENV == 'WEB'){
            $ctrl = _g(PARA_CTRL);
            $ac = _g(PARA_AC);
            if(defined("IS_ADMIN") && IS_ADMIN){
                $cate = _g("cate");
                if(!$cate){
                    $cate = "index";
                }
                $sub = _g("sub");
            }
		}

		if(!$ctrl){
            if(defined('DEF_CTRL'))
                $ctrl = DEF_CTRL;
            else
                return out_pc(9200,null,KERNEL_NAME);
        }


		if(!$ac){
            if(defined('DEF_AC'))
                $ac = DEF_AC;
            else
                return out_pc(9201,null,KERNEL_NAME);
        }

        if(defined("IS_ADMIN") && IS_ADMIN){
            if($cate != 'no'){
                $dir =  APP_DIR .DS. C_DIR_NAME . DS  .$cate .DS;
            }else{
                $dir =  APP_DIR .DS .C_DIR_NAME.DS."index" . DS ;
            }
        }else{
            $dir =  APP_DIR .DS. C_DIR_NAME . DS ;
        }


        $ctrl_file = ($dir . $ctrl .C_EXT);

        $this->ctrlFilePath = $ctrl_file;

		if( !file_exists($ctrl_file))
            return out_pc(9202,'ctrl文件不存在:'.$ctrl_file,KERNEL_NAME);

        if(defined("IS_ADMIN") && IS_ADMIN){
            include_once $ctrl_file;
        }
		if ( !class_exists($ctrl.C_CLASS))
            return out_pc(9203,'ctrl类不存在:'.$ctrl.C_CLASS,KERNEL_NAME);


		if(! method_exists($ctrl.C_CLASS,$ac))
            return out_pc(9204,'ac方法不存在:'.$ac,KERNEL_NAME);



        $this->ctrl = $ctrl;
        $this->ac = $ac;

        return out_pc(200,null,KERNEL_NAME);

	}
	//$paraData:ws 模式才会传入此值
	function initPara($paraData = null){
	    $data = array();
	    if($_REQUEST){
            $data = $_REQUEST;
        }
        $data["ctrl"] =$this->ctrl;
        $data["ac"] =$this->ac;
        $data['ctrlFilePath'] = $this->ctrlFilePath;
        $this->para = $data;
        //反射，根据POST的KEY-VALUE，对应到方法的参数上
//	    if($this->reflection){
//	        //json html
//            $content_type = get_client_content_type();
//
//            $info = new ReflectionMethod($ctrl,$ac);
//            //方法的所有参数
//            $p = $info->getParameters();
//
//            $para = [];
//
//            if($p){//是否方法有参数
//                //WEB模式
//                if(RUN_ENV == 'WEB' || RUN_ENV == 'CLI' ){
//                    if($content_type == 'application/json'){
//                        //json ，PHP是直接用流处理的，post中是获取不到的
//                        $data = file_get_contents("php://input");
//                        if( $data ){
//                            $data = json_decode($data,true);
//                            foreach($p as $k=>$v){
//                                $key = $v->getName();
//                                $para[$key] = $data[$key];
//                            }
//                        }
//                    }else{
//                        foreach($p as $k=>$v){
//                            $key = $v->getName();
//                            $para[$key] = _g($key);
//
//                        }
//                    }
//                }elseif(RUN_ENV == 'WEBSOCKET'){
//                    //web-socket 模式
//                    foreach($p as $k=>$v){
//                        $key = $v->getName();
//                        if(arrKeyIssetAndExist($paraData,$key))
//                            $para[$key] = $paraData[$key];
//                        else
//                            $para[$key] = null;
//                    }
//                }
//            }

//            $this->para = $_REQUEST;
//        }
//        else{
//            $ctrlClass = get_instance_of($ctrl);
//    		$ctrlClass->$ac();
//        }
	}

	function action(){
        $rid = $this->getRequestId();
        $this->requestId = $rid;
        $this->para['request_id'] = $rid;

        $this->logRequest();


        $ctrl = $this->ctrl .C_CLASS;
        $class = new $ctrl($this->para);

//        $reflection = new ReflectionClass($ctrl);
//        $me = $reflection->getMethod($this->ac);
//        //调用实际执行方法
//        $rs = $me->invokeArgs($class,'aaa');
        $rs = call_user_func(array($class,$this->ac),$this->para);
        return $rs;
    }

    function getRequestId(){
        $key = ContainerLib::get("kernelRedisObj")->getAppKeyById($GLOBALS[KERNEL_NAME]['rediskey']['request_id']['key'], "" , KERNEL_NAME);
        $script = "redis.call('incr', KEYS[1]) ; return  redis.call('get', KEYS[1])";
        $execRs = ContainerLib::get("kernelRedisObj")->eval($script,array($key),1);

//        var_dump($execRs);
//        $t = RedisPHPLib::getServerConnFD()->get($key);
//        var_dump($t);exit;
        return $execRs;
    }

    //添加 请求日志 mysql
    function logRequest(){
	    $data = $this->para;
        $data['rid'] = $this->requestId;
	    LogLib::inc()->access($this->para);
	    get_client_info();
        if(RUN_ENV != 'WEBSOCKET'){
//            $requestMerge = array_merge($_REQUEST,$para);
//            if(!$requestMerge){
//                $requestData = "-";
//            }else{
//                $requestData = json_encode($requestMerge);
//            }
//
//            $data = array(
//                'ctrl'=>$this->ctrl,
//                'AC'=>$this->ac,
//                'a_time'=>time(),
//                'IP'=>get_client_ip(),
//                'request'=>$requestData,
//                'client_data'=>json_encode(get_client_info()),
//            );

//            $id = AccesslogModel::db()->add($data);
//            $moreId = AccessLogMoreModel::add($data);
//            $this->accessMore_aid = $moreId;
//            return $moreId;
        }
    }
}
