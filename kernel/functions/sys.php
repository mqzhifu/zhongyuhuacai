<?php
//类自动加载
function autoload($class){

    if( strpos($class,M_CLASS) !== false){

        $l = strpos($class,M_CLASS);
        $class = substr($class, 0,$l);
        $class = lcfirst($class);

        $file = APP_DIR .DS .M_DIR_NAME .DS . $class .M_EXT;
        include_once $file;
    }elseif( strpos($class,C_CLASS) !== false){

        $l = strpos($class,C_CLASS);
        $class = substr($class, 0,$l);
        $class = lcfirst($class);
        $file = APP_DIR .DS .C_DIR_NAME .DS . $class .C_EXT;
        include_once $file ;
    }elseif( strpos($class,S_CLASS) !== false){

        $l = strpos($class,S_CLASS);
        $class = substr($class, 0,$l);
        $class = lcfirst($class);

        include_once BASE_DIR .DS .KERNEL_NAME.DS. S_DIR_NAME .DS . $class .S_EXT;
    }elseif( strpos($class,LIB_CLASS) !== false){
        $l = strpos($class,LIB_CLASS);
        $class = substr($class, 0,$l);
        $class = lcfirst($class);
        include_once KERNEL_DIR .DS . LIB_DIR_NAME .DS . $class .LIB_EXT;
    }
//    elseif( strpos($class,"Request") !== false || strpos($class,"Reply") !== false){
//        include_once APP_CONFIG."/protobuf_class/". $class.".php" ;
//    }elseif( strpos($class,"Client") !== false ){
//        include_once APP_CONFIG."/protobuf_class/". $class.".php" ;
//    }
}


function is_robot($uid){
    $service = new UserService();
    $user = $service->getUinfoById($uid);
    if(arrKeyIssetAndExist($user,'robot') && $user['robot'] == 1){
            return 1;
    }

    return 0;
}

function get_os(){
    $os = strtoupper(substr(PHP_OS,0,3));
    return $os;
}

function get_br()
{
    $os = get_os();
    $echoMsg = "";
    if($os == "WIN"){
        $echoMsg .= "\r\n";
    }else{
        $echoMsg .= "\n";
    }

    return $echoMsg;
}
function sendToUid($uid,$data,$json = 0,$logType = ''){
//    if($logType == 'AIRobot'){
//        LogLib::AIRobotWriteFileHash(["send to uid ",$uid,$data]);
//    }
//
//    if(is_robot($uid)){
//        LogLib::wsWriteFileHash(" uid:$uid,is robot,don't send data.");
//        return true;
//    }

    if(!$GLOBALS['uid_fd_table']->exist($uid)){
        LogLib::wsWriteFileHash(["no uid"]);
        return false;
    }

    if($json){
        $data = json_encode($data);
    }

    $fd = $GLOBALS['uid_fd_table']->get($uid,'fd');
    $rs = $GLOBALS['ws_server']->push($fd,$data);

    LogLib::wsWriteFileHash(["@@@send ok!",'fd',$fd,'uid',$uid,'rs',$rs,$data]);
}

function getErrInfo($errno){
    $type = $errno;
    switch ($errno){
        case E_ERROR:
            $type = 'E_ERROR';break;
        case E_WARNING:
            $type = 'E_WARNING';break;
        case E_PARSE:
            $type = 'E_PARSE';break;
        case E_NOTICE:
            $type = 'E_NOTICE';break;
        case E_CORE_ERROR:
            $type = 'E_CORE_ERROR';break;
        case E_CORE_WARNING:
            $type = 'E_CORE_WARNING';break;
        case E_COMPILE_ERROR:
            $type = 'E_COMPILE_ERROR';break;
        case E_COMPILE_WARNING:
            $type = 'E_COMPILE_WARNING';break;
        case E_USER_ERROR:
            $type = 'E_USER_ERROR';break;
        case E_USER_WARNING:
            $type = 'E_USER_WARNING';break;
        case E_USER_NOTICE:
            $type = 'E_USER_WARNING';break;
        case E_STRICT:
            $type = 'E_STRICT';break;
        case E_RECOVERABLE_ERROR:
            $type = 'E_RECOVERABLE_ERROR';break;
        case E_DEPRECATED:
            $type = 'E_DEPRECATED';break;
        case E_USER_DEPRECATED:
            $type = 'E_USER_DEPRECATED';break;
    }

    return $type;
}

