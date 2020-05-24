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

    static function getNearUserByMysql($lat,$lon,$km = 1){
        $m = $km * 1000;
        $field = mysql_gps_distance_field($lat,$lon,'lat','lon');
        $sql = "select uid,lat,log,$field as distance from ".self::$_table." where distance < $m order by distance asc ";
    }

    static function getNearUserByGeoHash($userGeoCode,$sex = "",$lastHour = "",$n = 6){
        $likeGeohash = substr($userGeoCode, 0, $n);
        $where = 'gps_geo_code like "%'.$likeGeohash.'%"';
        if($sex){
            $where .= " and sex = $sex ";
        }

        if($lastHour){
            $time = time()-  $lastHour * 60 *60;
            $where .= " and a_time >= $time ";
        }


        $sql = 'select uid,sex,a_time,lat,lon from '.self::$_table.' where '.$where ." group by uid order by gps_geo_code asc";
//        echo $sql;
        return self::db()->getAllBySQL($sql);
    }


}