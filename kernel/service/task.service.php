<?php
//任务系统
class TaskService {
    public $_config = null;
    //完成某一个任务，触发
    function trigger($uid, $configId,$data = null) {
        if( !$uid  ){
            return out_pc(8002);
        }
        if( !$configId){
            return out_pc(8020);
        }

        $config =TaskConfigModel::db()->getById($configId);
        if(!$config){
            return out_pc(1008);
        }

        //日常任务的失效期是1天，而且会有重复的随机任务的记录
        //成长任务 每条任务均是唯一且，只能做一次。  其中： 随机任务：失效期是30分钟，固定任务没有失效期

        $this->_config = $config;

        $authRs = $this->authTaskRecord($uid,$configId,$config);
        if($authRs['code'] != 200){
            return $authRs;
        }

        $config['user_task'] = $authRs['msg'];

        $data = array();

        $lib  = new TaskHookService();
        return $lib->trigger($uid,$config,$data);

    }

    function authTaskRecord($uid,$configId,$config){
        $lib =  new GamesService();
        if($config['type'] == 1){
            //日常任务是每天生成的，需要判断失效时间

            $list = $lib->getDailyTaskDay($uid);
//            $userTask = TaskUserModel::getDailyTaskByUidAndTaskId($uid,$configId);
        }else{
//            $userTask = TaskUserModel::getTaskByUidAndTaskId($uid,$configId);
            $list = $lib->getGrowupTaskDay($uid);
        }
        $userTask = null;
        foreach ($list as $k=>$v) {
            if($v['task_id'] == $configId){
                $userTask = $v;
                break;
            }
        }


        //用户并没有该任务,或者该任务已失效
        if(!$userTask){
            return out_pc(8206);
        }

        if($userTask['done_time']){//已经完成
            return out_pc(8207);
        }

        if($userTask['reward_time']){//已经领取
            return out_pc(8208);
        }

        return out_pc(200,$userTask);
    }

    //添加今日任务
    function addUserDailyTask($uid){
        $dailyTask = $this->getUserDailyTask($uid);
//        LogLib::appWriteFileHash($dailyTask);
        if($dailyTask){
            return out_pc(8205);
        }
//        取一个固定任务
//        $config1 = TaskConfigModel::db()->getAll(" type = ".TaskConfigModel::DAILY_TYPE . " and type_sub = ".TaskConfigModel::FIX_TASK . " limit 1 ");
//        取一个随机任务
//        $config2 = TaskConfigModel::db()->getAll(" type = ".TaskConfigModel::DAILY_TYPE . " and type_sub = ".TaskConfigModel::RANDOM_TASK . " limit 1 ");
//        $config = array_merge($config1,$config2);
//        $addIds = $this->addTask($uid,$config);


        $config =  TaskConfigModel::db()->getAll(" type = ".TaskConfigModel::DAILY_TYPE );
        $addIds = $this->addTask($uid,$config);

        // 2019/06/19代码上线，先注释掉;
        // lucky开宝箱0点11点18点展示逻辑;
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['lucky_day_three_times']['key'],$uid.date("Ymd"),IS_NAME);
        RedisPHPLib::getServerConnFD()->hSet($key,'click_one',1);
        RedisPHPLib::getServerConnFD()->hSet($key,'click_two',1);
        RedisPHPLib::getServerConnFD()->hSet($key,'click_three',1);
        // RedisPHPLib::getServerConnFD()->hGetAll($key);

