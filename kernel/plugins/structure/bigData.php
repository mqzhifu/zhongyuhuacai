<?php
//大数据 处理
class BigData{
    public $testPath = "D:\www\z_service\kernel\plugins\structure/testData/";
    //一个大文件里，全是整形数字 ，找出出现 次数最多的前10个数
    function testFindNumberCnt(){
        $lines = $this->getFileLine();
        _p("total lines:$lines");

        if($lines < 50000){

        }else{
            $fileMapFD = [];
            $tmpFileNums = 10;
            for ($i=0 ; $i <$tmpFileNums ; $i++) {
                $newFileName = $this->testPath."findNumberCntTmp$i".".txt";
                //创建文件，并打开FD
                $fileMapFD[$i] = fopen($newFileName,"w+");
            }
            $fileName = "findNumberCnt.txt";
            $pathFile = $this->testPath.$fileName;

            $fd = fopen($pathFile,'r');

            $tmp = "";
            while(!feof($fd)){
                $x = 0;
                $numbers = [];
                $numberBuf = fread($fd,102400);
                for ($i=0 ; $i < strlen($numberBuf) ; $i++) {
                    if( $numberBuf[$i] == "\n"){
                        $x++;
                        $numbers[$x] = $tmp;
                        $tmp="";
                        continue;
                    }
                    $tmp .= $numberBuf[$i];
                }

                foreach ($numbers as $k=>$v) {
                    $mod = $v % $tmpFileNums;
                    fwrite($fileMapFD[$mod],$v. "\r\n");
                }
            }

            $hashMapCnt = [];
            foreach ($fileMapFD as $k=>$fd) {
                rewind($fd);
                while(!feof($fd)){
                    $x = 0;
                    $numberBuf = fread($fd,102400);
                    for ($i=0 ; $i < strlen($numberBuf) ; $i++) {
                        if( $numberBuf[$i] == "\n"){
                            $x++;
                            $numbers[$x] = $tmp;
                            if(isset($hashMapCnt[$k][$tmp])){
                                $hashMapCnt[$k][$tmp]++;
                            }else{
                                $hashMapCnt[$k][$tmp] = 1;
                            }
                            $tmp="";
                            continue;
                        }
                        $tmp .= $numberBuf[$i];
                    }

                }
            }

            //关闭FD
            //删除临时文件
        }
    }

    function getFileLine($ln = "\r"){
        $fileName = "findNumberCnt.txt";
        $pathFile = $this->testPath.$fileName;
        $size = filesize($pathFile);
        $fileSize = format_bytes($size,'mb');
        if($fileSize > 0.5){//500KB 以上
            $fd = fopen($pathFile,'r');
            $numbers = [];
            $x = 0;
            $tmp = "";
            while(!feof($fd)){
                $numberBuf = fread($fd,102400);
                for ($i=0 ; $i < strlen($numberBuf) ; $i++) {
                    if( $numberBuf[$i] == "\n"){
                        $x++;
                        $numbers[$x] = $tmp;
                        $tmp="";
                        continue;
                    }
                    $tmp .= $numberBuf[$i];
                }
            }

            return count($numbers);
        }else{
            $fileContent = file($pathFile);
            return count($fileContent);
        }
    }

    function makeTestData(){
        $file = $this->testPath. "findNumberCnt.txt";
        $end = 100000;
        $fd = fopen($file,"w+");
        for ($i=0 ; $i < $end; $i++) {
            $r = rand(0,100000);
            fwrite($fd,$r . "\n");
        }

        exit;
    }

    function findNumberCnt($k){

    }
}