<?php
//矩阵相关
class Matrix{
    public $arr = null;
    public $debug = 1;

    function tt($info,$br = 1){
        if($this->debug){
            echo $info;
            if($br){
                echo  "<br/>";
            }
        }
    }
    //整数，快速幂
    function quickPowerInt($x,$n){
        $bit = $this->getBinPower($n);

        $sum = 1;
        foreach ($bit as $k=>$v) {
//            _p("bit:$v ,x=$x, pow bit:".pow(2,$v). ",x pww: ".pow($x , pow(2,$v)) );
            $sum *= pow($x , pow(2,$v));
        }


        return $sum;
    }
    //将幂 转换成 2进制 位数
    function getBinPower($n){
        $bin = decbin($n);
//        var_dump($bin);
        $bit  = [] ;
        $j = 0;
        for ($i= strlen($bin) - 1 ; $i >=0 ; $i--) {
            if($bin[$i]){
                $bit[] = $j ;
            }
            $j++;
        }
        return $bit;
    }

    function fibonacciMatrix($n){
        if($n < 1){
            return 0;
        }
        if($n == 1 || $n == 2){
            return 1;
        }
        $x = $n - 2;
        $baseMatrix = [[1,1],[1,0]];
        $bit = $this->getBinPower($x);

        $sum = $baseMatrix;
        foreach ($bit as $k=>$v) {
            $power = pow(2,$v);
            if($power == 1){
                continue;
            }
            $tmp = $baseMatrix;
            _p("pow:$power");
            for ($i=0 ; $i <$power ; $i++) {
                $tmp =  $this->multiply($tmp,$baseMatrix);
                var_dump($tmp);
            }

            $sum =  $this->multiply($tmp,$sum);
        }
        var_dump($sum);exit;
        exit;
        var_dump($tmp);exit;

    }

