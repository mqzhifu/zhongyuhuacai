<?php
class Other{
    public $debug = 1;

    function tt($info,$br = 1){
        if($this->debug){
            echo $info;
            if($br){
                echo  "<br/>";
            }
        }
    }
    //求阶乘的结果，结尾为0的数
    function zeroNum1($num){
        $rs = 0;
        $cur = 0;
        for ($i=5 ; $i <=$num ; $i=$i+5) {
            $cur = $i;
            var_dump($cur);
            while ($cur % 5 == 0){
                $rs++;
                $cur /= 5;
            }
        }


        exit;
    }

    //求，1-N（N为正整数，且递增），中，一共出现多少个1
    function intSeeOne($n = 0){
//        $n = 14;
        $count = 0;
        for ($i=1 ; $i != $n+1 ; $i++) {
//            echo $i . " ";
            $count += $this->intSeeOneCount($i);
//            $this->tt("");
        }
        $this->tt("1 - $n , rs:".$count );
    }

    function intSeeOneCount($n){
        $count = 0;
        while($n != 0){
            if($n % 10 == 1){
                $count++;
//                echo $n . "";
            }


            $n /= 10;
            $n = (int)$n;
        }

        return $count;
    }
    //方法2
    function intSeeOne2($n = 0){

    }

    public $cn = array("一","二","三","四","五","六","七","八","九","十");
    public $cnUnit = array(0=>"十",1=>'百',2=>'千',3=>'万',4=>'亿');
    function numberToLanguage($n,$type){
        $en = array("one",'two','three','four','five','six','seven'.'eight','nine','ten','eleven','twelve','thirteen','fourteen','fifteen'.'sixteen','seventeen','eighteen','nineteen'    ,'twenty');


        $n = 99;
        $n = 8888;

        if($type == 'cn'){
            $nStr =(string)$n;
            $length = strlen($nStr);
            if($n < 10)
                return $this->cn[$n - 1];


            if($n <= 99){
                return $this->numberToLanguageTo99($nStr);
            }

            if($n <= 999){
                $hundred = $this->cn[$nStr[0] - 1] . $this->cnUnit[1];
                $rs = $hundred;
                $ten = $this->numberToLanguageTo999(substr($nStr,1));
                return $rs . $ten;
            }

            if($n<= 9999){
                $thousand = $this->cn[$nStr[0] - 1] . $this->cnUnit[2];
                return $thousand . $this->numberToLanguageTo999(substr($nStr,1));
            }

        }
    }

    function numberToLanguageTo999($nStr){
        $hundred = $this->cn[$nStr[0] - 1] . $this->cnUnit[1];
        $rs = $hundred;
        $ten = $this->numberToLanguageTo99(substr($nStr,1));
        return $rs . $ten;
    }


    function numberToLanguageTo99($nStr){
        $tenBit = $this->cn[$nStr[0] - 1] . $this->cnUnit[0];
        $rs = $tenBit;
        if($nStr[1] != 0){
            $rs = $tenBit . $this->cn[$nStr[1] - 1];
        }
        return $rs;
    }
    //分在糖果
    function distributeSugar(){
        $arr = array(
            1,4,7,10,8,7,6,5,3,12,14,19,13,10
        );

        var_dump($arr);

        //先将数组，打散若干的小数组，类似 爬坡
        $list = [];
        $current = 0;
        $direction = "left";//left or right
        $listLastElement = $arr[0];
        $list[$current][] = $listLastElement;
        for ($i=1 ; $i <= count($arr) - 1 ; $i++) {
            $listLastElement =  $list[$current][count($list[$current]) - 1];
            echo "$listLastElement:$listLastElement ";
            if($arr[$i] > $listLastElement){
                if($direction == "left"){
                    $list[$current][] = $arr[$i];
                }else{
                    $direction = 'left';
                    $current++;
                    $list[$current][] = $listLastElement;
                    $list[$current][] = $arr[$i];
                }
            }else{
                if($direction == "right"){
                    $list[$current][] = $arr[$i];
                }else{
                    $direction = 'right';
                    $current++;
                    $list[$current][] = $listLastElement;
                    $list[$current][] = $arr[$i];
                }
            }
        }
        var_dump($list);

        for ($i=0 ; $i < count($list); $i++) {
            if(   $i == count($list) ){

            }else{
                if(  $i % 2 == 0){
                    $cntCurrentList = count( $list[$i]);
                    $cntNextList = count( $list[$i+1]);
                    if( $cntCurrentList >= $cntNextList){
                        $loopEnd = $cntCurrentList;
                    }else{
                        $loopEnd = $cntCurrentList - 1;
                    }

                    for($j=1;$j<=$loopEnd;$j++){
                        echo $j . " ";
                    }
                }else{
                    $cntCurrentList = count( $list[$i]);
                    $cntNextList = count( $list[$i-1]);
                    if( $cntCurrentList >= $cntNextList){
                        $loopEnd = $cntCurrentList - 1;
                    }else{
                        $loopEnd = $cntCurrentList;
                    }

                    for($j=1;$j<=$loopEnd;$j++){
                        echo $j . " ";
                    }
                }

//                $listLastElement =  $list[$i][count($list[$i]) - 1];
//                $nextListFirstElement = $list[$i][0];
            }
        }

    }

}