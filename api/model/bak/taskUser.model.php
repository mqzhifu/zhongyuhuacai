<?php

/**
 *
 * 任务配置
 */
class TaskUserModel {
    static $_table = 'task_user';
    static $_pk = 'id';
    static $_db_key = DEF_DB_CONN;
    static $_db = null;

    const FAILED_MIN = 30;

    const STATUS_NORMAL = 0;
    const STATUS_FAIL = 1;

    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }


    //获取日常任务
    //包括 ：1随机2固定，
    //状态未失效，未领取奖励的
    static function getTodayDailyByUid($uid,$start_time = 0 ,$end_time = 0){
        if(!$start_time || !$end_time){
            $start_time  = strtotime(date("Y-m-d")." 00:00:00");
            $end_time =  strtotime(date("Y-m-d")." 23:59:59");
        }
//        //随机任务
//        $sql = "select * from ".self::$_table." where reward_time = 0 and  status = ".self::STATUS_NORMAL."  and uid = $uid and s_time >= $start_time and e_time <= $end_time and task_config_type =  ".Obj_Task_config::DAILY_TYPE. " and task_config_type_sub =  ".Obj_Task_config::RANDOM_TASK;
//        $rand = self::db()->getAllBySQL($sql);

        $where = "   uid = $uid   and task_config_type =  ".TaskConfigModel::DAILY_TYPE ." and s_time >= $start_time and e_time <= $end_time " ;
        $fix = self::db()->getAll($where);

        return $fix;
    }

    //获取成长任务
    static function getGrownUpByUid($uid){
        //用户第一次进入的时候，会把所有任务均插入进来，因为成长任务不能重复做~
        $where = "   uid = $uid   and task_config_type =  ".TaskConfigModel::GROWNUP_TYPE ;
        $db_data = self::db()->getAll($where);

        return $db_data;
    }


    static function addDailyAllTastByUid($uid,$start_time = 0 ,$end_time = 0){
        if(!$start_time || !$end_time){
            $dayTime = dayStartEndUnixtime();
        }else{
            $dayTime['s_time'] = $start_time;
            $dayTime['e_time'] = $end_time;
        }
        //随机任务，3-4个
//        $rand = rand(3,4);
//        Mod_Log_useraction::taskapi('rand :',$rand);
//        $RandomDailyTask = MooController::get('Obj_Task_config')->getRandomDailyTask($rand);

//        Mod_Log_useraction::taskapi('RandomDailyTask :',$RandomDailyTask);
        //固定任务6个
        $FixDailyTask = TaskConfigModel::getFixDailyTask(6);

//        //先定义一个这样的数据，用来存储随机数，保证这一次产生的随机数不会出现重复
//        $randElement = array();
//        $gameList = MooController::get('Obj_Games')->getList();
//        $gameListCnt = count($gameList);
//        $data = array();
//        foreach($RandomDailyTask as $k=>$v){
//            $row = array(
//                'uid'=>$uid,
//                'task_id'=>$v['id'],
//                'step'=>0,
//                'done_time'=>0,
//                'gold'=>$v['gold'],
//                'point'=>$v['point'],
//                'status'=>0,
//                'reward_time'=>0,
//                'a_time'=>time(),
//                'u_time'=>time(),
//                's_time'=>$dayTime['s_time'],
//                'e_time'=>$dayTime['e_time'],
//                'task_config_type'=>$v['type'],
//                'task_config_type_sub'=>$v['type_sub'],
//            );
//
//            if($v['is_random_game']){
//                //得先保证 游戏至少有5个，不然下面的死循环，可能会有问题
//                if( $gameListCnt > 5){
//                    //下面的代码，其实只是保证 随机的游戏ID ，不出现 重复
//                    //目前随机游戏这种任务，只有3个，如果小于游戏最大数，就会有问题
//                    if(!$randElement){
//                        $rand = rand(0,$gameListCnt - 1);
//                    }else{
//                        $rand = rand(0,$gameListCnt - 1);
//                        while(in_array($rand,$randElement)){
//                            $rand = rand(0,$gameListCnt - 1);
//                        }
//                    }
//                    $randElement[] = $rand;
//                    $row['game_id'] = $gameList[$rand]['game_id'];
//
//                }else{
//                    continue;
//                }
//
//            }
//
//            $data[] = $row;
//        }
//        Mod_Log_useraction::taskapi('randElement :',$randElement);

        $data = array();

        foreach($FixDailyTask as $k=>$v){
            $row = array(
                'uid'=>$uid,
                'task_id'=>$v['id'],
                'step'=>0,
                'done_time'=>0,
//                'gold'=>$v['gold'],
//                'point'=>$v['point'],
                'status'=>0,
                'reward_time'=>0,
                'share_done_time'=>0,
                'a_time'=>time(),
                'u_time'=>time(),
                's_time'=> $dayTime['s_time'],
                'e_time'=> $dayTime['e_time'],
                'task_config_type'=>$v['type'],
                'task_config_type_sub'=>$v['type_sub'],
            );

            $data[] = $row;
        }

        $insert_ids = [];
        foreach($data as $k=>$v){
            $insert_id = self::db()->add($v);
            $insert_ids[] = $insert_id;
        }

        return $insert_ids;

    }


