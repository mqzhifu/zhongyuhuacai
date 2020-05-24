<?php
class AccesslogModel {
	static $_table = 'access_log';
	static $_pk = 'id';
	static $_db_key = DEF_DB_CONN;
	static $_db = null;

	static function db(){
		if(self::$_db)
			return self::$_db;
		
		self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}
	
	static function getName($uname){
		return self::db()->getRow(" uname = '$uname' ");
	}

	static function getById($uid){
        $user = self::db()->getById($uid);
        if($user){

        }
    }
    //默认取当前时间 - 5分钟 内，用户的总访问次数
    static function getUserReqCntByTime($uid,$time = 300){
        $time = time() - $time;
        return self::db()->getCount(" uid = $uid and a_time > $time");
    }

    //默认取当前时间 - 5分钟 内，用户的总访问次数
    static function getIPReqCntByTime($IP,$time = 300){
        $time = time() - $time;
        return self::db()->getCount(" IP = '$IP' and a_time > $time");
    }

    static function addReq(){
        $contentType = get_client_content_type();
        $request = $_REQUEST;
	    if($contentType == 'application/json'){
            $data = file_get_contents("php://input");
//            echo 2222;
//            var_dump($request);
//            var_dump($data);
            if($data){
                $data = json_decode($data,true);
                if($data && is_array($data)){
                    $request = array_merge($request,$data);
                }
            }
        }
	    $data = array(
	        'ctrl'=>CTRL,
            'AC'=>AC,
            'a_time'=>time(),
            'IP'=>get_client_ip(),
            'request'=>json_encode($request),
        );

	    $id = self::db()->add($data);
	    return $id;
    }

    static function upInfo(){

    }
	
}