    function testFibonacci(){
        $n = 10;

        _p("n=$n");

        $Recursion = new Recursion();
        $rs = $Recursion->fibonacci1($n);
        _p("递归，循环次数:".$Recursion->fibonacci1Cnt.",rs:".$rs);

        $rs = $Recursion->fibonacci2($n);
        _p("正常，循环次数:".$Recursion->fibonacci1Cnt.",rs:".$rs);


        $this->fibonacciMatrix($n);

        exit;

    }
    //最小矩阵路径和============================================
    function testMinPathSum(){
        $arr = [
            [1,3,5,9],
            [8,1,3,4],
            [5,0,6,1],
            [8,8,4,0]
        ];
        $rs = $this->minPathSum($arr);
        var_dump($rs);

        $this->minPathSumZip($arr);
    }
    //给出一个 2维矩阵，每个元素都是一个数字
    //从左上角，矩阵的第一个元素开始起，要么向下，要么向右，沿矩阵行走，走到，该矩阵 的最后右下角的元素，依次累加走过的元素的数字的和
    //动态规划 时间复杂 M * N 空间复杂  M * N
    function minPathSum($arr){
        $this->showMatrix($arr);
        _p(" ");

        //先 生成一个  跟数组 一样大小的  矩阵
        $dp = [];
        $sum = 0;
        //初始化，第一列
        for($i=0;$i<count($arr);$i++){
            $sum += $arr[$i][0];
            $dp[$i][0] = $sum;
        }

        $this->showMatrix($dp);
        _p(" ");
        //初始化第一行
        $sum = 0;
        for($i=0;$i<count($arr[0]);$i++){
            $sum += $arr[0][$i];
            $dp[0][$i] = $sum;
        }

        var_dump($dp);

        //第一行，第一列，已经初始化了，所以循环都是1开始，也就是从第2行第2列开始
        for($i=1;$i<count($arr);$i++){
            for($j=1;$j<count($arr[0]);$j++){
                //上一行的，上一列
                $up = $dp[$i-1][$j];
                //当前行，前一列
                $left = $dp[$i][$j-1];

                $yesDirection = 0;
                if($up > $left){
                    $yesDirection = $left;
                }elseif($up < $left){
                    $yesDirection = $up;
                }else{//这是相等的情况，先忽略

                }

                $dp[$i][$j] = $yesDirection + $arr[$i][$j];
            }
        }

        $this->showMatrix($dp);
        _p(" ");

        //最右下角的，那个就是结果
        return $dp[ count($arr) -1 ][count($arr[0]) - 1];
    }
    //同上，也是动态规划，但是把空间复杂度降到了  min(m,n)，也就是动态规划压缩
    function minPathSumZip($arr){
        $dp = [];
        for ($i=0 ; $i < count($arr) ; $i++) {
            for ($j=0 ; $j < count($arr[0]) ; $j++) {
                if($i == 0){//初始化第一行
                    if($j == 0){
                        $dp[] = $arr[0][0];
                        continue;
                    }else{
                        $dp[]  =  $dp[$j-1]+$arr[0][$j];
                    }
                    continue;
                }

                var_dump($dp);
                $up = $dp[$j];
                if($j == 0){
                    $left = $up;
                }else{
                    $left = $dp[$j-1];
                }

                $upPlus = $up + $arr[$i][$j];
                $leftPlus = $left + $arr[$i][$j];
                _p("up:".$up.",left:$leftPlus");
                if($j == 0){
                    $dp[$j] = $upPlus;
                    continue;
                }
                if($upPlus > $leftPlus ){
                    $dp[$j] = $leftPlus;
                }else{
                    $dp[$j] = $upPlus;
                }
            }
        }

        return $dp;
    }
    //================================================
    //给定一个数：X(金额),根据 已有 面值 钱，计算出 多少种 找钱方式， 也就是 (面值*X) 累加 = X
    function testChangeMoney(){
        $arr = [5,2,3];
        $this->changeMoney2($arr,20);
    }
    //这个是最笨的方法，就是一层一层的循环，用最上层一个金额依次向下层循环
    //因为是最笨的，for 循环的次数也是写死的，所以只做参考使用
    function changeMoney($moneyType,$x){


        $m1 = $moneyType[0];
        $m2 = $moneyType[1];
        $m3 = $moneyType[2];

        $totalCnt = 0;
        $successTotalCnt = 0;

        for($y =0;$y<=$x;$y=$y+$m3){
            $p = $y;
            for($i =0;$i<=$x;$i=$i+$m1){
                $z = $i;
                for($j =0;$j<=$x;$j=$j+$m2){
                    $totalCnt++;
                    if($z + $j + $p == $x){
                        $successTotalCnt++;
                        $mod1 = $z/$m1;
                        $mod2 = $j/$m2;
                        $mod3 = $y / $m3;
                        $this->tt("$z({$m1}X{$mod1})+$j({$m2}X{$mod2})+$p({$m3}X{$mod3})");
                    }
                }
            }
        }

        $this->tt("total count:".$totalCnt);
        $this->tt("successTotalCnt:".$successTotalCnt);

    }
    //同上，实现方法：动态规划法   如：5 3 2
    //先生成一矩阵 面值 * X
    //行：代表钱的面值.(有几种面值，就有几行)，列代表 金额，确切的说是 从1到X，步长为1的所有金额
    //填值公式：以行为单位，比如 第一行是5块面值，因为列是所有金额的细分，使用一张5块的纸币，能否满足第一列（如：0 1 2 3 4 5 6 ）显然 1 2 3 4 6 肯定不能满足，只有5能满足
    //          0是个特殊值，证明总额0元，那实际上一张纸币不使用，就证明是0元。
    //首先，初始化第1行0元因为是一张不出，所以都满足,即填充1.
    //然后，初始化第一列，5 15 20  这3个数满足，即填充1.
    //接下来就核心了：开始循环，从第2列第2列开始，以行为单位，填充每行上的每列的值，也就是：计算满足X的金额，需要多少种纸币
    //  1、只使用本行货币，也就是单一一种纸币，如：2块钱，使用10张，满足总额为10的，使用2张，满足总额为4的。如果满足为1，否则为0
    //  2、考虑完单一纸币完后，还需要考虑 ，不使用本行的纸币，只使用上一行的纸币。说白了：就是把上一行的情况汇总下来。
    //  3、最后考虑，即使用本行的纸币，又使用上一行的纸币，这里算是最难懂的。假设当前列走到为20的总额，2块面额可以：2X5 + 5X2 = 20，5张2块，2张5块的。
    //相比第一种最笨的方法，动态规划  就是以当前行跟上一行做比较，或者说上一行保留了计算过的结果。计算速度能快点，但是占用的空间要多一点。时间复杂度 N(纸币数) * X(总额)

