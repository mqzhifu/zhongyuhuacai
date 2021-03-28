<?php
class ClearMysqlTable{
    public $_file_upload_dir = "";
    public function __construct($c){
        $this->commands = $c;
    }

    public function run(){
        $tables = array("admin_log","user","master","house","orders","orders_pay_list");
        foreach ($tables as $k=>$v){
            $sql = "truncate table $v";
            echo $sql ."\n";
            HouseModel::db()->execute($sql);
        }
    }

}