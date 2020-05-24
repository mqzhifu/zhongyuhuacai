<?php
class BinaryTreeTraining{
    //已知二叉树中包含的都是整数（正0负），求出，某几个元素值 的和，最长路径
    function sumUnsortIntMaxRange($nodeIndex,$k,$sum,$level){
        if($nodeIndex === null){
            return -2;
        }

        $node = $this->getNodeByIndex($nodeIndex);

        $currentSum = $sum + $node->data;
        $v = $currentSum - $k;

        $this->hashMap[$currentSum] = $level;
        if(isset($this->hashMap[$v])){
            if($level - $this->hashMap[$v] > $this->maxLen){
                $this->maxLen = $level - $this->hashMap[$v];
            }
        }

        $this->sumUnsortIntMaxRange($node->left,$k,$currentSum,$level+1);
        $this->sumUnsortIntMaxRange($node->right,$k,$currentSum,$level+1);

    }
    //从一颗树中（可能是搜索树，也可能不是），找寻最大<搜索数>
    public $findSearchTreeInTreeSum = null;
    function findSearchTreeInTree($tree){
        $data = $this->findSearchTreeInTreeForeachPostorder($tree,$tree->rootNodeIndex,'root');
        foreach ($this->findSearchTreeInTreeSum as $k=>$v) {
            $str = "";
            $node = $tree->getNodeByIndex($k);
            $str .= "data:". $node->data;
            if(isset($v['left']['cnt']))
                $str .= " 左元素个数:".$v['left']['cnt'] ;

            if(isset($v['left']['max']))
                $str .=  " 最大:" .$v['left']['max'] ;

            if(isset($v['left']['min']))
                $str .=  " 最小:" .$v['left']['min'];


            if(isset($v['right']['cnt']))
                $str .= " 右元素个数:".$v['right']['cnt'] ;

            if(isset($v['right']['max']))
                $str .=  " 最大:" .$v['right']['max'] ;

            if(isset($v['right']['min']))
                $str .=  " 最小:" .$v['right']['min'];



//            if($v['left']['max'] < $node->data && $v['right']['min'] > $node->data ){
//                $str .=
//            }

            _p($str);

        }





        exit;

    }
    //总节点数
    function findSearchTreeInTreeForeachPostorder($tree,$nodeIndex,$direction){
        if($nodeIndex === null){
            return null;
        }
        $node = $tree->getNodeByIndex($nodeIndex);
        //向左查找，然后向右
        $leftRs = $this->findSearchTreeInTreeForeachPostorder($tree,$node->left,'left');
        $rightRs = $this->findSearchTreeInTreeForeachPostorder($tree,$node->right,'right');
        _p($node->data . " ". $direction . " ".$leftRs. " ".$rightRs);
        if($leftRs === null && $rightRs === null){//这是个叶子节点
//            _p("叶子节点:".$node->data);
            //父结点<左|右>结点数、最大值、最小值 初始化
            $this->findSearchTreeInTreeSum[$node->parent][$direction]['cnt'] = 1;
            $this->findSearchTreeInTreeSum[$node->parent][$direction]['max'] = $node->data;
            $this->findSearchTreeInTreeSum[$node->parent][$direction]['min'] = $node->data;

            return $nodeIndex;
        }

        //因为是后序，最后一次递归，返回的是根节点
        //又因为，其实每次都是计算父结点的值，所以到了根节点不需要计算了
        if($nodeIndex == $tree->rootNodeIndex){

        }else{
            $sum = 1;
            $min = $node->data;
            $max =  $node->data;
            if(isset($this->findSearchTreeInTreeSum[$nodeIndex]['left']['cnt'])){
                $sum += $this->findSearchTreeInTreeSum[$nodeIndex]['left']['cnt'];
                if($max < $this->findSearchTreeInTreeSum[$nodeIndex]['left']['max']){
                    $max = $this->findSearchTreeInTreeSum[$nodeIndex]['left']['max'];
                    $this->findSearchTreeInTreeSum[$node->parent][$direction]['max'] = $max;
                }

                if($min > $this->findSearchTreeInTreeSum[$nodeIndex]['left']['min']){
                    $min = $this->findSearchTreeInTreeSum[$nodeIndex]['left']['min'];
                    $this->findSearchTreeInTreeSum[$node->parent][$direction]['min'] = $min;
                }
            }else{
                $this->findSearchTreeInTreeSum[$node->parent][$direction]['max'] = $max;
                $this->findSearchTreeInTreeSum[$node->parent][$direction]['min'] = $min;
            }

            if(isset($this->findSearchTreeInTreeSum[$nodeIndex]['right']['cnt'])){
                $sum += $this->findSearchTreeInTreeSum[$nodeIndex]['right']['cnt'];

                if($max < $this->findSearchTreeInTreeSum[$nodeIndex]['right']['max']){
                    $this->findSearchTreeInTreeSum[$node->parent][$direction]['max'] = $this->findSearchTreeInTreeSum[$nodeIndex]['right']['max'];
                }

                if($min > $this->findSearchTreeInTreeSum[$nodeIndex]['right']['min']){
                    $this->findSearchTreeInTreeSum[$node->parent][$direction]['min'] = $this->findSearchTreeInTreeSum[$nodeIndex]['right']['min'];
                }
            }else{
                $this->findSearchTreeInTreeSum[$node->parent][$direction]['min'] = $min;
                $this->findSearchTreeInTreeSum[$node->parent][$direction]['max'] = $max;
            }

            $this->findSearchTreeInTreeSum[$node->parent][$direction]['cnt'] = $sum;
        }

        return $nodeIndex;
    }

    function findSearchTreeInTreeForeachNodeMin(){

    }

    function findSearchTreeInTreeTest(){
        $tree  = new BinaryTree();
        $tree->autoRotate = 0 ;

//        $arr = array(6,3,16,1,5,11,20,2,4,7,13,10)   ;
        $arr = array(6,10,3,20,2,4,7,26,25)   ;
        $tree->addGroup($arr);
        $tree->showTreeByDeep();
        //找出一个节点，比如：头节点，如果该节点的，左树中最大的值，小于该节点，并且，右树中最小的节点大于该节点值
        //基本上就是遍历树的过程
        $rs = $this->findSearchTreeInTree($tree);
        var_dump($rs);exit;
    }
}