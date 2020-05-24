<?php
//会话存DB
class SessionDbLib{
	
	public $db_config = array(
						'uid'=>'','login_ip'=>'','login_time'=>'','uname'=>'','expire'=>'',
						'detail'=>"",'role_id'=>'','resource'=>'','type'=>'','user_type'=>''
	);
	public $config = array();
	public $expire =  SESS_EXPIRE;
	public $_sessType = SESS_TYPE;
	public $mExpireTime = 0;
	function __construct(){
		if(ini_get('session.save_handler') != 'user'){
			stop('session type error~');
		}
		
		$this->db = getDb(DEF_DB_CONN);
		session_set_save_handler(
				array(&$this, 'open'), //在运行session_start()时执行
				array(&$this, 'close'), //在脚本执行完成或调用session_write_close() 或 session_destroy()时被执行,即在所有session操作完后被执行
				array(&$this, 'read'), //在运行session_start()时执行,因为在session_start时,会去read当前session数据
				array(&$this, 'write'), //此方法在脚本结束和使用session_write_close()强制提交SESSION数据时执行
				array(&$this, 'destroy'), //在运行session_destroy()时执行
				//session.gc_maxlifetime = 1440,
				//执行概率:session.gc_probability 和 session.gc_divisor的值决定,open,read之后,session_start之后,open,read和gc
				array(&$this, 'gc')
		);
	}

	public function open($savePath, $sessionName){
		return true;
	}
	
	public function close(){
		return true;
	}
	
	public function read($id){
		$user = SessionModel::db()->getRow( " session_id = '" .$id ."' and user_type = '".ACCESS_TYPE."'");
		if($user){
			if( time() > $user['expire'] ){
				$this->destroy($id);
			}else{
				return $user['data'];
			}
		}
	}
	
	public function write($id, $session_data){
		if(!$session_data)return 0;
		$data = $this->unserializes($session_data);
		$user = SessionModel::db()->getRow( " session_id = '" .$id ."' and user_type = '".ACCESS_TYPE."'");
		$expire =  $this->getExpire();
		if(!$user){
			if(isset($data['img_code'])){//图片验证码
				$addData = array();
				$addData['expire'] = $expire;
				$addData['session_id'] = $id;
				$addData['data'] = $session_data;
				$addData['user_type'] = USER_TYPE;
				// 				var_dump($addData);exit;
				$this->db->add($addData, 'session');
			}else{
// 				foreach($this->db_config as $k1=>$v1){
// 					$f = 0;
// 					foreach($data[SESS_KEY_NAME] as $k2=>$v2){
// 						if($k1 == $k2){
// 							if(!$v2)stop('SESSION value 错误(write)');
// 							$f = 1;
// 							break;
// 						}
// 					}
// 					if(!$f)stop('SESSION key 错误(write)');
// 				}
				$addData = array();
				$addData['expire'] =$expire;
				if(ACCESS_TYPE =='ADMIN')
					$uid = $data[ACCESS_TYPE]['admin_uid'];
				else
					$uid = $data[ACCESS_TYPE]['uid'];
				
				$addData['uid'] = $uid;
				$addData['session_id'] = $id;
				$addData['data'] = $session_data;
// 				$addData['login_time'] = $data[SESS_KEY_NAME]['login_time'];
// 				$addData['login_ip'] = $data[SESS_KEY_NAME]['login_ip'];
// 				$addData['type'] = USER_TYPE;
				$addData['user_type'] =ACCESS_TYPE;
				SessionModel::db()->add($addData, 'session');
			}
		}else{
			if( time() > $user['expire'] ){
				$this->destroy($id);
				exit;
			}
			$upData = array();
// 			if($data['img_code']){//图片验证码
	
// 			}else{
// 				foreach($this->db_config as $k1=>$v1){
// 					if('expire' == $k1)continue;
// 					$f = 0;
// 					foreach($data[SESS_KEY_NAME] as $k2=>$v2){
// 						if($k1 == $k2){
// 							if(!$v2)stop('SESSION value 错误(write)');
// 							$f = 1;
// 							break;
// 						}
// 					}
// 					if(!$f)stop('SESSION key 错误(write)');
// 				}
// 			}
				
// 			$upData['login_time'] = $data[ACCESS_TYPE]['login_time'];
// 			$upData['login_ip'] = $data[SESS_KEY_NAME]['login_ip'];

			if(ACCESS_TYPE =='ADMIN')
				$uid = $data[ACCESS_TYPE]['admin_uid'];
			else
				$uid = $data[ACCESS_TYPE]['uid'];
			
			$upData['expire'] = $expire;
			$upData['data']   = $session_data;
			$upData['uid'] = $uid;
// 			$upData['type'] = USER_TYPE;
			$upData['user_type'] = ACCESS_TYPE;
				
			SessionModel::db()->update($upData, '', "session_id = '" .$id   ."' and user_type = '".ACCESS_TYPE."' limit 1 ");
		}
	}
	
	public function destroy($id){
		//$sql = "update session set expire = -1,session_id = 'expire',data = '-1' where session_id = '" .$id ."' and user_type = '".ACCESS_TYPE."' limit 1";
		$sql = "delete from session where session_id = '$id' limit 10";
		$this->db->execute($sql);
		return true;
	}
	
	public function gc($maxlifetime){
		$t = time();
		//$sql = "update session set expire = -1,session_id = 'expire',data = '-1' where expire < $t limit 1";
		$sql = "delete from session where expire < $t limit 10 ";
		$this->db->execute($sql);
		return true;
	}
	
// 	function setSession($userInfo){
// 		$userInfo['expire'] = $this->getExpire();
// 		foreach($this->db_config as $k1=>$v1){
// 			if('resource' == $k1)continue;
// 			$f = 0;
// 			foreach($userInfo as $k2=>$v2){
// 				if($k1 == $k2){
// 					if(!$v2)stop('SESSION value 错误(setSession)');
// 					$f = 1;
// 					break;
// 				}
// 			}
// 			if(!$f)stop('SESSION key 错误(setSession)');
// 		}
// 		$_SESSION[SESS_KEY_NAME] = $userInfo;
// 	}
	
	function getExpire(){
		if($this->mExpireTime){
			return $this->mExpireTime;
		}else{
			$this->mExpireTime = time() + $this->expire;
			return $this->mExpireTime;
		}
	}
	
	function unserializes($data_value) {
		$vars = preg_split(
				'/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\|/',
				$data_value, -1, PREG_SPLIT_NO_EMPTY |
				PREG_SPLIT_DELIM_CAPTURE
		);
		for ($i = 0; isset($vars[$i]); $i++) {
			$result[$vars[$i++]] = unserialize($vars[$i]);
		}
		return $result;
	}
}
?>