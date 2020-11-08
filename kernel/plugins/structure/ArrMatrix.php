<?php
//数组与矩阵
class ArrMatrix{

    public $debug = 1;

    function tt($info,$br = 1){
        if($this->debug){
            echo $info;
            if($br){
                echo  "<br/>";
            }
        }
    }


    function showRectangle($arr = [] ){
        $arr = array(
            array( 1,2,3,4,),
            array(5,6,7,8,),
            array( 9,10,11,12),
            array(13,14,15,16),

        );

        foreach ($arr as $k=>$v) {
            foreach ($v as $k2=>$v2) {
                if($v2 <10)
                    $v2 = "0".$v2;
                echo $v2 ." ";
            }

            echo "<br/>";
        }

        $leftX = 0;
        $leftY = 0;

        $rightX = count($arr) - 1;
        $rightY = count($arr[0]) - 1;



        while($leftY <= $rightY && $leftX <= $rightX ){
            $this->showRectanglePrint($arr,$leftX++,$leftY++,$rightX--,$rightY--);
        }
    }

    function showRectanglePrint($arr,$leftX,$leftY,$rightX,$rightY){
        $this->tt("start:".$leftX." ".$leftY." ".$rightX." ".$rightY);
        $curLeftX = $leftX;
        $curLeftY = $leftY;


        if($leftX == $rightX) {
            exit(-2);
        }elseif($leftY == $rightY){
            exit(-1);
        }else{
            while ($curLeftY != $rightY){
                echo $arr[$leftX][$curLeftY]." ";
                $curLeftY++;
            }

            $this->tt("");

            while ($curLeftX != $rightX){
                echo $arr[$curLeftX][$rightY]." ";
                $curLeftX++;
            }

            $this->tt("");

            while ($curLeftY != $leftY){
                echo $arr[$rightX][$curLeftY]." ";
                $curLeftY--;
            }

            $this->tt("");

            while ($curLeftX != $leftX){
                echo $arr[$curLeftX][$leftY]." ";
                $curLeftX--;
            }

            $this->tt("");
        }

    }
    //将矩形旋转90度
    function rotate90Rectangle($arr = []){
        $arr = array(
            array( 1,2,3,4,),
            array(5,6,7,8,),
            array( 9,10,11,12),
            array(13,14,15,16),

        );

        $this->print2Arr($arr);

        $leftX = 0;
        $leftY = 0;

        $rightX = count($arr) - 1;
        $rightY = count($arr[0]) - 1;

        $this->arr = $arr;
        while ($leftX < $rightX){
            $this->rotate90RectangleProcess($leftX++,$leftY++,$rightX--,$rightY--);
        }

        $this->print2Arr($this->arr);
    }

    function print2Arr($arr){
        foreach ($arr as $k=>$v) {
            foreach ($v as $k2=>$v2) {
                if($v2 <10)
                    $v2 = "0".$v2;
                echo $v2 ." ";
            }

            echo "<br/>";
        }
    }

    function rotate90RectangleProcess($leftX,$leftY,$rightX,$rightY){
        $times = $rightY - $leftY;
        $this->tt("start:".$leftX." ".$leftY." ".$rightX." ".$rightY. ",time:$times");
        for($i=0;$i != $times;$i++){
            $tmp = $this->arr[$leftX][$leftY + $i];
            $this->arr[$leftX][$leftY+$i]  = $this->arr[$rightX - $i][$leftY];
            $this->arr[$rightX - $i][$leftX] = $this->arr[$rightX][$rightY - $i];
            $this->arr[$rightX][$rightY - $i] = $this->arr[$leftX + $i][$rightY];
            $this->arr[$leftX + $i][$rightY] = $tmp;
        }
    }
    //一个整形数组，从中找出，最小的K个值
    function arrTopK(){

    }

    //一个整形数组，计算其中，需要 排序的值的个数
    function needSortMinLength(){

    }
    //求一个矩阵中 最大矩阵 ，假设：矩阵二维数组中的元素只有0和1，求连续1组成的最大矩阵
    function matrixRectangle($arr = []){
        $arr = array(
            array(0,1,1,1,1,),
            array(0,1,0,0,1,),
            array(0,1,0,0,1,),
            array(0,1,1,1,1,),
            array(0,1,0,1,1,),
        );

        $arr = array(
            array(0,1,1,1,1,),
            array(0,1,1,0,1,),
            array(0,1,0,0,1,),
            array(0,1,1,1,1,),
            array(0,1,0,1,1,),
        );



        $this->print2Arr($arr);

        $rowLength = count($arr[0]);
        $lineLength = count($arr);

        for ($i=0 ; $i < $lineLength ; $i++) {
            if($i == $lineLength - 1){//证明 是最后一行了
                break;
            }

            for ($j=0 ; $j < $rowLength ; $j++) {
                if($arr[$i][$j] == 0){
                    continue;
                }

//                var_dump($j);
//                var_dump($arr[$i][$j]);exit;
                $location = 0;
                $Rectangle = null;
                for($x = $j ; $x < $rowLength;$x++){
                    if($arr[$i][$x] == 0){
                        break;
                    }
                    $Rectangle[$i][] = $i.",".$x;
                    $location =$x;
                }

                if(!$location){
                    continue;
                }


                if(count($Rectangle[$i]) <=1){//证明 只有一个元素，是无法组成矩阵 的
                    continue;
                }

                if(count($Rectangle[$i]) <=2){//证明 只能2列，最多也就是2X2
                    $this->matrixRectangleInner($arr,$i,$j,$location,$lineLength,$Rectangle);
                    continue;
                }

                //这是大于2列的情况，假如3列，程序会直接 按3列处理，会忽略到  1跟2 ，2跟3 列 也可能 是一个矩阵 的情况
                $this->matrixRectangleInner($arr,$i,$j,$location,$lineLength,$Rectangle);
//                for($p=$j){
//
//                }

            }
        }

    }


    function matrixRectangleInner($arr,$i,$j,$location,$lineLength,$Rectangle){
        $leftStart = $j;
        $leftEnd = $location;
        //从$leftStart起始位置到$leftEnd终点位置，都是1
        //下面证明,下一列，在相同的两个位置之间，是不是也也都是1
        $maxRowElement =  $leftEnd - $leftStart +1;
        $lineStart = $i + 1;


        $this->tt("leftStart:$leftStart,leftEnd:$leftEnd,maxRowElement:$maxRowElement,lineStart=$lineStart");
        $stop = 0;
        for ($y=$lineStart ; $y < $lineLength ; $y++) {
            if($stop){
                break;
            }
            $rowNumber = [];
            $count = 0;
            for ($z=$leftStart ; $z <=$leftEnd ; $z++) {

                if($arr[$y][$z] == 0 ){
                    $this->tt("循环停止位置:".$y." ".$z);
                    $stop = 1;
                    break;
                }
                $count++;
                $rowNumber[] =  $y.",".$z;
            }

            if($count == $maxRowElement){//证明这一行都能和上一行 对上，也就是这一行满足所有条件
                $Rectangle[$y] =  $rowNumber;
            }
        }

        if(count($Rectangle) > 1){
            var_dump($Rectangle);
        }
    }
}