<?php
/**
 * Created by PhpStorm.
 * Date: 2019/3/13
 * Time: 11:32
 */

/**
 * Class messageSetCtrl
 */
class messageSetCtrl extends BaseCtrl{
    // 小游戏 - 消息 - 列表页
    public function message()
    {
        // 获取消息设置游戏列表页信息;
        $gameInfo = $this->checkGame();
        $gameId = $gameInfo["id"];
        $gamesSetList = $this->getGamesSetList($gameId);
        $this->assign('gameid', $gameId);
        $this->assign("gamesSetList", $gamesSetList);

        // 静态文件
        $this->addCss("/assets/open/css/massage-set-index.css");
        $this->display("messageSet/game_message.html");
    }

    // 小游戏 - 消息 - 创建页/编辑页
    public function messagePutPage()
    {
        // 检验游戏
        $gameInfo = $this->checkGame();
        $gameid = $gameInfo['id'];

        // 静态文件
        $this->addCss("/assets/open/css/massage-set-create.css");
        $this->addCss("/assets/open/css/wickedpicker.min.css");

        $this->addJs("/assets/open/scripts/jquery.form.min.js");
        $this->addJs("/assets/open/scripts/wickedpicker.min.js");

        // 查询id
        $id = _g("id");
        if (is_numeric($id) && $id) {
            $this->checkRuleId($gameid, $id);
            // 编辑页面
            $this->assign("action", "update");
            // 获取规则信息
            $rule = GameMessageRuleModel::get($id, $gameid);
            $rule = GameMessageRuleModel::formatRuleData($rule);
            $this->assign("rule", $rule);
        } else {
            // 创建页面
            $this->assign("action", "create");
            // 获取规则信息
            $rule = GameMessageRuleModel::formatRuleData([]);
            $this->assign("rule", $rule);
        }

        // 模板类型
        $templateTypes = GameMessageRuleModel::getTemplateTypes();
        $this->assign("templateTypes", $templateTypes);
        // 发送规则
        $sendRules = GameMessageRuleModel::getSendRules();
        $this->assign("sendRules", $sendRules);

        $this->display("messageSet/game_message_put.html");
    }

    // 检查规则ID是否有效
    public function checkRuleId($gameid, $id)
    {
        $check = GameMessageRuleModel::checkId($gameid, $id);
        if (!$check) {
            if (isAjax()) {
                $this->outputJson(99, "无此规则权限", []);
            } else {
                jump("/game/message/?gameid=".$gameid);
                exit(0);
            }
        }
    }

