<?php

class GameCategoryModel
{
    const TABLE = "games_category";
    const PK = "id";

    public static $instance;

    public static function db()
    {
        if (self::$instance) {
            return self::$instance;
        }

        self::$instance = DbLib::getDbStatic(DEF_DB_CONN, self::TABLE, self::PK);
        return self::$instance;
    }

    // 获取所有的分类信息
    public static function getCategory()
    {
        $sql = "SELECT * FROM ".self::TABLE." ORDER BY pid ASC, id ASC";
        return self::db()->query($sql);
    }
}
