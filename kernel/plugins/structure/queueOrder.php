<?php
//队列 先进先出

include_once PLUGIN.DS."structure".DS."linkDouble.php";
class QueueOrder{
    public $head = null;
    public $foot = null;
    public $length = 0;

    public $link = null;

    public $debug = 1;

    function tt($info){
        if($this->debug){
            echo $info . "<br/>";
        }
    }

    function __construct(){
        $this->link = new LinkDouble();
        $this->link->debug = 0;
    }

    function push($data){

    }


    function pushHead($data){
        //在头部加一个元素
        $this->link ->add($data,1,1);
    }

    function pushFoot($data){
        $this->link ->add($data);
    }

    function popHead(){
        $this->link->delOne(1,2,1);
    }

    function popFoot(){
        $rs = $this->link->delOne(1,2,2);
        if($rs != 1){

            exit("popFoot error:".$rs);
        }
    }
    //从头部开始弹出所有
    function popALLFromHead(){
        $data =  $this->link->getAllByFooter();
        if($data == -1){
            return null;
        }
//        var_dump($data);
        $list = array();
        foreach ($data as $k=>$v) {
            $list[] = $v['data'];
        }

        $rs = $this->link->delAll();

        return $list;
    }
    //从尾部开始弹出所有
    function popALLFromFoot(){

    }
    //获取
    function getAllByHeader(){
        $data =  $this->link->getAllByHeader();
        if($data == -1){
            return -1;
        }
        $list = array();
        foreach ($data as $k=>$v) {
            $list[] = $v['data'];
        }

        return $list;
    }
    //获取
    function getAllByFooter(){
        $data = $this->link->getAllByFooter();
        $list = array();
        foreach ($data as $k=>$v) {
            $list[] = $v['data'];
        }

        return $list;
    }

    function isEmpty(){
        return $this->link->empty();
    }

    function getOneByFooter(){
        $node = $this->link->getOneByFooter();
        if($node == -1)
            return -1;

        return $node['data'];
    }

    function getOneByHeader(){
        $node = $this->link->getOneByHeader();
        if($node == -1)
            return -1;

        return $node['data'];
    }
}