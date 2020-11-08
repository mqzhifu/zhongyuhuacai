<?php
class UserInfoByRedis{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
        ini_set('display_errors','On');

//        if(!arrKeyIssetAndExist($attr,'ac')){
//            exit("please ac=xxx ,guestUserToken   goldcoin3Log  userDailyTask  activeUserStoringMysql . \n");
//        }

//        $ac = $attr['ac'];
//        $this->$ac();


        $userALLKey = RedisPHPLib::getServerConnFD()->keys("instantplay_uinfo_*");
        if(!$userALLKey){
            exit("no data");
        }

        foreach ($userALLKey as $k=>$v) {
            $avatarUrl = RedisPHPLib::getServerConnFD()->hGet($v,'avatar');
            if($avatarUrl && strpos($avatarUrl,'xyx/static/xyx/static')){
                echo $v ." ".$avatarUrl ."\n";
            }
        }


    }
}