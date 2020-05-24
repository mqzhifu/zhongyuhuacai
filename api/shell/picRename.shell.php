<?php
class picRename{
    function __construct($c){
        $this->commands = $c;
    }

    function renamePic(){
//        if($attr['PCK_AREA'] == 'cn'){
//
//        }else{
//
//        }
//        if($attr['ENV'] == 'dev'){
//
//        }
        if(PCK_AREA == 'en'){
            $this->tool_rename_file('dongnanya','boy');
            $this->tool_rename_file('dongnanya','girl');

            $this->tool_rename_file('china','boy');
            $this->tool_rename_file('china','girl');

            $this->tool_rename_file('oumei','boy');
            $this->tool_rename_file('oumei','girl');
        }else{
            $this->tool_rename_file('china','boy');
            $this->tool_rename_file('china','girl');
        }
    }

    public function run($attr){
        set_time_limit(0);

        if(PHP_OS == 'WINNT'){
            exec('chcp 936');
        }

//        $this->renamePic($attr);

        $this->addAdminUser();

        if(PCK_AREA == 'en'){
            $this->insert('china','boy');
            $this->insert('china','girl');

            $this->insert('dongnanya','boy');
            $this->insert('dongnanya','girl');

            $this->insert('oumei','boy');
            $this->insert('oumei','girl');
        }else{
            $this->insert('china','boy');
            $this->insert('china','girl');
        }
    }

    function addAdminUser(){
        $userService = new UserService();
        if(ENV == 'dev'){
            if(PCK_AREA == 'en'){
                $adminUid = 100009;
                $adminUserNickname = 'Helper';
                $avatar ='http://cdn-source-xyx.heyshell.com/xyx/static/images/icon_assistant.png';
            }else{
                $adminUid = 100000;
                $adminUserNickname = '小助手';
                $avatar ='http://mgres.kaixin001.com.cn/xyx/static/images/icon_assistant.png';
            }

        }elseif(ENV == 'release'){
            if(PCK_AREA == 'en'){
                $adminUserNickname = 'Helper';
                $adminUid = 200009;
                $avatar ='http://cdn-source-xyx.heyshell.com/xyx/static/images/icon_assistant.png';
            }else{
                $adminUid = 200000;
                $adminUserNickname = '小助手';
                $avatar ='http://mgres.kaixin001.com.cn/xyx/static/images/icon_assistant.png';
            }
        }else{
            exit(" EVN ERR");
        }

        $adminUser = $userService->getUinfoById($adminUid);
        if($adminUser){
            exit(" adminUser has  in db ");
        }

        UserModel::db()->query("truncate table user");


        $data = array(
            'id'=>$adminUid,
            'avatar'=>$avatar,
            'type'=>3,
            'name'=>$adminUserNickname,
            'nickname'=>$adminUserNickname,
            'push_status'=>1,
            'hidden_gps'=>1,
            'a_time'=>time(),
            'u_time'=>time(),
            'robot'=>1,
            'point'=>0,
            'birthday'=>0,
            'goldcoin'=>0,
            'diamond'=>0,
            'sex'=>2,
            'vip_endtime'=>0,
            'push_type'=>0,
        );

        UserModel::db()->add($data);
        $data = array('uid'=>$adminUid);
        UserDetailModel::db()->add($data);
    }

