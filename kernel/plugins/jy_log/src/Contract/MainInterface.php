<?php
namespace Jy\Log\Contract;

interface MainInterface{

    function setMsgFormat($str);//日志格式
    function setDelimiter($str);//每行内容的，分隔符
    function setMsgFormatDatetime($str);//日志格式中的 日期 格式
    function setFilter($str);//过滤掉一些特定值
    function setDeepTrace($str);//跟踪回溯层级

}