<?php
class ImageCtrl extends BaseCtrl{
    public $_desc = null;
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
            if($k == 'wx_little_share_qrcode'){//这个图片是按照   产品ID做为文件夹，然后下面放图片，不兼容
                continue;
            }

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