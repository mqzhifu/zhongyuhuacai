<?php
class bannerModel {
    static $_table = 'banner';
    static $_pk = 'id';
    static $_db_key = "instantplay";
    static $_db = null;

    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = new DbLib(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    public static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }
    //获取首页轮播图
    static function getIndexList(){
        $list = BannerModel::db()->getAll();
        return self::format($list);
    }

    static function format($list){
        if(!$list){
            return $list;
        }

        $data = null;
        foreach ($list as $k=>$v){
            $row = $v;
            if(arrKeyIssetAndExist($v,'pic')){
                $row['pic'] = get_banner_url($v['pic']);
            }
            $data[] = $row;
        }

        return $data;
    }
}