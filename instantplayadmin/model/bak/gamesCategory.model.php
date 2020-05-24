<?php
class GamesCategoryModel {
	static $_table = 'games_category';
	static $_pk = 'id';
	static $_db_key = DEF_DB_CONN;
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
	
	static function login($uname,$ps){
		return self::db()->getRow(" uname = '$uname' and ps = '$ps'");
	}
	
	static function getName($uname){
		return self::db()->getRow(" uname = '$uname' ");
	}

	static function getById($uid){
        $user = self::db()->getById($uid);
        if($user){

        }
    }

    static function getNameAndParentsname($subId){
        $row = self::db()->getById($subId);
        if(!$row){
            return "--";
        }

        if(!$row['pid']){
            return $row['name']."({$row['name_cn']})";
        }

        $p = self::db()->getById($row['pid']);
        if(!$p){
            return $row['name']."({$row['name_cn']})";
        }

        return $p['name']."({$p['name_cn']})"."-". $row['name']."({$row['name_cn']})";;
    }


    static function getOptions($selectId = 0){
        $categoryOne = GamesCategoryModel::db()->getAll(" pid = 0");
        $str = "";
        foreach($categoryOne as $k=>$v){
//
//                                                <optgroup label="Picnic">
//                                                    <option>Mustard</option>
//                                                    <option>Ketchup</option>
//                                                    <option>Relish</option>
//                                                </optgroup>
//                                                <optgroup label="Camping">
//                                                    <option>Tent</option>
//                                                    <option>Flashlight</option>
//                                                    <option>Toilet Paper</option>
//                                                </optgroup>


            $str .= "<optgroup label='{$v['name']}({$v['name_cn']})'>";
            $second = GamesCategoryModel::db()->getAll(" pid = ".$v['id']);
            foreach($second as $k2=>$v2){
                $selected = "";
                if($selectId && $v2['id'] == $selectId){
                    $selected = "selected='selected'";
                }
                $str .= "<option $selected value='{$v2['id']}'>{$v2['name']}({$v2['name_cn']})</option>";
            }

            $str .="</optgroup>";
        }

        RETURN $str;
    }
	
}