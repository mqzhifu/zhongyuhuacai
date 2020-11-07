<?php
class DbCtrl extends BaseCtrl{
    private $_db_base_path_linux = "/home/www/zhongyuhuacai_doc";
    private $_db_base_path_win = "D:\www\zhongyuhuacai_doc";
    function index(){

//        <script src="../../assets/global/plugins/jstree/dist/jstree.min.js"></script>
//        <link rel="stylesheet" type="text/css" href="../../assets/global/plugins/jstree/dist/themes/default/style.min.css"/>
//        <script src="../../assets/admin/pages/scripts/ui-tree.js"></script>

        if(get_os() =="WIN"){
            $dir = $this->_db_base_path_win;
        }else{
            $dir = $this->_db_base_path_linux;
        }
        $dir = my_dir($dir);

//        foreach ($dir as $k=>$v){
//            echo $k ." ";
//            print_r($v);echo "<br/>";
//        }
//        exit;

        $dirTreeHtml = '<li data-jstree=\'{ "opened" : true }\' >doc<ul>';
        $dirTreeHtml .= $this->foreachDir($dir);
        $dirTreeHtml .="</ul></li>";

        $this->assign("dirTreeHtml",$dirTreeHtml);



        $this->addCss("/assets/global/plugins/jstree/dist/themes/default/style.min.css");
        $this->addJs('/assets/global/plugins/jstree/dist/jstree.min.js');
        $this->addJs('/assets/admin/pages/scripts/ui-tree.js');

        $this->display("/system/db_tree.html");


    }



    function foreachDir($dir){

        $dirTreeHtml = "";
        foreach ($dir as $k=>$v){
            if($k == '.git'){
                continue;
            }

           if(is_string($v)){
               $fileExt = explode(".",$v);
               if($fileExt[1] != "sql"){
                   continue;
               }
           }


            if(is_array($v)){
                $dirTreeHtml .= '<li data-jstree=\'{ "opened" : true }\'>'.$k.'<ul>' . $this->foreachDir($v) ."</ul></li>";
            }else{
                $dirTreeHtml .= '<li data-jstree=\'{ "icon" : "fa fa-file-code-o icon-state-success "}\'>'.$v.'</li>';
            }
        }
        return $dirTreeHtml;
    }

}