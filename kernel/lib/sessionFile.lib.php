<?php
//会话存文件
class SessionFileLib{
	function setSession($userInfo,$key){
		$_SESSION[$key] = $userInfo;
	}
	
	function getSession($key){
		if(isset($_SESSION[$key])){
			return $_SESSION[$key] ;
		}
	}

	function getValue($key,$sess_key){
		if(isset($_SESSION[$sess_key][$key])){
			return $_SESSION[$sess_key][$key] ;
		}
	}
	
	function setValue($key,$value,$sess_key){
		return $_SESSION[$sess_key][$key] = $value ;
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
