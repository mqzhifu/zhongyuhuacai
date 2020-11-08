<?php
class StackMin{
    //假设一个栈中都是数字，获取最小值为O（1）
    //实现方法就是2个栈，一个正常的，另一个保存最小值
    public $stack = null;
    public $stackMin = null;
    function __construct(){
        $this->stack = new StackArr();
        $this->stackMin = new StackArr();
    }

    function getMin(){
        return $this->stackMin->getHead();
    }

    function push($data){
        $this->stack->push($data);
        if($this->stackMin->isEmpty()){
            $this->stackMin->push($data);
        }else{
//            $this->stackMin->cursor = 0;//先将游标归0，从头开始迭代
            $this->stackMin->cursor = count($this->stackMin->nodePool) - 1;

//            $cnt = 1;
            while( ($element = $this->stackMin->getOneByPeek()) != -1){
//                if($cnt >= 10){//防止死循环
//                    break;
//                }

                if($data >  $element){
                    break;
                }elseif($data <=  $element){
                    $this->stackMin->push($data);
                    break;
                }
//                $cnt ++;
            }
        }
    }

    function pop(){
        $stackTopElement = $this->stack->getHead();
        $this->stack->pop();
        if(!$this->stackMin->isEmpty()){
            $topData =
            $element = $this->stackMin->getHead();
            if($stackTopElement == $element){
                $this->stackMin->pop();
            }
        }
    }

    function showAll(){
        foreach ($this->stack->nodePool as $k=>$v) {
            _p($v." ",0);
        }
        _p(" ");

        foreach ($this->stackMin->nodePool as $k=>$v) {
            _p($v." ",0);
        }

    }

    function test($class){
        $class->push(9);
        $class->push(7);
        $class->push(8);
        $class->push(1);
        $class->push(2);
        $class->push(1);
        $class->push(10);

        $class->showAll();

        $min = $class->getMin();
        _p("min:".$min);

        $class->pop();
//        _p("min:".$min);

        $class->pop();
//        _p("min:".$min);

        $class->showAll();


        $class->pop();
        $min = $class->getMin();
        _p("min:".$min);

        $class->pop();
        _p("min:".$min);

        $class->pop();
        $min = $class->getMin();
        _p("min:".$min);

    }
}