function jump($url){
    header("Location:".$url);
    exit;
}

function getSqlLog(){
		global $db_sql_cnt;
		$rs = "";
		foreach($db_sql_cnt as $k=>$v){
			$rs .= $v."<Br/>";
		}
		return $rs;
}
function microtime_float(){
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

//是否为AJAX请求
function isAjax() {
	if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) ) {
		if('xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
			return true;
	}
	if(!empty($_POST['ajax']) || !empty($_GET['ajax']))
		return true;
	return false;
}

function getAvatarByOid($oid){
	if(!$oid)
		return "/www/images/nouser.png";
	$uinfo = wxUserModel::db()->getRow(" openid = '".$oid."'");
	if(!$uinfo)
		return "/www/images/nouser.png";


	$uname = "/www/images/nouser.png";
	if( isset($uinfo['headimgurl']) && $uinfo['headimgurl'])
		$uname = $uinfo['headimgurl'];

	return $uname;
}

function getAdminAvatarid($id){
	if(!$id)
		return "/www/images/nouser.png";
	$uinfo = adminUserModel::db()->getById($id);
	if(!$uinfo)
		return "/www/images/nouser.png";


	$uname = "/www/images/nouser.png";
	if( isset($uinfo['avatar']) && $uinfo['avatar'])
		$uname = $uinfo['avatar'];

	return $uname;
}


function getUnameByOid($oid){
	if(!$oid)
		return "";
	$uinfo = wxUserModel::db()->getRow(" openid = '".$oid."'");
	if(!$uinfo)
		return "";


	$uname = "";
	if( isset($uinfo['nickname']) && $uinfo['nickname'])
		$uname = $uinfo['nickname'];

	return $uname;
}

function echo_json($data,$code = 200){
	$rs = array('data'=>$data,'code'=>$code);
	echo json_encode($rs,true);
	exit;
}

function admin_db_log_writer($msg,$admin_uid,$cate,$addtime = 0){
	if(!$msg || !$admin_uid || !$cate )
		return 0;

	if(!$addtime)
		$addtime = time();

	$ip = get_client_ip();
	$data = array('memo'=>$msg,'uid'=>$admin_uid,'add_time'=>$addtime,'IP'=>$ip,'cate'=>$cate);
	return AdminLogModel::db()->add($data);
}