    function changeMoney2($moneyType,$x){
        //初始化第一列
        $dp =[];
        for($i=0;$i<=$x;$i++){
            $dp[0][$i] = 0;
        }
        //初始化第一行，把面值都写进去
        for($i=1;$moneyType[0] * $i <= $x;$i++){
            $dp[0][$moneyType[0] * $i] = 1;
        }

        $this->tt("");

        for($i=0;$i<count($moneyType);$i++){
            $dp[$i][0] = 1;
        }

        for($i=1;$i< count($moneyType);$i++){
            for($j=1;$j<=$x;$j++){
                $num = 0;
                for($k=0;$j - $moneyType[$i] * $k >=0 ;$k++){
                    $num += $dp[$i-1][$j-$moneyType[$i] * $k];
                }
                $dp[$i][$j] = $num;
            }
        }

        $this->showDPHtml($moneyType,$dp,$x);
    }
    //=================换钱数-找钱数============================


    //换钱的最小币种
    function changeMoneyLast($moneyType,$x){
        $dp =[];
        $limiter = 99;//算是占位数字吧，只要够大（无限大），因为 程序是计算哪个更小的，所以这个大的值是直接 被忽略的，可以理解 为0
//        for($i=0;$i<=$x;$i++){
//            $dp[0][$i] = $limiter;
//        }
//

        for($i=0;$i<count($moneyType);$i++){
            $dp[$i][0] = 0;
        }
//        $dp[0][0] = $limiter;
        for($j=1;$j <= $x;$j++){
            $dp[0][$j] = $limiter;
            if($j - $moneyType[0] >= 0  ){
                if( $dp[0][$j-$moneyType[0]] != $limiter){
                    $dp[0][$j] = $dp[0][$j - $moneyType[0]] +1;
                }

            }
        }


        $this->tt("");



        $left = 0;
        for($i=1;$i<count($moneyType);$i++){
            for($j=1;$j<=$x;$j++){
                $left = $limiter;
                if($j - $moneyType[$i] >= 0 ){
                    if( $dp[$i][$j-$moneyType[$i]] != $limiter){
                            $left = $dp[$i][$j - $moneyType[$i]] +1 ;
                    }
                }

                if($left <= $dp[$i-1][$j]){
                    $dp[$i][$j] = $left;
                }else{
                    $dp[$i][$j] = $dp[$i-1][$j];
                }


            }
        }

        $this->showDPHtml($moneyType,$dp,$x);
    }




