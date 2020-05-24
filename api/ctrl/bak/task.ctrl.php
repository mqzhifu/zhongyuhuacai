<?php
class TaskCtrl extends BaseCtrl  {

    //获取用户今日任务/成长任务 - 列表
    function getUserList() {
        $daily = $this->taskService->getUserDailyTask($this->uid);
        $growUp = $this->taskService->getUserGrowUpTask($this->uid);

        // 版本兼容处理;
        $versionInfo = $this->clientInfo;
        $app_version = $versionInfo['app_version'];
        if(!empty($daily)){
            if($app_version < '1.1.5'){
                $tmp = array();
                foreach ($daily as $k => $v){
                    if(14 == $v['taskId']){
                        unset($daily[$k]);
                        continue;
                    }
                    $tmp[] = $v;
                }
                $daily = $tmp;
            }
            if($app_version < '1.1.7'){
                foreach ($daily as $kk => $vv){
                    $tmpAll = array('18','19','20','21','22','23');
                    if(in_array($vv['taskId'], $tmpAll)){
                        unset($daily[$kk]);
                    }
                }
            }
        }

        if(!empty($growUp)){
            $versionInfo = $this->clientInfo;
            $app_version = $versionInfo['app_version'];
            if($app_version < '1.1.7'){
                foreach ($growUp as $key => $value){
                    $tmpAlls = array('15','16','17');
                    if(in_array($value['taskId'], $tmpAlls)){
                        unset($growUp[$key]);
                    }
                }
            }
        }
        // dump($daily);exit();
        // 应前端要求字段返回强制转换成int;
        $gameInfo = TaskConfigModel::db()->getById(19);
        if(!empty($daily)){
            foreach ($daily as &$v){
                $v['gameId'] = 0;
                if(19 == $v['taskId']) {
                    $v['gameId'] = (int)$gameInfo['game_id'];
                }
                $v['taskId'] = (int)$v['taskId'];
                $v['taskConfigId'] = (int)$v['taskConfigId'];
                if(7 == $v['taskId'] || 6 == $v['taskId'] || 11 == $v['taskId']){
                    $v['totalStep'] = 1;
                }
                if(6 == $v['taskId']){
                    $v['rewardGoldcoin'] = 3000;
                }
                if(7 == $v['taskId']){
                    $v['rewardGoldcoin'] = 2750;
                }
                if(23 == $v['taskId']){
                    $v['rewardGoldcoin'] = '30w';
                }
            }
            $last_names = array_column($daily,'sort');
            array_multisort($last_names,SORT_DESC, $daily);
        }
        if(!empty($growUp)){
            foreach ($growUp as &$vv){
                if(1 == $vv['taskId']){
                    $vv['totalStep'] = 1;
                }
                $vv['taskId'] = (int)$vv['taskId'];
                $vv['taskConfigId'] = (int)$vv['taskConfigId'];
            }
            $last_names_tmp = array_column($growUp,'sort');
            array_multisort($last_names_tmp,SORT_DESC, $growUp);
        }

        $arr = array(
            'daily'=>$daily,
            'growUp'=>$growUp,
        );


        return $this->out(200,$arr);
    }
    //
    function init(){
        $this->taskService->addUserDailyTask($this->uid);
//        $this->taskService->addUserGrowUpTask($this->uid);
        if($this->uinfo['type'] != UserModel::$_type_guest){
            $rs[] = "im init task===============";
            $rs = $this->taskService->trigger($this->uid,5);
//            LogLib::appWriteFileHash($rs);
        }

        // 版本兼容，新用户是没有最新添加的三个新手任务的需要补全 Add By XiaHB time:2019/06/27 Begin;
        self::ompatibleNewGroupTask($this->uid);
        // 三天连续分享游戏,如果没连续清空已完成步骤;
        self::emptyThreeDaysTask($this->uid);// task_id = 16;
        // 老用户已经微信登陆过的，有了头像，昵称，性别等信息，需要做兼容;
        // self::compatibleUserInfoTask($this->uid);// task_id = 17;
        // 版本兼容，新用户是没有最新添加的三个新手任务的需要补全 Add By XiaHB time:2019/06/27   End;

        return $this->out(200);
    }

