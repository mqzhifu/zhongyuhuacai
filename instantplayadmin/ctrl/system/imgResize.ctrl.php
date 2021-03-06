<?php
class ImgResizeCtrl extends BaseCtrl{

    function add(){
        if(_g('opt')){
            $newPathFile = IMG_RESIZE.DS ."aaa";
            $lib = new ImgResizeLib($_FILES['pic']['tmp_name'],$newPathFile ,200,200);
            exit;
        }

        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

//        $this->addHookJS("/people/img_resize_add_hook.html");
        $this->addHookJS("/layout/file_upload.js.html");
        $this->display("/system/img_resize_add.html");
    }

    function detail(){
        $uid = _g("id");
        $user = UserModel::db()->getById($uid);
        $user['dt'] = get_default_date($user['a_time']);
        $user['status_desc'] = UserModel::STATUS_DESC[$user['status']];


        $user['avatar_url'] = get_avatar_url($user['avatar']);
        $user['birthday_dt'] =  get_default_date($user['birthday']);
        $user['type_desc'] = UserModel::getTypeDescByKey($user['type']);
        $orders = OrderModel::getListByUid($uid);
        $userLog = UserLogModel::getListByUid($uid);

        $this->assign("user",$user);
        $this->assign("orders",$orders);
        $this->assign("userLog",$userLog);

        $this->display("/people/user_detail.html");
    }

    function getDataListTableWhere(){
        $where = 1;

        $id = _g("id");
        $uname = _g("uname");
        $nickname = _g('nickname');
        $sex = _g('sex');
        $mobile = _g('mobile');

        $email = _g("email");
        $type = _g("type");

        $birthday_from = _g('birthday_from');
        $birthday_to = _g('birthday_to');

        $from = _g('from');
        $to = _g('to');

        if($id)
            $where .=" and id = '$id' ";

        if($uname)
            $where .=" and uname like '%$uname%' ";

        if($nickname)
            $where .=" and nickname like '%$nickname%' ";

        if($sex)
            $where .=" and sex =$sex ";

        if($mobile)
            $where .=" and mobile = '$mobile' ";

        if($email)
            $where .=" and recommend ='$email' ";

        if($type)
            $where .=" and mobile = '$type' ";


//        if($from = _g("from")){
//            $from .= ":00";
//            $where .= " and add_time >= '".strtotime($from)."'";
//        }
//
//        if($to = _g("to")){
//            $to .= ":59";
//            $where .= " and add_time <= '".strtotime($to)."'";
//        }


        if($from){
            $where .=" and a_time >=  ".strtotime($from);
        }

        if($to)
            $where .=" and a_time <= ".strtotime($to);

        if($birthday_from)
            $where .=" and birthday >=  $birthday_from";

        if($birthday_to)
            $where .=" and birthday <=  $birthday_to";

        return $where;
    }

    function index(){

        $config = ConfigCenter::get(APP_NAME,"img");
        $this->_desc = $config['upload_path_desc'];


        $uploadService = new UploadService();
        $dir =$uploadService->getAllUploadImage();
//        $dir = FILE_UPLOAD_DIR;
//        if(get_os() =="WIN"){
//            $dir = "D:\www\zhongyuhuacai_static\upload/";
//        }
//        $dir = my_dir( $dir);
//        var_dump($dir);exit;
//        foreach ($dir as $k=>$v){
//            echo $k ." ";
//            print_r($v);echo "<br/>";
//        }
//        exit;

        $dirTreeHtml = '<li data-jstree=\'{ "opened" : true }\' >upload<ul>';
        $dirTreeHtml .= $this->foreachDir($dir);
        $dirTreeHtml .="</ul></li>";

        $this->assign("dirTreeHtml",$dirTreeHtml);



        $this->addCss("/assets/global/plugins/jstree/dist/themes/default/style.min.css");
        $this->addJs('/assets/global/plugins/jstree/dist/jstree.min.js');
        $this->addJs('/assets/admin/pages/scripts/ui-tree.js');

        $this->display("/system/image_tree.html");


    }

    function getFileContent(){
        $rootPath = get_static_url() . "upload";
        $filePath = _g('file_path');
        $filePath = substr($filePath,1);
        $fullDir = $rootPath . $filePath;
//        $content = file_get_contents($fullDir);
//        $content = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;",$content);
//        $content = str_replace("\n","<br/>",$content);
//        $content = str_replace("    ","&nbsp;&nbsp;&nbsp;&nbsp;",$content);

        echo $fullDir;
    }

    function foreachDir($dir,$parent = "/"){

        $dirTreeHtml = "";
        foreach ($dir as $k=>$v){
            if(is_array($v)){
                $num = count($v);
                foreach ($v as $k2=>$v2){
                    if(is_array($v2)){
                        --$num;
                    }elseif($v2 == 'desc.txt'){
                        --$num;
                    }
                }

                $empty = "";
                if($num <= 0 ){

                    $empty = ' , "icon" : "fa fa-folder icon-state-danger"  ';
                }

                $dirTreeHtml .= '<li data-jstree=\'{ "opened" : true '.$empty.' }\'>'.$k."({$this->_desc[$k]})(总数量:$num)".'<ul>' . $this->foreachDir($v ,$parent .DS .$k) ."</ul></li>";
            }else{
                if($v != 'desc.txt'){
                    //{ "icon" : "fa fa-briefcase icon-state-success " }
                    $dirTreeHtml .= '<li data-jstree=\'{ "icon" : "fa fa-file-image-o icon-state-success "}\'><span onclick="show_file_content(this)" data= "'.$parent.'"  >  '.$v.'</span></li>';
                }
            }
        }
        return $dirTreeHtml;
    }

}