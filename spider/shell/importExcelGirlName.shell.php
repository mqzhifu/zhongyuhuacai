<?php
class ImportExcelGirlName
{
    public $config = null;
    public $spider = null;
    public function __construct($c)
    {
        $this->commands = $c;
        $config = ConfigCenter::get(APP_NAME, "main");
        $this->config = $config['common'];
        $this->spider = new SpiderData();
    }
    function GetDiskPath(){
        $hard_disk_path = "/Volumes/Elements/film/欧美";
//        $hard_disk_path = "/Users/clarissechamley/Desktop/a";
//        $hard_disk_path = "/Volumes/Elements/A/欧美待处理";
//        $hard_disk_path = "/Volumes/Elements/A/欧美待处理2";
//        $hard_disk_path = "/Users/xiaoz/Desktop/a-x/欧美";
//        $hard_disk_path = "/Users/mayanyan/Desktop/x-a";
        return $hard_disk_path;
//
    }
    public function run($argc){
//        $this->filterDbField();
        //从excel 中 读取 girlName
//        $excelData = $this->importExcelGirlsName();

        $hard_disk_path = $this->GetDiskPath();
//        $this->compareTwoDiskFile();
        //先检查文件名正确性
        //文件名规则：只能出现一个：中划线，前面是名字
        //所有文件名均变成小写字母,名字中的点会被替换成空格
        $this->fixFileName($hard_disk_path);
        //扫描硬盘上的文件，并根据文件名分析出 girlName
        $diskData = $this->scanDiskFileName($hard_disk_path);
        out("girls cnt:".count($diskData));
        //更新已有文件的总数
        $this->upRecordFileName($diskData);
//        $this->checkGirlInDb($diskData);
        //抓取全部数据
//        $this->spider->curlGetGirlInfo($diskData);
    }
    //比较两个目录，是否有重复的文件女名
    function compareTwoDiskFile(){
        $hard_disk_path_1 = "/Volumes/Elements/A/欧美";
        $hard_disk_path_2 = "/Volumes/Elements/A/欧美待处理";

        $diskData_1 = $this->scanDiskFileName($hard_disk_path_1);
        $diskData_2 = $this->scanDiskFileName($hard_disk_path_2);
        foreach ($diskData_1 as $k1=>$v1){
            if(isset($diskData_2[$k1])){
                out("repeat:".$k1 .  " " . $v1. " ". $diskData_2[$k1]);
            }
        }
    }
    //抓取回来的数据，更新本地硬盘的文件数
    function upRecordFileName($girlInfoList){
        $list = GirlOumeiModel::db()->getAll();
        if(!count($list)){
            out("GirlOumeiModel get all list is empty ");
            exit;
        }
        foreach ($list as $k=>$record){
            $flag = 0;
            foreach ($girlInfoList as $girlName=>$fileNum){
//                out($girlName);
                if($record['name'] == $girlName){
                    out("$girlName:".$fileNum);
                    $flag = 1;
                    break;
                }
            }
            if(!$flag){
               out("err:"."upRecordFileName 没有找到 girlName:".$record['name']);
            }else{
                $updateData = array("up_time"=>time(),"file_num"=>$fileNum);
                GirlOumeiModel::db()->upById($record['id'],$updateData);
            }


//            if(!$rs){
//                out("err10=======update failed....");
//            }
        }
    }
    function filterDbField(){
        $list = GirlOumeiModel::db()->getAll();
        foreach ($list as $k=>$v){
            $updateData = $this->spider->parserField($v);
           $rs = GirlOumeiModel::db()->upById($v['id'],$updateData);
            if(!$rs){
                out("err10=======update failed....");
            }
        }
    }

    function checkGirlInDb($girlList){
        $diffList = array();
        foreach ($girlList as $k=>$v){
            $row = GirlOumeiModel::db()->getRow("name = '$k'");
            if(!$row){
                $diffList[$k] = $v;
            }
        }
        if(!count($diffList)){
            out("checkGirlInDb: cnt = 0");
            return 0;
        }
        $this->spider->curlGetGirlInfo($diffList);
    }
    function compareDiff($diskData,$excelData){
        out("//============ compare ==========");
        foreach ($diskData as $k1=>$v1){
            $search = 0;
            foreach ($excelData as $k2=>$v2){
                if($k1 == $v2[0]){
                    $search = 1;
                    break;
                }
            }

            if(!$search){
                out($k1);
            }
        }

    }

