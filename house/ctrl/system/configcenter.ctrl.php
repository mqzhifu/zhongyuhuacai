<?php
class ConfigcenterCtrl extends BaseCtrl{

    function index(){

//        <script src="../../assets/global/plugins/jstree/dist/jstree.min.js"></script>
//        <link rel="stylesheet" type="text/css" href="../../assets/global/plugins/jstree/dist/themes/default/style.min.css"/>
//        <script src="../../assets/admin/pages/scripts/ui-tree.js"></script>

        $dir = my_dir(BASE_DIR .DS ."configcenter");

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

        $this->display("/system/configcenter_tree.html");


    }

    function getFileContent(){
        $rootPath = BASE_DIR ."/configcenter";
        $filePath = _g('file_path');
        $filePath = substr($filePath,1);
        $fullDir = $rootPath . $filePath;
        $content = file_get_contents($fullDir);
        $content = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;",$content);
        $content = str_replace("\n","<br/>",$content);
        $content = str_replace("    ","&nbsp;&nbsp;&nbsp;&nbsp;",$content);

        echo $content;
    }

    function foreachDir($dir,$parent = "/"){

        $dirTreeHtml = "";
        foreach ($dir as $k=>$v){
            if(is_array($v)){
                $dirTreeHtml .= '<li data-jstree=\'{ "opened" : true }\'>'.$k.'<ul>' . $this->foreachDir($v,$parent .DS . $k) ."</ul></li>";
            }else{
//                $dirTreeHtml .= '<li data-jstree=\'{ "icon" : "fa fa-file-code-o icon-state-success "}\'>'.$v.'</li>';
                $dirTreeHtml .= '<li data-path="'.$parent.'" data-jstree=\'{ "icon" : "fa fa-file-code-o icon-state-success "}\'><span onclick="show_file_content(this)" data= "'.$parent.'"  >  '.$v.'</span></li>';
            }
        }
        return $dirTreeHtml;
    }
}