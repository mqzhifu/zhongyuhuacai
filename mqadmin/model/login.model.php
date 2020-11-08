<?php
class LoginModel {
	static $_table = 'login';
	static $_pk = 'id';
	static $_db_key = DEF_DB_CONN;
	static $_db = null;

	static $_login_type_login = 1;
    static $_login_type_logout = 2;

    static function getTypeDesc(){
        return array(self::$_login_type_login=>'登入',self::$_login_type_logout=>'登出');
    }


	static function db(){
		if(self::$_db)
			return self::$_db;
		
		self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}
	
	static function login($uname,$ps){
		return self::db()->getRow(" uname = '$uname' and ps = '$ps'");
	}
	
	static function getName($uname){
		return self::db()->getRow(" uname = '$uname' ");
	}

	static function getUserByDate($uid,$start_time = null,$end_time =null){
        if(!$start_time || !$end_time){
            $dayTime = dayStartEndUnixtime();
        }else{
            $dayTime['s_time'] = $start_time;
            $dayTime['e_time'] = $end_time;
        }

        $where = " a_time >=  ".$dayTime['s_time']. " and a_time <= ".$dayTime['e_time'];
        return self::db()->getAll($where);

    }

    /**
     * XiaHB
     * 根据ip地址查询login库;
     * @param $count
     * @param $callback
     */
    public function queryByIp($count, $callback)
    {
        $page = 1;
        do {
            $limit = (($page - 1) * $count).", ".$count;
            $where = " ip IS NOT null AND addr IS null ";
            $sql = "SELECT id,ip,addr FROM ".self::$_table." WHERE ".$where." LIMIT ".$limit;
            $result = self::db()->query($sql);
            $countResult = count($result);
            if ($countResult == 0) {
                break;
            }
            if ($callback($result, $page) === false) {
                return false;
            }
            unset($result);
            $page++;
        } while ($countResult == $count);
        return true;
    }
}