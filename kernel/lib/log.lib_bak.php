<?php
//系统日志
class LogLib {
    static function seasLogInfo($dir,$module,$str){
        SeasLog::setBasePath($dir);
        SeasLog::setLogger($module);
        return SeasLog::info($str );
    }

    //错误日志  error fatal exception
    static function systemWriteFileHash($module,$title  ,$content =null ) {
        if(!$title && !$content){
            return -1;
        }

        $str = $title;
        if($content){
            $str .= json_encode($str);
        }

        $dir = LOG_PATH.DS .APP_NAME .DS."system";

        return self::seasLogInfo($dir,$module,$str);
    }
    //访问日志
    static function accessWriteFileHash($title = null  ,$content =null ) {
        if(!$title && !$content){
            return -1;
        }

        $str = $title;
        if($content){
            $str .= json_encode($content);
        }
        $dir = LOG_PATH.DS .APP_NAME ;

        return self::seasLogInfo($dir,"access",$str);
    }

    //响应日志
    static function responseWriteFileHash($title = null  ,$content =null ) {
        if(!$title && !$content){
            return -1;
        }

        $str = $title;
        if($content){
            $str .= json_encode($content);
        }
        $dir = LOG_PATH.DS .APP_NAME ;

        return self::seasLogInfo($dir,"response",$str);
    }



    //WS总日志
    static function wsWriteFileHash( $content  ) {
    }

    static function MysqlWriteFileHash( $content  ) {
    }

    static function matchUserWriteFileHash( $content  ) {
    }

    static function sendmailWriteFileHash( $content  ) {
    }

    static function appWriteFileHash($title = null, $content  = null ) {
        if(!$title && !$content){
            return -1;
        }

        $str = $title;
        if($content){
            $str .= json_encode($content);
        }
        $dir = LOG_PATH.DS .APP_NAME ;

        return self::seasLogInfo($dir,"app",$str);
    }

    //根据时间，HASH分散存储
    //实际上用了seasLog扩展，基本没用了这个方法
    static function writeHash($content,$module,$hashType = 'day'){

        if(is_array($content)){
            $content = json_encode($content);
        }
        $date = "[".date("Y-m-d H:i:s").']';
        $content =$date. " [pid:".getmypid(). "]  ".$content."\n";

        $file = date("Y-m-d") . ".log";
        $path_dir = LOG_PATH.DS.APP_NAME.DS.$module.DS;
        $path_file = $path_dir.$file;

        if(!file_exists($path_dir)) {
            mkdir($path_dir, 0777, true);
        }

        $fd = fopen($path_file,"a+");
        fwrite($fd,$content);

    }

}
