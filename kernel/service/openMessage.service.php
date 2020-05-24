<?php

class OpenMessageService
{
    // 最大发送条数
    const MESSAGE_LIMIT = 5;

    // 小游戏退出事件调用
    // gameid 游戏ID
    // toId 接收者UID
    // eventOfTime 事件时间：Y-m-d H:i:s
    // event 事件附加参数
    // 注意：该方法放在游戏退出处调用，但是由于是另外一个系统，需要将open/model/GameMessageRule与GameMessageTask文件放在对应的应用model下。
    public function handleMessagingGamePlaysEvent($gameid, $toId, $eventOfTime, $event)
    {
        LogLib::appWriteFileHash([
            "key" => "message:request",
            "content" => [
                "gameid" => $gameid,
                "toId" => $toId,
                "eventOfTime" => $eventOfTime,
                "event" => $event,
            ],
            "time" => date("Y-m-d H:i:s"),
        ]);
        // 任务列表
        $tasks = [];

        // 获取全部规则 - 按照trigger_time进行排序
        $rules = GameMessageRuleModel::db()->getAll("game_id='".$gameid."' ORDER BY trigger_time");
        // 单条任务规则
        $singleRules = [];
        $manyRules = [];
        foreach ($rules as $rule) {
            if (in_array($rule["type"], [
                GameMessageRuleModel::SEND_RULE_DAYS_TIME,
                GameMessageRuleModel::SEND_RULE_TIME,
            ])) {
                $singleRules[] = $rule;
            } else if (in_array($rule['type'], [
                GameMessageRuleModel::SEND_RULE_EVERYDAY_TIME,
                GameMessageRuleModel::SEND_RULE_EVERYDAY_CLOSE,
            ])) {
                $manyRules[] = $rule;
            }
        }
        // 整理manyRule规则排序
        $sort = [];
        $eventTime = date("H:i:s", strtotime($eventOfTime));
        foreach ($manyRules as $item) {
            if ($item["type"] == 4) {
                $sort[] = $eventTime;
            } else {
                $sort[] = $item["trigger_time"];
            }
        }
        array_multisort($sort, SORT_ASC, $manyRules);

        // 一规则一任务
        foreach ($singleRules as $rule) {
            $task = [];
            $task["gameid"] = $gameid;
            $task["toId"] = $toId;
            $task["template"] = $this->generateTemplate($rule, $event);
            $task["eventOfTime"] = $eventOfTime;
            $task["sendOfTime"] = $this->generateSendOfTime($eventOfTime, $rule);
            $tasks[] = $task;
        }

        // 一规则多任务
        $singleCount = count($tasks);
        $days = 0;
        do {
            $days++;
            foreach ($manyRules as $rule) {
                $task = [];
                $task["gameid"] = $gameid;
                $task["toId"] = $toId;
                $task["template"] = $this->generateTemplate($rule, $event);
                $task["eventOfTime"] = $eventOfTime;
                $task["sendOfTime"] = $this->generateSendOfTime($eventOfTime, $rule, $days);
                $tasks[] = $task;
                // 若已经为上线，则调出生成规则
                if (count($tasks) == self::MESSAGE_LIMIT) {
                    break;
                }
            }
        } while ($singleCount != count($tasks) && count($tasks) < self::MESSAGE_LIMIT);

        // 将tasks任务通过sendOfTime进行排序
        $sort = [];
        foreach ($tasks as $task) {
            $sort[] = $task["sendOfTime"];
        }
        array_multisort($sort, SORT_ASC, $tasks);

        // 刷新任务数据
        $this->reflushTask($gameid, $toId, $tasks);
    }