    //从excel 中 读取 girlName
    function importExcelGirlsName(){
        include PLUGIN ."phpexcel/PHPExcel.php";
        $objRead = new PHPExcel_Reader_Excel2007();
        $obj = $objRead->load("/Volumes/Elements/欧美.xlsx");

        $cellName = array('A', 'B', 'C', 'D', 'E',"H");

        $currSheet = $obj->getSheet(0);   //获取指定的sheet表
        $columnH = $currSheet->getHighestColumn();   //取得最大的列号
        $columnCnt = array_search($columnH, $cellName);
        $rowCnt = $currSheet->getHighestRow();   //获取总行数


//        out("columnH:".$columnH);
//        out("columnCnt:".$columnCnt);
        out("rowCnt:".$rowCnt);

        $data = array();

        for($_row=2; $_row<=$rowCnt; $_row++){  //读取内容,第一行是标题用不着
            for($_column=0; $_column<=$columnCnt; $_column++){
                $cellId = $cellName[$_column].$_row;
                $cellValue = $currSheet->getCell($cellId)->getValue();
                //$cellValue = $currSheet->getCell($cellId)->getCalculatedValue();  #获取公式计算的值
                if($cellValue instanceof PHPExcel_RichText){   //富文本转换字符串
                    $cellValue =  $cellValue->__toString();
                }
                $cellValue = strtolower(trim($cellValue));
//                if(!$cellValue){
//                    continue;
//                }
                $data[$_row][] = $cellValue;
            }
        }

        $girlNameStrMaxLen = 0;
        foreach ($data as $k=>$v){
            if (strlen($v[0]) > $girlNameStrMaxLen){
                $girlNameStrMaxLen = strlen($v[0]);
            }
        }

        foreach ($data as $k=>$v){
            out( $this->outAddSpace($v[0],$girlNameStrMaxLen). " 身高：".$v[1] );
//            out("姓名：".$v[0]. " 身高：".$v[1]);
        }

        return $data;
    }

    function outAddSpace($str,$maxLen){
        $m = $maxLen - strlen($str);
        if($m <=0 ){
            return $str;
        }

        $space = "";
        for($i=0;$i<$m;$i++){
            $space = $space . " ";
        }

        return $str . $space;

    }

    //扫描硬件文件名，对文件名进行统一格式化处理
    function scanDiskFileName($hard_disk_path){

        $girlNameList_disk = $this->processOneDir($hard_disk_path);
        ksort($girlNameList_disk);
        $this->girlListIteratorShow($girlNameList_disk);
//        return $girlNameList_disk1;
        out("//==============================");
//        $total = $this->mergerTotalGirls($girlNameList_disk1,$girlNameList_disk1);
//        $this->girlListIteratorShow($total);
        return $girlNameList_disk;
//        return $total;
    }
    //从磁盘的两个位置读取 girlName ，但是得合并了
    function mergerTotalGirls($list1,$list2){
        $total = [];
        foreach ($list1 as $k1=>$v1){
            $search = 0;
            foreach ($list2 as $k2=>$v2){
                if($k1 == $k2){
                    $total[$k1] = $list1[$k1] + $list2[$k1];
                    $search = 1;
                    break;
                }
            }
            if(!$search){
                $total[$k1 ] = $v1;
            }
        }

        foreach ($list2 as $k2=>$v2){
            $search = 0;
            foreach ($list1 as $k1=>$v1){
                if($k1 == $k2){
                    $search = 1;
                    break;
                }
            }
            if(!$search){
                $total[$k2 ] = $v2;
            }
        }
        return $total;
    }
    //从磁盘中扫描文件，根据文件名，拿到 girlName ，进行格式化输出
    function girlListIteratorShow($girlNameList){
        $girlNameStrMaxLen = 0;
        foreach ($girlNameList as $k=>$v){
            if (strlen($k) > $girlNameStrMaxLen){
                $girlNameStrMaxLen = strlen($k);
            }
        }

        foreach ($girlNameList as $k=>$v){
//            out($k ." : ".$v);
            out($this->outAddSpace($k,$girlNameStrMaxLen) ." : ".$v);
        }
    }
    //修正文件名前半部分，主要是将女名统一化处理
    function fixFileName($hard_disk_path){
        $file_list = scan_file($hard_disk_path,1);
        if(!$file_list || count($file_list) <= 0){
            var_dump("scan_dir_file list empty....");exit;
        }

        out("processOneDir $hard_disk_path , file count:".count($file_list));
        $realFileCnt = 0;//真实的文件，需要处理的文件总数
        $noNeedCnt = 0;
        $ignoreCnt = 0;
        foreach ($file_list as $k=>$fileName){
            //一些临时文件以.开头的，不用处理
            if(substr($fileName,0,1) == "."){
                $ignoreCnt++;
                out("ignore file:".$fileName);
                continue;
            }

            $realFileCnt++;
            $fileNameArr = explode("-",$fileName);
            if(count($fileNameArr) > 2 || count($fileNameArr) < 2){
                out("文件名错误：只允许出来一个中划线(-) ,fileName:".$fileName);
                exit;
            }
            //名字中的点会被替换成空格
            $girlName = strtolower(str_replace("."," ",strtolower(trim($fileNameArr[0])) ));
            $index = strpos($fileName,"-");
            $newFileName = $girlName ."-". substr($fileName,$index+1);
//            out("oldFileName:".$fileName . " *__________________* newFileName:".$newFileName);
            if($fileName == $newFileName){
                out("文件名正常，不需要处理($fileName)");
                $noNeedCnt++;
                continue;
            }
            rename($hard_disk_path."/".$fileName,$hard_disk_path."/".$newFileName);
//            var_dump(333);exit;
        }
        out("realFileCnt:".$realFileCnt . " no need file count:".$noNeedCnt . " , ignoreCnt:".$ignoreCnt);
    }



