<?php

//栈============
include_once PLUGIN.DS."structure".DS."stack.php";
//        //先测试下基本栈是否正确
$StackArr = new StackArr();
//        $arr = array(1,5,2,9,10,4,3,);
//        $StackArr->pushGroup($arr);
//        $data = $StackArr->popAll();
//        var_dump($data);

//        include_once PLUGIN.DS."structure".DS."stackMin.php";
//        $StackArrMin = new StackMin();
//        $StackArrMin->test($StackArrMin);

include_once PLUGIN.DS."structure".DS."recursion.php";
$Recursion = new Recursion();
//        $Recursion->stackReverseTest();

//        $Recursion->hanoi(1,'A','B','C');
//        _p(" ");
//        $Recursion->hanoi(2,'A','B','C');
//        exit;


include_once PLUGIN.DS."structure".DS."queue.php";
include_once PLUGIN.DS."structure".DS."queueTraining.php";
$qt = new QueueTraining();
//        $qt->windowTest();
//        $qt->maxTreeTest();
//        $qt->maxRectangleTest();
//        $rs = $qt->subsetBitTest();
//        $qt->subsetMaxLessSubsetMinTest();
//        exit;
//栈============

include_once PLUGIN.DS."structure".DS."linkDouble.php";
include_once PLUGIN.DS."structure".DS."linkLoop.php";
include_once PLUGIN.DS."structure".DS."linkTraining.php";
$lt = new LinkTraining();
//        $lt->getTwoOrderLinkCommonPartTest();
//        $lt->testDelMiddle();
//        $lt->testLoop();
//        $lt->josephLoopLinkTest();
//        $lt->josephLoopRecursionTest();
//        $lt->setupLinkNumberTest();
//        $lt->linkAddLink();
//        $lt->isLoopLink();
//        $lt->sortGroupPartTest();
//        exit;
//链表============================================================



//        include_once PLUGIN.DS."structure".DS."binaryTree.php";
//        $tree  = new BinaryTree();
//        $tree->debug = 0;
//        $arr = array(10,1,11,3,4,20,16,7,2,5,13,6);
//        $tree->addGroup($arr);
//        $tree->showTreeByDeep();
//        $tree->testPrintAll();



//        $height = $tree->getHeight($tree->rootNodeIndex,0);
//        var_dump($height);exit;
//        $tree->showTreeEdge();
//        $data = $c->foreachPreorderStack($c->rootNodeIndex,1);
//        $tree->saveToFile(1);
//        $tree->foreachByMorrisTest();
//        $KMP->violence("abcdefg","bcd");
//        $KMP->search("abcacabdd","cab");
//        exit;

include_once PLUGIN.DS."structure".DS."binaryTreeTraining.php";
$btt =  new BinaryTreeTraining();
//        $btt->findSearchTreeInTreeTest();
//        exit;

//        include_once PLUGIN.DS."structure".DS."kmp.php";
//        $KMP = new KMP();



//二叉树============================================================





include_once PLUGIN.DS."structure".DS."matrix.php";
$m = new Matrix();
//        $m->testFibonacci();exit;
//        $m->testMinPathSum();exit;
//        $m->testChangeMoney();exit;
//            $m->testLongestIncreasingSubsequence();exit;
//        $m->testGameMapMinHP();exit;
//        $m->testPokerGameFirstLast();exit;
//        $m->testQueen8();



//        $m->changeMoneyLast($money,$x);
//        $rs = $m->loopChangeMoneyMap($money,0,$x);
//        echo "loop cnt".($m->loopChangeMoneyMapCnt)."<br/>";
//        echo "rs:".($rs)."<br/>";
//        var_dump($m->loopChangeMoneyMapData);
//        $rs = $m->unsortUnsignedIntegerSumRange(20);
//        exit;

//        $arr = array(2,1,5,3,6,4,8,9,7);
//        $m->longestIncreasingSubsequence($arr);
//        $m->longCommonSubsequence();
//        exit;
//        $arr = array(
//            array(1,3,5,9),
//            array(8,1,3,4),
//            array(5,0,6,1),
//            array(8,8,4,0),
//        );
//        $m->minPathSum($arr);
//        $m->arr= null;
//        $arr  = array(1,2,1,1,1);
//        $m->addTestNumber($arr);
//        $rs = $m->unsortUnsignedIntegerSumRange(3);

//        $m = new Matrix();
//        $arr  = array(1,7,4,1,7,9,3,1,6,7,8);
//        $m->addTestNumber($arr);
//        $rs = $m->unsortIntegerSumRange(20);
//        exit;

//递归
//        include_once PLUGIN.DS."structure".DS."fibonacci.php";
//        $f = new Fibonacci();
//        $rs = $f->m2(20);
//        var_dump($f->cnt);
//        var_dump($f->numberPool);
//        exit;

//==================矩阵=======================================

//        include_once PLUGIN.DS."structure".DS."bloomFilter.php";
//        $bloom = new BloomFilter(100);

//        $bloom->formulas();
//        $bloom->loadDataFromDisk();
//        $bloom->makeTestData();
//        $bloom->add("absdfsdfsdfsdfcd");

//=============布隆 过滤器

include_once PLUGIN.DS."structure".DS."stackTopOrder.php";
$sto  = new StackTopOrder(10);
$sto->test();

exit;



include_once PLUGIN.DS."structure".DS."bigData.php";
$b  = new BigData();
$b->testFindNumberCnt();exit;


//================大数据

//        include_once PLUGIN.DS."structure".DS."stringOP.php";
$s = new StringOP();
//
//        $dict = new WordDictionaryTree();
//        $dict->addWord("abcd");
//        $dict->addWord("abc");
//        $dict->addWord("efg");
//        var_dump($dict->map);
//
//        $dict->search("bbb");
//        $dict->search("abc");
//        $dict->search("efg");
//
//        exit;

//        0000 0011
//        111111100
//        100000100
//        var_dump(~3);exit;
//        include_once PLUGIN.DS."structure".DS."bit.php";
//        $b = new Bit();
//        $rs = $b->swap(1,2);
//        $rs = $b->max(-3 ,2);
//
//        var_dump($rs);exit;

//        include_once PLUGIN.DS."structure".DS."ArrMatrix.php";
//        $am = new ArrMatrix();
//        $am->showRectangle();
//        $am->rotate90Rectangle();
//        $am->matrixRectangle();

include_once PLUGIN.DS."structure".DS."other.php";
$o =  new Other();
//        $o->zeroNum1(10);
//        $o->intSeeOne(114);
//        $o->intSeeOne(999);

//        $rs = $o->numberToLanguage(1,'cn');
//        var_dump($rs);exit;
//        $o->intSeeOne2(999)
//
$o->distributeSugar();

exit;
return $this->out(200);