<?php
//
//class TaskHookDailyLib {
//    public $_config = null;
//    public $_uid = 0;
//    private $_data = null;
//
//    function trigger($uid, $config,$data) {
//        $this->_config = $config;
//        $this->_uid = $uid;
//        $this->_data = $data;
//        switch ($this->_config['id']){
//            //日常任务
//            case 1://登陆
//                $rs = $this->login();
//                break;
//            case 2://合并塔防
//                $rs = $this->mergeTower();
//                break;
//            case 3://杀了多少怪
//                $rs = $this->killMonster();
//                break;
//            case 4://完成一次收徒
//                //mod materStudent addstudent.php
//                $rs = $this->sunflower();
//                break;
//            case 5://粮草
//                //未测试
//                $rs = $this->makeTower();
//                break;
//            case 6://使用技能
//                //未测试
//                $rs = $this->useSkill();
//                break;
//            default:
//        }
//        return $rs;
//    }
//
//    //今日登陆一次
//    function login(){
//        $cntLogin = LoginModel::getUserByDate($this->_uid);
//        if(!$cntLogin ){
//            return false;
//        }
//
//        $cnt = count($cntLogin);
//        if($cnt > $this->_config['step_num']) {//证明数据有问题,但是也得冗错一下吧~
//            $cnt = $this->_config['step_num'];
//        }
//
//        if($cnt == $this->_config['step_num']){//已完成
//            $rs = $this-> upTaskRecord(time(),$cnt, $this->_data );
//            return $rs;
//        }else{
//            $rs = $this-> upTaskRecord(0,$cnt, $this->_data );
//            return $rs;
//        }
//
//    }
//
//    function mergeTower(){
//        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['gameActionCnt']['key'],$this->_uid);
//        $key .= "_".date("Y-m-d");
//
//        $data = RedisPHPLib::get($key,1);
//        $cnt = 0;
//        if($data && arrKeyIssetAndExist($data,'mergeTower')){
//            $cnt = $data['mergeTower'];
//        }
//
//        return $this->upStepRs($cnt);
//
//    }
//
//    function killMonster(){
//        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['gameActionCnt']['key'],$this->_uid);
//        $key .= "_".date("Y-m-d");
//
//        $data = RedisPHPLib::get($key,1);
//
//        $cnt = 0;
//        if($data && arrKeyIssetAndExist($data,'killedMonster')){
//            $cnt = $data['killedMonster'];
//        }
//
//        $this->upStepRs($cnt);
//
//
//    }
//
//    function sunflower(){
//        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['gameActionCnt']['key'],$this->_uid);
//        $key .= "_".date("Y-m-d");
//
//        $data = RedisPHPLib::get($key,1);
//
//        $cnt = 0;
//        if($data && arrKeyIssetAndExist($data,'sunflower')){
//            $cnt = $data['sunflower'];
//        }
//
//        $this->upStepRs($cnt);
//    }
//
//    function makeTower(){
//        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['gameActionCnt']['key'],$this->_uid);
//        $key .= "_".date("Y-m-d");
//
//        $data = RedisPHPLib::get($key,1);
//
//        $cnt = 0;
//        if($data && arrKeyIssetAndExist($data,'makeTower')){
//            $cnt = $data['makeTower'];
//        }
//
//        $this->upStepRs($cnt);
//    }
//
//    function useSkill(){
//        $today = dayStartEndUnixtime();
//        $cnt = AngryLogModel::db()->getCount(" uid = ".$this->_uid . " and a_time >= {$today['s_time']} and a_time <=  {$today['e_time']} ");
//
//        $this->upStepRs($cnt);
//    }
//
//    function upStepRs($cnt){
//        if($cnt > $this->_config['step_num']) {//证明数据有问题,但是也得冗错一下吧~
//            $cnt = $this->_config['step_num'];
//        }
//
//        if($cnt == $this->_config['step_num']){//已完成
//            $rs = $this-> upTaskRecord(time(),$cnt, $this->_data );
//            return $rs;
//        }else{
//            $rs = $this-> upTaskRecord(0,$cnt, $this->_data );
//            return $rs;
//        }
//    }
//
//    function upTaskRecord($done_time = 0 , $step = null,$memo_info = null){
//        $data =array('u_time'=>time());
//        if($memo_info){
//            $data['hook_info'] = json_encode($memo_info);
//        }
//
//        if($done_time){
//            $data['done_time'] = $done_time;
//        }
//
//        if($step){
//            $data['step'] = $step;
//        }elseif($step === 0){
//            $data['step'] = $step;
//        }
//
//        $upRs = TaskUserModel::db()->upById($this->_config['user_task']['id'],$data);
//        return $upRs;
//    }
//
//
////    //玩XX游戏XX次
////    function playedSometimeGame($num){
////        Mod_Log_useraction::taskhook('playedSometimeGame start:');
////        $dayTime = Mod_Tools::dayStartEndUnixtime();
////        Mod_Log_useraction::taskhook('datetime :',$dayTime);
////
////        //取出，用户当天的所有PK日志
////        $pklog = MooController::get('Obj_Pk_pklog')->getPklogByUid($this->_uid,null,$dayTime['s_time'],$dayTime['e_time'],$this->_config['user_task']['game_id']);
////        Mod_Log_useraction::taskhook('Obj_Pk_pklog dada:',$pklog);
////        if(!$pklog){
////            return $this->err('Obj_Pk_pklog getPklogByUid no data',700);
////        }
////
////        $inc = count($pklog);
////        if( $inc < $num ){
////            $rs = $this-> upTaskRecord(0,$inc, $this->_data );
////        }else{
////            $rs = $this-> upTaskRecord(time(),$num, $this->_data );
////            return $this->ok(['edit_rs'=>$rs]);
////        }
////
////    }
////    //连赢XXX场任意游戏
////    function playGameKeepWin($num){
////        Mod_Log_useraction::taskhook('playGameKeepWin start:');
////
////        $dayTime = Mod_Tools::dayStartEndUnixtime();
////        //取出，用户当天的所有PK日志
////        $pklog = MooController::get('Obj_Pk_pklog')->getPklogByUid($this->_uid,null,$dayTime['s_time'],$dayTime['e_time'],null," id desc ");
////        if(!$pklog){
////            return $this->err('Obj_Pk_pklog getPklogByUid no data',733);
////        }
////
//////        $inc_arr = [];
////        $inc = 0;
////        foreach($pklog as $k=>$v){
////            if($v['win'] == 1 && $v['uid'] == $this->_uid){
////                $inc ++ ;
////            }else{
////                break;
////            }
////        }
////
////        if($inc < $num){
////            $rs = $this-> upTaskRecord(0,$inc, $this->_data );
////        }else{
////            $rs = $this-> upTaskRecord(time(),$num, $this->_data );
////            return $this->ok(['edit_rs'=>$rs]);
////        }
////
////    }
////    //红包-抽奖次数
////    function lottery($num){
////        Mod_Log_useraction::taskhook('Mod_Task_hookdaily lottery,num '.$num);
////
////        $dayTime = Mod_Tools::dayStartEndUnixtime();
////        Mod_Log_useraction::taskhook('date_time:',$dayTime);
////
////        $activeLog = MooController::get('Obj_Activebase_Active')->getTodayUserActiveByUid($this->_uid,$dayTime['s_time'],$dayTime['e_time']);
////        if(!$activeLog){
////            Mod_Log_useraction::taskhook('Mod_Task_hookdaily lottery,getTodayUserActiveByUid is null ');
////            return false;
////        }
////
////        $cnt = count($activeLog);
////
////        Mod_Log_useraction::taskhook('true cnt:'.$cnt);
////
////        if($cnt >= $num ){//已完成
////            $step = $num;
////            $rs = $this-> upTaskRecord(time(),$step, $this->_data );
////            return $this->ok(['edit_rs'=>$rs]);
////        }else{
////            $step = $cnt;
////            $this-> upTaskRecord(0,$step, $this->_data );
////        }
////
////
////    }
//
//
//
//
//
//
//
//}
