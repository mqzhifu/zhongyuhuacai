<?php
namespace Jy\Log\Log;

use Jy\Log\Contract\Main;

class File extends Main {

    private $_codeErrMessage = array(
        400=>'code is null',
        401=>'code not is key',
        500=>"set private variable err.",
        501=>"setHashType failed, type value is error.",
        502=>"base path is not dir:{0}",
        503=>"create dir failed,path {0}",
    );

    private $_totalRecord = 0;//将所有日志 统一记录到某一个文件中

    private $_projectName = "";//项目名，用于 分类文件夹
    private $_module = "";//模块名，属于项目的子集，用于分类文件夹

    private $_wrap = "\r\n";//换行符

    private $_hashType = "day";//year month day
    private $_hashTypeDesc = array("year",'month','day','hour');
    private $_filePrefix = "";//日记文件 前缀名
    private $_ext = ".log";//文件扩展名
    private $_path = "";//日志文件在于的 基位置

    private $_writePath  = "";//类内部使用，如果有 分类文件夹，临时写入
    private $_fd = null;//类内部使用，缓存文件句柄，后期优化
    private $_level = "";//类内部使用

    private $_buffMem = 0;//是否开启 一次请求，所有写日志都只是先缓存，最后再flush到文件中。减少IO
    private $_buffContent = [];

    function __construct(){
        parent::__construct();
//        $sysInfo = \Jy\Common\RequestContext\RequestContext::get('sys_data');
//        if($sysInfo){
//            $this->setSysBaseInfo($sysInfo);
//        }
    }

    function init($k,$v){
        if(isset($this->$k)){
            $this->$k = $v;
            return $this;
        }
        $this->throwException(500);
    }

    //统计不可调试日志
    function emergency($message ,array $context = array()){
        $this->_level = "emergency";
        $info =  parent::emergency($message,$context);
        $this->flush($info);
    }
    //警报日志
    function alert($message ,array $context = array()){
        $this->_level = "alert";
        $info =  parent::alert($message,$context);
        $this->flush($info);
    }
    //危险日志
    function critical($message ,array $context = array()){
        $this->_level = "critical";
        $info =  parent::critical($message,$context);
        $this->flush($info);
    }
    //错误日志
    function error($message ,array $context = array()){
        $this->_level = "error";
        $info =  parent::error($message,$context);
        $this->flush($info);
    }
//    //警告日志
    function warning($message ,array $context = array()){
        $this->_level = "warning";
        $info =  parent::warning($message,$context);
        $this->flush($info);
    }
    //提醒日志
    function notice($message ,array $context = array()){
        $this->_level = "notice";
        $info =  parent::notice($message,$context);
        $this->flush($info);
    }
//  //普通日志
    function info($message ,array $context = array()){
        $this->_level = "info";
        $info =  parent::info($message,$context);
        $this->flush($info);
    }
    //调试日志
    function debug($message ,array $context = array()){
        $this->_level = "debug";
        $info =  parent::debug($message,$context);
        $this->flush($info);
    }

    function log($level,$message ,array $context = array()){
        $this->_level = "log";
        $info =  parent::log($level,$message,$context);
        $this->flush($info);
    }
    //=============================================
    function setHashType($type){
        if(!in_array($type,$this->_hashTypeDesc)){
            $this->throwException(501);
        }
        $this->_hashType = $type;
    }
    //====================以上可对外开放======================

    //初始化路径
    function initPath($category){
        $this->checkBasePath();

        $this->_writePath = $this->_path;
//        if($this->_projectName){
//            $this->_writePath .=  "/".$this->_projectName;
//            $this->checkPathAndMkdir();
//        }
//
//        if($this->_module){
//            $this->_writePath .=  "/".$this->_module;
//            $this->checkPathAndMkdir();
//        }
//
//        $this->_writePath .=  "/".$category;
        $this->checkPathAndMkdir();
    }

    function getPath(){
        return $this->_path;
    }

    //在基目录下，新建一个文件，记录所有类型的日志，可以看成是一个：总日志文件
    function totalRecord($info){
        if(!$this->_totalRecord)
            return 0;

        $pre = "";
        if($this->_projectName){
            $pre .= "[{$this->_projectName}]";
        }

        if($this->_module){
            $pre .= "[{$this->_module}]";
        }

        $pre .= "[{$this->_level}]";

        $file = $this->_filePrefix."total".$this->_ext;
        $info = $pre   . $info . $this->_wrap;
        $this->writeFile($this->_path  ."/" .$file,$info);
    }

    function writeFile($pathFile,$info){
        if(!isset($this->_fd[md5($pathFile)])){
            $this->_fd[md5($pathFile)] = fopen($pathFile,"a+");
        }
        fwrite(  $this->_fd[md5($pathFile)],$info);

//        $fd = fopen($pathFile,"a+");
//        fwrite($fd,$info);
//        fclose($fd);
    }

