<?php
//前端路由器
class DispathAdminLib{
    //开户反射路由
    public $reflection = 1;
	function __construct($frame = null){
		$this->clientFrame = $frame;
	}
	function authDispath( ){

        $ctrl = _g(PARA_CTRL,'ctrl',0);
        $ac = _g(PARA_AC,'ac',0);

        if(!$ctrl){
            if(defined('DEF_CTRL'))
                $ctrl = DEF_CTRL;
            else
                ExceptionFrameLib::throwCatch('ctrl参数为空','G_PARA');
        }

        if(!$ac) {
            if (defined('DEF_AC'))
                $ac = DEF_AC;
            else
                ExceptionFrameLib::throwCatch('ac参数为空', 'G_PARA');
        }

        $cate = _g("cate");
        $sub = _g("sub");

        if($sub == 'no'){
            $sub = "";
        }

        if($cate == 'no'){
            $cate = "";
        }

        if($cate && $sub){
            $dir =  APP_DIR .DS. C_DIR_NAME . DS .$cate .DS .$sub .DS ;
        }elseif($cate){
            $dir =  APP_DIR .DS. C_DIR_NAME . DS .$cate .DS  ;
        }elseif($sub){
            exit(" sub 不应该 单独出现");
        }else{
            $dir =  APP_DIR .DS. C_DIR_NAME . DS ;
        }

        include_once APP_DIR .DS. C_DIR_NAME . DS . "base.ctrl.php";

        $ctrl_file = ($dir . $ctrl .C_EXT);

		if( !file_exists($ctrl_file))
            ExceptionFrameLib::throwCatch('ctrl文件不存在:'.$ctrl_file,'FILE');


		include_once $ctrl_file;
		if ( !class_exists($ctrl.C_CLASS))
            ExceptionFrameLib::throwCatch('ctrl类不存在:'.$ctrl.C_CLASS,'FILE');
		if(! method_exists($ctrl.C_CLASS,$ac))
            ExceptionFrameLib::throwCatch('ac方法不存在:'.$ac,'FILE');

        $this->ctrl = $ctrl;
        $this->ac = $ac;
        $this->cate = $cate;
        $this->sub = $sub;

	}
	//$paraData:ws 模式才会传入此值
	function action($paraData = null){
        $ac = $this->ac;
        $ctrl = $this->ctrl .C_CLASS;

	    if($this->reflection){
//	        $c = new ReflectionParameter (array($ctrl,$ac),1);
//            $x = $c->getType();
//            $x->getName();


            $content_type = get_client_content_type();

//var_dump($content_type);

            $info = new ReflectionMethod($ctrl,$ac);
            $p = $info->getParameters();

            $para = [];

            if($p){//是否方法有参数
                //WEB模式
                if(RUN_ENV == 'WEB' || RUN_ENV == 'CLI' ){
                    if($content_type == 'application/json'){
                        $data = file_get_contents("php://input");
                        if( $data ){
                            $data = json_decode($data,true);
                            foreach($p as $k=>$v){
                                $key = $v->getName();
                                $para[$key] = $data[$key];
                            }
                        }
                    }else{
                        foreach($p as $k=>$v){
                            $key = $v->getName();
                            $para[$key] = _g($key);

                        }
                    }
                }elseif(RUN_ENV == 'WEBSOCKET'){
                    //web-socket 模式
                    foreach($p as $k=>$v){
                        $key = $v->getName();
                        if(arrKeyIssetAndExist($paraData,$key))
                            $para[$key] = $paraData[$key];
                        else
                            $para[$key] = null;
                    }
                }
            }


            $class = new $ctrl($this->clientFrame,$this->ctrl,$this->ac,$this->cate,$this->sub);

            $reflection = new ReflectionClass($ctrl);
            $me = $reflection->getMethod($ac);
            return $me->invokeArgs($class,$para);
        }else{
            $ctrlClass = get_instance_of($ctrl);
    		$ctrlClass->$ac();
        }
	}
	
}