    // 获取发送时间
    protected function generateSendOfTime($eventOfTime, $rule, $days = 1)
    {
        $date = new DateTime($eventOfTime);

        $sendOfTime = '';
        switch ($rule["type"]) {
            case GameMessageRuleModel::SEND_RULE_DAYS_TIME:
                $interval = new DateInterval("P".($rule["trigger_day"] - 1)."D");
                $date->add($interval);
                $sendOfTime = $date->format("Y-m-d")." ".$rule["trigger_time"];
                break;
            case GameMessageRuleModel::SEND_RULE_EVERYDAY_TIME:
                if ($date->format("Y-m-d") >= $rule["trigger_time"]) {
                    $interval = new DateInterval("P".$days."D");
                } else {
                    $interval = new DateInterval("P".($days - 1)."D");
                }
                $date->add($interval);
                $sendOfTime = $date->format("Y-m-d")." ".$rule["trigger_time"];
                break;
            case GameMessageRuleModel::SEND_RULE_TIME:
                $interval = new DateInterval("PT".($rule["trigger_minute"] + 1)."M");
                $date->add($interval);
                $sendOfTime = $date->format("Y-m-d H:i:00");
                break;
            case GameMessageRuleModel::SEND_RULE_EVERYDAY_CLOSE:
                $interval = new DateInterval("P".$days."D");
                $date->add($interval);
                $interval = new DateInterval("PT1M");
                $date->add($interval);
                $sendOfTime = $date->format("Y-m-d H:i:00");
                break;
        }

        return $sendOfTime;
    }

    // 获取对应模板
    protected function generateTemplate($rule, $event)
    {
        $template = "";

        switch ($rule["template_type"]) {
            case GameMessageRuleModel::TEMPLATE_TYPE_GENERIC:
                $template = $this->genericTemplate($rule, $event);
                break;
        }

        return $template;
    }

    // 常规模板
    protected function genericTemplate($rule, $event)
    {
        // 随机获取一个文案
        $copywritings = unserialize($rule['copywritings']);
        $copywriting = $copywritings[array_rand($copywritings, 1)];

        // 获取游戏信息
        $ginfo = GamesModel::db()->getById($rule["game_id"]);
        // 开放平台服务
        $openGamesService = new OpenGamesService();
        // 游戏服务
        $gamesService = new GamesService();

        // 组装数据
        $result = [];
        $result["id"] = $ginfo["id"];
        $result["screen"] = $ginfo["screen"];
        $result["name"] = $ginfo["name"];
        $result["small_img"] = $openGamesService->getAppStaticImageUrl($ginfo["small_img"]);
        $result["list_img"] = $openGamesService->getAppStaticImageUrl($ginfo["list_img"]);
        $result["index_reco_img"] = $openGamesService->getAppStaticImageUrl($ginfo["index_reco_img"]);
        $result["summary"] = $ginfo["summary"];
        $result["background_color"] = $ginfo["background_color"];
        $result["play_url"] = $ginfo["play_url"];
        $result["played_num"] = $gamesService->getPlayedNum($ginfo["id"]);
        // 附加数据
        $result["template_type"] = $rule["template_type"];
        $result["extra_title"] = $copywriting["title"];
        $result["extra_subtitle"] = $copywriting["subtitle"];
        $result["extra_image_url"] = $copywriting["image_url"];

        // 返回模板
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    // 刷新任务数据
    protected function reflushTask($gameid, $to, $tasks)
    {
        // 将历史任务设置为过期
        GameMessageTaskModel::setTaskExpired($gameid, $to);
        // 设置新的任务列表
        GameMessageTaskModel::addInBatches($tasks);
    }

    // 读取数据写入队列
    public function pushToQueue(){
        // 获取当前时间
        $current = date("Y-m-d H:i:00");
        printf("Datetime: %s\n", $current);

        $userService = new UserService();
        $identifier = (ENV == "release") ? "200000" : "100000";

        GameMessageTaskModel::queryBySendOfTime($current, 10000, function ($result, $page) use ($userService, $identifier) {
            foreach ($result as $item) {
                // 任务主键
                $data["id"] = $item["id"];
                // 管理员userSign
                $uinfo = $userService->getUinfoById($identifier);
                $data['userSign'] = $uinfo['im_tencent_sign'];
                // IM identifier
                $data['identifier'] = $identifier;
                // 发送人ID
                /*
                $ginfo = GamesModel::db()->getById($item["game_id"]);
                $data['fromUid'] = $ginfo["uid"];
                */
                $data['fromUid'] = "";
                // 接收人ID
                $data['toUid'] = $item["to_id"];
                // 内容
                $data['content'] = $item['template'];

                printf("Redis data:".$item["id"].":%s\n", serialize($data));

                RedisPHPLib::lpush("open:queue:message", serialize($data));
            }
            foreach ($result as $value){
                $updateData["status"] = 1;
                $updateData["updated_at"] = date("Y-m-d H:i:s");
                GameMessageTaskModel::db()->update($updateData, " id=" . $value['id'] . " limit 1");
            }
        });
    }
}
