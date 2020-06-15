<?php
class insertRobot{
    private $userInfoTextPath = "";
    function __construct($c){
        $this->commands = $c;

        $env = ENV;
        if(ENV == 'dev' || ENV == 'local' || ENV == 'pre'){
            $env = 'dev';
        }
        $path = DS ."instantplayadmin".DS.$env.DS ."avatar/ai/";
        $this->userInfoTextPath = FILE_UPLOAD_DIR .$path;
    }

    function renamePic(){
//        if(PCK_AREA == 'en'){
//            $this->tool_rename_file('dongnanya','boy');
//            $this->tool_rename_file('dongnanya','girl');
//
//            $this->tool_rename_file('china','boy');
//            $this->tool_rename_file('china','girl');
//
//            $this->tool_rename_file('oumei','boy');
//            $this->tool_rename_file('oumei','girl');
//        }else{
//            $this->tool_rename_file('china','boy');
//            $this->tool_rename_file('china','girl');
//        }
    }

    public function run($attr){
        set_time_limit(0);

        if(PHP_OS == 'WINNT'){
            exec('chcp 936');
        }

//        $this->renamePic($attr);

        $this->addAdminUser();

//        if(PCK_AREA == 'en'){
//            $this->insert('china','boy');
//            $this->insert('china','girl');
//
//            $this->insert('dongnanya','boy');
//            $this->insert('dongnanya','girl');
//
//            $this->insert('oumei','boy');
//            $this->insert('oumei','girl');
//        }else{
            $this->insert('china','boy');
            $this->insert('china','girl');
//        }
    }

    function addAdminUser(){
//        $userService = new UserService();
        if(ENV == 'dev' || ENV == 'local' || ENV == 'pre'){
//            if(PCK_AREA == 'en'){
//                $adminUid = 100009;
//                $adminUserNickname = 'Helper';
//                $avatar ='http://cdn-source-xyx.heyshell.com/xyx/static/images/icon_assistant.png';
//            }else{
                $adminUid = 100000;
                $adminUserNickname = '系统-小助手';
                $avatar = get_avatar_url("/ai/system_helper.png");
//            }
        }elseif(ENV == 'release'){
//            if(PCK_AREA == 'en'){
//                $adminUserNickname = 'Helper';
//                $adminUid = 200009;
//                $avatar ='http://cdn-source-xyx.heyshell.com/xyx/static/images/icon_assistant.png';
//            }else{
                $adminUid = 200000;
                $adminUserNickname = '系统-小助手';
                $avatar = get_avatar_url("/ai/system_helper.png");
//            }
        }else{
            exit(" EVN ERR");
        }

//        $adminUser = $userService->getUinfoById($adminUid);
        $adminUser = UserModel::db()->getById($adminUid);
        if($adminUser){
            exit(" adminUser has  in db ");
        }

//        UserModel::db()->query("truncate table user");


//        var_dump(234234);exit;
        $data = array(
            'id'=>$adminUid,
            'avatar'=>$avatar,
            'type'=>UserModel::$_type_name,
//            'name'=>$adminUserNickname,
            'uname'=>$adminUserNickname,
            'nickname'=>$adminUserNickname,
            'sex'=>UserModel::$_sex_female,
            'a_time'=>time(),
            'inner_type'=>UserModel::INNER_TYPE_ROBOT,
//            'push_status'=>1,
//            'hidden_gps'=>1,
//            'u_time'=>time(),
//            'robot'=>1,
//            'point'=>0,
//            'birthday'=>0,
//            'goldcoin'=>0,
//            'diamond'=>0,
//            'vip_endtime'=>0,
//            'push_type'=>0,
        );

//        var_dump($data);exit;
        $newId = UserModel::db()->add($data);
//        $data = array('uid'=>$adminUid);
//        UserDetailModel::db()->add($data);
        out("add system adminuser ok  $newId");
    }

    public function insert($module,$sexName){
//        $userService = new UserService();

        if(ENV == 'dev' || ENV == 'local' || ENV == 'pre'){
//            if(PCK_AREA == 'en'){
//                $inc_uid = 150000;
//            }else{
                $inc_uid = 110000;
//            }
//
        }elseif(ENV == 'release'){
//            if(PCK_AREA == 'en'){
//                $inc_uid = 250000;
//            }else{
                $inc_uid = 210000;
//            }
        }else{
            exit(" EVN ERR");
        }

//        $firstUser = $userService->getUinfoById($inc_uid);
        $firstUser = UserModel::db()->getById($inc_uid);
        if($firstUser){
            $inc_uid = 'null' ;
        }


        $file =$this->userInfoTextPath."/$module/{$sexName}.txt";
        $file_data = file($file);
        if(!$file_data){
            exit('open file failed,no data:'.$file);
            exit('no data');
        }


        $inc = 1;

        out(count($file_data));
//        var_dump($file_data[216]);exit;
        foreach($file_data as $k=>$v){
            out("num:".$k);
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

            echo $this->getSrcPicDir().DS. $avatar;
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
                'avatar'=>"ai/".$avatar,
                'type'=>UserModel::$_type_name,
//                'name'=>$v,
                'uname'=>$v,
                'nickname'=>$v,
                'sex'=>UserModel::$_sex_female,
                'a_time'=>time(),
                'inner_type'=>UserModel::INNER_TYPE_ROBOT,
                'sex'=>$sex,
            );

            $newUid = UserModel::db()->add($data);
            echo " new uid $newUid\n";
//            $sql = "INSERT INTO `user_detail` (`id`,`uid`,`sign`,`summary`,`avatars`,`language`,`channel`,`company`,`telphone`,`fax`,`invite_code`) VALUES (null,$newUid,'','','','','','','','','')";
//            echo $sql . "\n";
//            $rs2 = UserModel::db()->execute($sql);

//            echo "\r\n";
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
        return $this->userInfoTextPath;
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