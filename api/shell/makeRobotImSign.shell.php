<?php
class makeRobotImSign{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr)
    {

        $this->makeSign();exit;

//        $lib = new UserService();
//        $sign = $lib->generateSign( );
//        $this->makeSign();exit;

        $ImTencentLib = new ImTencentLib();

        $users  = UserModel::db()->getAll("robot = 1");
        if(!$users){
            exit(" no user");
        }

        if(ENV == 'dev'){
            if(PCK_AREA == 'en'){
                $adminId = 100009;
            }else{
                $adminId = 100000;
            }
        }else{
            if(PCK_AREA == 'en'){
                $adminId = 200009;
            }else{
                $adminId = 200000;
            }
        }




        $userDetail = UserDetailModel::db()->getRow("uid = $adminId");
        $adminUserIm_tencent_sign = $userDetail['im_tencent_sign'];
        if(!$adminUserIm_tencent_sign){
            exit('$adminUserIm_tencent_sign is null');
        }

        $url = "https://console.tim.qq.com/v4/im_open_login_svc/multiaccount_import?usersig=$adminUserIm_tencent_sign&identifier=$adminId&sdkappid={$ImTencentLib->getSdkAppId()}&contenttype=json&random=";
        echo $url;
        $loginUserIds = array();
        foreach ($users as $k=>$v) {
            $loginUserIds[] = "".$v['id'];
            if($k % 90 === 0 || $k ==  count($users) -1 ){
                $r = rand(100000,999999);
                $accounts = array("Accounts"=>$loginUserIds);
                $accounts = json_encode($accounts);
                $rs = CurlLib::send($url.$r,2,$accounts,null,1);
                var_dump($rs);

                $loginUserIds = array();
            }


        }




    }


    function makeSign(){
        $list = UserModel::db()->getAll(" robot = 1",null,' id ');
//        $list = UserModel::db()->getAll(" id = 100000 ",null,' id ');
        if(!$list){
            exit(" no data ");
        }

        $lib = new UserService();
        foreach($list as $k=>$v){
            echo $v['id']."\n";
            $sign = $lib->generateSign($v['id']);
            echo $sign."\n";
            $rs = $lib->upUserDetailInfo($v['id'],array('im_tencent_sign'=>$sign));
            echo " rs : ".$rs['code']."\n";
        }
    }
}

function o($str){
//    $encode = mb_detect_encoding($str, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
//    var_dump($encode);
//    var_dump($str);
//    var_dump(iconv("UTF-8","gbk//TRANSLIT",$str));
    if(PHP_OS == 'WINNT'){
        $str = iconv("UTF-8","GBK//IGNORE",$str)."\r\n";
    }

    echo $str."\n";
}