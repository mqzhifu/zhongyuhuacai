<?php
class ClearMacTmpFile
{
    public $config = null;

    public function __construct($c)
    {
        $this->commands = $c;
        $config = ConfigCenter::get(APP_NAME, "main");
        $this->config = $config['common'];
    }
    public function run($argc){
        $hard_disk_path = "/Volumes/SAMSUNG-TF/Switch16.0.3";
        $file_list = get_dir($hard_disk_path);
        var_dump($file_list);exit;
        foreach ($file_list as $k=>$v){
//            if(substr($v,0,1) == "."){
                out( $k,$v);
//                continue;
//            }

        }
    }



    function processOneDir($path){
        $file_list = scan_file($path,1);
        if(!$file_list){
            var_dump("file_list empty....");exit;
        }

        out("processOneDir $path , file count:".count($file_list));


        $girlNameList = array();
        $realFileCnt = 0;
        foreach ($file_list as $k=>$v){

            if(substr($v,0,1) == "."){
//                out("ignore file:".$v);
                continue;
            }
//            out($v);
            $realFileCnt++;
            $fileNameArr = explode("-",$v);
//            var_dump($fileNameArr);
            if(count($fileNameArr) > 2 || count($fileNameArr) < 2){
                out("============ err3:".$v);
                continue;
            }
            $girlName = strtolower(trim($fileNameArr[0]));
            $girlName = strtolower(str_replace("."," ",$girlName));
//            out($girlName);
//            out("".$fileNameArr[0]);
            if(strpos($girlName," and ") === false){
                if(!isset($girlNameList[$girlName] ) ){
                    $girlNameList[$girlName] = 1;
                }else{
                    $girlNameList[$girlName] ++;
                }
            }else{
                $girlGroup = explode(" and ",$girlName);
                foreach ($girlGroup as $k2=>$v2){
//                    if($v2 == 'alice'){
//                        var_dump($v);exit;
//                    }
                    $v2 = trim($v2);
                    if(!isset($girlNameList[$v2] ) ){
                        $girlNameList[$v2] = 1;
                    }else{
                        $girlNameList[$v2] ++;
                    }
                }
            }
        }
        out("realFileCnt: ".$realFileCnt);

        return $girlNameList;
    }
}

//function out($str,$ln = 1){
//    if($ln){
//        $str .= "\n";
//    }
//    echo $str;
//}
