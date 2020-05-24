<?php
class AppVersionModel {
	static $_table = 'app_version';
	static $_pk = 'id';
	static $_db = null;
    static $_db_key = DEF_DB_CONN;


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

    /**
     * 获取最新的版本信息;
     * @return array
     */
	public function getOneRow(){
        $sql = "SELECT * FROM ".self::$_table." ORDER BY id DESC LIMIT 1 ;";
        $result = self::db()->query($sql);
        if(!empty($result[0]) && is_array($result[0])){
            return $result[0];
        }else{
            return [];
        }
    }

    /**
     * 更新最新的版本信息;
     * @param $id
     * @param $data
     * @return int
     */
    public function addInfo($data)
    {
        $insertData = [];
        // 整理数据
        $insertData["status"] = 0;
        $insertData["version_code"] = $data['version_code'];
        $insertData["api_version"] = $data["api_version"];
        $insertData["version_name"] = $data["version_name"];
        $insertData["app_force"] = $data["app_force"];
        $insertData["size"] = '16M';
        $insertData["machine_code"] = 1;// Android;
        $insertData["update_url"] = 'http://mgres.kaixin001.com.cn/xyx/static/downLoad/android/kaixin_xyx.apk';
        $insertData["summary"] = $data["summary"];
        $insertData['a_time'] = time();
        $result = self::db()->add($insertData);
        return $result;
    }
	
}