<?php
//生成数据结构字典-WORD 格式
class DBWord {
	function __construct($c){
		$this->commands = $c;
	}

	public function run($attr){
		set_time_limit(0);
		if(!isset($attr['db_key']))
			exit('db_key=xxx');

		if(!isset($GLOBALS['db_config']))
			exit('db_config is null');

		if(!isset($GLOBALS['db_config'][$attr['db_key']]))
			exit('db_key not in db_config');

		if(!isset($attr['target_dir']))
			exit('target_dir=xxx');

		if(!is_dir($attr['target_dir']))
			exit('target_dir not path!');

		if(!isset($attr['db_name']))
			exit('db_name=xxx');

		$dbname = $attr['db_name'];
		
		
		require_once PLUGIN.'PHPWord.php';
		
		$indexDesc = array('UNI'=>'唯一','PRI'=>'主键','MUL'=>'索引');

		$PHPWord = new PHPWord();
		$section = $PHPWord->createSection();
		
		$section->addText('数据字典');
		$section->addTextBreak(2);

		$db = getDb('test_information_schema');

		$sql = "select DEFAULT_CHARACTER_SET_NAME,DEFAULT_COLLATION_NAME from SCHEMATA where SCHEMA_name = '$dbname' ";//数据库信息
		$dbInfo = $db->getRowBySql($sql);
		if(!$dbInfo)exit('databases null');

		$section->addText("数据库名：".$dbname);
		$section->addText("字符集：".$dbInfo['DEFAULT_CHARACTER_SET_NAME']);

		$styleTable = array('borderSize'=>6, 'borderColor'=>'006699', 'cellMargin'=>80);
		$styleFirstRow = array('borderBottomSize'=>18, 'borderBottomColor'=>'0000FF', 'bgColor'=>'66BBFF');

		$styleCell = array('valign'=>'center');
		$styleCellBTLR = array('valign'=>'center', 'textDirection'=>PHPWord_Style_Cell::TEXT_DIR_BTLR);

		$fontStyle = array('bold'=>true, 'align'=>'center');

		$PHPWord->addTableStyle('myOwnTableStyle', $styleTable, $styleFirstRow);

		$sql = "select * from TABLES where table_schema = '$dbname'";

		$tables = $db->getAllBySQL($sql);
		if(!$tables)exit('db tables: null');

		foreach($tables as $k=>$v){
			echo "table:".$v['TABLE_NAME'] ."\n";
			$table = $section->addTable('myOwnTableStyle');

			$table->addRow();

			$styleCell=array('gridSpan' => 2);

			$tname = $v['TABLE_NAME'];
			$table->addCell(1800,$styleCell)->addText("表名：".$tname);

			$sql = "select * from TABLES where table_schema = '$dbname' and table_name = '$tname'";//表相关信息

			$tableInfo = $db->getRowBySql($sql);
			$table->addCell(1600,$styleCell)->addText("引擎：".$tableInfo['ENGINE']);

			$desc = $tableInfo['TABLE_COMMENT'];
			$table->addCell(4400,$styleCell)->addText("描述：".$desc);

			$sql = "select * from COLUMNS where table_schema = '$dbname' and table_name = '$tname' order by ORDINAL_POSITION";//列相关信息
			$columns = $db->getAllBySql($sql);
			if(!$columns){
				echo " table columns is null" ;
				continue;
			}

			for($i=0;$i<3;$i++){
				$table->addCell(0,"");
			}

			$table->addRow();
			$table->addCell(900)->addText("字段");
			$table->addCell(900)->addText("类型");
			$table->addCell(800)->addText("默认");
			$table->addCell(800)->addText("空");
			$table->addCell(800)->addText("描述");
			$table->addCell(400)->addText("索引");
			foreach($columns as $k=>$v){
				$table->addRow();
				if(isset($v['COLUMN_NAME']))
					$table->addCell(900)->addText($v['COLUMN_NAME']);
				else
					$table->addCell(900)->addText("");

				if(isset($v['COLUMN_TYPE']))
					$table->addCell(900)->addText($v['COLUMN_TYPE']);
				else
					$table->addCell(900)->addText("");

				if(isset($v['COLUMN_DEFAULT']))
					$table->addCell(900)->addText($v['COLUMN_DEFAULT']);
				else
					$table->addCell(900)->addText("");

				if(isset($v['IS_NULLABLE']))
					$table->addCell(900)->addText($v['IS_NULLABLE']);
				else
					$table->addCell(900)->addText("");
				$common = "";
				if(isset($v['COLUMN_COMMENT'])){
// 					$common =  iconv('utf-8', 'gbk', $v['COLUMN_COMMENT']);
					$table->addCell(3000)->addText($v['COLUMN_COMMENT']);
				}
				else
					$table->addCell(900)->addText("");


				$t = "";
				if(isset($v['COLUMN_KEY'])){
					if(isset($indexDesc[$v['COLUMN_KEY']]))
						$t = $indexDesc[$v['COLUMN_KEY']];

					if(isset($v['EXTRA'])){
						if( $v['EXTRA'] == 'auto_increment')
							$t .= ' 自增';
					}
					$table->addCell(900)->addText($t);
				}else{
					$table->addCell(900)->addText("");
				}


			}

			$sql = "select * from STATISTICS where table_schema = '$dbname' and table_name = '$tname'";//索引
			$index = $db->getAllBySql($sql);
			if($index){
				foreach($index as $k=>$v){
					$table->addRow();

					$table->addCell("300")->addText("索引名：".$v['INDEX_NAME']);
					$table->addCell("300")->addText("列名：".$v['COLUMN_NAME']);
				}
			}
			$section->addTextBreak(2);
		// 	$sql = "select * from TABLE_CONSTRAINTS where table_schema = '$dbname' and table_name = '$tname'";//表约束
		// 	$limit = $db->getAll($sql);
		// 	if($limit){
		// 		foreach($index as $k=>$v){
		// 			$table->addRow();


		// 			$table->addCell()->addText("约束名：".$v['CONSTRAINT_TYPE']);
		// 			$table->addCell()->addText("字段：".$v['COLUMN_NAME']);
		// 		}
		// 	}

		// 	$sql = "select * from KEY_COLUMN_USAGE where table_schema = '$dbname' and table_name = '$tname'";//描述了具有约束的键列

		// 	$limit = $db->getAll($sql);
		// 	var_dump($limit);

		// 	exit;
		}
		
		$objWriter = PHPWord_IOFactory::createWriter($PHPWord , 'Word2007');
		$objWriter->save($attr['target_dir'].DS."db.docx");
			
	}
}