        return out_pc(200,$addIds);
    }

    function addTask($uid,$config){
        $lib = new GamesService();
        $addIds = [];

        $sTime = 0;
        $eTime = 0;
        if($config[0]['type'] == TaskConfigModel::DAILY_TYPE){
            $today = dayStartEndUnixtime();
            $sTime =$today['s_time'];
            $eTime =$today['e_time'];
        }

        $dataAll = [];
        foreach($config as $k=>$v){
            $data = array(
                'uid'=>$uid,
                'a_time'=>time(),
                'task_id'=>$v['id'],
                'step'=>0,
                'done_time'=>0,
                'goldcoin'=>$v['goldcoin'],
                'point'=>$v['point'],
                'reward_time'=>0,
                's_time'=>$sTime,
                'e_time'=>$eTime,
                'u_time'=>0,
                'task_config_type'=>$v['type'],
                'task_config_type_sub'=>$v['type_sub'],
                'total_step'=>$v['step_num'],
                'sort'=>$v['sort'],
            );
            // task_user 分库分表逻辑; modify by XiaHB time:2019/06/27 Begin;
            if($config[0]['type'] == TaskConfigModel::DAILY_TYPE){
                $newId = TaskUserMoreModel::add($data);
            }else{
                $newId = TaskUserModel::db()->add($data);
            }
            // task_user 分库分表逻辑; modify by XiaHB time:2019/06/27   End;
            // $newId = TaskUserModel::db()->add($data);
            $addIds[] = $newId;
            $data['id']  = $newId;
            $dataAll[] = $data;
        }

        if($config[0]['type'] == TaskConfigModel::DAILY_TYPE){
            $lib->setDailyTaskDay($uid,$dataAll);
        }else{
            $lib->setGrowupTaskDay($uid,$dataAll);
        }


        return $addIds;
    }

    function addUserGrowUpTask($uid){
        $dailyTask = $this->getUserGrowUpTask($uid);
        LogLib::appWriteFileHash($dailyTask);
        if($dailyTask){
            return out_pc(8205);
        }

        $config = TaskConfigModel::db()->getAll(" type = ".TaskConfigModel::GROWNUP_TYPE);
        $addIds = $this->addTask($uid,$config);
        return out_pc(200,$addIds);
    }

    function reward($taskId,$uid,$isShare = 0){
        if(!$taskId){
            return out_pc(8024);
        }

        if(!$uid){
            return out_pc(8002);
        }

        $task = TaskUserModel::db()->getById($taskId);
        if(!$task){
            return out_pc(1011);
        }
        if($task['uid'] != $uid){
            return out_pc(8216);
        }

        if(!$task['done_time']){//未完成
            return out_pc(8217);
        }

        if($task['status'] == 1){//已经刷新了
            return out_pc(8218);
        }

        if($isShare){
            if(!$task['reward_time']){//必须是已经领取了，才能2次领取
                return out_pc(8223);
            }

            if(arrKeyIssetAndExist($task,'share_done_time')){
                return out_pc(8224);
            }
        }else{
            if($task['reward_time']){//已经领取了
                return out_pc(8219);
            }
        }

        $userService = new UserService();
        //增加金币
        if(arrKeyIssetAndExist($task,'goldcoin')){
            $goldcoin = $task['reward_goldcoin'];
            $rs1 = $userService->addGoldcoin($uid,$goldcoin,GoldcoinLogModel::$_type_task_reward);
        }
        //增加积分
        if(arrKeyIssetAndExist($task,'goldcoin')){
            $goldcoin = $task['reward_goldcoin'];
            $rs1 = $userService->addPoint($uid,$goldcoin,PointLogModel::$_type_task_reward);
        }

        $data =array(
            'u_time'=>time(),
            'reward_time'=>time(),
        );

        $upRs = TaskUserModel::db()->upById($taskId,$data);

        return out_pc(200,$upRs);
//



//        $gold = 0;
//        $point = 0;
//        $egtNum = 0;
//        //增加金币
//        if(Mod_Tools::arrKeyIssetAndExist($task,'gold')){
//            $gold = $task['gold'];
//            $rs = MooController::get('Mod_User')->addGoldCoin($uid,$task['gold'],'task_sys_getReward',$taskId);
//
//            //徒弟完成任务，师傅会有金币
//            //先找出师傅，再加
//            $master = MooController::get('Obj_Master_addStudent')->StudentInfo($uid);
//            Mod_Log_useraction::taskapi('master info:',$master);
//            if($master && $master['master_uid']){
//                MooController::get('Mod_User')->addGoldCoin($master['master_uid'],100,'student_task_master',$taskId);
//            }
//            Mod_Log_useraction::taskapi('addGoldCoin rs:'.$rs);
//        }
//        //增加积分
//        if(Mod_Tools::arrKeyIssetAndExist($task,'point')){
//            $point = $task['point'];
//            $rs = MooController::get('Mod_User')->addpoint($uid,$task['point'], 'task' , $taskId);
//            Mod_Log_useraction::taskapi('addpoint rs:'.$rs);
//        }
//
//        //如果领取奖励的任务是一个 日常任务，且一切正常，触发  成长任务
//        if($upRs && $task['task_config_type'] == Obj_Task_config::DAILY_TYPE){
//            Mod_Log_useraction::taskapi('in hook');
//            MooController::get('Mod_Task')->hook($uid,20,['task_id'=>$taskId ]);
//        }
//
//        $rs = array('up_record_rs'=>$upRs,'gold'=>$gold,'point'=>$point,'egtNum'=>$egtNum);


    }

    //生成 今日-日常-任务
    function getUserDailyTask($uid) {
        $lib = new GamesService();
        //获取今日任务
//        $dailyTask = TaskUserModel::getTodayDailyByUid($uid);
        $dailyTask = $lib->getDailyTaskDay($uid);
        if($dailyTask){
            $dailyTask = $this->formatData($dailyTask);
        }else{
            //这里其实主要是做个兼容
            $dailyTask = TaskUserModel::getTodayDailyByUid($uid);
            if($dailyTask){
                $lib->setDailyTaskDay($uid,$dailyTask);
                $dailyTask = $this->formatData($dailyTask);
            }
        }

        return $dailyTask;
    }
    //获取成长任务
    function getUserGrowUpTask($uid) {
        $lib =  new GamesService();
        //获取今日任务
//        $dailyTask = TaskUserModel::getGrownUpByUid($uid);
        $growupTask = $lib->getGrowupTaskDay($uid);


        if($growupTask){
            $growupTask = $this->formatData($growupTask);
        }else{
            //这里其实主要是做个兼容
            $growupTask = TaskUserModel::getGrownUpByUid($uid);
            if($growupTask){
                $lib->setGrowupTaskDay($uid,$growupTask);
                $growupTask = $this->formatData($growupTask);
            }
        }

        return $growupTask;
    }

    function formatData($data){
        $taskIds = split_arr($data,'task_id');
        $taskInfo = TaskConfigModel::db()->getAllByIds($taskIds);
        //格式化数据
        $rs = array();
        foreach($data as $k1=>$v1){
            foreach($taskInfo as $k2=>$v2){
                if($v1['task_id'] == $v2['id']){
                    $row = array( 'rewardGoldcoin'=>$v2['goldcoin'],  'taskId'=>$v1['task_id'] ,'totalStep'=>$v1['total_step'],'step'=>$v1['step'],'title'=>$v2['title'],'desc'=>$v2['content'], 'sort'=>$v2['sort'] );
                    break;
                }
            }


            $row['taskConfigId'] =  $v1['task_id'];
            if($v1['task_id'] == 7){
                //开宝箱这个比较特殊，需要给出倒计时
                $lib =  new LotteryService();
                $time = $lib->getResetTimeImpl($v1['uid']);
                $row['countdown'] = $time;
            }else{
                $row['countdown'] = 0;
            }

            if(arrKeyIssetAndExist($v1,'reward_time')){//证明已领取
                $row['bntStatus'] = 3;
            }elseif($v1['done_time']){//已完成
                $row['bntStatus'] = 2;
            }else{//未完成|待做任务
                $row['bntStatus'] = 1;
            }

            $rs[] = $row;

        }

        return $rs;
    }

}