    //一组无序的，正整数（无符号），求：数组任意范围内，数字 累加和等于XXX
    function unsortUnsignedIntegerSumRange($k){
        $this->tt(json_encode($this->arr));

        $left = 0;
        $right = 0;
        $len = 0;
//        $sum = 0;
        $sum = $this->arr[$left];
        //当left指针已到最后一个元素后
        while($right < count($this->arr) ){
            $this->tt("left:$left,right:$right,sum:$sum");
            if( $sum == $k){//证明 几个元素之和等于XX值，记录下 下标，A-B 的距离 长
                $thisLen = $right - $left + 1;
                if( $thisLen > $len){
                    $len = $thisLen;
                }
                $this->tt("len:$thisLen");

                $left++;
                $sum -= $this->arr[$left];
            }elseif($sum < $k){
                $right ++;
                if($right >= count($this->arr)){
                    break;
                }
                $sum += $this->arr[$right];
            }elseif($sum > $k){
                $left ++;
                $sum -= $this->arr[$left];
            }else{
                exit(" -1 ,error.");
            }
        }
    }
    //一组无序的，整开（正、0、负）数，求：数组任意范围内，数字 累加和等于XXX
    function unsortIntegerSumRange($k){
        $this->tt("k:$k");
        $sum = 0;
        foreach ($this->arr as $k2=>$v) {
            $sum += $v;
        }
        $count = count($this->arr) - 1;
        $this->tt("s[i]=[0...". $count." ]=$sum");

        $map = array(0=>-1);//第一个值
        $sum = 0;
        foreach ($this->arr as $k2=>$v) {
            $sum += $v;
            $key = $sum - $k;
            if( isset($map[$key])){
                $this->tt("sum:$sum,map key:$key,map value:".$map[$key].",k2:$k2");
            }
            $map[$sum] = $k2;
        }

        var_dump($map);


        $this->unsortUnsignedIntegerSumRange($k);



//        $this->unsortUnsignedIntegerSumRange($k);
    }



    public $loopChangeMoneyMapCnt = 0;//循环次数统计
    public $loopChangeMoneyMapData = null;//HASH表保存，一个区间计计算过的汇总值
    //$arr:[5,10,20...]  货币面值
    //$index:$arr的索引值 ，也就是计算到第几轮
    //$x:要计算的总金币值 ，如100
    function loopChangeMoneyMap($arr  ,$index = 0,$x){
        if(isset($arr[$index]))
            $this->tt("index:".$index.",value:".$arr[$index].".x:".$x);

        $success = 0;
        //先判断，是否到数组结尾的后面一个值，实际是溢出索引
        //实际：最后一个<货币面值>元素，后一个.
        if($index == count($arr)){
            //走到这里就是最后一步了，因为上层有个$x-$arr[$index] * $i,如果等于0，就证明 相等,也就证明找到了组满足条件的数
            if($x == 0){
                $success =  1;
            }else{
                $success =  0;
            }
        }else{
            for($i=0;$arr[$index] * $i <=$x;$i++){
                $this->loopChangeMoneyMapCnt++;//统计循环次数，无用
                //优化，原本，就是一层一层的计算，但实际上，很多计算是重复的
                //如：X=100,[5,10,20]  5x4+10x0+20x4=100  5x2+10x1+20x4=100,都有一个20x4，就没有必要再循环去计算这个式子
                //于是，增加一个HASH 数组，保存之前计算过的结果
                if(isset($this->loopChangeMoneyMapData[$index+1][$x-$arr[$index] * $i])){
                    $mapValue = $this->loopChangeMoneyMapData[$index+1][$x-$arr[$index] * $i];
                    if($mapValue == -1){
                        $success += 0;
                    }else{
                        $success +=$mapValue;
                    }

                }else{
                    $success +=  $this->loopChangeMoneyMap($arr,$index+1,$x-$arr[$index] * $i);
                    if($success){//无用，只是做输出
                        $tmpSum = $arr[$index] * $i;
                        $this->tt($arr[$index]."x".$i."=$tmpSum($x)");
                    }
                }
            }
        }

        if($success == 0){
            //未找到
            $this->loopChangeMoneyMapData[$index][$x] = -1;
        }else{
            $this->loopChangeMoneyMapData[$index][$x] = $success;
        }

        return $success;
    }
    //这个是我自己写的，未参考PDF
    //只是单纯的递归循环，实现
    function loopChangeMoney($numberArr = null,$key = 0){
//        $this->tt("key=".$key);
        if($key == count($this->data) - 1){//证明是最后一层了
            $numberArrSum = 0;
            $numberArrStr = "";
            foreach ($numberArr as $k=>$v) {
                $numberArrSum += $v;
                $numberArrStr .= $v ."+";
            }
            $numberArrStr = substr($numberArrStr,0,strlen($numberArrStr)-1);

            foreach ($this->data[$key] as $k=>$v) {
                $this->loopCnt++;
                if($numberArrSum+ $v == $this->x){
                    $this->successTotalCnt++;
                    $this->tt("ok:".$numberArrStr."+".$v);
                }
            }

            return -1;
        }


        $keyPlus = $key + 1;
        foreach ($this->data[$key] as $k=>$v) {
            $numberArrPlus = $numberArr;
            $numberArrPlus[] = $v;
            $this->loopChangeMoney($numberArrPlus,$keyPlus);
        }


    }

