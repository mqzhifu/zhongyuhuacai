<?php
//递归相关的程序
class Recursion{
    //斐波那契数列,每一个数，等于前2个数之和
    //递归实现,O^N 复杂度
    public $fibonacci1Cnt = 0;
    function fibonacci1($n){
        $this->fibonacci1Cnt++;
        if($n < 1){
            return 0;
        }
        //前2项是基数组，写死的
        if($n == 1 || $n == 2){
            return 1;
        }

        return $this->fibonacci1($n-1) + $this->fibonacci1($n-2);
    }

    function fibonacci2($n){
        $this->fibonacci1Cnt  = $n - 2;

        $twoNumberSum = 0;
        $pre1 = 1;
        $pre2 = 1;
//        $sum = 0;
        for ($i=3 ; $i <=$n ; $i++) {
            $twoNumberSum = $pre1 + $pre2;
//            $sum += $twoNumberSum;

            _p($pre1 ." " .$pre2);
            $pre1 = $pre2;
            $pre2 = $twoNumberSum;


        }


        return $twoNumberSum;
    }


    public $cnt = 0;
    function hanoi($n,$from,$tmp,$to){
        $this->cnt++;
        _p($this->cnt . ",n=$n hode from :$from to : $to,use tmp $tmp ");
        if($n == 0){
            _p("dene 0");
            return 0;
        }


        $this->hanoi($n - 1,$from,$to,$tmp);
        $x = $n - 1;
        echo "step ".$x." ,move $from to $to <br/>";
        $this->hanoi($n - 1,$tmp,$from,$to);
    }

    //将一个栈，逆过来。
    public $stackReverseArr = null;
    function stackReverse($stack){
        $element = $stack->pop();
        if($stack->isEmpty()){
            $this->stackReverseArr->push($element);
            return 1;
        }else{
            $this->stackReverseArr->push($element);
            $this->stackReverse($stack);

        }
    }

    function stackReverseTest(){
        $stack =  new StackArr();
        $this->stackReverseArr = new StackArr();

        $arr = array(1,9,5,12,10,4,2,3);
        $stack->pushGroup($arr);

        $stack->showAll();
//        var_dump($stack);

        $this->stackReverse($stack);

        $this->stackReverseArr->showAll();
        exit;
    }
}