    function buffFlushFile(){

        if(!$this->_buffContent){
            return false;
        }
        $filePath = $this->_writePath . "/" .$this->_level. $this->_ext;

//        $contentStr = "";
//        foreach ($this->_buffContent as $k=>$v) {
//            $info = $this->replaceCalledInfo(json_decode($v,true));
//            $info = $this->replaceSysBaseInfo($info);
//            $contentStr .= json_encode($info) . $this->_wrap;
//
//        }
//        $this->writeFile($filePath,$contentStr);

        foreach ($this->_buffContent as $k=>$v) {
            $this->writeFile($filePath,$v);
        }
    }

    //持久化到文件中
    function flush($info){
        $this->initPath($this->_level);
        $this->totalRecord($info);

        $ext= $this->_ext;
//        if ($this->_hashType){
        if(0){
            if($this->_hashType == 'year'){
                $filePath = $this->_writePath . "/" . $this->_filePrefix . date("Y").$ext;
            }elseif($this->_hashType == 'month'){
                $filePath = $this->_writePath . "/" . $this->_filePrefix .  date("Y"). date("m").$ext;
            }elseif($this->_hashType == 'day'){
                $filePath = $this->_writePath . "/"  .$this->_filePrefix.  date("Y"). date("m") . date("d").$ext;
            }elseif($this->_hashType == 'hour'){
                $filePath = $this->_writePath . "/" .$this->_filePrefix . date("Y"). date("m"). date("d") . date("H").$ext;
            }else{
                exit("-1");
            }

        }else{
//            $filePath = $this->_writePath . "/" .$this->_filePrefix .$ext;
            $filePath = $this->_writePath . "/" .$this->_level. $ext;
        }

        $info = $info . $this->_wrap;

//        if(!$this->_buffMem){
            $this->writeFile($filePath,$info);
//        }else{
//            $this->_buffContent[] = $info;
//        }
    }
    //检查设置路径正确否
    function checkBasePath(){
        if(!is_dir($this->_path)){
            $this->throwException(502,array($this->_path));
        }
    }
    //检查路是否存在 ，不存在 则尝试创建
    function checkPathAndMkdir(){
        if(!is_dir($this->_writePath)){
            $rs = mkdir($this->_writePath);
            if(!$rs){
                $this->throwException(503,array($this->_writePath));
            }
        }
    }

    function throwException($code,$replace = ""){
        if(!$code){
            throw new \Exception($this->_codeErrMessage[400]);
        }

        if(!isset($this->_codeErrMessage[$code]) || !$this->_codeErrMessage[$code]){
            throw new \Exception($this->_codeErrMessage[401]);
        }
        if(!$replace){
            throw new \Exception($this->_codeErrMessage[$code]);
        }else{
            $message = $this->_codeErrMessage[$code];
            foreach ($replace as $key => $v) {
                $message = str_replace("{" . $key ."}",$v,$message);
            }

            throw new \Exception($message);
        }
    }

    //判断目录是否有写权限
    function fileModeInfo($file_path){
        /* 如果不存在，则不可读、不可写、不可改 */
        if (!file_exists($file_path))
        {
            return false;
        }
        $mark = 0;
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
        {
            /* 测试文件 */
            $test_file = $file_path . '/cf_test.txt';
            /* 如果是目录 */
            if (is_dir($file_path))
            {
                /* 检查目录是否可读 */
                $dir = @opendir($file_path);
                if ($dir === false)
                {
                    return $mark; //如果目录打开失败，直接返回目录不可修改、不可写、不可读
                }
                if (@readdir($dir) !== false)
                {
                    $mark ^= 1; //目录可读 001，目录不可读 000
                }
                @closedir($dir);
                /* 检查目录是否可写 */
                $fp = @fopen($test_file, 'wb');
                if ($fp === false)
                {
                    return $mark; //如果目录中的文件创建失败，返回不可写。
                }
                if (@fwrite($fp, 'directory access testing.') !== false)
                {
                    $mark ^= 2; //目录可写可读011，目录可写不可读 010
                }

                @fclose($fp);
                @unlink($test_file);
                /* 检查目录是否可修改 */
                $fp = @fopen($test_file, 'ab+');
                if ($fp === false)
                {
                    return $mark;
                }
                if (@fwrite($fp, "modify test.\r\n") !== false)
                {
                    $mark ^= 4;
                }
                @fclose($fp);
                /* 检查目录下是否有执行rename()函数的权限 */
                if (@rename($test_file, $test_file) !== false)
                {
                    $mark ^= 8;
                }
                @unlink($test_file);
            }
            /* 如果是文件 */
            elseif (is_file($file_path))
            {
                /* 以读方式打开 */
                $fp = @fopen($file_path, 'rb');
                if ($fp)
                {
                    $mark ^= 1; //可读 001
                }
                @fclose($fp);
                /* 试着修改文件 */
                $fp = @fopen($file_path, 'ab+');
                if ($fp && @fwrite($fp, '') !== false)
                {
                    $mark ^= 6; //可修改可写可读 111，不可修改可写可读011...
                }
                @fclose($fp);
                /* 检查目录下是否有执行rename()函数的权限 */
                if (@rename($test_file, $test_file) !== false)
                {
                    $mark ^= 8;
                }
            }
        }
        else
        {
            if (@is_readable($file_path))
            {
                $mark ^= 1;
            }
            if (@is_writable($file_path))
            {
                $mark ^= 14;
            }
        }
        return $mark;
    }

}