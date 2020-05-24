<?php

/**
 * @Author: xuren
 * @Date:   2019-06-04 11:03:31
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-06-04 16:24:29
 */
class TagsDetailModel {
    static $_table = 'tags_detail';
    static $_pk = 'id';
    static $_db_key =DEF_DB_CONN;
    static $_db = null;

    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }

    // 减少引用数
    static function addRefNum($tag_id){
    	$res = self::db()->getRow("tag_id=$tag_id");
    	if($res && isset($res['ref_num'])){
    		$ref_num  = $res['ref_num'] + 1;
    		$res2 = self::db()->update(['ref_num'=>$ref_num], "tag_id=$tag_id");
    		if($res2){
    			return true;
    		}
    	}

    	return false;
    }

    // 增加引用数
    static function reduceRefNum($tag_id){
    	$res = self::db()->getRow("tag_id=$tag_id");
    	if($res && isset($res['ref_num'])){
    		$ref_num  = $res['ref_num'] - 1;
    		if($ref_num >= 0){
    			$res2 = self::db()->update(['ref_num'=>$ref_num], "tag_id=$tag_id");
	    		if($res2){
	    			return true;
	    		}
    		}
    	}

    	return false;
    }

    /**
     * 获取所有tag 描述 id=>name 形式
     * @return [type] [description]
     */
    static function getAllTagsDesc(){
        $data = self::db()->getAll();
        if($data && isset($data[0]['tag_id']) && isset($data[0]['tag_name'])){
            return array_column($data ,'tag_name', 'tag_id');
        }else{
            return [];
        }
    }

}