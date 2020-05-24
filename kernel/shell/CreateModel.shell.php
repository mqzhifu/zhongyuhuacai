<?php
class CreateModel{
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
		
		if(!isset($attr['app_dir']))
			exit('app_dir=xxx');
		
		if(!is_dir($attr['app_dir']))
			exit('app_dir not path!');
		
		$db = new DbLib($GLOBALS['db_config'][$attr['db_key']]);
		$tables = $db->getTableIndex();
		if(!$tables)
			exit('tables index null');
		$c = 0;
		foreach($tables as $k=>$v){
			$file = $attr['app_dir'] .DS.M_DIR_NAME.DS.$k .M_EXT;
// 			echo $file."\n";exit;
			$fd = fopen($file, "w+");
			$c++;
			$file_model = $attr['app_dir']."/model.tmp.txt";
			$model = file_get_contents($file_model);
			$model = str_replace("##classname", ucfirst($k), $model);
			$model = str_replace("##table", $k, $model);
			$model = str_replace("##M_CLASS", M_CLASS, $model);
			$primary = '';
			if($v){
				foreach($v as $k2=>$v2){
					if($v2 == 'PRIMARY'){
						$primary = $k2;
						break;
					}
					
				}
				$model = str_replace("##primary",$primary, $model);
			}
			fwrite($fd, $model);
		}
		
		echo "Success:new file numbers~$c\n";
	}
}