//    static function getTaskByUidAndTaskId($uid,$tid){
//        $sql = "select * from ".self::$_table." where uid = $uid and task_id = ".$tid ;
//        return self::db()->getRowBySQL($sql);
//    }
//
//    static function getDailyTaskByUidAndTaskId($uid,$tid){
//        $today = dayStartEndUnixtime();
//        $sql = "select * from ".self::$_table." where uid = $uid and task_id = ".$tid . " and s_time >= ".$today['s_time'] ;
//        return self::db()->getRowBySQL($sql);
//    }



    //    static function clearUnvalidDailyTask(){
//        $now = time();
//        $data = array('status'=>self::STATUS_FAIL,'u_time'=>time());
//
//        //状态为正常，且没有领取的，且是日常任务，且为随机任务
//        $where = "  $now > e_time and status = ".self::STATUS_NORMAL ." and  task_config_type =  ".Obj_Task_config::DAILY_TYPE ;
//
//        $sql = "select id ,uid,e_time from ".self::$_table ." where " .$where;
//        $task = self::db()->getAllBySQL($sql);
//
//        if(!$task){
//            return - 1;
//        }
//
//
//        foreach($task as $k=>$v){
//            $str = $v['id'] . " ". $v['uid'] . " ".$v['e_time'];
//            Mod_Log_cmd::clearUnvalidDailyTask($str);
//        }
//
//
//
////        return self::db()->upById(self::$_table,$data,$where);
//    }


    //    //失效的记录刷新
//    static function refreshFialedGrownupTaskRecord($uid){
//        $now = time();
//        $data = array('status'=>self::STATUS_FAIL,'u_time'=>time());
//
//        //状态为正常，且没有领取的，且是成长任务，且为随机任务
//        $where = " uid = $uid and  $now > e_time and status = ".self::STATUS_NORMAL ." and reward_time = 0 and task_config_type =  ".Obj_Task_config::GROWNUP_TYPE . " and task_config_type_sub =  ".Obj_Task_config::RANDOM_TASK;
//        $this->dbDao->set(self::$_table,$data,$where);
//    }

    //成长任务，一共就是19个，做完拉倒~不存在 重新做任务，所以，一次性可以把19个任务都插入进去
//    static function addGrownupAllTastByUid($uid){
//        $AllGrouwnupTask = MooController::get('Obj_Task_config')->getAllGrouwnupTask();
//
//        //一共是出现5个任务(有效任务)，第一次添加的时候，固定任务占了4个，所以随便任务肯定是1个了
//        $rand_task_num = 1;
//        $inc = 1;//计数
//        $insert_ids = [];
//        foreach($AllGrouwnupTask as $k=>$v){
//            //固定任务没有失效时间
//            $s_time = 0;
//            $e_time = 0;
//
//            $status = 0;//有效，正常~
//            if($v['type_sub'] == Obj_Task_config::RANDOM_TASK){
//                //随机任务
//                if($inc <= $rand_task_num){
//                    $s_time = time();
//                    $e_time = $s_time + 30 * 60;//30分钟刷新一次
//                }else{
//                    $status = 1;//随机任务只有2个是生效的
//                }
//                $inc++;
//            }
//
//            $data = array(
//                'uid'=>$uid,
//                'task_id'=>$v['id'],
//                'step'=>0,
//                'done_time'=>0,
//                'gold'=>$v['gold'],
//                'point'=>$v['point'],
//                'status'=>$status,
//                'reward_time'=>0,
//                'a_time'=>time(),
//                'u_time'=>time(),
//                's_time'=>$s_time,
//                'e_time'=>$e_time,
//                'task_config_type'=>$v['type'],
//                'task_config_type_sub'=>$v['type_sub'],
//            );
//
//            $insert_id = $this->dbDao->set(self::$_table,$data);
//            $insert_ids[] = $insert_id;
//        }
//        return $insert_ids;
//    }



