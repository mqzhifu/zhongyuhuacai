<?php
class InitDb{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
        UserModel::getRow();exit;


        if(!isset($attr['db_name']))
            exit('db_name=xxx');

        $user = 'root';
        $ps = 'root';
        $path = "C:/Users/carssbor/Desktop/";
        $h = '127.0.0.1';
        $db_name = 'rctailor_test';

        $file = $path.$attr['db_name'];

        $db = getDb('local_rc');


        $sql = "set global max_allowed_packet = 100 * 1024 * 1024;set global connect_timeout = 120;";
        echo $sql ."\n";
        $db->querySql($sql);

		$sql = 'set character_set_client=utf8;set character_set_connection=utf8;';
		echo $sql ."\n";
		$db->querySql($sql);
		
		$sql = 'set character_set_database=utf8;set character_set_filesystem = utf8;';
		echo $sql ."\n";
		$db->querySql($sql);
		
		$sql = 'set character_set_server = utf8;set character_set_results = utf8;';
		echo $sql ."\n";
		$db->querySql($sql);
		
		$sql = 'drop database '.$db_name;
		echo $sql ."\n";
		$db->querySql($sql);
		$sql = "create database $db_name charset=utf8";
		echo $sql ."\n";
		$db->querySql($sql);
		
		
		$command = " mysql --default-character-set=utf8 -h$h -u{$user} -p{$ps} $db_name  < $file ";
		echo $command ."\n";
		
		exec($command);
		
// 		$file = $path."text.sql";
// 		$command = " mysql --default-character-set=utf8 -h$h -u{$user} -p{$ps} $db < $file ";
// 		echo $command ."\n";
		
// 		exec($command);
		
// 		$file = $path."house.sql";
// 		$command = " mysql --default-character-set=utf8 -h$h -u{$user} -p{$ps} $db < $file ";
// 		echo $command ."\n";
		
// 		exec($command);
		
// 		$file = $path."shop.sql";
// 		$command = " mysql --default-character-set=utf8 -h$h -u{$user} -p{$ps} $db < $file ";
// 		echo $command ."\n";
		
// 		exec($command);
		
// 		$file = $path."resource.sql";
// 		$command = " mysql --default-character-set=utf8 -h$h -u{$user} -p{$ps} $db < $file ";
// 		echo $command ."\n";
		
// 		exec($command);
		
		
	}
}
