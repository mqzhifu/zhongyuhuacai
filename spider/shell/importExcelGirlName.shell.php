<?php
class ImportExcelGirlName
{
    public $config = null;

    public function __construct($c)
    {
        $this->commands = $c;
        $config = ConfigCenter::get(APP_NAME, "main");
        $this->config = $config['common'];
    }
    public function run($argc){
        //从excel 中 读取 girlName
//        $excelData = $this->importExcelGirlsName();
        $diskData = $this->scanDiskFileName();
        out("girls cnt:".count($diskData));
//        $this->filterDbField();
//        $this->checkGirlInDb($diskData);
//        $this->curlGetGirlInfo($diskData);
//        $this->compareDiff($diskData,$excelData);
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
//            array("alias"=>$v["alias"],"age"=>$v["age"],"height"=>$v["height"],"weight"=>$v["weight"],"born"=>$v["born"]);
        out("updateData alias:".$updateData['alias']." ".$updateData['age']." ".$updateData['height']." ".$updateData['weight']." ".$updateData['born']);
        return $updateData;

    }
    function filterDbField(){
        $list = GirlOumeiModel::db()->getAll();
        foreach ($list as $k=>$v){
            $updateData = $this->parserField($v);
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
        var_dump($diffList);exit;
        $this->curlGetGirlInfo($diffList);
    }

    function curlGetGirlInfo($girlNameList){
        $domain = "https://www.babepedia.com/babe/";
//        $cnt = 0;
//        return 0;
        foreach ($girlNameList as $k=>$v){
//            if($cnt > 10){
//                break;
//            }
            $curlLib = new CurlLib();
            $girlName = str_replace(" ","_",$k);
            $url = $domain . $girlName;
            out($url);
            $html = $curlLib->send($url,1,null,1);
            if(!$html || $html['code'] != 200 || !$html['msg']){
                out("err=====================1 :".$k);
                return false;
            }
            $this->parserHtml($html["msg"],$k);
//            var_dump($html);exit;
            usleep(50);//睡眠 500 毫秒，避免被对方给拉黑
//            $cnt++;
        }
    }

    function parserHtml($html,$girlName){

        if (stripos($html,"No results") !== false){
            out("err=====================2 sorry not found:".$girlName);
            return false;
        }

        $row = array(
            "name"=>$girlName,"file_num"=>0,"add_time"=>time(),"up_time"=>0,
            "alias"=>"","age"=>"","height"=>"","weight"=>"","born"=>"","birthplace"=>"",
            "nationality"=>"","ethnicity"=>"","measurements"=>"","bra_cup_size"=>"","body_type"=>"");

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
//        var_dump($row);
        //$updateData = array("alias"=>$v["alias"],"age"=>$v["age"],"height"=>$v["height"],"weight"=>$v["weight"],"born"=>$v["born"]);
        $processField = $this->parserField($row);
        $row['age'] = $processField["age"];
        $row['alias'] = $processField["alias"];
        $row['weight'] = $processField["weight"];
        $row['height'] = $processField["height"];
        $row['born'] = $processField["born"];



        var_dump($row);
        $id = GirlOumeiModel::db()->add($row);
        var_dump($id);



//        var_dump("finish");exit;

    }
    function trimRow($row){
        $newRow = array();
        foreach ($row as $k=>$v){
            $newRow[$k] = trim($v);
        }

        return $newRow;
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
    function scanDiskFileName(){
        $hard_disk1_path = "/Volumes/Elements/A/欧美";
//        $hard_disk2_path = "/Users/xiaoz/Desktop/a-x/欧美";
//        $hard_disk1_path = "/Users/mayanyan/Desktop/x-a";

        $girlNameList_disk1 = $this->processOneDir($hard_disk1_path);
        ksort($girlNameList_disk1);
        $this->girlListIteratorShow($girlNameList_disk1);
        return $girlNameList_disk1;
        out("//==============================");
//        $girlNameList_disk2 = $this->processOneDir($hard_disk2_path);
//        ksort($girlNameList_disk2);
//        $this->girlListIteratorShow($girlNameList_disk2);
//        out("//==============================");
//        $total = $this->mergerTotalGirls($girlNameList_disk1,$girlNameList_disk1);
//        $this->girlListIteratorShow($total);

        return $total;
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
