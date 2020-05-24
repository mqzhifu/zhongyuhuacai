<?php

class GameMessageTaskModel
{
    const TABLE = "open_game_message_task";
    const PK = "id";

    public static $instance;

    // 发送状态
    const STATUS_PENDING = 0;       // 待发送
    const STATUS_SENDING = 1;       // 发送中
    const STATUS_COMPLETE = 2;      // 发送完成
    const STATUS_EXPIRED = 3;       // 过期
    const STATUS_EXCEPTION = 4;     // 异常

    public static function db()
    {
        if (self::$instance) {
            return self::$instance;
        }

        self::$instance = DbLib::getDbStatic(DEF_DB_CONN, self::TABLE, self::PK);
        return self::$instance;
    }

    // 添加一条任务数据
    public static function add($data)
    {
        $createdAt = date("Y-m-d H:i:s");
        $insertData["game_id"] = $data["gameid"];
        $insertData["to_id"] = $data["toId"];
        $insertData["template"] = $data["template"];
        $insertData["status"] = self::STATUS_PENDING;
        $insertData["event_of_time"] = $data["eventOfTime"];
        $insertData["send_of_time"] = $data["sendOfTime"];
        $insertData["created_at"] = $createdAt;
        $insertData["updated_at"] = $createdAt;

        $result = self::db()->add($insertData);
        return $result;
    }

    // 批量添加任务数据
    public static function addInBatches($data)
    {
        $insertData = [];
        $createdAt = date("Y-m-d H:i:s");
        foreach ($data as $item) {
            $tmp = [];
            $tmp["game_id"] = $item["gameid"];
            $tmp["to_id"] = $item["toId"];
            $tmp["template"] = $item["template"];
            $tmp["status"] = self::STATUS_PENDING;
            $tmp["event_of_time"] = $item["eventOfTime"];
            $tmp["send_of_time"] = $item["sendOfTime"];
            $tmp["created_at"] = $createdAt;
            $tmp["updated_at"] = $createdAt;
            $insertData[] = $tmp;
        }

        $result = self::db()->addAll($insertData);
        return $result;
    }

    // 查询指定发送时间的任务
    public static function queryBySendOfTime($sendOfTime, $count, $callback)
    {
        $page = 1;

        do {
            // builder sql
            $limit = (($page - 1) * $count).", ".$count;
            $where = "status='".self::STATUS_PENDING."' AND send_of_time='".$sendOfTime."'";
            $sql = "SELECT * FROM ".self::TABLE." WHERE ".$where." LIMIT ".$limit;

            // 查询
            $result = self::db()->query($sql);

            $countResult = count($result);

            if ($countResult == 0) {
                break;
            }

            if ($callback($result, $page) === false) {
                return false;
            }

            unset($result);

            $page++;
        } while ($countResult == $count);

        return true;
    }

    // 修改任务状态
    public static function changeStatus($id, $status)
    {
        if (!in_array($status, [
            self::STATUS_PENDING,
            self::STATUS_SENDING,
            self::STATUS_COMPLETE,
            self::STATUS_EXPIRED,
            self::STATUS_EXCEPTION,
        ])) {
            return false;
        }

        $updateData = [];
        $updateData["status"] = $status;
        $updateData["updated_at"] = date("Y-m-d H:i:s");
        $result = self::db()->update($updateData, "WHERE id='".$id."' LIMIT 1");
        return $result;
    }

    // 将历史任务设置为过期
    public static function setTaskExpired($gameid, $to, $limit=1000)
    {
        $updateData = [];
        $updateData["status"] = self::STATUS_EXPIRED;
        $updateData["updated_at"] = date("Y-m-d H:i:s");
        $where = "game_id='".$gameid."' AND to_id='".$to."' AND status='".self::STATUS_PENDING."' limit ".$limit;
        $result = self::db()->update($updateData, $where);
        return $result;
    }
}