//框架抛出异常-抛出异常
//function stop( $error ){
//	ExceptionFrameLib::halt($error );
//}
// PHP_SELF:当前所执行的脚本的文件名，如:b.com/test/test.php?k=v，则PHP_SELF的值为/test/test.php。
// SCRIPT_NAME：当前执行的脚本的路径，如:B.com/test/test.php?k=v，则SCRIPT_NAME的值为/test/test.php。
// SCRIPT_FILENAME：前执行的脚本的绝对路径，如:B.com/test/test.php?k=v，值 为/var/www/test/test.php。注：相对路径，CLI方式来执行，例如../test/test.php，即../test/test.php。
// PATH_INFO：客户端提供的路径信息，即在实际执行脚本后面尾随的内容，但是会去掉query string。如:b.com/test/test.php/a/b?k=v，则PATH_INFO的值为/a/bREQUEST_URI：包含HTTP协议中定义的URI内容。如果请求http://example.com/test/test.php?k=v，则REQUEST_URI为/test/test.php?k=v
// PHP_SELF和SCRIPT_NAME区别：b.com/test/test.php/a/b?k=v候，PHP_SELF为/test/test.php/a/b，SCRIPT_NAME为/test/test.php，可以看出PHP_SELF比SCRIPT_NAME多了PATH_INFO的内容。
//发现了一件很好玩的事情，WIN下，有个文件叫：admin.php,如果在地址栏里输入大写的：ADMIN.PHP,也是能执行的，而此时，$_SERVER['SCRIPT_FILENAME']的结果就是小写的：admin.php
function _get_script_url() {
	$rs = "";
	$scriptName = basename($_SERVER['SCRIPT_FILENAME']);
	if(basename($_SERVER['SCRIPT_NAME']) === $scriptName) {
		$rs = $_SERVER['SCRIPT_NAME'];
	}else if(basename($_SERVER['PHP_SELF']) === $scriptName) {
		$rs = $_SERVER['PHP_SELF'];
	} else if(($pos = strpos($_SERVER['PHP_SELF'],'/'.$scriptName)) !== false) {
		$rs = substr($_SERVER['SCRIPT_NAME'],0,$pos).'/'.$scriptName;
	}else{
		stop('文件名错误');
	}
	return $rs;
}
//seo robot 文件
function checkrobot($useragent = '') {
	static $kw_spiders = array('bot', 'crawl', 'spider' ,'slurp', 'sohu-search', 'lycos', 'robozilla');
	static $kw_browsers = array('msie', 'netscape', 'opera', 'konqueror', 'mozilla');

	$useragent = strtolower(empty($useragent) ? $_SERVER['HTTP_USER_AGENT'] : $useragent);
	if(strpos($useragent, 'http://') === false && dstrpos($useragent, $kw_browsers)) return false;
	if(dstrpos($useragent, $kw_spiders)) return true;
	return false;
}


function out_pc($code = 200,$msg = '',$appKey = APP_NAME){
    if(!$msg){
        if($msg === '' || $msg === null){//弱类型，防止0或者空数组被忽略
            if(arrKeyIssetAndExist($GLOBALS[$appKey]['err_code'],$code)){
                $msg = $GLOBALS[$appKey]['err_code'][$code];
            }
        }
    }
    return array('code'=>$code,'msg'=>$msg);
}
function out_ajax($code = 500,$msg = "",$appKey = APP_NAME){
//    header('Content-Type:application/json; charset=utf-8');
    if($msg === ""){
        $msg = $GLOBALS['code'][$code];
    }
    echo json_encode(array('code'=>$code,'msg'=>$msg));
    exit;
}
//输出
function out($info ,$br = 1){
    $msg = $info;
    if($br){
        $os = getOs();
        if (preg_match("/cli/i", php_sapi_name())){
            if($os == "WIN"){
                $msg .= "\r\n";
            }else{
                $msg .= "\n";
            }
        }else{
            $msg .=  "<br/>";
        }
    }
    echo $msg;
}

function getEmailHref($email){
    $a_email = array(
        'sina'=>'mail.sina.com.cn',
        '163'=>'mail.163.com',
        'qq'=>'mail.qq.com',
        '126'=>'mail.126.com',
        'hotmail'=>'login.live.com',
        'gmail'=>'mail.google.com',
        'yahoo'=>'mail.aliyun.com',
        'aliyun'=>'mail.aliyun.com'
    );

    preg_match_all('/@(.*?)\./',$email,$a_href);
    if( isset($a_href[1][0])){
        if(  isset(  $a_email[$a_href[1][0]] ) ){
            return $a_email[$a_href[1][0]];
        }
    }

    return "#";
}

