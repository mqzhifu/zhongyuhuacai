<?php

/**
 * @Author: xuren
 * @Date:   2019-04-02 18:25:18
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-04-04 16:55:36
 */
class importQA{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr)
    {
        if(PHP_OS == 'WINNT'){
            exec('chcp 936');
        }

        set_time_limit(0);
//    	$data = file("D:\qa.txt");

        include PLUGIN ."phpexcel/PHPExcel.php";


        $objRead = new PHPExcel_Reader_Excel2007();
//        $rs = $objRead->canRead("d:/a.xlsx");
        $obj = $objRead->load("d:/a.xlsx");  //建立excel对象
//        $currSheet = $obj->getSheet(0);   //获取指定的sheet表

        $cellName = array('A', 'B', 'C', 'D', 'E', 'F','G');



        $currSheet = $obj->getSheet(0);   //获取指定的sheet表
        $columnH = $currSheet->getHighestColumn();   //取得最大的列号
        $columnCnt = array_search($columnH, $cellName);
        $rowCnt = $currSheet->getHighestRow();   //获取总行数
        $data = array();

        for($_row=1; $_row<=$rowCnt; $_row++){  //读取内容
            for($_column=0; $_column<=$columnCnt; $_column++){
                $cellId = $cellName[$_column].$_row;
                $cellValue = $currSheet->getCell($cellId)->getValue();
                //$cellValue = $currSheet->getCell($cellId)->getCalculatedValue();  #获取公式计算的值
                if($cellValue instanceof PHPExcel_RichText){   //富文本转换字符串
                    $cellValue = $cellValue->__toString();
                }
                $data[$_row][] = $cellValue;
            }
        }


        $header = $data[1];
        unset($data[1]);

    	foreach ($data as $k=>$v) {
//    	    $line = explode("\t",$v);
            $line = $v;
            foreach ($line as $k2=>$v2) {
                o($v2);
            }
            $option = array($line[2],$line[3],$line[4],$line[5]);
            $option = json_encode($option);


//            if(!$option[6]){
//                $option[6] = 1;
//            }


            $sql = "insert into qa(`cate`,`title`,`level`,`type`,`option`,`rs`) values('{$line[0]}','{$line[1]}','{$line[6]}','1','{$option}','1')";
//            $sql = iconv("GBK",'utf-8',$sql);
            $rs = UserModel::db()->execute($sql);

            o($rs,1);
    	}

    }
}

function o($str,$n = 0){
    if(PHP_OS == 'WINNT'){
        $str = iconv("UTF-8","GBK//IGNORE",$str)."\r\n";
    }

    echo $str;
    if($n){
        echo "\n";
    }
}
