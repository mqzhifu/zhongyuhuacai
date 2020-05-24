<?php
//会话控制
class SessionLib{
	public $config = array();
	public $expire =  SESS_EXPIRE;
	public $_sessType = SESS_TYPE;
	public $_work = null;
	function __construct(){
		//session_id();//设置SESSION_ID 的值
		//session_name('PHPSESSID');//浏览器与服务器传送SID的变量名，如：PHPSESSID=12345
//		ini_set('session.gc_maxlifetime', SESS_EXPIRE);//GC垃圾回收时间
//		ini_set('session.use_cookies', 1);//是否用COOKIE保存SID
		if('DB' == SESS_TYPE){
			$this->_work = new SessionDbLib();
		}
// 		$this->sessKey = $this->getSessKey();
        if(!session_id()){
            session_start();
        }
	}
	
// 	function getSessKey(){
// 		if(ACCESS_TYPE == 'ADMIN')
// 			return SESS_ADMIN_KEY;
// 		else
// 			return SESS_USER_KEY;
// 	}
	
	function setSession($userInfo){
		$_SESSION[APP_NAME] = $userInfo;
	}
	
	function getSession(){
		return $_SESSION[APP_NAME] ;
	}

	function getValue($key){
		if(isset($_SESSION[APP_NAME][$key])){
			return $_SESSION[APP_NAME][$key] ;
		}
	}
	
	function setValue($key,$value){
		return $_SESSION[APP_NAME][$key] = $value ;
	}

	function none(){
		session_unset();
		session_destroy();
	}
	
	function getImgCode(){
		return $_SESSION['img_code'];
	}
	
	function delImgCode(){
		$_SESSION['img_code'] = null;
	}
	
	function setImgCode($code){
		$_SESSION['img_code'] = $code;
	}
}
