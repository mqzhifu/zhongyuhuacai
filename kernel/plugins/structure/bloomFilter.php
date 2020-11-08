<?php
class BloomFilter{
    public $hashFunction = null;
    public $hashClass = null;
    public $bitMap = [];
    public $diskDataPath = "D:\www\z_service\kernel\plugins\structure\bloomFilterData.txt";
    public $tolerableErrorRate = 0.0001;//容错 率，%0.01
    function __construct($bitMapLength){
        $this->hashClass =  new BloomFilterHash();
        $this->hashFunction = get_class_methods("BloomFilterHash");

        for($i=0;$i<$bitMapLength;$i++){
            $this->bitMap[] = 0;
        }
    }

    function add($string){
        foreach ($this->hashFunction as $k=>$v) {
           $hash =$this->hashClass->$v($string);
           $bit = $hash % count($this->bitMap);
            $this->bitMap[$bit] = 1;
        }


        var_dump($this->bitMap);exit;

    }

    function formulas($line = 0){
//        $e =  0.69314718056;，如下是约等于
//        $e = 0.7;
//        var_dump(log(8,2));exit;


        $line = 100000;//总行数
        $p = 0.0001;//错误率0.01%
        $a = log($p);//以自然数E为底
        $b = $line * $a;
        $e2 = log(2);//以自然数E为底2的对数
        $e2Power = pow($e2,2);
        $length = abs( $b / $e2Power);
        $bitMapNum = round($length,0);

        _p("line:$line,p=$p, a=$a , b=$b , e2 = $e2 ,e2power:$e2Power ,bitMapNum= $bitMapNum");

        $k = $e2 * $bitMapNum / $line;
        $k = round($k,0);

        _p("func num:$k");


        exit;
    }

    //
    function loadDataFromDisk(){
        set_time_limit(10);
        $fd = fopen($this->diskDataPath,"r");
//        $fd = fopen("D:\www\z_service\kernel\plugins\structure/test.txt","r");
//        $rs = fseek($fd,1);
//        var_dump($rs);

        $line = 1;//最后一行是没有 换行符的
        while(!feof($fd)){
            $bufLine = fread($fd,20000);
            for ($i=0 ; $i < strlen($bufLine) ; $i++) {
                if(  $bufLine[$i]  == "\n" ){
                    $line++;
                }
            }
        }


        var_dump($line);


        exit;
    }

    function makeTestData(){
        $word = "abcdefghijklmnopqrstuvwxyzABCDEFGHIZKLMNOPQRSTUVWXYZ";
        $wordLen = strlen($word);

        $fd = fopen($this->diskDataPath,"w+");
        $end = 100000;
        $base = "http://local.z.com/";
        for ($i=0 ; $i < $end ; $i++) {
            $r = rand(0,$wordLen - 11);
            $str = $base .substr($word,$r,10)."\r\n";
            fwrite($fd,$str);
        }

        exit;
    }
}


class BloomFilterHash
{
    /**
     * 由Justin Sobel编写的按位散列函数
     */
    public function JSHash($string, $len = null)
    {
        $hash = 1315423911;
        $len || $len = strlen($string);
        for ($i=0; $i<$len; $i++) {
            $hash ^= (($hash << 5) + ord($string[$i]) + ($hash >> 2));
        }
        return ($hash % 0xFFFFFFFF) & 0xFFFFFFFF;
    }

    /**
     * 该哈希算法基于AT＆T贝尔实验室的Peter J. Weinberger的工作。
     * Aho Sethi和Ulman编写的“编译器（原理，技术和工具）”一书建议使用采用此特定算法中的散列方法的散列函数。
     */
    public function PJWHash($string, $len = null)
    {
        $bitsInUnsignedInt = 4 * 8; //（unsigned int）（sizeof（unsigned int）* 8）;
        $threeQuarters = ($bitsInUnsignedInt * 3) / 4;
        $oneEighth = $bitsInUnsignedInt / 8;
        $highBits = 0xFFFFFFFF << (int) ($bitsInUnsignedInt - $oneEighth);
        $hash = 0;
        $test = 0;
        $len || $len = strlen($string);
        for($i=0; $i<$len; $i++) {
            $hash = ($hash << (int) ($oneEighth)) + ord($string[$i]); } $test = $hash & $highBits; if ($test != 0) { $hash = (($hash ^ ($test >> (int)($threeQuarters))) & (~$highBits));
    }
        return ($hash % 0xFFFFFFFF) & 0xFFFFFFFF;
    }

