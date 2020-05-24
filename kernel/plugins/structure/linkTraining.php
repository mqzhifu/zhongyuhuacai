<?php
//判断一个链表是不是回文结构
//回文结构：一组数字，正序跟反序是一样的，比如： 1 2 3 4 5 4 3 2 1   ，无论正序，反序 都是一样的
class BackSort{
    function judge($arrData){
        if(!$arrData || !is_array($arrData) || count($arrData) == 1 ){
            return -1;
        }

        //将数组除2，后半部分扔进栈里，弹出出来，就是倒序，也就是跟 前半部分如果相等就是
        $divisor =  count($arrData) / 2;
        $leftEnd = $divisor  -1;
        if(!is_int($divisor)){
            $divisor = (int)$divisor + 1;
            $leftEnd = $divisor - 2;
        }
        include_once PLUGIN.DS."structure".DS."stack.php";
        $stack =  new StackArr();

        for ($i = $divisor;$i< count($arrData);$i++ ){
            $stack->push($arrData[$i]);
        }

        $descData = $stack->popAll();
        $rs = true;
        for($i=0;$i<=$leftEnd;$i++){
            if($arrData[$i] != $descData[$i]){
                $rs = false;
                break;
            }
        }

        return $rs;
    }
}

class LinkTraining{
    function sortGroupPartTest(){
        $arr = array(10,4,7,2,6,30,19,21,22,12,16,15);
        $link = new LinkDouble();
        $link->addGroup($arr);
        //每3个元素组成一个新组，然后逆序
        $this->sortGroupPart($link,3);
    }
    //实现，一个链表，N个元素逆序
    function sortGroupPart($link,$k){
        $p = $link->head;
        $groupNode = null;
        $tmpLastPoint = null;
        while (1){
            $node = $link->getNodeByIndex($p);
            if(!is_object($node)){
                break;
            }

            $groupNode[] = $node;
            if(count($groupNode) == $k){//满足一组数，开始做逆序

                for ($i=$k - 1 ; $i >0 ; $i--) {
                    if(!$tmpLastPoint && $i == $k - 1){//证明是第一组，倒过来，第一个元素，是头
                        //因为，NODE 没有定义成类，麻烦 ，不写了
                    }
                }
            }

        }
    }

    function isLoopLink(){
        $link = new LinkLoop();
        $arr = array(1,0,9,8);
        $link->addGroup($arr);

        $slow = $link->first;
        $fast = $link->first;

        $flag = 0;
        $cnt = 1;
        $first = 0;
        while (1){

            $slowNode = $link->getNodeByIndex($slow);
            $fastNode = $link->getNodeByIndex($fast);

            _p($slow." ".$fast . ",".$slowNode->data." ".$fastNode->data);

            if($slowNode == null || $fastNode == null){
                break;
            }
            $slow = $slowNode->next;
            $fastNodeTmp =  $link->getNodeByIndex($fastNode->next);
            $fast = $fastNodeTmp->next;

            if($first && $slow == $fast){
                $flag = 1;
                break;
            }

            if(!$first){
                $first = 1;
            }

            $cnt++;
            if($cnt >= 10){

                exit("dead loop");
            }

        }

        return $flag;
    }

    //假定，两个链表的DATA值均为数字，且是0-9，链表中每个元素的DATA代表一个位，然后，用两个链表相加
    function linkAddLink(){
        $link1 = new LinkDouble();
        $arr = array(1,0,9,8);
        $link1->addGroup($arr);

        $arr = array(0,9,9,8);
        $link2 = new LinkDouble();
        $link2->addGroup($arr);

        $link1List = $link1->getAllByFooter();
        $link2List = $link2->getAllByFooter();

        $end = $link1->nodePoolLength;
        if($link2->nodePoolLength > $end){
            $end = $link2->nodePoolLength ;
        }

        $ca = 0;
        $number = "";
        for ($i=0 ; $i < $end; $i++) {
            $add1 = 0;
            if(isset($link1List[$i]['data'])){
                $add1 = $link1List[$i]['data'];
            }
            $add2 = 0;
            if(isset($link2List[$i]['data'])){
                $add2 = $link2List[$i]['data'];
            }


            $rs = $add1 +$add2 + $ca;
            if($rs >= 10){
                $ca = 1;
                $rs = substr($rs,1,1);
            }

            $number .= $rs;
        }

        var_dump($number);exit;
    }

