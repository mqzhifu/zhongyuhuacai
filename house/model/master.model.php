<?php
class MasterModel {
	static $_table = 'master';
    static $_pk = 'id';
    static $_db = null;
    static $_db_key = DB_CONN_DEFAULT;

    static $_sex_male = 1;//男
    static $_sex_female = 2;//女

    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = new DbLib(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    public static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }

    static function getSexDesc(){
        return array(self::$_sex_male=>'男',self::$_sex_female=>'女');
    }

    static function keyInSex($key){
        return in_array($key,array_flip(self::getSexDesc()));
    }

    static function getSexDescByKey($key){
        if(!self::keyInSex($key)){
            return "未知";
        }
        $arr = self::getSexDesc();
        return $arr[$key];
    }

	
	static function login($uname,$ps){
//	    var_dump(" uname = '$uname' and ps = '$ps'");
		return self::db()->getRow(" uname = '$uname' and ps = '$ps'");
	}
	
	static function getName($uname){
		return self::db()->getRow(" uname = '$uname' ");
	}

	static function getOption(){
        $list = self::db()->getAll(1);
        $str = "";
        foreach($list as $k=>$v){
            $str.= "<option value='{$v['id']}'>{$v['nickname']}</option>";
        }

        return $str;
    }

    static function getFieldById($adminUid,$field){
	    if(!$adminUid){
            return "--";
        }
        $user = self::db()->getById($adminUid);
        if($user[$field]){
            return $user[$field];
        }

        return "默认昵称";

    }

    static function getSearchWhereByKeyword($keyword,$fieldName = ""){
        $where = "";

        $keywordWhere = " name like '%$keyword%' ";
        $list = self::db()->getAll($keywordWhere,null, " id ");
        if(!$list){
            $where .= " and 0";
        }else{
            $ids = "";
            foreach ($list as $k=>$v){
                $ids .= $v['id'] . " ,";
            }
            $ids = substr($ids,0,strlen($ids)-1);
            $where .= " and $fieldName in ( $ids ) ";
        }
        return $where;
    }
}