    function testLongestIncreasingSubsequence(){
        $arr = array(2,1,5,3,6,4,8,9,7);
        $this->longestIncreasingSubsequence($arr);
    }

    //最长递增子序列 LIS
    //数组  2，1，5，3，6，4，8，9，7
    //结果   5 6 8 9 10

    function longestIncreasingSubsequence($arr){
        foreach ($arr as $k=>$v) {
            echo $v ." ";
        }
        $this->tt(" ");

        $dp = [];
        //先计算出,数组中，以每个数结尾，他前面的数所组成的最大递增序列的 长度（*），是个一维的
        for($i=0;$i< count($arr) ;$i++){
            //默认值
            $dp[$i] = 1;
            //先从原数组中，取出一个数，然后，到矩阵数组中计算
            //因为，每次循环的次数，是依次递增的。所以每次从<原数组>取出的数也可以看成是  以这个数结尾的<数>序列
            //第一次，是2，DP[O]=1,下面循环是不成立，不执行
            //第二次DP[1]=1，循环成立，还需要满足1个条件：
            //  1 当前这个值(根据I从原数据中获取)是否大于原数组前的几个数据（J循环，是依次从0循环到I）
            //$arr[1]=1 ,跟$arr[0]=2 比对，并不大于，循环停止$dp[1]=1
            //第3次，I=2, 5，循环，跟前2个数做比较.J=0  ,5>2($arr[0]) ，满足条件1，当前I值对应的DP值是1，小于2(dp[0]+1)，当前DP值就为2(dp[0]+1),J=1 , 5>1($arr[1]，满足条件1，当前I值对应的DP值2，>=2(dp[1]+1)，不做处理
            //前三次后DP的结果为:  1 1 2，对应的位置为 0 1 2 ,再对应到原数组  2 1 5  ，以2结尾最长：1，以1结尾最长是1，以 2 1 5 最长为2.
            for($j=0;$j<$i;$j++){
                if($arr[$i] > $arr[$j]){
//                    if($dp[$i] >= $dp[$j]+1){
//                        $dp[$i] = $dp[$i];
//                    }else{
//                        $dp[$i] = $dp[$j]+1;
//                    }
                    if( $dp[$j]+1 >= $dp[$i]){
                        $dp[$i] = $dp[$j]+1;
                    }
                }
            }
        }

        //找出的结果为：1,1,2,3,3,4,5,4
        //找到最长因子数：5，位置为7，对应ARR的值为9
        //开始从这个数，往左遍历，找出倒数第2个数
        //满足条件：1 arr[6] < $arr[7] && dp[$i] == $db[7] - 1

        var_dump($dp);
        //找出DP中最大的那个值
        $maxKey = 0;
        $len = 0;
        foreach ($dp as $k=>$v) {
            if($v > $dp[$maxKey] ){
                $maxKey = $k;
                $len = $v;
            }
        }
        var_dump($maxKey);

        $lis = array();
        $lis[$len-1] = $arr[$maxKey];

        for($i=$maxKey;$i>0;$i--){
            if($arr[$i] < $arr[$maxKey] ){
                if($dp[$i] == $dp[$maxKey]-1){
                    $lis[--$len] = $arr[$i];
                    $maxKey = $i;
                }
            }
        }


        var_dump($lis);exit;

    }
    //最长公共因子
    function longCommonSubsequence(){
        $str1 = "1A2C3D4B56";
        $str2 = "B1D23CA45B6A";

        $str1Len =  strlen($str1);
        $str2Len =  strlen($str2);

        //$str1Len = M , $str2Len = N

        $dp = [];
        $dp[0][0] = $this->equal($str1[0] , $str2[0]);
        for($i=1;$i<$str1Len;$i++){
            $dp[$i][0] =  $this->equal( $str1[$i] , $str2[0]);
            if($dp[$i-1][0] >= $this->equal( $str1[$i] , $str2[0])){
                $dp[$i][0] = $dp[$i-1][0];
            }
        }

        for($j=1;$j<$str2Len;$j++){
            $dp[0][$j] =  $this->equal( $str2[$i] , $str1[0]);
            if($dp[0][$j-1] >= $this->equal( $str2[$i] , $str1[0])){
                $dp[0][$j] = $dp[0][$j-1];
            }
        }

        $this->showDPStringHtml($dp,$str1,$str2);
        exit;
//        var_dump($dp);exit;
    }
    //================================
    function testGameMapMinHP(){
        $arr = array(
            [-2,-3,3],
            [-5,-10,1,],
            [0,30,-5]
        );
        $this->gameMapMinHP($arr);
    }
    //给出一张地图，每个点可能出现 ：加血 | 扣血
    //找出一条路径，从左上角到右下角，走完之后，HP > 0 ，且是最优路径
    //最后计算出：最少初始值 HP 是多少
    function gameMapMinHP($arr){
        $this->showMatrix($arr);

        //实际结果是 计算左上角的那个值，也就是初始血量，那实际就得从右下角出发
        //所以，初始化DP，就得从右下角开始


        $dp = [];

        $rowLengthMax = count($arr) - 1;
        $lineLengthMax = count($arr[0]) - 1;

        //初始化行
        $sum = 0;
        for ($i=$rowLengthMax ; $i >= 0 ; $i--) {
            if($i == $rowLengthMax ){
                $sum = $arr[$i][$lineLengthMax];
            }else{
                $sum -= $arr[$i][$lineLengthMax];
            }

            $dp[$i][$lineLengthMax] = $sum;
        }
        //初始化列
        $sum = 0;
        for ($i=$lineLengthMax ; $i >= 0 ; $i--) {
            if($i == $rowLengthMax ){
                $sum = $arr[$rowLengthMax][$i];
            }else{
                $sum -= $arr[$rowLengthMax][$i];
            }

            $dp[$rowLengthMax][$i] = $sum;
        }
        //测试输出 使用
        for ($i=0 ; $i <=$rowLengthMax ; $i++) {
            for ($j=0 ; $j <=$lineLengthMax ; $j++) {
                if(!isset($dp[$i][$j])){
                    $dp[$i][$j] = 0;
                }
            }
        }

        $this->showMatrix($dp);

        for ($i= $rowLengthMax - 1 ; $i >=0 ; $i--) {
            for ($j=$lineLengthMax - 1; $j >=0 ; $j--) {
                _p("i:$i,j:$j");
                $down = $dp[$i+1][$j];
                $right = $dp[$i][$j+1];

                $downLess = $down - $arr[$i][$j];
                $rightLess = $right - $arr[$i][$j];

                $rsDown = 1;
                if($downLess > $rsDown){
                    $rsDown = $downLess;
                }

                $rsRight = 1;
                if($rightLess > $rsRight){
                    $rsRight = $rightLess;
                }

                $yesWay = $rsDown;
                if($rsRight > $rsDown)
                    $yesWay = $rsRight;

//                if($downLess <= $right){
//                    $yesWay = $downLess;
//                }else{
//                    $yesWay = $right;
//                }
                $dp[$i][$j] = $yesWay;
            }
        }

        $this->showMatrix($dp);


        exit;


//        for ($i=count($arr) -1 ; $i >=0 ; $i--) {
////            _p("i:$i");
//            for ($j=count($arr[0]) -1 ; $j >=0 ; $j--) {
////                _p("j:$j");
//                if($i == count($arr) - 1){//最后一行
//                    if($j == count($arr[0]) - 1){
//                        $dp[$i][$j] = $arr[$i][$j];
//                    }else{
//                        $dp[$i][$j] = $dp[$i][$j+1] + $arr[$i][$j];
//                    }
//                }else{
//                    if($j == count($arr[0]) - 1){//最后一列
//                        $dp[$i][$j] =  $dp[$i+1][$j] +  $arr[$i][$j];
//                    }else{
//                        $dp[$i][$j] = $dp[$i][$j+1] + $arr[$i][$j];
//                    }
//                }
//
//            }
//        }
        $this->showMatrix($dp);
        _p(" ");

        //从倒数第2行开始，从每行的倒数第2列开始
        for ($i=count($dp) -2 ; $i >=0 ; $i--) {
            for ($j=count($dp[0]) -2 ; $j > 0 ; $j--) {
                _p("i : $i ,j : $j");
                $down = $dp[$i+1][$j];
                $right = $dp[$i][$j+1];

                _p("down : $down ,right : $right");
                if($down <= $right){
                    $rightWay = $down;
                }else{
                    $rightWay = $right;
                }

                $dp[$i][$j] = $rightWay + $arr[$i][$j];
            }
        }

        $this->showMatrix($dp);

    }
    //======================
    //一组扑克，明文，两个人参与。一次只能拿最上面的一张，或者最下面的一张。
    //求：2个人最后谁胜出
    function testPokerGameFirstLast(){
        $arr = [5,10,41,1];
        var_dump($arr);
        $this->pokerGameFirstLastRecursion($arr);


    }
    //递归算法，时间复杂度O^2
    function pokerGameFirstLastRecursion($arr){
        $f = $this->pokerRecursionF($arr,0,count($arr) - 1);
        $s = $this->pokerRecursionS($arr,0,count($arr) - 1);
        _p("f:$f,s:$s");
    }
    //先拿的人
    function pokerRecursionF($arr,$i,$j){
        _p("pokerRecursionF,i=$i,j=$j",0);
        if($i == $j){
            //只有一张牌了，那肯定是先拿人，直接拿走
            _p(",i=j ". $arr[$i]);
            return $arr[$i];
        }
        //先拿的人，可以选择拿第一张牌，也可以拿最后一张牌
        //第一张牌+后面的牌
        _p("");
        $f = $arr[$i] + $this->pokerRecursionS($arr,$i+1,$j) ;
        //最后一张牌+后面的牌
        $s = $arr[$j] + $this->pokerRecursionS($arr,$i,$j - 1) ;
        _p("f=$f,s=$s");
        //哪个大我就拿哪个
        if($f > $s){
            return $f;
        }
        return $s;
    }
    //后拿的人
    function pokerRecursionS($arr,$i,$j){
        _p("pokerRecursionS,i=$i,j=$j",0);
        if($i == $j){
            _p(",i=j 0");
            //只有一张牌了，那肯定是先拿人，直接拿走，后拿的人毛都没有
            return 0;
        }
        _p("");
        $f = $this->pokerRecursionF($arr,$i+1,$j);
        $s = $this->pokerRecursionF($arr,$i,$j- 1);
        _p("f=$f,s=$s");
        if($f < $s){
            return $f;
        }
        return $s;
    }

