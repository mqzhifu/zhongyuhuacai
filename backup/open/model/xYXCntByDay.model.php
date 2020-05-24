<?php

class XYXCntByDayModel{
	static $_table = 'xyx_cnt_day';
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

	public static function getDataByGameIdAndDay($gameid, $dayTime){
		$startTime = strtotime(date("Y-m-d", $dayTime));
		$endTime = $startTime + 86399;
		$sql = " select * from xyx_cnt_day where game_id=$gameid and a_time between $startTime and $endTime ";
		return self::db()->getRowBySQL($sql);
	}


}