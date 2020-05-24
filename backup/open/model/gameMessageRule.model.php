<?php

class GameMessageRuleModel
{
    const TABLE = "open_game_message_rule";
    const PK = "id";

    public static $instance;

    // 消息模板
    const TEMPLATE_TYPE_GENERIC = 1;        // 常规模板

    // 发送规则
    const SEND_RULE_DAYS_TIME = 1;
    const SEND_RULE_EVERYDAY_TIME = 2;
    const SEND_RULE_TIME = 3;
    const SEND_RULE_EVERYDAY_CLOSE = 4;

    //发送条数限制
    const SEND_BAR_LIMITATION = 5;

    public static function db()
    {
        if (self::$instance) {
            return self::$instance;
        }

        self::$instance = DbLib::getDbStatic(DEF_DB_CONN, self::TABLE, self::PK);
        return self::$instance;
    }

    // 添加规则
    public static function add($data)
    {
        $insertData = [];
        // 数据整理
        $insertData["game_id"] = $data["gameid"];
        $insertData["name"] = $data["name"];
        $insertData["template_type"] = $data["template_type"];
        $insertData["copywritings"] = serialize($data["copywritings"]);
        $insertData["type"] = $data["type"];
        $insertData["trigger_day"] = $data["trigger_day"];
        $insertData["trigger_time"] = $data["trigger_time"].":00";
        $insertData["trigger_minute"] = $data["trigger_minute"];
        $datetime = date("Y-m-d H:i:s");
        $insertData["created_at"] = $datetime;
        $insertData["updated_at"] = $datetime;

        $result = self::db()->add($insertData);
        return $result;
    }

    // 编辑规则
    public static function edit($id, $gameid, $data)
    {
        $updateData = [];
        // 整理数据
        $updateData["name"] = $data["name"];
        $updateData["template_type"] = $data["template_type"];
        $updateData["copywritings"] = serialize($data["copywritings"]);
        $updateData["type"] = $data["type"];
        $updateData["trigger_day"] = $data["trigger_day"];
        $updateData["trigger_time"] = $data["trigger_time"].":00";
        $updateData["trigger_minute"] = $data["trigger_minute"];
        $updateData["updated_at"] = date("Y-m-d H:i:s");

        $result = self::db()->update($updateData, "id='".$id."' and game_id='".$gameid."' limit 1");
        return $result;
    }

    // 获取指定游戏的规则
    public static function getAll($gameid, $limit = GameMessageRuleModel::SEND_BAR_LIMITATION)
    {
        $sql = "SELECT * FROM ".self::TABLE." WHERE game_id='".$gameid."' ORDER BY id LIMIT ".$limit;
        $result = self::db()->query($sql);
        return $result;
    }

    // 获取指定规则的信息
    public static function get($id, $gameid)
    {
        $sql = "SELECT * FROM ".self::TABLE." WHERE id='".$id."' AND game_id='".$gameid."' LIMIT 1";
        $result = self::db()->query($sql);
        if (isset($result[0])) {
            return $result[0];
        } else {
            return [];
        }
    }

    // 查询执行游戏有多少规则
    public static function getCount($gameid)
    {
        $count = self::db()->getCount("game_id='".$gameid."'");
        return $count;
    }

    // 检查游戏与规则ID是否合法
    public static function checkId($gameid, $id)
    {
        $count = self::db()->getCount("id='".$id."' and game_id='".$gameid."'");
        return $count == 1 ? true : false;
    }

    // 格式化规则数据
    public static function formatRuleData($data)
    {
        !isset($data["id"]) && $data["id"] = "";
        !isset($data["name"]) && $data["name"] = "";
        !isset($data["template_type"]) && $data["template_type"] = 0;
        if (isset($data["copywritings"])) {
            $data["copywritings"] = unserialize($data["copywritings"]);
        } else {
            $data["copywritings"] = [
                ["title"=>"", "subtitle"=>"", "image_url"=>""],
                ["title"=>"", "subtitle"=>"", "image_url"=>""],
            ];
        }
        !isset($data["type"]) && $data["type"] = 0;
        !isset($data["trigger_day"]) && $data["trigger_day"] = '';
        isset($data["trigger_time"]) && $data["trigger_time"] = date("H:i", strtotime($data["trigger_time"]));
        !isset($data["trigger_time"]) && $data["trigger_time"] = '00:00';
        !isset($data["trigger_minute"]) && $data["trigger_minute"] = '';

        return $data;
    }

    // 获取模板类型列表
    public static function getTemplateTypes()
    {
        return [
            self::TEMPLATE_TYPE_GENERIC => '常规模板',
        ];
    }

    // 获取发送规则列表
    public static function getSendRules()
    {
        return [
            self::SEND_RULE_DAYS_TIME => '关闭时长',
            self::SEND_RULE_EVERYDAY_TIME => '定时任务',
            self::SEND_RULE_TIME => '关闭延时',
            self::SEND_RULE_EVERYDAY_CLOSE => '关闭定时',
        ];
    }

    // 获取触发点文案
    public static function getPointTip($type, $triggerDay, $triggerTime, $triggerMinute)
    {
        $tip = "";
        switch ($type) {
            case self::SEND_RULE_DAYS_TIME:
                $tip .= "关闭后第".$triggerDay."天的".$triggerTime;
                break;
            case self::SEND_RULE_EVERYDAY_TIME:
                $tip .= "每天".$triggerTime;
                break;
            case self::SEND_RULE_TIME:
                $tip .= "关闭后".$triggerMinute."分钟";
                break;
            case self::SEND_RULE_EVERYDAY_CLOSE:
                $tip .= "每天关闭时间";
                break;
        }
        return $tip;
    }

    /**
     * @param $set_id
     * @return int
     */
    public static function dlGameSet($set_id)
    {
        $updateData = [];
        $updateData["name"] = '天天';
        $insertData["updated_at"] = date("Y-m-d H:i:s");
        $result = self::db()->update($updateData, "id ='".$set_id."'");
        return $result;
    }
}
