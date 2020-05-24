<?php

class GameHostingModel
{
    const TABLE = "open_game_hosting";
    const PK = "id";

    public static $instance;

    // 游戏素材状态值
    const STATUS_DEVELOPMENT = 1;
    const STATUS_TEST = 2;
    const STATUS_AUDIT_FAILURE = 3;
    const STATUS_AUDIT_SUCCESS = 4;
    const STATUS_AUDITING = 5;
    const STATUS_PRODUCTION = 6;
    const STATUS_HAD_PRODUCTED = 7;
    const STATUS_LOSE_EFFECT = 8;

    // 游戏地址CDN
    const CDN_API = "http://mgres.kaixin001.com.cn/xyx/";

    public static function db()
    {
        if (self::$instance) {
            return self::$instance;
        }

        self::$instance = DbLib::getDbStatic(DEF_DB_CONN, self::TABLE, self::PK);
        return self::$instance;
    }

    // 获取有多少版本信息
    public static function getVersion($gameid)
    {
        $where = "game_id='{$gameid}'";
        $count = self::db()->getCount($where);
        return $count + 1;
    }

    // 获取指定游戏的列表信息
    public static function getList($gameid, $page=1, $size=1)
    {
        $list = [];
        // 分页大小
        $list["pageSize"] = $size;
        // 当前页号
        $list["pageIndex"] = $page;
        // 总页数
        $where = "game_id='{$gameid}'";
        $count = self::db()->getCount($where);
        $list["pageCount"] = ceil($count / $size);
        // 数据
        $offset = ($page - 1) * $size;
        $limit = $size;
        $sql = "SELECT * FROM ".self::TABLE." WHERE game_id='{$gameid}' ORDER BY status DESC, id DESC LIMIT {$offset}, {$limit}";
        $data = self::db()->query($sql);
        $list["data"] = $data;

        return $list;
    }

    // 获取游戏版本访问地址
    public static function getGameUrl($gameids, $status)
    {
        // 查询条件
        $where = "1=1";
        if (!empty($gameids) && is_array($gameids)) {
            $where .= " and game_id in (".implode(", ", $gameids).")";
        }
        if (!empty($status)) {
            $where .= " and status in (".implode(", ", $status).")";
        }

        // 数据
        $sql = "SELECT game_id, version, status FROM ".self::TABLE." WHERE ".$where;
        $data = self::db()->query($sql);
        foreach ($data as $key => $value) {
            $data[$key]["pay_url"] = self::CDN_API.$value["game_id"]."/".$value["version"]."/";
        }

        return $data;
    }

    // 获取指定游戏的最新的开发版
    public static function getLastDevelopmentVersion($gameid)
    {
        $status = [
            self::STATUS_DEVELOPMENT,
            self::STATUS_TEST,
        ];
        $sql = "SELECT * FROM ".self::TABLE." WHERE game_id='".$gameid."' AND status in (".implode(", ", $status).") ORDER BY id DESC limit 1";
        $data = self::db()->query($sql);
        return isset($data[0]) ? $data[0] : [];
    }

    public static function getAllDevelopmentVersion($gameid){
        $status = [
            self::STATUS_DEVELOPMENT,
            self::STATUS_TEST,
        ];
        $sql = "SELECT * FROM ".self::TABLE." WHERE game_id='".$gameid."' AND status in (".implode(", ", $status).") ORDER BY id DESC";
        $data = self::db()->query($sql);
        return isset($data) ? $data : [];
    }

    // 获取指定游戏最新的审核版本
    public static function getLastAuditVersion($gameid)
    {
        $status = [
            self::STATUS_AUDITING,
            self::STATUS_AUDIT_SUCCESS,
            self::STATUS_AUDIT_FAILURE,
        ];
        $sql = "SELECT * FROM ".self::TABLE." WHERE game_id='".$gameid."' AND status in (".implode(", ", $status).") ORDER BY id DESC limit 1";
        $data = self::db()->query($sql);
        return isset($data[0]) ? $data[0] : [];
    }

    // 获取指定游戏的上线版本
    public static function getLastProductionVersion($gameid)
    {
        $sql = "SELECT * FROM ".self::TABLE." WHERE game_id='".$gameid."' AND status='".self::STATUS_PRODUCTION."' LIMIT 1";
        $data = self::db()->query($sql);
        return isset($data[0]) ? $data[0] : [];
    }

    // 可回退的版本
    public static function getRollbackVersion($gameid)
    {
        $sql = "SELECT * FROM ".self::TABLE." WHERE game_id='".$gameid."' AND status='".self::STATUS_HAD_PRODUCTED."' ORDER BY id DESC LIMIT 3";
        return self::db()->query($sql);
    }

    // 指定游戏是否有上线版
    public static function hasProductionVersion($gameid)
    {
        $where = "game_id='{$gameid}' AND status='".self::STATUS_PRODUCTION."'";
        $count = self::db()->getCount($where);
        return $count == 0 ? false : true;
    }
}