    function processOneDir($path){
        $file_list = scan_file($path,1);
        if(!$file_list){
            out("file_list empty....");
            exit;
        }

        out("processOneDir $path , file count:".count($file_list));


        $girlNameList = array();
        $realFileCnt = 0;
        foreach ($file_list as $k=>$v){

            if(substr($v,0,1) == "."){
                out("ignore file:".$v);
                continue;
            }
//            out($v);
            $realFileCnt++;
            $fileNameArr = explode("-",$v);
            if(count($fileNameArr) > 2 || count($fileNameArr) < 2){
                out("文件名错误：只允许出来一个中划线(-) , filName:".$v);
                exit(33);
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

class SpiderData{
    //抓取全部数据
    function curlGetGirlInfo($girlNameList){
        $domain = "https://www.babepedia.com/babe/";
        $cnt = 0;
        foreach ($girlNameList as $girlName=>$v){
            $exist =GirlOumeiModel::db()->getRow("name = '$girlName'");
            if($exist){
                out("err=====================11 :".$girlName);
                continue;
            }

            $curlLib = new CurlLib();
            $girlNameReplace = str_replace(" ","_",$girlName);
            $url = $domain . $girlNameReplace;
            out($url);
            $html = $curlLib->send($url,1,null,1);
            if(!$html || $html['code'] != 200 || !$html['msg']){
                out("err=====================1 :".$girlName);
                return false;
            }
            $row = $this->parserHtml($html["msg"],$girlName);
            if (!$row){
                out("parserHtml err");
                continue;
            }
            $id = GirlOumeiModel::db()->add($row);
            if(!$id){
                out("insert db failed.");
                exit("err========21");
            }else{
                out("success db id:".$id);
            }

            usleep(50);//睡眠 50 毫秒，避免被对方给拉黑
            $cnt++;

            out("total num:".count($girlNameList) . " process cnt:".$cnt);
        }
    }

    function parserHtml($html,$girlName){

        if (stripos($html,"No results") !== false){
            out("err=====================2 sorry not found:".$girlName);
            return false;
        }

        $row = array(
            "type"=>1,"name"=>$girlName,"file_num"=>0,"add_time"=>time(),"up_time"=>0,
            "alias"=>"","age"=>"","height"=>"","weight"=>"","born"=>"","birthplace"=>"",
            "nationality"=>"","ethnicity"=>"","measurements"=>"","bra_cup_size"=>"","body_type"=>""
        );

        $myPregRs = $this->myPregMatchAll('/<h2 id=\'aka\'>(.*)<\/h2>/isU',$html,1,0);
        if($myPregRs){
            $row["alias"] = str_replace("&nbsp;"," ",$myPregRs);
//            out("$girlAlias:".$girlAlias);
        }
        //<ul id="biolist">
        $ulLi = $this->myPregMatchAll('/<ul id=\"biolist\">(.*)<\/ul>/isU',$html,1,0);

        if($ulLi) {
            $row["age"] = $this->myPregMatchAll('/<span class=\"label\">Age:<\/span>(.*)<\/li/isU', $ulLi, 1, 0);
            $row["height"] = $this->myPregMatchAll('/<span class=\"label\">Height:<\/span>(.*)<\/li/isU', $ulLi, 1, 0);
            $row["weight"] = $this->myPregMatchAll('/<span class=\"label\">Weight:<\/span>(.*)<\/li/isU', $ulLi, 1, 0);
            $row["born"] = $this->myPregMatchAll('/<span class=\"label\">Born:<\/span>(.*)<\/li/isU', $ulLi, 1, 0);
            $row["birthplace"] = $this->myPregMatchAll('/<span class=\"label\">Birthplace<\/span>(.*)<\/li/isU', $ulLi, 1, 0);
            $row["nationality"] = $this->myPregMatchAll('/<span class=\"label\">Nationality:<\/span>(.*)<\/li/isU', $ulLi, 1, 0);
            $row["ethnicity"] = $this->myPregMatchAll('/<span class=\"label\">Ethnicity:<\/span>(.*)<\/li/isU', $ulLi, 1, 0);

            $row["measurements"] = $this->myPregMatchAll('/<span class=\"label\">Measurements:<\/span>(.*)<\/li/isU', $ulLi, 1, 0);
            $row["bra_cup_size"] = $this->myPregMatchAll('/<span class=\"label\">Bra\/cup size:<\/span>(.*)<\/li/isU', $ulLi, 1, 0);
            $row["body_type"] = $this->myPregMatchAll('/<span class=\"label\">Body type:<\/span>(.*)<\/li/isU', $ulLi, 1, 0);
        }
        $row = $this->trimRow($row);
//        var_dump($row);exit;
//        var_dump($row);
        //$updateData = array("alias"=>$v["alias"],"age"=>$v["age"],"height"=>$v["height"],"weight"=>$v["weight"],"born"=>$v["born"]);
        $processField = $this->parserField($row);
        $row['age'] = $processField["age"];
        $row['alias'] = $processField["alias"];
        $row['weight'] = $processField["weight"];
        $row['height'] = $processField["height"];
        $row['born'] = $processField["born"];

        return $row;

    }

    function myPregMatchAll($eg,$str,$indexOne,$index2){
        preg_match_all($eg,$str,$match);
//        var_dump($match);
        if(isset($match[$indexOne]) && $match[$indexOne]){
            if(isset($match[$indexOne][$index2]) && $match[$indexOne][$index2]){
                return $match[$indexOne][$index2];
            }
        }
        return "";
    }

    function trimRow($row){
        $newRow = array();
        foreach ($row as $k=>$v){
            $newRow[$k] = trim($v);
        }

        return $newRow;
    }

    function parserField($row){
        $v = $row;

        $updateData = array("alias"=>$v["alias"],"age"=>$v["age"],"height"=>$v["height"],"weight"=>$v["weight"],"born"=>$v["born"]);
        if($v["alias"]){
            $newAlias = $v["alias"];
            if(substr($v["alias"],0,3) == "aka"){
                $newAlias = trim(substr($v["alias"],3));
            }
            $grepAlias = $this->myPregMatchAll('/(.*)<img(.*)/isU', $newAlias, 1, 0);
            if(!$grepAlias){
                $updateData['alias'] = $newAlias;
//                    out("err=======6:alias not grep.".$v["alias"]);
            }else{
                $updateData['alias'] = $grepAlias;
            }
        }

        if($v["age"]){
            $newAge = trim(str_replace("years young","",$v["age"]));
            if(!$newAge){
                out("err=======6:age not grep.".$v["age"]);
            }else{
                $updateData['age'] = $newAge;
            }

        }

        if($v["height"]){
            $grepHeight = $this->myPregMatchAll('/\(or (.*) cm\)/isU', $v["height"], 1, 0);
            if(!$grepHeight){
                out("err=======4:height not grep.".$v["height"]);
            }else{
                $updateData['height'] = $grepHeight;
            }
        }

        if($v["weight"]){
            $grepWeight = $this->myPregMatchAll('/\(or (.*) kg\)/isU', $v["weight"], 1, 0);
            if(!$grepWeight){
                out("err=======5:weight not grep.".$v["weight"]);
            }else{
                $updateData['weight'] = $grepWeight;
            }
        }

        if($v["born"]){
            $grepBirthdaysMonth = $this->myPregMatchAll('/\/birthdays\/(.*)\'/isU', $v["born"], 1, 0);
            if(!$grepBirthdaysMonth){
                out("err=======7:grepBirthdaysMonth not grep.".$v["born"]);
            }

            $grepBirthdaysYeah = $this->myPregMatchAll('/\/born-in-the-year\/(.*)\'/isU', $v["born"], 1, 0);
            if(!$grepBirthdaysYeah){
                out("err=======8:grepBirthdaysYeah not grep.".$v["born"]);
            }

            if($grepBirthdaysMonth && $grepBirthdaysYeah){
                $arr  = explode("-",$grepBirthdaysMonth);
                $Birthday = $grepBirthdaysYeah ."-".$arr[1] ."-".$arr[1];
                $updateData['born'] = $Birthday;
            }
        }

        if(!$updateData['age']){
            $updateData['age'] = 0;
        }

        if(!$updateData['height']){
            $updateData['height'] = 0;
        }

        if(!$updateData['weight']){
            $updateData['weight'] = 0;
        }

//            array("alias"=>$v["alias"],"age"=>$v["age"],"height"=>$v["height"],"weight"=>$v["weight"],"born"=>$v["born"]);
//        out("updateData alias:".$updateData['alias']." ".$updateData['age']." ".$updateData['height']." ".$updateData['weight']." ".$updateData['born']);
        out("updateData alias:");
        foreach ($updateData as $k=>$v){
            out($k."=>".$v);
        }
        return $updateData;

    }
}

//function out($str,$ln = 1){
//    if($ln){
//        $str .= "\n";
//    }
//    echo $str;
//}
