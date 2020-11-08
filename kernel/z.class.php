<?php

//初始分有为2个部分，1公共部分，专属部分
//专属部分：1 WB端，2指令行端，3 API调用

class Z{

    static function run(){
        self::init();
        self::runWebApp();
    }

	static function init(){
        //包含项目配置文件信息  设置ENV
        include_once BASE_DIR ."/". APP_NAME."/env.php";
        //所有框架需要的 常量
        ConfigCenter::get("kernel","constant");
        include_once KERNEL_DIR .DS ."lib/log.lib.php";

        ConfigCenter::get(KERNEL_NAME,"err_code");//kernel 错误码
        ConfigCenter::get(KERNEL_NAME,"app");//kernel 应用程序配置信息
        ConfigCenter::get(KERNEL_NAME,"main");//kernel 主配置文件，主要是网关限制
        ConfigCenter::get(KERNEL_NAME,"rediskey");//kernel redis KEY 配置信息，主要是网关限制

//        ConfigCenter::getEnv(KERNEL_NAME,"domain_".LANG);
        ConfigCenter::getEnv(KERNEL_NAME,"mysql_".LANG);
        ConfigCenter::getEnv(KERNEL_NAME,"redis_".LANG);
        //常量检查
        Z::checkConst();
        Z::checkExt();

        ConfigCenter::includeKernelFuncFiles();

        //框架开始执行时间-开始时间
		$GLOBALS['start_time'] = microtime(TRUE);

		if(DEBUG){//测试模式-打开出错信息
			ini_set('display_errors', 1);
			if(1 == DEBUG){
				error_reporting(E_ALL);
			}else{
				error_reporting(E_ERROR);
			}
		}else{//生产模式 关闭 错误提示
			ini_set('display_errors', 0);
			error_reporting(0);
		}
        LogLib::inc()->debug(["init class:","autoload","fatalShutdown","appError","throwCatch"]);
		//类自动加载函数
		spl_autoload_register('autoload');
		//捕获-fatal脚本停止/脚本结束钩子 fatal error
        register_shutdown_function(array("ExceptionFrameLib",'fatalShutdown'));
		// 设定warning notice 错误
		set_error_handler(array('ExceptionFrameLib','appError'));
		//设定 异常处理
 		set_exception_handler(array('ExceptionFrameLib','throwCatch'));

		//===========内存信息==================
        LogLib::inc()->debug("init memory : ");
		define('MEMORY_LIMIT_ON',function_exists('memory_get_usage'));
		if(MEMORY_LIMIT_ON) $GLOBALS['start_use_mems'] = memory_get_usage();
		$memorylimit = @ini_get('memory_limit');
		if($memorylimit && return_bytes($memorylimit) < 33554432 ) {
			ini_set('memory_limit', '256m');
		}
		//===========内存信息==================

        ConfigCenter::getEnv(APP_NAME,"domain_".LANG);
        ConfigCenter::getEnv(APP_NAME,"mysql_".LANG);
        ConfigCenter::getEnv(APP_NAME,"redis_".LANG);

        ConfigCenter::get(APP_NAME,"constant");
        //初始化 zipkin  traceId  requestId
        LogLib::inc()->debug("init zipkin : ");
        if(defined("OPEN_TRACE") && OPEN_TRACE){
            ConfigCenter::getEnv(APP_NAME,"zipkin");
            TraceLib::getInc()->setHost($GLOBALS[APP_NAME]['zipkin']['host']);
            TraceLib::getInc()->setUri($GLOBALS[APP_NAME]['zipkin']['uri']);
            TraceLib::getInc()->setPort($GLOBALS[APP_NAME]['zipkin']['port']);
        }

        LogLib::inc()->debug(["client_info",get_client_info()]);

        LogLib::inc()->debug("kernel::Z init finish.");
        //此处不用初始化了，框架最初调用LOGLIB 的时候，已经初始化过了
//        TraceLib::getInc()->getRequestId(32);
//        TraceLib::getInc()->getTraceId(32);
	}
	//指令行方式运行RUN_ENV
	static function runConsoleApp(){
		defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));