// 取得对象实例 支持调用类的静态方法
function get_instance_of($name, $method='', $args=array()) {
	static $_instance = array();
// 	$identify = empty($args) ? $name . $method : $name . $method . to_guid_string($args);
	if ( ! isset($_instance[$name]) ) {
		if (class_exists($name)) {
			$o = new $name();
			$_instance[$name] = $o;
// 			if (method_exists($o, $method)) {
// 				if (!empty($args)) {
// 					$_instance[$name] = call_user_func_array(array(&$o, $method), $args);
// 				} else {
// 					$_instance[$name] = $o->$method();
// 				}
// 			}else{
// 				$_instance[$name] = $o;
// 			}
// 		}else{
// 			stop("new class:". $name);
		}else{
            ExceptionFrameLib::throwCatch("class not exists:". $name);
        }
	}
	return $_instance[$name];
}
// 获取配置值
//function C($name=null, $value=null) {
//	static $_config = array();
//	// 无参数时获取所有
//	if (empty($name))   return $_config;
//	// 优先执行设置获取或赋值
//	if (is_string($name)) {
//		if (!strpos($name, '.')) {
//			$name = strtolower($name);
//			if (is_null($value))
//				return isset($_config[$name]) ? $_config[$name] : null;
//			$_config[$name] = $value;
//			return;
//		}
//		// 二维数组设置和获取支持
//		$name = explode('.', $name);
//		$name[0]   =  strtolower($name[0]);
//		if (is_null($value))
//			return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : null;
//		$_config[$name[0]][$name[1]] = $value;
//		return;
//	}
//	// 批量设置
//	if (is_array($name)){
//		return $_config = array_merge($_config, array_change_key_case($name));
//	}
//	return null; // 避免非法参数
//}
function ajax_stop($msg){
	$js = array('err'=>1,'msg'=>$msg,'data'=>'');
	echo json_encode($js);
	exit;
}


//GET POST REQUEST
function _g( $name ) {
    $ret = false;
	if (isset($_POST[$name])) {
        $ret = $_POST[$name];
    }elseif (isset($_GET[$name])) {
        $ret = $_GET[$name];
    }elseif (isset($_REQUEST[$name])) {
        $ret = $_REQUEST[$name];
    }
	if(!is_array($ret)){
 		$ret = trim($ret);         		//清理空格字符
// 		$ret = nl2br($ret);         	//将换行符转化为<br />
// 		$ret = strip_tags($ret);      	//过滤文本中的HTML标签
// 		$ret = htmlspecialchars($ret); 	//将文本中的内容转换为HTML实体
 		// $ret = addslashes($ret);      	//加入字符转义
	}

	return $ret;
}
//发送HTTP状态
function send_http_status($code) {
	static $_status = array(
	// Success 2xx
			200 => 'OK',
			// Redirection 3xx
			301 => 'Moved Permanently',
			302 => 'Moved Temporarily ',  // 1.1
			// Client Error 4xx
			400 => 'Bad Request',
			403 => 'Forbidden',
			404 => 'Not Found',
			// Server Error 5xx
			500 => 'Internal Server Error',
			503 => 'Service Unavailable',
	);
	if(isset($_status[$code])) {
		header('HTTP/1.1 '.$code.' '.$_status[$code]);
		// 确保FastCGI模式下正常
		header('Status:'.$code.' '.$_status[$code]);
	}
}

function getOs(){
    $os = strtoupper(substr(PHP_OS,0,3));
    return $os;
}

function getAppSmarty($path = ''){
	$Template = get_instance_of('TemplateLib');
	$Template->setPath(APP_DIR."/view/");
	$Template->setCompilePath( APP_SMARTY_COMPILE_DIR);
	return $Template;
}

function getKernelSmarty($path = ''){
    $Template = get_instance_of('TemplateLib');
    $Template->setPath(KERNEL_DIR."/view/");
    $Template->setCompilePath( KERNEL_SMARTY_COMPILE_DIR);
    return $Template;
}


