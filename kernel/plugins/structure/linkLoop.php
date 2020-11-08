<?php
//循环-链表结构
class LinkLoop{
    public $first = 0;
    public $nodePool = null;//内存池，保存结点
    public $nodePoolLength = 0;
    public $nodePoolAddress = 0;
    //在尾部-添加一个结点
    function add($data){
        if($this->isEmpty()){
            $nodeIndex = $this->createNode($data);
            $this->first = $nodeIndex;
            $this->nodePool[$nodeIndex]->next = $nodeIndex;
            $this->nodePool[$nodeIndex]->last = $nodeIndex;

            return $nodeIndex;
        }

        $nodeIndex = $this->createNode($data);
        $findEndNode = $this->findEndNode();
        $findEndNode->next = $nodeIndex;

        $this->nodePool[$nodeIndex]->next = $this->first;

        $this->nodePool[$this->first]->last = $nodeIndex;

        $this->nodePool[$nodeIndex]->last =  $findEndNode->index;
    }

    function addGroup($arr){
        foreach ($arr as $k=>$v) {
            $this->add($v);
        }

    }

    function getALL(){
        $firstIndex = $this->first;
        $index = $this->first;
        $node = $this->nodePool[$index];
        $rs = array($node);
        while($node->next != $firstIndex){
            $node = $this->nodePool[$node->next];
            $rs[] = $node;
        }
        return $rs;
    }

    function findEndNode(){
        $firstIndex = $this->first;
        $index = $this->first;
        $node = $this->nodePool[$index];
        while($node->next != $firstIndex){
            $node = $this->nodePool[$node->next];
        }
        return $node;
    }

    function findNodeByData($data){
        $firstIndex = $this->first;
        $index = $this->first;
        $node = $this->nodePool[$index];
        while(1){
            if($node->data == $data){
                return $node;
            }
            if($node->next == $firstIndex){
                break;
            }
            $node = $this->nodePool[$node->next];

        }
        return -1;
    }

    function createNode($data){
        $index = $this->nodePoolAddress;

        $node = new LinkLoopNode();
        $node->data = $data;
        $node->index = $index;

        $this->nodePool[$index] = $node;
        $this->nodePoolAddress++;
        $this->nodePoolLength++;

        return $index;
    }
    //删除一个节点
    function delOneByData($data){
        $node = $this->findNodeByData($data);
        if(is_int($node)){
            exit("err ,no find.");
        }

        if($node->index == $this->first){
            $this->first = $this->nodePool[$this->first]->next;
        }

        $this->nodePool[$node->next]->last = $node->last;
        $this->nodePool[$node->last]->next = $node->next;
        unset($this->nodePool[$node->index]);

        $this->nodePoolLength--;

        return 1;
    }

    function isEmpty(){
        if($this->nodePoolLength == 0){
            return true;
        }

        return false;
    }

    function getNodeByIndex($nodeIndex){
        if(!isset($this->nodePool[$nodeIndex])){
            return null;
        }
        return $this->nodePool[$nodeIndex];
    }
}

class LinkLoopNode{
    public $next = null;
    public $last = null;
    public $data = null;
    public $index = null;
}