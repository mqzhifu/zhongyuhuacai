<?php
//有序 堆栈，最大的在最下面，最小的最上面，保留TOP K 个值
class StackTopOrder{
    public $stack = null;
    public $k = 0;

    function __construct($k){
        $this->stack = new StackArr();
        $this->k = $k;
    }

    //这个方法效果有点低，每次把所有堆栈里的值全POP，再全PUSh
    function push($data)
    {
        if ($this->stack->isEmpty()) {
            return $this->stack->push($data);
        }
        $list = $this->stack->popAll();
        for ($p=0 ; $p <count($list) ; $p++) {
            if ($data < $list[$p]) {
                break;
            }

            if($data == $list[$p]){//这算是个特殊情况吧，相等也是没意义的
                $p = 0;
                break;
            }
        }

        _p("data:$data,position:$p,list:".json_encode($list));

        if ($p) {
            $newList = [];
            if(count($list) == $p){//证明是最后一个元素增加
                $list[] = $data;
                $newList = $list;
            }else{
                foreach ($list as $k=>$v) {
                    if($k == $p){
                        $newList[] = $data;
                        $newList[] = $v;
                    }else{
                        $newList[] = $v;
                    }
                }
            }
        }else{
            $newList = $list;
        }

        if(count($newList) > $this->k){
            unset($list[0]);
        }

        for ($i=count($newList) -1 ; $i >=0 ; $i--) {
            $this->stack->push($newList[$i]);
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
    }

    function test(){
        $this->push(2);
        $this->push(5);
        $this->push(4);
        $this->push(3);
        $this->push(2);
        $this->push(11);
        $this->push(7);

        $this->showAll();


    }
}