<?php
class MenuModel {
	static $_table = 'menu';
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

    static function getMenu($ids='all'){
        if ($ids=='all') {
            $menus = self::db()->getAll();
            $ids = array_column($menus, 'id');
            $ids = implode(",", $ids);
        }
//        // 新增area_code查询字段，用以区分国内外菜单;
//        $area_code = (PCK_AREA == 'cn')?1:2;
        $root = self::db()->getAll(" pid = 0 AND is_show = 1 and id in ($ids) order by sort DESC ");
        foreach ($root as $k=>$v) {
            // $sub = self::db()->getAll( "pid = ".$v['id']);
            $sub = self::db()->getAll( "pid = {$v['id']} AND is_show = 1   AND id in ($ids) order by sort ASC");
            foreach ($sub as $k2=>$v2) {
                if(arrKeyIssetAndExist($v2,'dir_name')){
                    // $three = self::db()->getAll( "pid = ".$v2['id']);
                    $three = self::db()->getAll( "pid = {$v2['id']} AND  is_show = 1  AND id in ($ids) ");
                    $sub[$k2]['sub'] = $three;
                }
            }
            $root[$k]['sub'] = $sub;
        }

        return $root;

    }
}