//		define("APP_SHELL_DIR",APP_DIR .DS. "shell");
//		set_include_path(get_include_path() . PATH_SEPARATOR .  APP_DIR . DS .'/shell');
		$Cmd = new CmdlineLib();

		$Cmd->addCommands(APP_DIR ."/shell/" );
		$Cmd->runCommand();
	}
	static function runWebSocket(){
        self::checkWebConst();
        include_once APP_CONFIG.DS."server.php";

        if(APP_NAME == 'game'){
            $class = new SwooleWebSocketMatchLib();
        }else{
            $class = new SwooleWebSocketLib();
        }

        $class->run();
    }
	//web方式进行访问
	static function runWebApp(){
        LogLib::inc()->debug("kernel runWebApp start : ");
        self::checkWebConst();

        set_time_limit(TIME_LIMIT);
		//****************session***************************
		if(SESS_TYPE == 'DB'){
			if(ini_get('session.save_handler') != 'user')
				ini_set('session.save_handler', 'user');
		}else{
            LogLib::inc()->debug(["init session_store_dir",APP_SESS_STORE_DIR]);
            session_save_path(APP_SESS_STORE_DIR);
            if(!file_exists(APP_SESS_STORE_DIR)) {
                mkdir(APP_SESS_STORE_DIR, 0777, true);
            }
        }

        //****************session***************************
		//控制器 参数名
		define('PARA_CTRL', 'ctrl');
		//方法    参数名
		define('PARA_AC', 'ac');
//		$script_path = $script_file = _get_script_url();
//		if(arrKeyIssetAndExist($_SERVER,'QUERY_STRING'))
//            $script_file = $script_path . "?" .  $_SERVER['QUERY_STRING'];
//
//		//请求文件的名称
//		define('SCRIPT_NAME',$script_file);
		//初始化SESSION
        LogLib::inc()->debug(["init session_start"]);
		new SessionLib();
		//初始化路由
        $router = new RouterLib();

        //获取redis 实例，并缓存到容器里.主要是给kernel 网关 过滤数据
        $redisConfig = ConfigCenter::getEnv(KERNEL_NAME,"redis_".LANG)['zhongyuhuacai'];
        LogLib::inc()->debug(["init kernel redis",$redisConfig]);
        $kernelRedisObj = new RedisPHPLib($redisConfig);
        LogLib::inc()->debug(["init kernel redisOptKey"]);
        RedisOptLib::init($kernelRedisObj);

        try{
            $rs = $router->check();
            if($rs['code'] != 200){
                $msg = $rs['code'] . "-".$rs['msg'];
                ExceptionFrameLib::throwCatch($msg,'dispath');
            }

            $returnData = $router->action();
            if(OUT_TYPE == 'json'){
                $returnData =  json_encode($returnData);
            }

            $exec_time = $GLOBALS['start_time'] - microtime(TRUE);
            $logData = $exec_time;
            if( $returnData ){
                $tmpReturnData = $returnData;
                if( strlen( $tmpReturnData ) >1000){
                    $tmpReturnData = substr( $tmpReturnData,0,1000);
                }

                $logData .= $tmpReturnData;
            }
            LogLib::inc()->response($logData);
            if(OUT_TYPE == 'json'){
                echo $returnData;
            }
//                $accessData = array(
//                    'uid'=>$this->uid,
//                    'return_info'=>$msg,
//                    'exec_time'=>$exec_time,
//                );
//
//                if( strpos(APP_NAME,'admin') === false){
//                    if($this->ctrl !='sdk'){
//                        $accessData['code'] = $code;
//                        AccessLogMoreModel::upById($this->accessMore_aid,$accessData);
//                    }
//
//                }
            exit;

        }catch (Exception $e){
            ExceptionFrameLib::throwCatch($e);
        }

		//getSqlLog();//所有SQL记录日志

	}

	static function getRunEnv(){
	    return array('CLI','WEB','WEBSOCKET');
    }

//    static function getCountry(){
//        return array('cn','user','en');
//    }

//    static function countryMapLang($COUNTRY){
//	    $arr =  array("cn"=>'cn','en'=>'en','user'=>'en');
//	    return $arr[$COUNTRY];
//    }

    static function outError($code,$replace = array()){
        ThrowErr::unknow(KERNEL_NAME,$code,$replace);
    }

    static function getEnv(){
	    return array("local",'pre','dev','release');
    }
    //初始化的常量值，必埴项检查
	static function checkConst(){
        LogLib::inc()->debug(["checkConst"]);
        if (!defined('KERNEL_DIR'))
            self::outError(9118);

        if (!is_dir(KERNEL_DIR)) {
            self::outError(9119);
        }

        if (!defined('BASE_DIR'))
            self::outError(9101);

        if (!is_dir(BASE_DIR)) {
            self::outError(9113);
        }

        if (!defined('RUN_ENV'))
            self::outError(9102);

        $run_env = self::getRunEnv();
        if (!in_array(RUN_ENV, $run_env)) {
            self::outError(9114);
        }

        if (!defined('APP_NAME'))
            self::outError(9103);

        if (!is_dir(APP_DIR))
            self::outError(9115);

        if (!defined('ENV'))
            self::outError(9108);

        $env = self::getEnv();
        if (!in_array(ENV, $env)) {
            self::outError(9120);
        }

        if (!in_array(APP_NAME, array_keys($GLOBALS[KERNEL_NAME]['app']))) {
            self::outError(9121);
        }

        if ($GLOBALS[KERNEL_NAME]['app'][APP_NAME]['status'] != 'open') {
            self::outError(9001);
        }
//        if (!defined('COUNTRY'))
//            self::outError(9116);
//
//        $country = self::getCountry();
//        if (!in_array(COUNTRY, $country)) {
//            self::outError(9117);
//        }
//        if (!defined('DEF_DB_CONN'))
//            self::outError(9104);
//
//        if (!defined('DEF_REDIS_CONN'))
//            self::outError(9105);
    }

    static function checkWebConst(){
        if(!defined('DOMAIN_URL'))
            self::outError(9123);

        if(!defined('STATIC_URL'))
            self::outError(9124);
    }

    static function checkExt(){
        LogLib::inc()->debug(["checkExt"]);
	    $arr = array('gd','mysqli','json','mbstring','openssl','redis','xml','zlib','curl','dom','json','reflection','spl','pcre',
//            'seaslog',
//            'swoole',
//            'grpc',
//            'protobuf',
//            'vld',
//            'zookeeper',
//            'phar',
        );

	    foreach ($arr as $k=>$v) {
            $rs = extension_loaded($v);
            if(!$rs){
                self::outError(9300,array($v));
            }
	    }
    }
}
//配置中心
class ConfigCenter{
    static $_dir =BASE_DIR ."/configcenter";
    static $_configPool = null;

