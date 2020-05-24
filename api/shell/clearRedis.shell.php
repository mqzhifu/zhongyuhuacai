<?php
//清理 redis 无用缓存
//持久化 活跃用户
class ClearRedis{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
        ini_set('display_errors','On');

        if(!arrKeyIssetAndExist($attr,'ac')){
            exit("please ac=xxx ,guestUserToken   goldcoin3Log  userDailyTask  activeUserStoringMysql . \n");
        }

        $ac = $attr['ac'];
        $this->$ac();
//        $data = file("/usr/home/wangdongyan/instantplay_goldcoin_3_log");
//        $redis = new Redis();
//        $redis->connect("10.10.7.6",6379);



    }
    //玩过的游戏
    function playedGame(){

    }

    //清理游客的 uinfo 跟 token
    function guestUserToken(){
        $time = strtotime("2019-04-14 00:00:00");
        $users = UserModel::db()->getAll( " type = 10 and robot = 2 and a_time < $time " ,null," id ");
        if(!$users){
            exit("no data");
        }

        $rs1 = 0;
        $rs2 = 0;
        foreach ($users as $k=>$v) {
            echo $v['id'] . " ";

            $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['userinfo']['key'],$v['id'] );
            $rs1 = RedisPHPLib::getServerConnFD()->del($key);

            echo $key . " : " .$rs1 . " ";

            $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['token']['key'],$v['id'] );
            $rs2 = RedisPHPLib::getServerConnFD()->del($key);

            echo $key . " : " .$rs2 . "\n";
        }

    }

    function goldcoin3Log(){
        $key = RedisPHPLib::getAppKeyById( $GLOBALS['rediskey']['goldcoin_3_log']['key'],'');

        $today = dayStartEndUnixtime();
        $startT = $today['s_time'] - 2 * 24 * 60 * 60;
        $delStartTime = $today['s_time'] - 60 * 24 * 60 * 60;

        echo date("Y-m-d",$delStartTime) . " ". date("Y-m-d",$startT) ."\n";

        $data = RedisPHPLib::getServerConnFD()->keys($key ."*");
        if(!$data){
            exit(" no data");
        }

        foreach ($data as $k=>$v) {
            $v = str_replace("\n","",$v);
            echo $v;
            $rs = RedisPHPLib::getServerConnFD()->zRemRangeByScore($key,$delStartTime,$startT);
            echo ":".$rs ."\n";
        }
    }

    function userDailyTask(){
        $key = RedisPHPLib::getAppKeyById( $GLOBALS['rediskey']['daily_task_day']['key'],'');

        $data = RedisPHPLib::getServerConnFD()->keys($key ."*");
        if(!$data){
            exit(" no data");
        }

        $today = dayStartEndUnixtime();
        $today = $today['s_time'] - 24 * 60 * 60 ;
        $today = date("Y-m-d",$today);
        $today_day = date("Ymd",$today);
        echo $today ."\n";

        $rs = 0;
        foreach ($data as $k=>$v) {
            $v = str_replace("\n","",$v);
            echo $v . " ";
            $line = explode("-",$v);
            echo $line[0] . " " . $line[1] ." ";
            if($line[1] < $today_day){
                echo " no need\n";
            }else{
                $rs = RedisPHPLib::getServerConnFD()->del($v);
                echo " : ".$rs ."\n";
            }
        }

    }
    //活跃用户存储到mysql中
    function activeUserStoringMysql(){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['everyday_active_user']['key'],date("Ymd",strtotime("-1 day")),IS_NAME);
        $activeDayResult = RedisPHPLib::getServerConnFD()->hGetAll($key);
        if(empty($activeDayResult) || !isset($activeDayResult)){
            exit(" no data");
        }

        echo $key."\n";

        echo "count data list:".count($activeDayResult) ."\n" ;

        $count_success = 0;
        $count_fail = 0;


        $game = new GamesService();

        foreach ($activeDayResult as $k => $value){
            echo $k . " ";
            $uid = $k;// 用户id;
            $end_time = $value;// 结束时间戳;
            $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['day_active_user']['key'],$uid,IS_NAME);
            $start_time_array = RedisPHPLib::getServerConnFD()->hGetAll($key);// 开始时间戳;
            $day_now =  date("Ymd",strtotime("-1 day"));

            $start_time = 0;
            if(arrKeyIssetAndExist($start_time_array,$day_now)){
                $start_time = $start_time_array[$day_now];
            }

            echo date("Y-m-d H:i:s",$start_time) . " " . date("Y-m-d H:i:s",$end_time);

            $today_playgame_gold = $game->getToadyUserPlayGameGoldcoin($uid);
            $today_playgame_time =  $game->getToadyUserPlayGameTime($uid);
            $today_sum_gold = $game->getToadyUserSumGoldcoin($uid);

            echo $today_playgame_gold . " ";echo $today_sum_gold . " ";echo $today_sum_gold . " ";

            $insertData = [];

            $insertData['uid'] = $uid;
            $insertData['start_time'] = $start_time;
            $insertData['end_time'] = $end_time;
            $insertData['a_time'] = time();
            $insertData['sum_gold'] = (int)$today_sum_gold;
            $insertData['playgame_time'] = (int)$today_playgame_time;
            $insertData['playgame_gold'] = (int)$today_playgame_gold;

            $rs = userActiveLogModel::db()->add($insertData);
            echo " : ".$rs ."\n";
            if($rs){
                $count_success ++;
            }else{
                $count_fail ++;
            }
        }

//        RedisPHPLib::getServerConnFD()->del($key);

        echo "success : $count_success \n";
        echo "fail : $count_fail \n";
        exit;
    }

    /**
     * 用户每天会用三次幸运趣刮刮的提示通过哈希存在redis中;
     * 脚本删除这些垃圾数据（按月删除）;
     */
    public function clearLuckyDay3Times(){
        $MonthTime = date("Ym",strtotime("-1 months", time()));
        $key = RedisPHPLib::getAppKeyById( $GLOBALS['rediskey']['lucky_day_three_times']['key'],'');
        $data = RedisPHPLib::getServerConnFD()->keys($key ."*$MonthTime*");
        if(!$data){
            exit(" no data ");
        }
        foreach ($data as $k=>$v) {
            $v = str_replace("\n","",$v);
            echo $v;
            $rs = RedisPHPLib::getServerConnFD()->hDel($v, 'click_one', 'click_two', 'click_three');
            echo ":".$rs ."\n";
        }
    }
}