    function pokerGameFirstLast(){
    }

    //=======================

    function testQueen8(){
        $arr = [];
        //8X8的格式
        $this->queen8Recursion($arr,0,8);

        for ($i=0 ; $i <8 ; $i++) {
            for ($j=0 ; $j <8 ; $j++) {
                echo "$i,$j ";
            }

            echo "<br/>";
        }
    }
    //8皇后
    //$arr:递归，成员变量，每一行保存的值,$i从第几行开始,$n 为几个皇后
    function queen8Recursion($arr,$i,$n){
        if($i == $n){
            var_dump($arr);
            return 1;
        }
        $res = 0;
        for ($j=0 ; $j < $n; $j++) {
            if($this->isValid($arr,$i,$j)){
                $arr[$i] = $j;
                $res += $this->queen8Recursion($arr,$i+1,$n);
            }
        }

        return $res;
    }

    function isValid($arr,$i,$j){
        for ($k=0 ; $k <  $i ; $k++) {
            if($j == $arr[$k] || abs($arr[$k] - $j) == abs($i - $k)){
                return false;
            }
        }

        return true;
    }


    //============================各种打印  及 基方法=====================================
    function equal($a,$b){
        if($a == $b){
            return 1;
        }
        return 0;
    }



    function addTestNumber($arr){
        foreach ($arr as $k=>$v) {
            $this->arr[] = $v;
        }

    }

