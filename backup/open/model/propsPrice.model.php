<?php

/**
 * 支付接入
 * @Author: xuren
 * @Date:   2019-03-06 11:13:34
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-03-12 10:32:38
 */
class PropsPriceModel{

	public static $_table = 'open_props_price';
    public static $_pk = 'id';
    public static $_db_key = DEF_DB_CONN;
    public static $_db = null;

    // 操作系统类型
    public static $IOS_TYPE_IOS = 2;
    public static $IOS_TYPE_ANDROID = 1;

    // 货币类型
    public static $CURRENCY_TYPE_RMB = 1;

	public static function db ()
    {
        if (self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key, self::$_table, self::$_pk);
        return self::$_db;
    }

    public static function __callStatic ($func, $arguments)
    {
        return call_user_func_array(array(self::db(), $func), $arguments);
    }

    /**
     * 添加一条道具支付接入数据
     * @var integer
     */
    public static function addItem($gameId, $price, $goodsName, $iosType = 1, $currencyType = 1){
    	$data = [];
    	$data['game_id'] = $gameId;
    	$data['iostype'] = $iosType;
    	$data['currency_type'] = $currencyType;
    	$data['price'] = $price;
    	$data['goods_name'] = $goodsName;
    	return self::db()->add($data);
    }

    public static function getList($gameId){
    	$sql2 = "game_id=$gameId";
    	$list['data'] = self::db()->getAll($sql2);
    	return $list;
    }

    public static function getItemsByGameId($gameId, $page = 1, $size = 1){
    	if(!$gameId){
    		return [];
    	}
    	$list = [];
    	// 分页大小
        $list["pageSize"] = $size;
        // 当前页号
        $list["pageIndex"] = $page;
        // 总页数
    	$sql = "game_id=$gameId";
    	$count = self::db()->getCount($sql);
    	$pageCount = ceil($count/$size);
    	$list['pageCount'] = $pageCount;

    	$offset = ($page-1)*$size;

    	$sql2 = "game_id=$gameId order by id desc limit $offset,$size";
    	$list['data'] = self::db()->getAll($sql2);

    	return $list;
    }

}