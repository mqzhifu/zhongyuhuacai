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
        $this->curlGetGirlInfo($diskData);
//        $this->compareDiff($diskData,$excelData);
    }

    function curlGetGirlInfo($girlNameList){
        $domain = "https://www.babepedia.com/babe/";
        $cnt = 0;
        foreach ($girlNameList as $k=>$v){
            if($cnt > 10){
                break;
            }
            $curlLib = new CurlLib();
            $girlName = str_replace(" ","_",$k);
            $url = $domain . $girlName;
            out($url);
            $html = $curlLib->send($url,1,null,1);
            if(!$html || $html['code'] != 200 || !$html['msg']){
                out("err===================== :".$k);
                return false;
            }
            $this->parserHtml($html["msg"],$k);
//            var_dump($html);exit;
            sleep(500);//睡眠 500 毫秒，避免被对方给拉黑
            $cnt++;
        }
    }

    function parserHtml($html,$girlName){

        if (stripos($html,"sorry") !== false){
            out("err===================== sorry not found:".$girlName);
            return false;
        }

        $row = array("Alias"=>"","Age"=>"","Height"=>"","Weight"=>"","Born"=>"","Birthplace"=>"","Nationality"=>"","Ethnicity"=>"","Measurements"=>"","BraCupSize"=>"","BodyType"=>"");

        $myPregRs = $this->myPregMatchAll('/<h2 id=\'aka\'>(.*)<img/isU',$html,1,0);
        if($myPregRs){
            $row["Alias"] = str_replace("&nbsp;"," ",$myPregRs);
//            out("$girlAlias:".$girlAlias);
        }
                                               //<ul id="biolist">
        $ulLi = $this->myPregMatchAll('/<ul id=\"biolist\">(.*)<\/ul>/isU',$html,1,0);

        if($ulLi){
            $row["Age"] = $this->myPregMatchAll('/<span class=\"label\">Age:<\/span>(.*)<\/li/isU',$ulLi,1,0);
            $row["Height"] = $this->myPregMatchAll('/<span class=\"label\">Height:<\/span>(.*)<\/li/isU',$ulLi,1,0);
            $row["Weight"] = $this->myPregMatchAll('/<span class=\"label\">Weight:<\/span>(.*)<\/li/isU',$ulLi,1,0);
            $row["Born"] = $this->myPregMatchAll('/<span class=\"label\">Born:<\/span>(.*)<\/li/isU',$ulLi,1,0);
            $row["Birthplace"] = $this->myPregMatchAll('/<span class=\"label\">Birthplace<\/span>(.*)<\/li/isU',$ulLi,1,0);
            $row["Nationality"] = $this->myPregMatchAll('/<span class=\"label\">Nationality:<\/span>(.*)<\/li/isU',$ulLi,1,0);
            $row["Ethnicity"] = $this->myPregMatchAll('/<span class=\"label\">Ethnicity:<\/span>(.*)<\/li/isU',$ulLi,1,0);

            $row["Measurements"] = $this->myPregMatchAll('/<span class=\"label\">Measurements:<\/span>(.*)<\/li/isU',$ulLi,1,0);
            $row["BraCupSize"] = $this->myPregMatchAll('/<span class=\"label\">Bra\/cup size:<\/span>(.*)<\/li/isU',$ulLi,1,0);
            $row["BodyType"] = $this->myPregMatchAll('/<span class=\"label\">Body type:<\/span>(.*)<\/li/isU',$ulLi,1,0);

            var_dump($row);exit;
        }




        var_dump("finish");exit;

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
        $hard_disk1_path = "/Volumes/Elements/欧美";
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

        out("processOneDir $path , count:".count($file_list));


        $girlNameList = array();
        foreach ($file_list as $k=>$v){

            if(substr($v,0,1) == "."){
//                out("ignore file:".$v);
                continue;
            }
//            out($v);
            $fileNameArr = explode("-",$v);
//            var_dump($fileNameArr);
            if(count($fileNameArr) > 2 || count($fileNameArr) < 2){
                out("============ err1:".$v);
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
                    $v2 = trim($v2);
                    if(!isset($girlNameList[$v2] ) ){
                        $girlNameList[$v2] = 1;
                    }else{
                        $girlNameList[$v2] ++;
                    }
                }
            }
        }

        return $girlNameList;
    }
}

//function out($str,$ln = 1){
//    if($ln){
//        $str .= "\n";
//    }
//    echo $str;
//}
