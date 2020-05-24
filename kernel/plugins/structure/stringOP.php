<?php
class StringOP{
    function minSubLeng(){
        $str1 = "adabbca";
        $str2 = "acb";

        $map = [];
        for($i=0;$i<strlen($str2);$i++){
            $map[$str2[$i]] = 1;
        }


        $match = strlen($str2);
        $left = 0;
        $right = 0;
        for($i=0;$i<strlen($str1);$i++){

        }

        var_dump($map);exit;
    }

}
//字典树，减少单词存储空间
class  WordDictionaryTree{

    public $map = null;

    function addWord($word){

        $level = 0;
        for($i=0;$i<strlen($word);$i++){
            if(!isset($this->map[$level][$word[$i]])){
                $this->map[$level][$word[$i]] = [];
            }
            $level++;
        }
    }

    function search($word){

        $f = 1;
        $level = 0;
        for($i=0;$i<strlen($word);$i++){
            if(!isset($this->map[$level][$word[$i]])){
                $f = 0;
                break;
            }
            $level++;
        }


        var_dump($f);
        return $f;
    }
}