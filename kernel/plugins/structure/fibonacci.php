<?php
//斐波那契数列
//第3个数是前两个数的和
class Fibonacci{

    //调试模式
    public $debug = 1;

    function tt($info){
        if($this->debug){
            echo $info . "<br/>";
        }
    }

    public $numberPool = null;
    function __construct(){
    }

    //递归  ,复杂度：N~2 ，太扯，20个数就开始卡了
    public $cnt = 0;
    function m1($n){
        $this->cnt++;
        if($n == 0){
            return 0;
        }

        if($n == 1){
            return 1;
        }

        $number =$this->m1($n - 1) +  $this->m1($n - 2);
        $this->numberPool[]  = $number;
        return $number;
    }
    //复杂度：N
    function m2($n){
        if($n == 1 || $n== 2){
            return 1;
        }

        $f1 = 1;
        $f2 = 1;

        $this->numberPool[] = $f1;
        $this->numberPool[] = $f2;

        for($i=3;$i<=$n;$i++){
            $c = $f1+$f2;
            $this->numberPool[] = $c;
            $f1 = $f2;
            $f2 = $c;
        }
    }

}