    function showMatrix($arr){
        for ($i=0 ; $i <count($arr) ; $i++) {
            for ($j=0 ; $j < count($arr[0]); $j++) {
                if($j == count($arr[0]) - 1){
                    $this->tt($arr[$i][$j]);
                }else{
                    $this->tt($arr[$i][$j] . " ",0);
                }
            }
        }
        _p(" ");

//        foreach ($arr as $k=>$rows) {
//            foreach ($rows as $k2=>$number) {
//                if($k2 == count($rows) - 1){
//                    $this->tt($number);
//                }else{
//                    $this->tt($number . " ",0);
//                }
//            }
//        }
    }

    //矩阵 乘法,暂只支持 相差维度的两个  矩阵
    function multiply ($matrix1,$matrix2){
//        $matrix1 = array(
//            array(1,3),
//            array(2,-1),
//        );
//
//        $matrix2 = array(
//            array(2,-4),
//            array(3,0),
//        );

//        $this->showMatrix($matrix1);
//        $this->showMatrix($matrix2);

//        $this->tt(" ");


        //行
        $countRow1 = count($matrix1);
        //列
        $countLine1 = count($matrix1[0]);

        $countRow2 = count($matrix2);
        $countLine2 = count($matrix2[0]);


        $rs = null;

//        $this->tt("开始处理:");
        //line:当前列，row:当前行
        foreach ($matrix1 as $rowNo=>$rowNumbers) {
            foreach ($rowNumbers as $line=>$v) {
                $sumRow = 0;
                for($i=0;$i<$countRow1;$i++){
                    $sumNumber = $rowNumbers[$i] * $matrix2[$i][$line];
//                    $this->tt($rowNumbers[$i]." X ".$matrix2[$i][$line]."=".$sumNumber);
                    $sumRow += $sumNumber;
                }
//                $this->tt(" ");
                $rs[$rowNo][$line] = $sumRow;
            }
        }

//        $this->tt("结果");

//        $this->showMatrix($rs);

        return $rs;

    }

