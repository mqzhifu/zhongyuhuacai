<?php
//项目的，日常日志
class LogappLib {                    //c:CONST                     G:GET
	static public $err = array('C_N_D'=>1,'DB'=>2,'APP'=>3,'G_PARA'=>4,'FILE'=>5,'SYSTEM'=>6);
	static public  $_file_name = "php_err.log";

	static function accessWrite($message , $module = 0 ) {
		if(ACCESS_LOG == 'DB'){
			$db = getDb(DEF_DB_CONN);
			$rs = $db->checkTable('action_log');
			if(!$rs)
				stop('access_log table is null','DB');
			
			$ip = get_client_ip();
			$time = time();
			$y_m_d = date("Y-m-d");
			$day = date("d");
			$year = date("Y");
			$month = date("m");
				
			$uid = 0;
			
// 			if($module)
// 				$module = self::$err[$module];
			
			$data = array(
					'uid'=>$uid,
					'ctrl'=>CTRL,
					'ac'=>AC,
					'ip'=>$ip,
// 					'type'=>$module,
					'access_type'=>ACCESS_TYPE,
					'year'=>$year,
					'y_m'=>$month,
					'y_m_d'=>$y_m_d,
					'add_time'=>$time,
			);
			
			$db->add($data,'action_log');
			
		}elseif(ACCESS_LOG == 'FILE'){
			
		}
	}

    static function w($content = "",$path = "",$file = "",$n = 1,$time = 1){
		if(!$path){
			$path = BASE_DIR.DS."log";
		}

        if(!$content){
            $content = "null";
        }
        if(!$file){
            $file = self::$_file_name;
        }
        if(!is_dir($path)){
            exit(" log path not exists...");
        }
        $path_file = $path .DS . $file;
        $fd = fopen($path_file,"a");

//		var_dump($content);
		if(is_array($content)){
			$content = json_encode($content,true);
		}

		if($time == 1)
			$content  .= "|".date("Y-m-d H:i:s")."|";


		if($n == 1)
			$content  .= "\n";


        fwrite($fd,$content);
    }
	
	static function errorWrite($message,$type,$app) {
		if(ERROR_LOG == 'DB'){
//			if(strlen($message) > 255)$message = substr($message, 0,255);
//			$db = getDb(DEF_DB_CONN);
//
//			$rs = $db->checkTable('error_log');
//			if(!$rs)
//				stop('error_log table is null','DB');
//
//			$ip = get_client_ip();
//			$time = time();
//			$y_m_d = date("Y-m-d");
//			$day = date("d");
//			$year = date("Y");
//			$month = date("m");
//
//			$uid = 0;
//
//			$ctrl = "";
//			if(defined('CTRL'))
//				$ctrl = CTRL;
//
//			$ac = "";
//			if(defined('AC'))
//				$ac = AC;
			
// 			if($module){
// 				if(isset(self::$err[$module]))
// 					$module = self::$err[$module];
// 				else
// 					$module = 1;
// 			}
//			$data = array(
//					'uid'=>$uid,
//					'ctrl'=>$ctrl,
//					'ac'=>$ac,
//					'ip'=>$ip,
//					'msg'=>$message,
//// 					'type'=>$module,
//					'access_type'=>ACCESS_TYPE,
//					'year'=>$year,
//					'y_m'=>$month,
//					'y_m_d'=>$y_m_d,
//					'add_time'=>$time,
//			);
//// 			var_dump($data);
//			$rs =$db->add($data,'error_log');
			
			
			
		}elseif(ACCESS_LOG == 'FILE'){
			self::w($message,'','error.log');
		}
		
	}

	static function sys($content){
		self::w($content,'','syslog.log');
	}

	static function app($content,$n = 1 ,$time = 1){

		self::w($content,'','app.log',$n,$time);
	}

	static function ws($content,$n = 1 ,$time = 1){
        self::w($content,'','sw.log',$n,$time);
    }
	
}
