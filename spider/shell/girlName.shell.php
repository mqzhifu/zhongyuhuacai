<?php
class GirlName
{
    public $config = null;

    public function __construct($c)
    {
        $this->commands = $c;
        $config = ConfigCenter::get(APP_NAME, "main");
        $this->config = $config['common'];
    }
    public function run($argc){
        $hard_disk1_path = "/Volumes/Elements/欧美";
        $hard_disk2_path = "/Volumes/My Passport/欧美";

        $girlNameList_disk1 = $this->processOneDir($hard_disk1_path);
        ksort($girlNameList_disk1);
        $this->girlListIteratorShow($girlNameList_disk1);
        out("==========================================");
        $girlNameList_disk2 = $this->processOneDir($hard_disk2_path);
        ksort($girlNameList_disk2);
        $this->girlListIteratorShow($girlNameList_disk2);
        out("==========================================");
        $this->compareSameGirlNameList($girlNameList_disk1,$girlNameList_disk2);
    }

    function compareSameGirlNameList($list1,$list2){
        foreach ($list1 as $k1=>$v1){
            foreach ($list2 as $k2=>$v2){
                if($k1 == $k2){
                    out($k1);
                    break;
                }
            }
        }
        out("compareSameGirlNameList finish.");
    }

    function girlListIteratorShow($girlNameList){
        foreach ($girlNameList as $k=>$v){
            out($k ." : ".$v);
        }
    }

    function processOneDir($path){
        $file_list = scan_file($path,1);
        if(!$file_list){
            var_dump("file_list empty....");exit;
        }

        out("processOneDir $path , count:".count($file_list));


        $girlNameList = array();
        foreach ($file_list as $k=>$v){
            if(substr($v,0,1) == "."){
//                out("ignore file:".$v);
                continue;
            }

            $fileNameArr = explode("-",$v);
            $girlName = strtolower(trim($fileNameArr[0]));
//            out("".$fileNameArr[0]);
            if(strpos($girlName,"&") === false){
                if(!isset($girlNameList[$girlName] ) ){
                    $girlNameList[$girlName] = 1;
                }else{
                    $girlNameList[$girlName] ++;
                }
            }else{
                $girlGroup = explode("&",$girlName);
                foreach ($girlGroup as $k2=>$v2){
                    $v2 = trim($v2);
                    if(!isset($girlNameList[$v2] ) ){
                        $girlNameList[$v2] = 1;
                    }else{
                        $girlNameList[$v2] ++;
                    }
                }
            }
        }

        return$girlNameList;
    }
}

//function out($str,$ln = 1){
//    if($ln){
//        $str .= "\n";
//    }
//    echo $str;
//}