    public function insert($module,$sexName){
        $userService = new UserService();

        if(ENV == 'dev'){
            if(PCK_AREA == 'en'){
                $inc_uid = 150000;
            }else{
                $inc_uid = 110000;
            }

        }elseif(ENV == 'release'){
            if(PCK_AREA == 'en'){
                $inc_uid = 250000;
            }else{
                $inc_uid = 210000;
            }
        }else{
            exit(" EVN ERR");
        }
        $firstUser = $userService->getUinfoById($inc_uid);
        if($firstUser){
            $inc_uid = 'null' ;
        }


        $file = BASE_DIR."/ai/$module/{$sexName}.txt";
        $file_data = file($file);
        if(!$file_data){
            exit('open file failed,no data:'.$file);
            exit('no data');
        }


        $inc = 1;

        o(count($file_data));
//        var_dump($file_data[216]);exit;
        foreach($file_data as $k=>$v){
            o("num:".$k);
//            if($inc >1415){
//                break;
//            }

            if($k > 0 ){
                $inc_uid = 'null';
            }

            if($sexName == 'boy'){
                $sex = 1;
                $avatar = "$module/$sexName/$inc";
            }elseif( $sexName == 'girl'){
                $sex = 2;
                $avatar = "$module/$sexName/$inc";
            }else{
                exit('sex is error');
            }

            echo $avatar;
            if( file_exists($this->getSrcPicDir(). $avatar . ".jpg")){
                $avatar .= ".jpg";
            }else if( file_exists($this->getSrcPicDir(). $avatar . ".png")){
                $avatar .= ".png";
            }else{
                exit(" pic ext name file not exists");
            }

            $inc++;


//            var_dump($inc_uid);
//            continue;

            $data = array(
                'id'=>$inc_uid,
                'avatar'=>$avatar,
                'type'=>3,
                'name'=>$v,
                'nickname'=>$v,
                'push_status'=>1,
                'hidden_gps'=>1,
                'a_time'=>time(),
                'u_time'=>time(),
                'robot'=>1,
                'point'=>0,
                'birthday'=>0,
                'goldcoin'=>0,
                'diamond'=>0,
                'sex'=>$sex,
                'vip_endtime'=>0,
                'push_type'=>0,
            );

//            $sql = "INSERT INTO `user`
//(`id`,`ps`,`avatar`,`type`,`name`,`nickname`,`push_status`,`hidden_gps`,`is_online`,`a_time`,`u_time`,`robot`,`point`,`birthday`,`goldcoin`,`diamond`,`sex`,`vip_endtime`,`push_type`,`cellphone`,`real_name`,`id_card_no`,`country`,`province`,`city`,`tags`,`school`,`education`,`push_token`,`email`) VALUES
// ($inc_uid,'','$avatar',3,'{$v}','{$v}',1,1,1,1546604016,1546604016,1,0,0,0,0,$sex,0,0,'','','','','','','','',0,'','')";

//            $rs1 = UserModel::db()->execute($sql);
//            $newUid = UserModel::db()->getLastInsID();
            $newUid = UserModel::db()->add($data);
            echo " new uid $newUid\n";
            $sql = "INSERT INTO `user_detail` (`id`,`uid`,`sign`,`summary`,`avatars`,`language`,`channel`,`company`,`telphone`,`fax`,`invite_code`) VALUES (null,$newUid,'','','','','','','','','')";
            echo $sql . "\n";
            $rs2 = UserModel::db()->execute($sql);

            echo "\r\n";
        }
    }


    function scanFile($path,$first = 0) {
        global $result;
        if($first){
            $result = null;
        }
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

    function getSrcPicDir(){
        return BASE_DIR."/ai/";
    }

    function tool_rename_file($module,$sex){
        $pic_pat = $this->getSrcPicDir(). "$module/$sex/";
//        var_dump($pic_pat);exit;
        $rs = $this->scanFile($pic_pat,1);

        //先把所有重命名一下，防止下面重命名的时候，根当前名称重复
        $num = count($rs);
        foreach($rs as $k=>$v){
            $num++;
            $ext_name = explode('.',$v);
            $old_name= $pic_pat."/".$v;
            $new_name = $pic_pat."/".$num.".".$ext_name[1];
            echo $old_name ." ".$new_name . "\r\n";
            $s = rename($old_name,$new_name);
        }

        $rs = $this->scanFile($pic_pat,1);

        foreach($rs as $k=>$v){
            $num = $k+1;
            $ext_name = explode('.',$v);
            $old_name= $pic_pat."/".$v;
            $new_name = $pic_pat."/".$num.".".$ext_name[1];
            echo $old_name ." ".$new_name . "\r\n";
            $s = rename($old_name,$new_name);
        }

//        var_dump($rs);exit;
    }


    public function insertOld($module,$sexName){
        $userService = new UserService();


        $file = BASE_DIR."/ai/$module/{$sexName}.txt";
        $file_data = file($file);
        if(!$file_data){
            exit('open file failed,no data:'.$file);
            exit('no data');
        }


        $boyInc = 1;
        $girlInc = 1;
        $inc_uid = 210001;
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
//                $avatar = $path . "boy/$boyInc.jpg";
                $avatar = "boy/$boyInc.jpg";
                $boyInc++;
            }elseif($tmp[1] == '女'){
                $sex = 2;
//                $avatar = $path . "boy/$boyInc.jpg";
                $avatar = "girl/$girlInc.jpg";
                $girlInc++;
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