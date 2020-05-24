<?php
//该文件是所有 跟 目录-文件 相关操作 的函数
function hashFiles($path,$str,$ext_name = 0){
    if(!$str)
        return -1;

    if(!is_dir($path))
        return -2;


    $dd = date("Ymd");

    $f_path = $path . "/".$dd;

    if(!is_dir($f_path))
        mkdir($f_path);

    $file = time().rand(11111,99999);
    if($ext_name)
        $file .= ".".$ext_name;

    $path_file = $f_path."/".$file ;

    $fd = fopen($path_file,"w");
    fwrite($fd,$str);
    fclose($fd);
    return $dd."/".$file;

}
//检查一个PATH的目录是否存在，如果不存在创建之
function checkDir($Dir,$root_path = ROOT_PATH){
    $DirInfo = explode("/",$Dir);
    $f = $root_path;
    foreach($DirInfo as $d)
    {
        $f .= "/".$d;

        if(!is_dir($f)&& !file_exists($f))
        {
            @mkdir($f,0755);
        }

    }
}
function get_dir($directory){
    $rs = array();
    $mydir = dir($directory);
    while($file=$mydir->read()){
        if( $file!="." && $file!=".."){
            if( is_dir("$directory/$file")){
                $rs = get_dir("$directory/$file");
            }else{
                $rs[$directory][] = $file;
            }
        }
    }
    $mydir->close();
    return $rs;
}
//取出一个文件夹及子文件夹下的所有文件，但不包括子文件夹的路径信息
function scan_file($path) {
    global $result;
    $files = scandir($path);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            if (is_dir($path . '/' . $file)) {
                scan_file($path . '/' . $file);
            } else {
                $result[] = basename($file);
            }
        }
    }
    return $result;
}

function my_dir($dir) {
    $files = [];
    if(@$handle = opendir($dir)) {
        while(($file = readdir($handle)) !== false) {
            if($file != ".." && $file != ".") {
                if(is_dir($dir . "/" . $file)) { //如果是子文件夹，进行递归
                    $files[$file] = my_dir($dir . "/" . $file);
                } else {
                    $files[] = $file;
                }
            }
        }
        closedir($handle);
    }
    return $files;
}

//取得文件扩展名
function get_file_ext($path){
    $length = strlen($path);
    $l = "";
    for($i=$length-1;$i>=0;$i--){
        $str = substr($path, $i,1);
        if($str == '.'){
            $l = $i;
            break;
        }
    }
    $rs = substr($path, $l+1);
    return $rs;
}

//判断是否有权限
function file_mode_info($file_path)
{
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

