<?php
//清理 redis 无用缓存
//持久化 活跃用户
class DayGoldUserRank{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
        ini_set('display_errors','On');

//        if(!arrKeyIssetAndExist($attr,'ac')){
//            exit("please ac=xxx ,guestUserToken   goldcoin3Log  userDailyTask  activeUserStoringMysql . \n");
//        }

        $yesterDay = date("Ymd",time() - 24 * 60 * 60);
        $userCnt = DayGoldUserRankModel::db()->getRow(" `day` = '$yesterDay' ");
        if($userCnt){
            exit("has process,don't repeat opt");
        }
        $rewardGold = $GLOBALS['main']['dayGoldUserRanRewardFirstGold'];
        if(ENV == 'dev'){
            $rewardGold = 10;
        }
        $s_time = strtotime($yesterDay . " 00:00:00");
        $e_time = strtotime($yesterDay . " 23:59:59");
        $user = GoldcoinLogModel::db()->getRow(" a_time >= $s_time and a_time <=$e_time  and type != ".GoldcoinLogModel::$_type_gold_user_rank_first." group by uid order by goldNum desc limit 1",null," uid ,sum(num) as goldNum  ");
        if(!$user){
            exit(" no user");
        }
        $data = array('uid'=>$user['uid'],'gold_num'=>$user['goldNum'],'reward_gold_num'=>$rewardGold,'a_time'=>time(),'day'=>$yesterDay);
        $newId = DayGoldUserRankModel::db()->add($data);
        echo "DayGoldUserRankModel mysql table ,new id :$newId\n";

        $userService = new UserService();
        echo "rewardGold：$rewardGold\n";
        $userInfo = $userService->getUinfoById($user['uid']);
//        e_d_f($userInfo);

        $rs = $userService->addGoldcoin($user['uid'],$rewardGold,GoldcoinLogModel::$_type_gold_user_rank_first);
        var_dump($rs);


        $exchange = $GLOBALS['main']['goldcoinExchangeRMB'];
        $content = "1001{$user['uid']}0000";
        $Cusdata = array(
            'rewardMoney'=>(int)($rewardGold * $exchange ),
            'moneyType'=>1,
            'rank'=>1,
            'day'=>$s_time,
        );

        $lib = new PushXinGeLib();
        $rs = $lib->pushAndroidMsgOneMsgByToken($user['uid'],"dayGoldUserRank",$content,$Cusdata);

        var_dump($rs);

    }
}