function setSmartyPath($path){
	$SmartyClass = get_instance_of('Smarty');
	$SmartyClass->setTemplateDir($path);
}
//取得数据库实例
function getDb($dbName){
	static $dbLink = array();
	if(!isset($dbLink[$dbName])){
		$f = 0;
		foreach($GLOBALS['db_config'] as $k=>$v){
			if($k == $dbName){
				$f = 1;
				$config = $v;
			}
		}
		if(!$f){
			stop('DB_config error','no');
		}
		// 		include 'Model.class.php';
		$db = new DbLib($config);
		$dbLink[$dbName] =  $db;
	}
	return $dbLink[$dbName];
}
// 根据PHP各种类型变量生成唯一标识号
function to_guid_string($mix) {
	if (is_object($mix) && function_exists('spl_object_hash')) {
		return spl_object_hash($mix);
	} elseif (is_resource($mix)) {
		$mix = get_resource_type($mix) . strval($mix);
	} else {
		$mix = serialize($mix);
	}
	return md5($mix);
}
// 设置和获取统计数据
function N($key, $step=0) {
	static $_num = array();
	if (!isset($_num[$key])) {
		$_num[$key] = 0;
	}
	if (empty($step))
		return $_num[$key];
	else
		$_num[$key] = $_num[$key] + (int) $step;
}
// 记录和统计时间（微秒）
function G($start,$end='',$dec=4) {
	static $_info = array();
	if(is_float($end)) { // 记录时间
		$_info[$start]  =  $end;
	}elseif(!empty($end)){ // 统计时间
		if(!isset($_info[$end])) $_info[$end]   =  microtime(TRUE);
		return number_format(($_info[$end]-$_info[$start]),$dec);
	}else{ // 记录时间
		$_info[$start]  =  microtime(TRUE);
	}
}
// 显示运行时间
function showtime() {
	global $db_sql_cnt;
	$process_time = microtime(TRUE) - $GLOBALS['start_time'] ;
	$showTime   =   'Process: '.$process_time.'s ';
	// 显示数据库操作次数
	$showTime .= ' | DB_select :'.N('db_query').' ,DB_write '.N('db_write');
	// 显示内存开销
	$showTime .= ' | UseMem:'. number_format((memory_get_usage() - $GLOBALS['start_use_mems'])/1024).' kb';
	$showTime .= ' | LoadFile:'.count(get_included_files());
	$fun  =  get_defined_functions();
	$showTime .= ' | CallFun:'.count($fun['user']).','.count($fun['internal']);
	return $showTime;
}

function get_mac($os_type) {
	switch (strtolower ( $os_type )) {
		case "linux" :
			forLinux ();
		case "darwin" : // 苹果系统
			forLinux ();
			break;
		case "solaris" :
			break;
		case "unix" :
			break;
		case "aix" :
			break;
		default :
			$this->forWindows ();
			break;
	}
	
	$temp_array = array ();
	foreach ( $this->return_array as $value ) {
		
		if (preg_match ( "/[0-9a-f][0-9a-f][:-]" . "[0-9a-f][0-9a-f][:-]" . "[0-9a-f][0-9a-f][:-]" . "[0-9a-f][0-9a-f][:-]" . "[0-9a-f][0-9a-f][:-]" . "[0-9a-f][0-9a-f]/i", $value, $temp_array )) {
			$this->mac_addr = $temp_array [0];
			break;
		}
	}
	unset ( $temp_array );
	return $this->mac_addr;
}

function forWindows() {
	exec ( "ipconfig /all", $this->return_array );
	if ($this->return_array)
		return $this->return_array;
	else {
		$ipconfig = $_SERVER ["WINDIR"] . "\system32\ipconfig.exe";
		if (is_file ( $ipconfig ))
			@exec ( $ipconfig . " /all", $this->return_array );
		else
			@exec ( $_SERVER ["WINDIR"] . "\system\ipconfig.exe /all", $this->return_array );
		return $this->return_array;
	}
}
function forLinux() {
	//Ether——由ARP使用的以太网地址（MAC）
	exec ( "/sbin/ifconfig", $this->return_array, $r );
	return $this->return_array;
}

function getDbConst($key){
	static  $globalDb = array();
	if(!$globalDb){
		$db = getDb(DEF_DB_CONN);
		$sql = "select * from const";
		$globalDb = $db->getAllBySQL($sql);
	}
	
	return $globalDb[$key];
}





