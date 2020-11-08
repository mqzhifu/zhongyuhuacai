<?php
//数据库对比工具~对于线下环境  对比线上环境  DB 修改
class Cmd_compareDb extends Cmd {

    private $db = null;

    public function run()
    {
//        $this->db = Dao_Mysql_DB::getInstance();

        $base_db_file = "d:/compare_db/base.sql";
        $compare_db_file = "d:/compare_db/sg.sql";

        if(!file_exists($base_db_file)){

            exit('file <base> dir err');

        }

        if(!file_exists($compare_db_file)){

            exit('file <compare> dir err');

        }


        $base_db_str = file_get_contents($base_db_file);
        $compare_db_str = file_get_contents($compare_db_file);


        $base_table = $this->getTableData($base_db_str);
        $compare_table = $this->getTableData($compare_db_str);


        $diff_table = [];
        $diff_field = [];
        foreach($base_table as $k1=>$v1){
            $f = 0;
            foreach($compare_table as $k2=>$v2){
                if($k1 == $k2){
                    $f = 1;
                    $d = $this->compareTableField($v1,$v2);
                    $diff_field[$k1] = $d;
                    break;
                }
            }

            if(!$f){
                $diff_table[] = $k1;
            }
        }

        $n = "\r\n";

        $fd = fopen("d:/compare_db/diff.txt",'w+');
        fwrite($fd,"不同的表：$n");

        foreach($diff_table as $k=>$v){
            fwrite($fd,$v.$n);
        }

        fwrite($fd,"\r\n结构不同\r\n");
        foreach($diff_field as $k=>$v){
            fwrite($fd,"table:".$k.$n);
            foreach($v as $k2=>$v2){
                fwrite($fd,$v2.$n);
            }

            fwrite($fd,$n.$n);

        }



    }

    function compareTableField($table1,$table2){
        $table1_arr = explode("#n#",$table1);
        $table2_arr = explode("#n#",$table2);


        $diff_field = [];
        foreach($table1_arr as $k1=>$v1){
            $f = 0;
            foreach($table2_arr as $k2=>$v2){
                if($v1 == $v2){
                    $f = 1;
                    break;
                }
            }
            if(!$f){
                $diff_field[] = $v1;
            }
        }

        return $diff_field;
    }

    function getTableData($str){
        $base_db = str_replace("\n","#n#",$str);
        //以：CREATE TABLE开头以    ENGINEXXX分号 结尾
        $table_rule = "/CREATE TABLE(.*?)ENGINE(.*?);/";
        preg_match_all($table_rule,$base_db,$grep_rs_table,PREG_SET_ORDER);

        $base_table = [];
        $compare_table = [];
        $table_name_rule = "/E `(.*?)` \(/";
        foreach($grep_rs_table as $k=>$v){
            preg_match_all($table_name_rule,$v[0],$table_name,PREG_SET_ORDER);
            $base_table[$table_name[0][1]] = $v[0];
        }

        return $base_table;
    }
}