    // 小游戏 - 消息 - 保存规则
    public function messageSave()
    {
        // 检验游戏
        $gameInfo = $this->checkGame();
        $gameid = $gameInfo["id"];

        // 获取相关参数
        $name = _g("name");
        if ($name == "") {
            $this->outputJson(1, "规则名称错误", []);
        }
        $templateType = _g("template_type");
        if (!in_array($templateType, [GameMessageRuleModel::TEMPLATE_TYPE_GENERIC])) {
            $this->outputJson(1, "模板类型错误", []);
        }
        $copywritings = _g("copywritings");
        if ($copywritings[0]["title"] == "" ||
            $copywritings[0]["subtitle"] == "" ||
            $copywritings[0]["image_url"] == "" ||
            $copywritings[1]["title"] == "" ||
            $copywritings[1]["subtitle"] == "" ||
            $copywritings[1]["image_url"] == "") {
            $this->outputJson(1, "文案错误", []);
        }
        $type = _g("type");
        if (!in_array($type, [
            GameMessageRuleModel::SEND_RULE_DAYS_TIME,
            GameMessageRuleModel::SEND_RULE_EVERYDAY_TIME,
            GameMessageRuleModel::SEND_RULE_TIME,
            GameMessageRuleModel::SEND_RULE_EVERYDAY_CLOSE,
        ])) {
            $this->outputJson(1, "发送类型错误", []);
        }
        $triggerDay = _g("trigger_day");
        $triggerTime = _g("trigger_time");
        $triggerMinute = _g("trigger_minute");
        if ($type == 1) {
            if (!is_numeric($triggerDay) || $triggerDay == 0) {
                $this->outputJson(1, "触发天数错误", []);
            }
            if ($triggerTime === "") {
                $this->outputJson(1, "触发时间错误", []);
            }
        } else if ($type == 2) {
            if ($triggerTime === "") {
                $this->outputJson(1, "触发时间错误", []);
            }
        } else if ($type == 3) {
            if ($triggerMinute === "") {
                $this->outputJson(1, "触发时间错误", []);
            }
        }

        // 主键ID
        $id = _g("id");
        if (is_numeric($id) && $id) {
            $this->checkRuleId($gameid, $id);
            // 编辑
            $data = [];
            $data["name"] = $name;
            $data["template_type"] = $templateType;
            $data["copywritings"] = $copywritings;
            $data["type"] = $type;
            $data["trigger_day"] = $triggerDay;
            $data["trigger_time"] = $triggerTime;
            $data["trigger_minute"] = $triggerMinute;

            $result = GameMessageRuleModel::edit($id, $gameid, $data);
        } else {
            // 创建
            $count = GameMessageRuleModel::getCount($gameid);
            if ($count >= 5) {
                $this->outputJson(3, "最多只能创建5个规则", []);
            }
            $data = [];
            $data["gameid"] = $gameid;
            $data["name"] = $name;
            $data["template_type"] = $templateType;
            $data["copywritings"] = $copywritings;
            $data["type"] = $type;
            $data["trigger_day"] = $triggerDay;
            $data["trigger_time"] = $triggerTime;
            $data["trigger_minute"] = $triggerMinute;

            $result = GameMessageRuleModel::add($data);
        }

        if ($result) {
            // 添加消息规则更新日志
            ActivityLogModel::addLog($this->_uid, ActivityLogModel::$_event_up_msg_rule, $gameid);

            $this->outputJson(0, "成功", []);
        } else {
            $this->outputJson(2, "失败", []);
        }
    }

    /**
     * 获取游戏设置列表页信息
     * @return array
     */
    public function getGamesSetList($gameId){
        $getGamesSetList = GameMessageRuleModel::GetAll($gameId);
        if(is_array($getGamesSetList) && !empty($getGamesSetList)){
            foreach ($getGamesSetList as &$value){
                $value['template_type'] = (1 == $value['template_type'])?'常规模板':'非常规模板';
                $value['point_trips'] = GameMessageRuleModel::getPointTip($value['type'], $value['trigger_day'], $value['trigger_time'], $value['trigger_minute']);
                switch ($value['type']){
                    case '1':
                        $value['type'] = '关闭时长';
                        break;
                    case '2':
                        $value['type'] = '定时任务';
                        break;
                    case '3':
                        $value['type'] = '关闭延时';
                        break;
                    case '4':
                        $value['type'] = '关闭定时';
                        break;
                    default:
                        $value['type'] = '未知';
                        break;
                }
            }
            return $getGamesSetList;
        }else{
            return [];
        }
    }

    //多条游戏消息设置逻辑删除;
    public function dlGamesSet(){
        $gameIds = _g('name', 'require');
        if(empty($gameIds)){
            $this->outputJson(2, "主键缺失请重试!", []);
        }
        $gameSetIds = explode(",", $gameIds);
        $count = 0;
        foreach ($gameSetIds as $set_id){
            $res = GameMessageRuleModel::db()->delById($set_id);
            if(!$res){
                $count ++;
            }
        }
        if(0 == $count){
            $this->outputJson(0, "批量删除成功!", []);
        }else{
            $this->outputJson(1, "批量删除失败!", []);
        }
    }

    //单条游戏消息设置逻辑删除;
    public function dlGamesSetSingle(){
        $set_id = _g("id");
        $gameid = _g("gameid");
        if(empty($set_id) || empty($gameid)){
            jump("/messageSet/message/");
        }
        /*$updateData["updated_at"] = date("Y-m-d H:i:s");
        $res = GameMessageRuleModel::db()->update($updateData, " id=" . $set_id . " limit 1");*/
        $res = GameMessageRuleModel::db()->delById($set_id);
        if($res){
            jump("/messageSet/message/?gameid=$gameid");
        }else{
            jump("/messageSet/message/?gameid=$gameid");
        }
    }
}