    static function checkDependClassImport(){
        if(class_exists("LogLib")){//无法做到在，项目最最开始的地方即用此类，加个判断吧先
            return 1;
        }

        return 0;
    }

    static function debugLog($appName ,$file,$func){
        if(self::checkDependClassImport()){
            LogLib::inc()->debug(["ConfigCenter->$func()",$appName,$file]);
        }
    }

    static function get($appName ,$file){
        self::debugLog($appName ,$file,'get');

        if(isset(self::$_configPool[$appName][$file])  && self::$_configPool[$appName][$file] ){
            return self::$_configPool[$appName][$file];
        }
        $dir = self::$_dir ."/$appName/$file.php";
        self::$_configPool[$appName][$file] = include_once $dir;
        $GLOBALS[$appName][$file] = self::$_configPool[$appName][$file];
        return self::$_configPool[$appName][$file];
    }

    static function getEnv($appName ,$file){
        self::debugLog($appName ,$file,"getEnv");

        if(isset(self::$_configPool[$appName][$file])  && self::$_configPool[$appName][$file] ){
            return self::$_configPool[$appName][$file];
        }
        $dir = self::$_dir ."/$appName/env/".ENV."/$file.php";
        self::$_configPool[$appName][$file] = include_once $dir;
        $GLOBALS[$appName][$file] = self::$_configPool[$appName][$file];
        return self::$_configPool[$appName][$file];
    }
    //$funcName 为空，代表引入全部
    static function includeKernelFuncFiles($funcName = "all"){
        self::debugLog("includeKernelFuncFiles","kernel" ,$funcName);
        if(!$funcName || $funcName == "all"){
            include_once FUNC_DIR.DS.'sys.php';//公共函数 - 系统
            include_once FUNC_DIR.DS.'datetime.php';//公共函数 - 时间日期
            include_once FUNC_DIR.DS.'path_file.php';//公共函数 -
            include_once FUNC_DIR.DS.'str_arr.php';//公共函数 - 字符串
            include_once FUNC_DIR.DS.'client.php';//公共函数 - 客户端信息
            include_once FUNC_DIR.DS.'url.php';//公共函数 - 客户端信息
        }else{
            include_once FUNC_DIR.DS .$funcName;
        }
    }
}

class ThrowErr {
    static $_CODE = [];
    static function initCode($appName){
        self::$_CODE[$appName] = ConfigCenter::get($appName,'err_code');
    }

    static function unknow($appName ,$code = null,array $replace = []){
        self::initCode($appName);
        self::process(1,$appName,$code,$replace);
    }

    static function exception($appName ,$code = null,array $replace = []){
        self::initCode($appName);
        self::process(2,$appName,$code,$replace);
    }

    static function process($type ,$appName , $code,$replace = []){
        if(!$code && $code !== 0 ){
            $msg = self::$_CODE[$appName][9995];
        }elseif(!isset(self::$_CODE[$appName][$code])){
            $msg = self::$_CODE[$appName][9994];
            $msg = self::replaceMsg($msg,array($code));
        }else{
            $msg = self::$_CODE[$appName][$code];
            if($replace){
                $msg = self::replaceMsg($msg,$replace);
            }
        }

        if($type == 1){
            echo $msg;
            exit;
        }else{
            throw new Exception($msg,$code);
            exit(" doing something...");
        }
    }

    static function replaceMsg($message,$replace = array()){
        foreach ($replace as $key => $v) {
            $message = str_replace("{" . $key ."}", $v,$message);
        }
        return $message;
    }
}