    function showDPHtml($moneyType,$dp,$x){
        echo "<table border='1'>";

        echo "<tr><td><br/>";
        foreach ($moneyType as $k=>$v) {
            echo $v."<br/>";
        }
        echo "</td><td>";
        echo "<table>";
        for($i=0;$i<=$x;$i++){
            echo "<td>". $i ."</td>";
        }



        foreach ($dp as $k=>$v) {
            echo "<tr>";
            foreach ($v as $k2=>$v2) {
                echo "<td>".($v2."&nbsp;&nbsp;")."</td>";
            }
            echo "</tr>";
//            $this->tt("");
        }
        echo "</table>";
        echo "</td></tr>";

        echo "</table>";

        return 1;
    }


    function showDPStringHtml($dp,$str1,$str2){
        echo "<table border='1'>";

        echo "<tr><td><table><tr><td>&nbsp;</td></tr>";
        for ($i=0;$i<strlen($str1);$i++) {
            echo "<tr><td>". $str1[$i]."</td></tr>";
        }
        echo "</table></td><td>";
        echo "<table>";
        for ($i=0;$i<strlen($str2);$i++) {
            echo  "<td>".$str2[$i] . "</td>";
        }



        foreach ($dp as $k=>$v) {
            echo "<tr>";
            foreach ($v as $k2=>$v2) {
                echo "<td>".($v2."&nbsp;&nbsp;")."</td>";
            }
            echo "</tr>";
//            $this->tt("");
        }
        echo "</table>";
        echo "</td></tr>";

        echo "</table>";

        return 1;
    }

}