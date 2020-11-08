<?php
class Bit{
    function swap($a,$b){
        $a = $a ^ $b;
        $b = $a ^ $b;
        $a = $a ^ $b;

        return array($a,$b);
    }
    //获取32位有符号整形的，符号伴
    function getSignByIntFirstBit($x){
        return $x >> 31 & 1;
    }
    //翻转一个2进制伴,0变1，1变0
    function flipOneBit($x){
        return $x ^ 1;
    }

    function max($a,$b){
        $x = $a - $b;
        //取出符号伴
        $d = $this->getSignByIntFirstBit($x);
        //翻转一下
        $scA = $this->flipOneBit($d);
        $scB = $this->flipOneBit($scA);

        return $a * $scA + $b * $scB;
    }
}