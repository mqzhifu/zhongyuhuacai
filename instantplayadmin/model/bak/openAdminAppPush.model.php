<?php
/**
 * Created by PhpStorm.
 * User: xiahongbo
 * Date: 2019/3/12
 * Time: 11:52
 */
class openAdminAppPushModel {


    const TABLE = "open_admin_apppush";
    const STATUS_PENDING = 0;
    const PK = "id";

    public static $instance;

    // 发送类型;
    const SPECIFIED_SEND = 1;// 指定发送
    const PLATFORM_SENDING = 2;// 平台发送

    // PUSH类型;
    const OFFLINE_PUSH_MESSAGE = 1;// 离线PUSH消息
    const APPLY_THE_TOP_IN_PROMPT_MESSAGE = 2;// 应用内顶部提示消息
    const APPLY_THE_BOTTOM_IN_PROMPT_MESSAGE = 3;// 应用内底部提示消息

    // 平台选择;
    const PLATFORM_ANDROID = 1;// Android
    const PLATFORM_IOS = 2;// ios

    public static function db()
    {
        if (self::$instance) {
            return self::$instance;
        }

        self::$instance = DbLib::getDbStatic(DEF_DB_CONN, self::TABLE, self::PK);
        return self::$instance;
    }

    /**
     * 获取发送类型【页面展示】;
     * @return array
     */
    public static function getSendTypes()
    {
        return [
            self::SPECIFIED_SEND => '指定发送',
            self::PLATFORM_SENDING => '平台发送',
        ];
    }

    /**
     * 获取发送类型【页面展示】;
     * @return array
     */
    public static function getPushTypes()
    {
        return [
            self::OFFLINE_PUSH_MESSAGE => '离线PUSH消息',
            self::APPLY_THE_TOP_IN_PROMPT_MESSAGE => '应用内顶部提示消息',
            self::APPLY_THE_BOTTOM_IN_PROMPT_MESSAGE => '应用内底部提示消息',
        ];
    }

    /**
     * 平台选择【页面展示】;
     * @return array
     */
    public static function getPlatformTypes()
    {
        return [
            self::PLATFORM_ANDROID => 'Android',
            self::PLATFORM_IOS => 'ios',
        ];
    }

    /**
     * @param $insertData
     * @return int
     */
    public static function addData($insertData)
    {
        $insertData['status'] = 0;
        $insertData['created_time'] = date('Y-m-d H:i:s');
        $insertData['updated_time'] = date('Y-m-d H:i:s');
        $result = self::db()->add($insertData);
        return $result;
    }

    // 查询指定发送时间的任务
    public function queryBySendOfTime($sendOfTime, $count, $callback)
    {
        $page = 1;

        do {
            // builder sql
            $limit = (($page - 1) * $count).", ".$count;
            $where = "status='".self::STATUS_PENDING."' AND send_time='".$sendOfTime."'";
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


}