    /**
     * 反查task_user表，用户没有新的新手任务就补充;
     * @param $uid
     * @return bool
     */
    private static function ompatibleNewGroupTask($uid){
        // 1、判断数据是否已经存在;
        $result = TaskUserModel::db()->getAll(" uid = $uid AND task_id IN (15,16) ");
        if(!empty($result) && is_array($result)){
            return true;
        }
        // 2、若不存在写入数据库;
        $config_compatible =  TaskConfigModel::db()->getAll(" type = " .TaskConfigModel::GROWNUP_TYPE . ' AND id IN (15,16) ' );
        foreach($config_compatible as $k=>$v){
            $data = array(
                'uid'=>$uid,
                'a_time'=>time(),
                'task_id'=>$v['id'],
                'step'=>0,
                'done_time'=>0,
                'goldcoin'=>$v['goldcoin'],
                'point'=>$v['point'],
                'reward_time'=>0,
                'u_time'=>0,
                'task_config_type'=>$v['type'],
                'task_config_type_sub'=>$v['type_sub'],
                'total_step'=>$v['step_num'],
                'sort'=>$v['sort'],
            );
            $newId = TaskUserModel::db()->add($data);
            $addIds[] = $newId;
            $data['id']  = $newId;
            $dataAll[] = $data;
        }
        // 3、若不存在写入缓存;
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['growup_task_day']['key'],$uid,IS_NAME);
        if(!empty($dataAll) && is_array($dataAll)){
            foreach ($dataAll as $k=>$v) {
                RedisPHPLib::getServerConnFD()->hSet($key,$k,json_encode($v));
            }
        }
    }

    /**
     * 若前一天没有分享则取消;
     * @param $uid
     */
    private function emptyThreeDaysTask($uid){
        $tmp = new TaskService();
        $tmp->trigger($uid, 16);
        /*$info = TaskUserModel::db()->getRow("uid = $uid AND task_id = 16");
        if($info['total_step'] > $info['step'] && 0 != $info['step']){
            $today = dayStartEndUnixtime();
            // 判断昨天是否分享;
            $s_time_yesd = ($today['s_time'] - 24 * 60 * 60);
            $e_time_yesd = ($today['e_time'] - 24 * 60 * 60);
            $selectSql = "SELECT id FROM share WHERE uid = $uid AND game_id != 0 AND a_time >= {$s_time_yesd} AND a_time <= {$e_time_yesd}";
            $info_yesd = ShareModel::db()->getAllBySQL($selectSql);
            if(!$info_yesd){
                $tmp = new TaskService();
                $tmp->trigger($uid, 16);
            }
        }*/
    }

    /**
     * 兼容老用户;
     * @param $uid
     */
    /*private function compatibleUserInfoTask($uid){
        $tmp = new TaskService();
        $insertData = array(
            'uid' => $uid,
            'status' => 1,
            'a_time' => time(),
            'u_time' => time()
        );
        $user_info = UserModel::db()->getRow("id = $uid",'user','nickname, avatar, sex');
        if(!empty($user_info['nickname'])){
            $data = perfectPresonalMessageModel::db()->getAll("uid = $uid AND message_type = 1");
            if(empty($data)){
                $insertData['message_type'] = 1;
                perfectPresonalMessageModel::db()->add($insertData);
                $tmp->trigger($uid, 17);
            }
        }
        if(!empty($user_info['avatar'])){
            $data = perfectPresonalMessageModel::db()->getAll("uid = $uid AND message_type = 2");
            if(empty($data)){
                $insertData['message_type'] = 2;
                perfectPresonalMessageModel::db()->add($insertData);
                $tmp->trigger($uid, 17);
            }
        }
        if(!empty($user_info['sex'])){
            $data = perfectPresonalMessageModel::db()->getAll("uid = $uid AND message_type = 3");
            if(empty($data)){
                $insertData['message_type'] = 3;
                perfectPresonalMessageModel::db()->add($insertData);
                $tmp->trigger($uid, 17);
            }
        }
    }*/

}