<?php
class WsLogModel {
	static $_table = 'ws_log_';
	static $_pk = 'id';
	static $_db_key = "kxgame_log";
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

	static function getTableByDay($day){
        $table  = self::$_table . $day;
        return $table;
    }

    static function getTable(){
	    $table  = self::$_table . date("Ymd");
	    return $table;
    }

    static function add($data){
	    return self::db()->add($data,self::getTable());
    }

    static function upById($id,$data){
        return self::db()->upById($id,$data,self::getTable());
    }



    static function countCalcDayData($day = ''){
        if(!$day){
            $table = self::getTable();
        }else{
            $table = self::getTableByDay($day);
        }

        $sql = "select count(id) as cnt ,uid,a_time,e_time,
                group_concat(device_id) as device_id,group_concat(ip) as ip,group_concat(a_time) as a_time,group_concat(e_time) as e_time 
                from ".$table." group by uid order by a_time,device_id,ip";

        return self::db()->getAllBySQL($sql);
    }

    static function getPartDataByDay($day = ''){
        if(!$day){
            $table = self::getTable();
        }else{
            $table = self::getTableByDay($day);
        }

        $sql = "select a_time,e_time,uid from ".$table."";

        return self::db()->getAllBySQL($sql);
    }
    static function gerETimeDataByHourTime($day, $hourTime){
        $table = self::getTableByDay($day);

        $startTime = $hourTime;
        $endTime = $startTime+3599;

        $sql = "select e_time,a_time,uid,reg_time from ".$table." where (e_time between $startTime and $endTime) and e_time!=0";
        return self::db()->getAllBySQL($sql);
    }
}