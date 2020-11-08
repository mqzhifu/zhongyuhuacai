<?php
namespace Jy\Common\Valid\Contract;

Interface FilterInterface{
    function setMessage($message);//设置：匹配项，错误提示
    function setDelimiter($delimiter);//设置：长度分隔符
    function setRangeDelimit($rangeDelimiter);//设置：长度范围分隔符
    function match($data,$rule);//正则匹配
    function getMessage($rule);//获取：匹配项，错误提示
    function matchLength($value,$rule);//一个值的：最长、最短、范围
}