//    //获取用户<已领取奖励>的任意：日常任务
//    static function getRewardDailyByUid($uid){
//        $sql = " select * from ".self::$_table." where uid = $uid and reward_time > 0   and status = ".self::STATUS_NORMAL ." and task_config_type =  ".Obj_Task_config::DAILY_TYPE ;
//        return self::db()->getAllBySQL($sql);
//    }
//    //获取用户已完成的、有效的任务(包括：日常 成长 ，用于 首页 小红点)
//    static function getDoneValidTaskByUid($uid){
//        $sql = " select * from ".self::$_table." where uid = $uid and reward_time > 0   and status = ".self::STATUS_NORMAL ;
//        return self::db()->getAllBySQL($sql);
//    }

//    //获取用户目前存储数据库所有的成长任务数据
//    static function getAllTaskByUid($uid){
//        return self::db()->getAllBySQL("SELECT * FROM ".self::$_table." WHERE uid = '{$uid}' AND task_config_type = ".Obj_Task_config::GROWNUP_TYPE." GROUP BY task_id");
//    }

    //添加新增的成长任务
//    static function setNewTaskByUid($uid,$info,$start_time = 0,$end_time = 0){

//        if (!$info || !is_array($info)) {
//            return false;
//        }
//
//        if(!$start_time || !$end_time){
//            $dayTime = Mod_Tools::dayStartEndUnixtime();
//        }else{
//            $dayTime['s_time'] = $start_time;
//            $dayTime['e_time'] = $end_time;
//        }
//
//        foreach ($info as $key => $val) {
//
//            $taskInfo = $this->dbDao->get("SELECT * FROM task_config WHERE id = {$val}");
//
//            if (!$taskInfo) {
//                continue;
//            }
//
//            $row = array(
//                'uid' => $uid,
//                'task_id' => $val,
//                'step' => 0,
//                'done_time' => 0,
//                'gold' => $taskInfo['gold'],
//                'point' => $taskInfo['point'],
//                'status' => 0,
//                'reward_time' => 0,
//                'a_time' => time(),
//                'u_time' => time(),
//                's_time' => $dayTime['s_time'],
//                'e_time' => $dayTime['e_time'],
//                'task_config_type' => $taskInfo['type'],
//                'task_config_type_sub' => $taskInfo['type_sub'],
//            );
//
////            $this->dbDao->set(self::$_table,$row);
//        }

//    }

//    static function addOneTask($uid,$task_config_id){
//        $config = MooController::get('Obj_Task_config')->getById($task_config_id);
//
//        if(!$config){
//            exit('$task_config_id not in db.');
//        }
//
//        if($config['type'] == 1){//日常任务必须得有失效时间
//            $datetime = Mod_Tools::dayStartEndUnixtime();
//        }else{
//            //成长任务的  随机任务是30分钟失效时间
//            if($config['type_sub'] == 2){
//                $datetime['s_time'] = time();
//                $datetime['e_time'] = $datetime['s_time'] + 30 * 60;
//            }else{
//                $datetime['s_time'] = 0;
//                $datetime['e_time'] = 0;
//            }
//        }
//
//        $data = array(
//            'uid'=>$uid,
//            'task_id'=>$config['id'],
//            'step'=>0,
//            'done_time'=>0,
//            'gold'=>$config['gold'],
//            'point'=>$config['point'],
//            'status'=>0,
//            'reward_time'=>0,
//            'a_time'=>time(),
//            'u_time'=>time(),
//            's_time'=>$datetime['s_time'],
//            'e_time'=>$datetime['e_time'],
//            'task_config_type'=>$config['type'],
//            'task_config_type_sub'=>$config['type_sub'],
//        );
//
//        $insert_id = $this->dbDao->set(self::$_table,$data);
//
//        return $insert_id;

//    }

//    static public function synchroEgtTaskLog ($uid,$taskId) {
//        $egtTask = $this->dbDao->get("SELECT * FROM {self::$_table} WHERE uid = '{$uid}' AND task_id = '{$taskId}'");
//
//        if (!$egtTask) {
//            return true;
//        }
//
//        if (!$egtTask['step'] || !$egtTask['done_time']) {
//            return true;
//        }
//
//        return false;
//    }

}