    function copyLinkRandTest(){

    }
    //复制一个链表，但链表的NODE中有一个随机指向某一个NODE的指针
    function copyLinkRand(){
        //还得给 元素增加一个rand 成员属性，麻烦 ，先不弄了
        //大概解法：在原链表的每个元素尾COPY出一个新元素
    }

    function setupLinkNumberTest(){
        $link = new LinkDouble();
        $arr = array(10,4,7,2,6,30,19,21,22,12,16,15);
        $link->addGroup($arr);

        $this->setupLinkNumber($link,15);
    }
    //给定一个数字，把链表中小于该数字的元素调整到左边，大于的调整到右边，相等的放到中间部分
    function setupLinkNumber($link,$n){
        $left = 0;
        $right = $link->nodePoolLength - 1;
        $arr = [];
        $cursor = $link->head;
        while (1){
            $node = $link->getNodeByIndex($cursor);
            if( !$node['next'] || $node['next'] === null)
                break;

            if($node['data'] < $n){
                $arr[$left++] = $node['data'];
            }elseif($node['data'] > $n){
                $arr[$right--] = $node['data'];
            }
            $cursor = $node['next'] ;
        }

        for ($i=0 ; $i <  $link->nodePoolLength  ; $i++) {
            if(!isset($arr[$i])){
                $arr[$i] = $n;
            }
        }

        for ($i=0 ; $i <  $link->nodePoolLength  ; $i++) {
            _p($arr[$i]);
        }
    }

    function josephLoopRecursionTest(){
        $rs = $this->josephLoopRecursion(10,4);
        var_dump($rs);exit;
        exit;
    }
    //约瑟夫环，递归实现，效率更高
    //n=10 m=4
    //原始   0   1   2   3   4   5   6   7   8   9
    //删除掉一个值 ，变成 如下
    // 环   0   1   2        4   5   6   7   8   9
    //将上面环，重新再组成一个新的可连续的环
    //新环   6   7   8        0   1   2   3   4   5
    //回滚到上环公式：(新环值+M) % 旧环总个数   如： (4 + 4) mod 9 = 8

    //这样每一轮弹出一个，剩下的数，再组成一个新的环，如：10个数，就是10个轮，最后一轮只有一个数字，也就是递归的终结点

    /*
 *
 原始数字 : 1 2 3 4 5 6 7 8 9 10 对应 位置下标 0 1 2 3 4 5 6 7 8 9,M=3
第一次弹出 下标为2 的元素，队列为： 0 1 3 4 5 6 7 8 ,环型变成：3 4 5 6 7 8 9 0 1
将这个环的下标值，重新再编写一下0 1 2 3 4 5 6 7 8 ，转换成旧环的位置公式   下标 + M % 旧-元素个数总和
0 + 3 % 10 = 3,4 5 6 7 8 9 0 1
观察新环：0 1 2 3 4 5 6 7 8，这次要弹出的是依然还是3，套用公式 3 + 3 % 9 = 6，再返回到原始数字队列中，第二次弹出的就是6
10个数，最终得弹出9个数，剩下一个数。按照新环的算法，每弹一个数，都是基于上一轮的环。

根据这一次的新环，如何 计算 上一次弹出 的数字呢？
0 1 2 3 4 5 6 7 8  (?)  ，
上一轮的最后一个元素，就是弹出的那个数c


. 正常理解                       新环                          递归计算
0 1 2 3 4 5 6 7 8 9 ->2			 0 1 2 3 4 5 6 7 8 9                            0 + 3 % 10 = 3
0 1 3 4 5 6 7 8 9   ->5          7 8 ? 0 1 2 3 4 5 6     6 + 3 % 9 = 0
0 1 3 4 6 7 8 9     ->8          4 5   6 7 ? 0 1 2 3         3 + 3 % 8 = 6
0 1 3 4 6 7 9	    ->1          1 2   3   4 5 6 ? 0        0 + 3 % 7 = 3
0 3 4 6 7 9		    ->6          5 ?   0   1 2 3   4 	    3 + 3 % 6 = 0
0 3 4 7 9 		    ->0          2     3   4 ? 0   1 		        0 + 3 % 5 = 3
3 4 7 9		        ->7          3 ?   0   1   2  		            1 + 3 % 4 = 0
3 4 9		        ->4          0     1   2   ?		            1 + 3 % 3 = 1  0
3 9		            ->9          0 	   1   ?	                0 + 3 % 2 = 1
3                                0



0                   0 + 3 % 2 = 1
0 1                 1 + 3 % 3 = 1
0 1 2               1 + 3 % 4 = 0
0 1 2 3             0 + 3 % 5 = 3
0 1 2 3 4           3 + 3 % 6 = 0
0 1 2 3 4 5         0 + 3 % 7 = 3
0 1 2 3 4 5 6       3 + 3 % 8 = 6
0 1 2 3 4 5 6 7     6 + 3 % 9 = 0
0 1 2 3 4 5 6 7 8   0 + 3 % 10 = 3

*/

