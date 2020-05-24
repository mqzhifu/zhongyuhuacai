<?php
class picRename{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
        if(PHP_OS == 'WINNT'){
            exec('chcp 936');
        }

//        $this->tool_rename_file();
//        exit;
//        $userService = new UserService();


        $file = APP_CONFIG.'robot_europe_data.txt';
        $file_data = file($file);
        if(!$file_data){
            MooLog::info('open file failed,no data:'.$file);
            exit('no data');
        }


        $path = get_static_url()."upload/game/robot_europe/";
        $inc = 1;
        $inc_uid = 110001;
        o(count($file_data));
        foreach($file_data as $k=>$v){
            o("num:".$k);
//            if($inc >1415){
//                break;
//            }
            $tmp = explode("\t",$v);
            if(!$tmp){
                o('err row is null');
                continue;
            }

            foreach($tmp as $k3=>$v3){
                $tmp[$k3] = trim($v3);
            }


            if($tmp[1] == '男'){
                $sex = 1;
                $avatar = $path . "boy/$inc.jpg";
            }elseif($tmp[1] == '女'){
                $sex = 2;
                $avatar = $path . "girl/$inc.jpg";
            }else{
                $sex = 0;
            }


            $sql = "INSERT INTO `user` 
(`id`,`ps`,`avatar`,`type`,`name`,`nickname`,`push_status`,`hidden_gps`,`is_online`,`a_time`,`u_time`,`robot`,`point`,`birthday`,`goldcoin`,`diamond`,`sex`,`vip_endtime`,`push_type`,`cellphone`,`real_name`,`id_card_no`,`country`,`province`,`city`,`tags`,`school`,`education`,`push_token`,`email`) VALUES
 ($inc_uid,'','$avatar',3,'{$tmp[0]}','{$tmp[0]}',1,1,1,1546604016,1546604016,1,0,0,0,0,$sex,0,0,'','','','','','','','',0,'','')";


            $rs1 = UserModel::db()->execute($sql);

            $sql = "INSERT INTO `user_detail` (`id`,`uid`,`sign`,`summary`,`avatars`,`language`,`channel`,`company`,`telphone`,`fax`,`invite_code`) VALUES (null,$inc_uid,'','','','','','','','','')";

            $rs2 = UserModel::db()->execute($sql);

            o($tmp[0]." ".$tmp[1]." ".$sex . " ".$rs1 . " " .$rs2);

//            $rs = $userService->register($tmp[0],"e10adc3949ba59abbe56e057f20f883e",UserModel::$_type_name);
//            $uid = $rs['id'];
//            $rs = UserModel::db()->upById($uid,array('sex'=>$sex));
//            o($uid . " ".$rs);

            echo "\r\n";

            $inc_uid++;
            $inc++;
        }
    }

    function scanFile($path) {
        global $result;
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                if (is_dir($path . '/' . $file)) {
                    scanFile($path . '/' . $file);
                } else {
                    $result[] = basename($file);
                }
            }
        }
        return $result;
    }

    function tool_rename_file(){
        $pic_pat = CONFIG_DIR.'robot_europe/girl/';
        $rs = $this->scanFile($pic_pat);
        foreach($rs as $k=>$v){
            $num = $k+1;
            $ext_name = explode('.',$v);
            $old_name= $pic_pat."/".$v;
            $new_name = $pic_pat."/".$num.".".$ext_name[1];
            echo $old_name ." ".$new_name;
            $s = rename($old_name,$new_name);
            var_dump($s);
        }

        var_dump($rs);exit;
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