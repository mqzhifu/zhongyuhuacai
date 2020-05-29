<?php
class UserCommentModel {
	static $_table = 'user_comment';
    static $_pk = 'id';
    static $_db = null;
    static $_db_key = "instantplay";


    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = new DbLib(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }
    public static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }

    static function getListByUid($uid){
        $list = self::db()->getAll(" uid = $uid");
        $list = self::format($list);
        return $list;
    }

    static function getListByPid($pid){
        $list = self::db()->getAll(" pid = $pid");
        $list = self::format($list);
        return $list;
    }

    static  function format($list){
        if(!$list){
            return $list;
        }

        $data = null;
        foreach ($list as $k=>$v){
            $row = $v;
            if(arrKeyIssetAndExist($v,'pic')){
                $row['pic'] = get_comment_url($v['pic']);
            }
            $data[] = $row;
        }

        return $data;
    }


}