    function josephLoopRecursion($n,$m){
        if($n == 1)
            return 0;

        return ($this->josephLoopRecursion($n - 1, $m) + $m) % $n;

    }

    function josephLoopLinkTest(){
        $this->josephLoopLink(5,3);
    }
    //约瑟夫环，链表实现
    function josephLoopLink($n,$m){
        $link = new LinkLoop();
        for ($i=1;$i<=$n;$i++){
            $link->add($i);
        }
        //取出头位置，当做死循环的第一个元素，之后会依次拿出下一个元素赋值
        $node = $link->nodePool[$link->first];
        //步长，等于这个值 代码 得把这个数踢出去了
        $inrc = 1;
        while($link->nodePoolLength > 1){
            echo "index:".$node->index ." inrc:".$inrc."<br/>";
            if($inrc == $m){
                _p("踢出:".$node->data);
                $link->delOneByData($node->data);
                $inrc = 1;
                $node = $link->nodePool[$node->next];
                continue;
            }

            $inrc++;
            $node = $link->nodePool[$node->next];

        }
        $node = $link->nodePool[$link->first];
        var_dump($node->data);exit;
        exit;
    }
    //测试删除一个链表中的中位元素
    function testDelMiddle(){
        $link1  =  new LinkDouble();
        $link2  =  new LinkDouble();

        $arr1 = array(1,5,7,10,11,15,20,39);
        $link1->addGroup($arr1);

        $rs1 = $link1->getAll();
        $link1->delMiddle();

        $arr1 = array(1,5,7,10,11,15,20,39,40);
        $link2->addGroup($arr1);
        $link2->delMiddle();
        $rs2 = $link2->getAll();

        var_dump($rs1);
        var_dump($rs2);

    }
    //循环链表
    function testLoop(){
        $link = new LinkLoop();
        $arr = array(10,20,30,40,50,60,70);
        $link->addGroup($arr);

        $list = $link->getALL();
    }


    //获取两个链表的，公共起始位置
    function getTwoOrderLinkCommonPart($link1,$link2){
        $node1 = $link1->getNodeByIndex($link1->head);
        $node2 = $link2->getNodeByIndex($link2->head);
        $rs = [];


        while(1){
            _p($node1);_p($node2);
            if($node1 == -1 || $node2 == -1){
                break;
            }

            if($node1['data'] == $node2['data']){
                $rs[] = $node1['data'];
            }

            if($node1['next'] === null || $node2['next'] === null){
                break;
            }

            if($node1['data'] == $node2['data']){
                $node1 = $link1->getNodeByIndex($node1['next']);
                $node2 = $link2->getNodeByIndex($node2['next']);
            }elseif($node1['data'] < $node2['data']){
                $node1 = $link1->getNodeByIndex($node1['next']);
            }else{
                $node2 = $link1->getNodeByIndex($node2['next']);
            }
        }
        return $rs;
    }

    function getTwoOrderLinkCommonPartTest(){
        $link1  =  new LinkDouble();
        $link2  =  new LinkDouble();

        $arr1 = array(1,5,7,10,11,15,20,39);
        $link1->addGroup($arr1);
        $arr2 = array(5,7,10);
        $link2->addGroup($arr2);

        $this->getTwoOrderLinkCommonPart($link1,$link2);
    }
}