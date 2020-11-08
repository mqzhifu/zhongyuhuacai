<?php

/**
 * Class persistActiveUser
 */
class persistActiveUser{
    /**
     * persistActiveUser constructor.
     * @param $c
     */
    public function __construct($c){
        $this->commands = $c;
    }

    /**
     * user_active_log表持久化;
     */
    public function run(){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['everyday_active_user']['key'],date("Ymd",strtotime("-1 day")),IS_NAME);
        $activeDayResult = RedisPHPLib::getServerConnFD()->hGetAll($key);
        if(empty($activeDayResult) || !isset($activeDayResult)){
            exit(" no data");
        }

        echo "count data list:".count($activeDayResult) ."\n" ;

        $count_success = 0;
        $count_fail = 0;

        foreach ($activeDayResult as $k => $value){
            $uid = $k;// 用户id;
            $end_time = $value;// 结束时间戳;
            $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['day_active_user']['key'],$uid,IS_NAME);
            $start_time_array = $dayActiveResult = RedisPHPLib::getServerConnFD()->hGetAll($key);// 开始时间戳;
            $day_now =  date("Ymd",strtotime("-1 day"));
            $start_time = $start_time_array[$day_now];

            if(!empty($end_time) && !empty($start_time) && !empty($uid)){
                $insertData['uid'] = $uid;
                $insertData['start_time'] = $start_time;
                $insertData['end_time'] = $end_time;
                $insertData['a_time'] = time();
                $insertData['u_time'] = time();
                $rs = userActiveLogModel::db()->add($insertData);
                if($rs){
                    $count_success ++;
                }else{
                    $count_fail ++;
                }
            }
        }
        echo "success : $count_success \n";
        echo "fail : $count_fail \n";
        exit;
    }

    /**
     * @param $str
     */
    function o($str){
        if(PHP_OS == 'WINNT'){
            $str = iconv("UTF-8","GBK//IGNORE",$str)."\r\n";
        }
        echo $str."\n";
    }
}