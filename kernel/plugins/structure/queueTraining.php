<?php

class QueueTraining{
    //给出一个数组，元素均为数字，再找出所有<排列>子集合，再从每个子集合中找出最大值跟最小值 ，然后做减法，值<=NUM
    function subsetMaxLessSubsetMin($arr,$num){
        $subset = $this->subsetBit($arr);

        $list = [];
        $rsList = [];
        foreach ($subset as $k=>$v) {
//            var_dump($v);
            $maxMin = $this->arrMaxMin($v);
            $x = $maxMin['max'] - $maxMin['min'];
            $list[] = $x;
            if($x <= $num){
                $rsList[] = $maxMin['max'] . "- " . $maxMin['min'] . "=$x( ".json_encode($v)." )";
            }
        }

        var_dump($list);
        var_dump($rsList);
//        return $subset;
        echo "//=============================方法1分隔线===========================================<br/>";
        $qmax = new Queue();
        $qmin =  new Queue();

        $i = 0;
        $j = 0;
        $res = 0;
        while($i < count($arr)){
            _p("while start i:".$i);_p(" ");
            while($j < count($arr)){
                while (!$qmin->isEmpty()  && $arr[$qmin->getOneByFooter()] >= $arr[$j]){
                    _p("in qmin");
                    $qmin->popFoot();
                }
                $qmin->pushFoot($j);
                while (!$qmax->isEmpty()  &&  $arr[$qmax->getOneByFooter()] <= $arr[$j]){
                    _p("in qmax");
                    $qmax->popFoot();
                }
                $qmax->pushFoot($j);
                if($arr[$qmax->getOneByHeader()] - $arr[$qmin->getOneByHeader()] > $num){
                    _p("in break:".$arr[$qmax->getOneByHeader()] ."-". $arr[$qmin->getOneByHeader()]);
                    break;
                }
                $j++;
            }
            _p("max: head,".$qmax->link->head.",foot,".$qmax->link->foot     , 0);_p($qmax->getAllByHeader());
            _p("min: head,".$qmin->link->head.",foot,".$qmin->link->foot, 0);_p($qmin->getAllByHeader());

            if($qmin->getOneByHeader() == $i){
                $qmin->popHead();
            }
            if($qmax->getOneByHeader() == $i){
                $qmax->popHead();
            }
            _p("j - i =".$j.  "-". $i);
            $res  += $j - $i;

            $i++;
        }
        var_dump($res);exit;

        return $res;

    }
    //给出一个一维数组，均为数字，计算出该数组中，最大的元素跟最小的
    function arrMaxMin($arr){
        $max = $arr[0];
        $min = $arr[0];

        foreach ($arr as $k=>$v) {
            if($v > $max){
                $max = $v;
            }

            if($v < $min){
                $min = $v;
            }
        }

        return array('max'=>$max,'min'=>$min);

    }

    function subsetMaxLessSubsetMinTest(){
        $rs = $this->subsetMaxLessSubsetMin(array(1,2,3,4),2);
        exit;

    }
    //无进位，2进制递增,$effectiveBit:生效位数
    function binaryIncrement($str = "",$effectiveBit){
        $mod = 1;
        $rsStr = "";
        for ($i= strlen($str)  - 1 ; $i >= 0 ; $i--) {
            if($i >= $effectiveBit){
                break;
            }

            $add = $str[$i] + $mod;
            $rs = $add;
            if($add >= 2){
                $mod = 1;
                $rs = 0;
            }else{
                $mod = 0;
            }

            $rsStr .= $rs;

        }


        $rs = "";
        for ($i= strlen($rsStr)  - 1 ; $i >= 0 ; $i--) {
            $rs .= $rsStr[$i];
        }

        return $rs;
    }

    function subsetBitTest(){
        $rs = $this->subsetBit(array(1,2,3,4,5));
        var_dump($rs);exit;
    }

    //permutationCombination 排列组合，给出一个数组，找出里面的所有子集
    function subsetBit($arr,$m = 0){
        if(!$arr){
            return -1;
        }

        if($m > count($arr)){
            return -2;
        }

        if(!$m){
            $m = count($arr);
        }
        $rs = array();

        $x = "";
        for ($i=0 ; $i <$m ; $i++) {
            $x .= "0";
        }


        while ($x = $this->binaryIncrement($x,$m)){
            if(!$x || $x == '000'){
                break;
            }

            $str = [];
            for ($i=0 ; $i < strlen($x); $i++) {
                if($x[$i] == 0){
                    continue;
                }
                $str[] = $arr[$i];
            }

            $rs[] = $str;
        }

        return $rs;
    }


