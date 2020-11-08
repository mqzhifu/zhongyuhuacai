<?php
class KMP{
    //暴力解法
    function violence($original,$search){
        _p("original:$original,search:$search");
        for ($i=0 ; $i < strlen($original) ; $i++) {
            $location = $i;
            $f = 1;//1已找到0未找到
            for ($j=0 ; $j < strlen($search) ; $j++) {
                if($original[$location++] != $search[$j]){
                    _p("no");
                    $f = 0;
                    break;
                }

                if($location == strlen($original)){//原串已经到最后一个字符了
                    if($j ==  strlen($search) - 1){//搜索串也到最后一个字符了
                        _p("yes");
                    }else{
                        $f = 0;
                        _p("no");
                    }
                    break;
                }
            }
            _p("f:".$f);
        }
    }
    //abcdebca   bca
    function search($original,$search){
        _p("original:$original,search:$search");

//        $pre = $this->getPre($original,5);
//        $last = $this->getLast($original,5);
//        var_dump($pre);
//        var_dump($last);
//
//        $l = $this->compare($pre,$last);
//
//        var_dump($l);exit;

        $k = 0;
        for ($i=0 ; $i <strlen($original) ; $i++) {
            var_dump($k);
            if($k >= strlen($search)){
                _p("yes");
                break;
            }
            if($original[$i] != $search[$k]){
                if($k == 0 && $k == 1){

                }else{
                    $pre = $this->getPre($search,$k);
                    $last = $this->getLast($original,5);
                    $l = $this->compare($pre,$last);
                    $k = $l;
                }
                continue;
            }
            $k++;
        }
    }

    function compare($pre,$last){
        $max = 0;
        foreach ($pre as $k1=>$v1) {
            foreach ($last as $k2=>$v2) {
                if($v1 == $v2){
                    $l = strlen($v1);
                    if($l > $max){
                        $max = $l;
                    }
                }
            }
        }
        return $max;
    }

    function getPre($str,$k){
        $subStr = substr($str,0,$k-1);
        $arr = [];
        for ($i=0 ; $i <  strlen($subStr) ; $i++) {
           if(count($arr) == 0){
               $arr[] = $subStr[0];
           }else{
               $arr[] = $arr[count($arr) - 1] . $subStr[$i];
           }
        }

        return $arr;
    }

    function getLast($str,$k){
        $subStr = substr($str,1,$k-1);
        $arr = [];
        for ($i=strlen($subStr) - 1 ; $i >= 0   ; $i--) {
            if(count($arr) == 0){
                $arr[] = $subStr[$i];
            }else{
                $arr[] = $subStr[$i] . $arr[count($arr) - 1] ;
            }
        }

        return $arr;
    }
}