    /**
     * 类似于PJW Hash功能，但针对32位处理器进行了调整。它是基于UNIX的系统上的widley使用哈希函数。
     */
    public function ELFHash($string, $len = null)
    {
        $hash = 0;
        $len || $len = strlen($string);
        for ($i=0; $i<$len; $i++) {
            $hash = ($hash << 4) + ord($string[$i]); $x = $hash & 0xF0000000; if ($x != 0) { $hash ^= ($x >> 24);
            }
            $hash &= ~$x;
        }
        return ($hash % 0xFFFFFFFF) & 0xFFFFFFFF;
    }

    /**
     * 这个哈希函数来自Brian Kernighan和Dennis Ritchie的书“The C Programming Language”。
     * 它是一个简单的哈希函数，使用一组奇怪的可能种子，它们都构成了31 .... 31 ... 31等模式，它似乎与DJB哈希函数非常相似。
     */
    public function BKDRHash($string, $len = null)
    {
        $seed = 131;  # 31 131 1313 13131 131313 etc..
        $hash = 0;
        $len || $len = strlen($string);
        for ($i=0; $i<$len; $i++) {
            $hash = (int) (($hash * $seed) + ord($string[$i]));
        }
        return ($hash % 0xFFFFFFFF) & 0xFFFFFFFF;
    }

    /**
     * 这是在开源SDBM项目中使用的首选算法。
     * 哈希函数似乎对许多不同的数据集具有良好的总体分布。它似乎适用于数据集中元素的MSB存在高差异的情况。
     */
    public function SDBMHash($string, $len = null)
    {
        $hash = 0;
        $len || $len = strlen($string);
        for ($i=0; $i<$len; $i++) {
            $hash = (int) (ord($string[$i]) + ($hash << 6) + ($hash << 16) - $hash);
        }
        return ($hash % 0xFFFFFFFF) & 0xFFFFFFFF;
    }

    /**
     * 由Daniel J. Bernstein教授制作的算法，首先在usenet新闻组comp.lang.c上向世界展示。
     * 它是有史以来发布的最有效的哈希函数之一。
     */
    public function DJBHash($string, $len = null)
    {
        $hash = 5381;
        $len || $len = strlen($string);
        for ($i=0; $i<$len; $i++) {
            $hash = (int) (($hash << 5) + $hash) + ord($string[$i]);
        }
        return ($hash % 0xFFFFFFFF) & 0xFFFFFFFF;
    }

    /**
     * Donald E. Knuth在“计算机编程艺术第3卷”中提出的算法，主题是排序和搜索第6.4章。
     */
    public function DEKHash($string, $len = null)
    {
        $len || $len = strlen($string);
        $hash = $len;
        for ($i=0; $i<$len; $i++) {
            $hash = (($hash << 5) ^ ($hash >> 27)) ^ ord($string[$i]);
        }
        return ($hash % 0xFFFFFFFF) & 0xFFFFFFFF;
    }

    /**
     * 参考 http://www.isthe.com/chongo/tech/comp/fnv/
     */
    public function FNVHash($string, $len = null)
    {
        $prime = 16777619; //32位的prime 2^24 + 2^8 + 0x93 = 16777619
        $hash = 2166136261; //32位的offset
        $len || $len = strlen($string);
        for ($i=0; $i<$len; $i++) {
            $hash = (int) ($hash * $prime) % 0xFFFFFFFF;
            $hash ^= ord($string[$i]);
        }
        return ($hash % 0xFFFFFFFF) & 0xFFFFFFFF;
    }
}