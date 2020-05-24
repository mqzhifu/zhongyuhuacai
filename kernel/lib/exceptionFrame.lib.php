<?php
//异常处理
class ExceptionFrameLib extends Exception {
	
//	public function __construct($message,$code=0,$extra=false) {
//	    var_dump(333);exit;
//		parent::__construct($message,$code);
//	}
    //脚本结束，或者触发了fatal  必须静态
    static function fatalShutdown()
    {
        $e = error_get_last();
        if($e){//证明是 非正常结束（也可能是异常 被其它被捕了）
            $type = getErrInfo($e['type']);
            $str = "[type]:".$type.",[msg]:".$e['message'].",[file]:".$e['file'].",[line]".$e['line'];

            if(RUN_ENV == 'WEBSOCKET'){
                exit("shutdown_function WEBSOCKET");
                LogLib::inc()->fatal([$str]);
            }else {
                LogLib::inc()->fatal($str);
                if (!DEBUG) {
                    $arr = array("code" => 9993, 'msg' => 'fatal');
                    echo json_encode($arr);
                    exit;
                } else {
                    var_dump($str);
                    exit;
                }
            }
        }
    }
    //捕获 异常 触发
    static function throwCatch($exceptionInfo ) {
	    if(is_object($exceptionInfo)){
            $errInfo = self::parseExceptionObjToStr($exceptionInfo);
        }elseif(is_scalar($exceptionInfo) ){//标量
            $obj = new Exception($exceptionInfo);
            $errInfo = self::parseExceptionObjToStr($obj);
        }elseif(is_array($exceptionInfo)){
            $obj = new Exception(json_encode($exceptionInfo));
            $errInfo = self::parseExceptionObjToStr($obj);
        }else{
	        exit("throwCatch $exceptionInfo type error!");
        }

        if(RUN_ENV == 'WEBSOCKET'){
            exit("WEBSOCKET throwCatch");
//            LogLib::wsWriteFileHash([$errInfo[0]['msg']]);
        }elseif(RUN_ENV == 'WEB' || RUN_ENV == 'CLI'){
            LogLib::inc()->exception($errInfo);
//            if(!DEBUG){
//                $arr = array("code"=>9991,"msg"=>'throw new exception:'.$errInfo[0]['msg']);
//                echo json_encode($arr);
//
//            }else{
//                var_dump($errInfo);
//            }

//            $smarty = getKernelSmarty();
//            $header_html = $smarty->compile("error.html");
//            echo include_once $header_html;

            out("first:");
            foreach ($errInfo['first'] as $k=>$v) {
                out(" ".$k . " " .$v);
            }
            out(" ");
            out(" ");
            out("stack:");
            foreach ($errInfo['stack'] as $k=>$v) {
                out(" ".$k . " " .json_encode($v));
            }

        }
        exit;
    }
    //notice 之类的错误
	static public function appError($errno, $errstr, $errfile, $errline) {
        $type = getErrInfo($errno);
        $str  = "[type]: $type"."[msg]: $errstr "."[file]: $errfile "."[line]: $errline";

	    if(RUN_ENV == 'WEBSOCKET'){
            LogLib::wsWriteFileHash([$str]);
        }else{
            LogLib::inc()->error($str);
            if(!DEBUG){
                $arr = array("code"=>9992,"msg"=>'appError');
                echo json_encode($arr);

            }else{
                var_dump($str);
            }

            exit;
        }


	}
    //将异常信息，格式化
	static function parseExceptionObjToStr($e){
        $first = array(
            "message" => $e->getMessage(),
            "code" => $e->getCode(),
            "file" => $e->getFile(),
            "line" => $e->getLine(),
        );

        $trace = $e->getTrace();
//        $traceInfo = "";
//        foreach ($trace as $t) {
//            if(isset($t['msg']))
//                $traceInfo .=  ' (' . $t['msg'] . ') ';
//
//            if(isset($t['line']))
//                $traceInfo .=  ' (' . $t['line'] . ') ';
//
//            if(isset($t['file']))
//                $traceInfo .= $t['file'] . " ";
//
//            if(isset($t['class']))
//                $traceInfo .= $t['class'] . " ";
//
//            if(isset($t['type']))
//                $traceInfo .= $t['type'] . " ";
//
//            if(isset($t['function']))
//                $traceInfo .= $t['function'] . " ";
//
//            if(isset($t['args']) && $t['args']){
//                var_dump($t['args']);exit;
//                $traceInfo .= implode(',', $t['args']);
//            }
//            $traceInfo .=')'.$s;
//        }

        $data = array(
            'first'=>$first,
            'stack'=>$trace,
        );
        return $data;

    }
    //显示 到浏览器的错误信息
	static public function errInfo($trace){
			$img = '<td valign="top"  class="iconfont"><img src="'.STATIC_URL .'/common_img/licon.png"/></td>';
			$td = '<td valign="top" class="message">';
			$td_e = '<td valign="top" class="message"></td>';
			foreach ($trace as $t) {
				$traceInfo .= "<tr>$img{$td}";
				if(isset($t['line']))
					$traceInfo .=   $t['line'].'</td>'.$td ;
					
				if(isset($t['file']))
					$traceInfo .= $t['file'] . " ";
					
				if(isset($t['class']))
					$traceInfo .= $t['class'] . " ";
					
				if(isset($t['type']))
					$traceInfo .= $t['type'] . " ";
					
				if(isset($t['function']))
					$traceInfo .= $t['function'] . " ";
				
				$str = "";
				if(isset($t['args']) && is_array($t['args']) && $t['args'] ){
					foreach($t['args'] as $k=>$v){
						if(is_array($v)){
							
							
						}elseif(is_object($v)){	
						}else{
							$str .= $v;
						}
						
					}
// 					$traceInfo .= "(". $v2 .")";
				}
				// 					
				$traceInfo .= "</td>$td_e</tr>";
			}
		return $traceInfo;
	}

}
