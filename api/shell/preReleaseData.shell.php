<?php
//预发布，每周会从线上拉正式数据，同步到DB中
class PreReleaseData{
    public $_sql_file_dir = "/data1/import_online_mysql_data";
    public $_mysql_ip = '10.10.7.60';
    public $_mysql_user = 'games';
    public $_mysql_ps = 'pu6zMh2CQ55Q';

    function __construct($c){
        $this->commands = $c;
    }


    public function run($attr){
        ini_set('display_errors','On');

        if(!arrKeyIssetAndExist($attr,'ac')  ){
            exit("please ac=makeSqlShell ,ac=makeSourceShell \n");
        }

        $ac = $attr['ac'];
        $this->$ac($attr);
    }

    function makeSourceShell($attr){
        $files = $this->getDir($this->_sql_file_dir);

//        $IP = $attr['ip'];
//        $uname = $attr['uname'];
//        $ps = $attr['ps'];

        $str = "mysql --host={$this->_mysql_ip} -u{$this->_mysql_user} -p{$this->_mysql_ps} << EOF
drop database kxgame;
create database kxgame charset=utf8;
use kxgame
";
        foreach ($files as $k=>$v) {
            $str .= "source $v \n";
        }

        $str .= "EOF";
        echo $str;
    }

    public function makeSqlShell($attr){

//
//        if(!arrKeyIssetAndExist($attr,'ip') || !arrKeyIssetAndExist($attr,'uname') || !arrKeyIssetAndExist($attr,'ps')){
//            exit("please ip=xxx ,uname=xxx, ps=xxxx \n");
//        }

        $IP = $this->_mysql_ip;
        $uname = $this->_mysql_user;
        $ps = $this->_mysql_ps;


        $emptyTable = array('access_log','admin_log','task_user','sms_log','base_level','area','red_point','room','university','user_gift','point_log','black_word','pklog','push_log','masterstudent');
        $initData = array('menu','admin_user','roles','task_config','games_category','email_rule','sms_rule');

        $emptyTableExec = "";
        foreach ($emptyTable as $k=>$v) {
            $emptyTableExec .= $v ." ";
        }
        $exec1 = "mysqldump  -d -h$IP -u$uname -p$ps  --default-character-set=utf8 kxgame $emptyTableExec " ;


        $initTableExec = "";
        foreach ($initData as $k=>$v) {
            $initTableExec .= $v ." ";
        }
        $exec2 = "mysqldump  -h$IP -u$uname -p$ps  --default-character-set=utf8 kxgame $initTableExec " ;

        echo "echo ".$exec1 . "\n" ;
        echo $exec1 . " > {$this->_sql_file_dir}/empty_table.sql \n" ;
        echo "echo ".$exec2 ."\n";
        echo $exec2 ." > {$this->_sql_file_dir}/init_data.sql \n";


        $tables = $this->getTable();
        if(!$tables){
            var_dump($tables);exit;
        }

        $noAtimeArr = array();
        $yesAtimeArr = array();
        foreach ($tables as $k=>$v) {
//            echo "table:".$v . "===";
            $desc = $this->getTableDesc($v);
            $f = 0;
            foreach ($desc as $k2=>$v2) {
//                echo $v2['Field'] . " - ";
                if($v2['Field'] == 'a_time'){
                    $f = 1;
                    break;
                }
            }


            if(in_array($v,$emptyTable)){
                continue;
            }

            if(in_array($v,$initData)){
                continue;
            }

            if($f){
//                echo " yes ";
                $yesAtimeArr[$v]= $desc;
            }else{
//                echo " no ";
                $noAtimeArr[$v] = $desc;
            }

//            echo "\n";

        }

//        if($noAtimeArr){
//            foreach ($noAtimeArr as $k=>$v) {
//                echo $k . " ===";
//                foreach ($v as $k2=>$v2) {
//                    echo $v2['Field'] . " - ";
//                }
//
//                echo "\n";
//            }
//        }




        $time = time() - 60 * 24 * 60 * 60;
        foreach ($yesAtimeArr as $k=>$v) {
            $exec = "mysqldump -h$IP -u$uname -p$ps  --default-character-set=utf8 kxgame $k --where='a_time >=$time' " ;
            echo "echo $exec \n";
            echo $exec ." > {$this->_sql_file_dir}/$k.sql \n";
        }

        $noAtimeTable = "";
        foreach ($noAtimeArr as $k=>$v) {
            $noAtimeTable .= $k . " ";
        }

        $exec = "mysqldump   -h$IP -u$uname -p$ps  --default-character-set=utf8 kxgame $noAtimeTable " ;
        echo "echo $exec \n";
        echo $exec ."  >  {$this->_sql_file_dir}/no_atime.sql \n";

    }

    function getDir($path)
    {
        //判断目录是否为空
        if(!file_exists($path)) {
            return [];
        }

        $files = scandir($path);
        $fileItem = [];
        foreach($files as $v) {
            $newPath = $path .DIRECTORY_SEPARATOR . $v;
            if(is_dir($newPath) && $v != '.' && $v != '..') {
                $fileItem = array_merge($fileItem, getDir($newPath));
            }else if(is_file($newPath)){
                $fileItem[] = $newPath;
            }
        }

        return $fileItem;
    }

    function getMysql(){
//        if(PCK_AREA == 'cn'){
//            if(ENV == 'release'){
//                $fd = mysqli_connect('10.10.7.144','instantplay','2vLboyEVX9J','kxgame_log');
//            }else{
//                $fd = mysqli_connect('127.0.0.1','root','Dp74p966qPyTkBQ8','kxgame_log');
//            }
//        }else{
//            if(ENV == 'release'){
////                $fd = mysqli_connect('10.10.7.144','instantplay','2vLboyEVX9J','kxgame_log');
//            }else{
//                $fd = mysqli_connect('127.0.0.1','games','pu6zMh2CQ55Q','kxgame_log');
//            }
//        }

        $fd = mysqli_connect('10.10.7.227','games','pu6zMh2CQ55Qxyx','kxgame');


        return $fd;
    }

    function getTableDesc($table){
        $fd = $this->getMysql();
        $sql = " desc $table" ;
        $rs = mysqli_query($fd,$sql);
        if(!$rs){
            var_dump($rs);exit;
        }

        $result = array();
        if($rs) {
            while($row = mysqli_fetch_assoc($rs)){
                $result[]   =   $row;
            }
        }

        return $result;
    }

    function getTable(){
        $sql = " show tables";
        $fd = $this->getMysql();
        $rs = mysqli_query($fd,$sql);
        if(!$rs){
            var_dump($rs);exit;
        }

        $result = array();
        if($rs) {
            while($row = mysqli_fetch_assoc($rs)){
                $result[]   =   $row;
            }
        }

        foreach ($result as $k=>$v) {
            $tableNames[] = $v['Tables_in_kxgame'];
        }

        return $tableNames;
    }

}
