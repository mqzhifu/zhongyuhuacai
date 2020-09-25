<?php
class TopicModel {
	static $_table = 'topic_exchange';
	static $_pk = 'id';
	static $_db = null;
    static $_db_key = "instantplay";

    const TYPE_HEADERS = "headers";
    const TYPE_DIRCET = "direct";
    const TYPE_FANOUT = "fanout";
    const TYPE_TOPIC = "topic";
    const TYPE_X_DELAY_MESSAGES = "x-delay-messages";


    const TYPE_DESC = array(
        self::TYPE_HEADERS=>"头部KV匹配",
        self::TYPE_DIRCET=>"精准匹配关键字",
        self::TYPE_FANOUT=>"广播",
        self::TYPE_TOPIC=>"模糊匹配关键字",
        self::TYPE_X_DELAY_MESSAGES=>"延迟",
    );

    const TYPE_NORMAL_DESC = array(
        self::TYPE_HEADERS=>"头部KV匹配",
        self::TYPE_DIRCET=>"精准匹配关键字",
        self::TYPE_FANOUT=>"广播",
        self::TYPE_TOPIC=>"模糊匹配关键字",
    );

	static function db(){
		if(self::$_db)
			return self::$_db;

		self::$_db = new DbLib(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}

	static function getTypeOptions(){
	    $html = "";
	    foreach (self::TYPE_DESC as $k=>$v){
            $html .= "<option value='$k'>$v</option>";
	    }
	    return $html;
    }

    static function getNormalTypeOptions(){
        $html = "";
        foreach (self::TYPE_NORMAL_DESC as $k=>$v){
            $html .= "<option value='$k'>$v</option>";
        }
        return $html;
    }

    static function getOption(){
        $list = self::db()->getAll(1);
        $str = "";
        foreach($list as $k=>$v){
            $str.= "<option value='{$v['id']}'>{$v['name']}</option>";
        }

        return $str;
    }

    static function getFieldById($adminUid,$field,$defaultStr = '--'){
        if(!$adminUid){
            return $defaultStr;
        }
        $user = self::db()->getById($adminUid);
        if(!$user){
            return $defaultStr;
        }

        if($user[$field]){
            return $user[$field];
        }

        return $defaultStr;

    }
}