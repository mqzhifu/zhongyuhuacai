<?php
// * 检测到玩家连续30天内无登陆记录，清空lessGoldcoin;
class unloggedCoinClear{
    /**
     * unloggedCoinClear constructor.
     * @param $c
     */
    public function __construct($c){
        $this->commands = $c;
        $this->userService = new UserService();
        $this->gamesService = new GamesService();
    }

    public function run(){
        set_time_limit(0);

        //如下是全局缓存，先不清理
        //        money_token_day signLock


        $date_now = date('Y-m-d 23:59:59',strtotime('-30 day'));
        $compare_time = strtotime($date_now);

        //先以30天前注册的用户，为基数
        $cnt = UserModel::db()->getCount("  a_time <= $compare_time ");
        e_d_f($cnt,1);
        if(!$cnt){
            exit("no data 1");
        }
        //注册用户120W左右，一次都读出来有点扯，分批次处理
        $base = 10000;

        $times = $cnt / $base;
        $times = (int)$times + 1;

        //存储30天内未登陆UID
        $unloginUid = [];
        e_d_f(['page times',$times]);
        for($i=0;$i< $times;$i++){
            $start = $i * $base;
            $end = $start+ $base;
            $users = UserModel::db()->getAll("  a_time <= $compare_time status != 1 order by id asc limit $start,$end ",null,' id ');
            if(!$users){
                exit("no data 2");
            }

            foreach ($users as $k=>$v) {
                e_d_f(['uid'=>$v['id']]);
                //从当前用户的活跃天数数据中，判断最后一次活跃的时间
                $info = $this->gamesService->getDayActiveUser($v['id']);
                if($info){
                    foreach ($info as $k2=>$v2) {
                        if($v2 < $compare_time){
                            $unloginUid[] = $v['id'];
                            break;
                        }
                    }
                }else{
                    $unloginUid[] = $v['id'];
                }
            }

//            if(!$unloginUid)
//                exit(" no unloginUid");
//
//            e_d_f("cnt unloginUid:$unloginUid");
//            foreach ($unloginUid as $k=>$uid) {
//                $this->upUserInfo($uid,array('status'=>1));
//                $this->clearUserRedis($uid);
//            }

        }

    }
    //删除缓存
    function clearUserRedis($uid){
        //以下是，redis永久保存，不失效的值
        $rs = $this->gamesService->delFiendIncome($uid);
        e_d_f(" delFiendIncome $rs");
        $this->gamesService->delFriendGiveGoldCoinList($uid);
        e_d_f(" delFriendGiveGoldCoinList $rs");
        $this->gamesService->delUserPlayedGameList($uid);
        e_d_f(" delUserPlayedGameList $rs");
        $this->gamesService->delDayActiveUser($uid);
        e_d_f(" delDayActiveUser $rs");
        $this->gamesService->delGoldCoin3log($uid);
        e_d_f(" delGoldCoin3log $rs");
        $this->gamesService->delGrowupTaskDay($uid);
        e_d_f(" delGrowupTaskDay $rs");


        //以下虽然有些设置了失效值，但后加的，需要兼容
        $this->gamesService->delUserRedisInfo($uid);
        e_d_f(" delUserRedisInfo $rs");

        RedisOptLib::delToken($uid);
        e_d_f(" delToken $rs");


//        daily_task_day
//        lucky_day_three_times
//        daily_box_times
//        today_playgame_gold
//        p_s_g_time
    }

}