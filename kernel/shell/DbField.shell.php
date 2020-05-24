<?php
class DbField{
	function __construct($c){
		$this->commands = $c;
	}
	
	public function run($attr){
		if(!isset($attr['db_key']))
			exit('db_key=xxx');
		
		if(!isset($GLOBALS['db_config']))
			exit('db_config is null');
		
		if(!isset($GLOBALS['db_config'][$attr['db_key']]))
			exit('db_key not in db_config');
		
		if(!isset($attr['table']))
			exit('table=xxx or table = all');
		
		$db = getDb($attr['db_key']);
		if('all' == $attr['table'])
			$table = $db->getFields();
		else 
			$table = $db->getFields('',array($attr['table']));
		if(!$table)
			exit('table is null');
		
		$file = "F:/www/rckernel/config/field.php";
		$fd = fopen($file,'w+');
		
		$str = $this->formatArr($table);

		fwrite($fd, $str);
	}
	
	function formatDetail($table){
		$br = "\r\n";
		
		$str="<?php\r\nreturn array($br";
		foreach($table as $k=>$fileds){
			$str .= "\t'".$k."'=>array($br";
			foreach($fileds as $k=>$v){
				$str .= "\t\t'name'=>'". $v['COLUMN_NAME'] ."','is_null'=>'{$v['IS_NULLABLE']}','type'=>\"{$v['COLUMN_TYPE']}\",$br";
			}
			// 			echo "\n\n";
			$str .= "\t),$br";
		}
		$str .= ");$br";
		return $str;
	}
	
	function formatArr($table){
		$br = "\r\n";
		
		$str="<?php\r\nreturn array($br";
		foreach($table as $k=>$fileds){
			$str .= "\t'".$k."'=>array($br";
			foreach($fileds as $k=>$v){
				$str .= "\t\t\"".$v['COLUMN_NAME']."\"=>\$aaa,".$br;
			}
			// 			echo "\n\n";
			$str .= "\t),$br";
		}
		$str .= ");$br";
		return $str;
	}
	
}
