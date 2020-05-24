<?php
class ImMsgModel {
	static $_table = 'im_msg';
	static $_pk = 'id';
	static $_db_key = DEF_DB_CONN;
	static $_db = null;


	static $_type_text = 1;
    static $_type_voice = 3;
    static $_type_img = 2;
    static $_type_game = 4;

	static function db(){
		if(self::$_db)
			return self::$_db;
		
		self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}
	
	static function filter($str,$replaceStr = "***"){
        $data  = self::db()->getAll(" 1 ",null,"name");
        if(!$data){
            return $str;
        }

        foreach($data as $k=>$v){
            $str =  str_replace($v['name'],$replaceStr,$str) ;
        }

        return $str;
    }

    static function getTypeDesc(){
        return array(
            self::$_type_text=>'文本',
            self::$_type_img=>'图片',
            self::$_type_voice=>'语音',
            self::$_type_game=>'游戏邀请'
        );
    }

    static function keyInType($key){
        return in_array($key,array_flip(self::getTypeDesc()));
    }
	
}