    //给定一个数组，和一个K，求出每个K元素组成一个新数据的和
    function window($arr,$n){
        var_dump($arr);
        $list = null;
        for ($i=0 ; $i <=count($arr) - $n ; $i++) {
            $list[$i][] = $arr[$i];
            for ($j=1 ; $j <$n ; $j++) {
                $list[$i][] = $arr[$i+$j];
            }
        }
//        var_dump($list);
        $rs = [];
        foreach ($list as $k=>$v) {
            $max = $v[0];
            foreach ($v as $k2=>$v2) {
                if($v2 >$max){
                    $max = $v2;
                }
            }
            $rs[] = $max;
        }

        var_dump($rs);

        _p("====================================================================");
        //======以上时间复杂度为 length($arr) X n  ，最笨的方法  ==========

        $queue = new Queue();
        $cnt = 0;
        foreach ($arr as $k=>$v) {
            _p("cnt:".$cnt .", k:$k , v:".$v." . ",0);
            _p("link:",0);
            _p($queue->getAllByHeader() );
//            if($k > count($arr) - $n){
//                break;
//            }
//            _p($k . " $v:",0);
            if($queue->isEmpty()){
                $queue->pushHead($k);
                $cnt++;
                continue;
            }


            $head = $queue->getOneByHeader();
            if($head + $n  == $k){
                $queue->popHead();
            }

            $dead = 0;
            while( ( $lastElement = $queue->getOneByFooter() ) != -1 ){
                if($dead >= 100){
                    exit("dead loop");
                }
                if($v <= $arr[$lastElement]){
                    $queue->pushFoot($k);
                    $cnt++;
                    break;
                }

                $queue->popFoot();
                $dead++;
            }

            if($queue->isEmpty()){
                $queue->pushHead($k);
                $cnt++;
            }

            if($cnt >= $n){
                $info = $queue->getOneByHeader();
                _p("rs:".$arr[$info]);
            }
        }


        exit;
    }

    function windowTest( ){
        $arr = [4,3,5,4,3,3,6,7];
        $this->window($arr,3);
    }
    //最大值为根，余下为二叉树
    function maxTree($arr){
        var_dump($arr);
        exit;
    }

    function maxTreeTest(){
        $arr = [3,4,5,1,2];
        $this->maxTree($arr);
    }

    function maxRectangle($arr){
        var_dump($arr);

        $list = array();
        for ($i=0 ; $i <count($arr[0]) ; $i++) {
            $list[0][] = $arr[0][$i];
        }

        for ($i=1 ; $i < count($arr) ; $i++) {
            for ($j=0 ; $j < count($arr[0]) ; $j++) {
                if( $arr[$i][$j] == 0){
                    $list[$i][] = 0;
                }else{
                    $list[$i][] = $list[$i - 1][$j] + $arr[$i][$j];
                }

            }
        }

        var_dump($list);

        $stack = new StackArr();
        $line = 0;
        foreach ($list[count($list) - 1] as $k=>$v) {
            if($v == 0 ){
                continue;
            }
            _p($k . " , " .$v);
            if($stack->isEmpty()){
                $stack->push($v);
                continue;
            }

            $head = $stack->getHead();
            if($v >  $head){
                $stack->push($v);
            }else{
                while (1){
                    $element = $stack->pop();
                    if($element === false){
                        $stack->push($v);
                        break;
                    }
                    $line++;
                    if($element >$v){
                        continue;
                    }else{
                        $stack->push($v);
                        break;
                    }
                }
            }

        }

        var_dump($stack);

        $last = $stack->getFoot();
        while ($element = $stack->pop()){
            $line++;
        }

        var_dump($line,$last);exit;



    }

    function maxRectangleTest(){
        $arr = array(
            array(1,0,1,1),
            array(1,1,1,1),
            array(1,1,1,0),
        );

        $this->maxRectangle($arr);
    }
}