function dump($var, $echo=true, $label=null, $strict=true) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    }else
        return $output;
}



function get_path_by_id($photoid){
    $photoid_key = "uthing$#267$#889h6";
    if(!$photoid) return;

    $baseNum = 10000; //每xx个文件新增加一个文件夹
    $subbaseNum = 100;

    $folder1 = intval($photoid/$baseNum);
    $folder2 = intval( ($photoid-$folder1*$baseNum)/$subbaseNum );

    $str = str_encode($photoid, $photoid_key);
    $str = str_replace(array("/", "+"), array("_", "-"), $str);

    $securefolder = $str;
    $path = "{$folder1}/{$folder2}/{$securefolder}";

    return $path;
}


function get_app_download_info($andriod_download_url,$log_download_url){
	$os_info = get_useragent_OS();
	$rs = array('os_info'=>$os_info);
	$rs['browser_info'] = get_useragent_browser();

	if(is_weixin()){
		$rs['is_wx'] = "true";
	}else{
		$rs['is_wx'] = "false";
	}


	$js = '
    function download_app(){
        var hr = encodeURI("'.$log_download_url.'");
        var content = "topnews_download-";

        var OS = "'.$rs['os_info']['os'].'";
        var is_weixin = '.$rs['is_wx'].';
        if(is_weixin){
            content += "wenxin";
            if("ios" == OS){
                content += "wenxin-ios";
                push_server(hr,content);
                var url = "http://a.app.qq.com/o/simple.jsp?pkgname=com.uthing";
                location.href= url;
            }else{
                content += "wenxin-android";
                push_server(hr,content);
                location.href="http://a.app.qq.com/o/simple.jsp?pkgname=com.uthing";
            }
        }else{
            if("ios" == OS){
                content += "ios";
                push_server(hr,content);
                location.href="itms-apps://itunes.apple.com/cn/app/id952955630";
            }else{
                content += "android";
                push_server(hr,content);
                location.href="'.$andriod_download_url.'";
            }
        }


    }

    function push_server(hr,content){
        content = encodeURI(content);
        var url = '.PRODUCT_URL.' "/?action=topcnt&hr="+hr+"&content="+content;
//        alert(url);
        $.ajax({
            url: url,
            dataType: "json",
            success: function(){}
        });
    }';

	$rs['js'] = $js;
	return $rs;

}

//function getServiceMax(){
//    return 15;
//}

////防止REDIS重启
//function msg_redis_exist(){
//    return "msg_redis_exist";
//}
////用户部分群发站内信未读数
//function usr_msg_part_unread($uid){
//    return "usr_msg_part_unread_".$uid;
//}
////用户群发站内信未读数
//function usr_msg_group_unread($uid){
//    return "usr_msg_group_unread_".$uid;
//}
////站内信相关的REDIS~KEY
//function sys_group_msg_key(){
//    return "sys_group_msg_";
//}
////用户群发KEY
//function usr_group_msg_key($uid){
//    return "usr_group_msg_".$uid;
//}
////用户未读数
//function usr_msg_unread_key($uid){
//    return "usr_msg_unread_".$uid;
//}
////用户部分群发KEY
//function usr_msg_part_key($uid){
//    return "usr_msg_part_".$uid;
//}

//function getAdminUnameByid($id){
//	if(!$id)
//		return "客服1";
//	$uinfo = adminUserModel::db()->getById($id);
//	if(!$uinfo)
//		return "客服2";
//
//	$uname = "客服3";
//	if( isset($uinfo['nickname']) && $uinfo['nickname'])
//		$uname = $uinfo['nickname'];
//
//	return $uname;
//}

//单实例模式
// function Singleton ($className){
// 	static $_instens = array();
// 	if(!isset($_instens[$className])){
// 		$_instens[$className] = new $className;
// 	}
// 	return $_instens[$className];
// }


