<?php
//系统日志
class LogLib {
    public $_storeDesc = array(1=>"文件",2=>'数据库',3=>"文件+数据库");
    public $_store = 1;
    public $_hashType = 1;
    public $_hashTypeDesc = array(1=>'小时',2=>"天",3=>'月',4=>"年",);
    public $_categoryDesc = array(1=>"访问日志",2=>"异常",3=>"致使错误",4=>"普通错误");
    public $_extName = ".log";
    static public $_inc = null;

    static function inc(){
        if(self::$_inc){
            return self::$_inc;
        }

        self::$_inc = new self();
        return self::$_inc;
    }

    function access($content =null  ){
        $dir = LOG_PATH.DS .APP_NAME .DS."access";
        $this->write($dir,$content);
    }

    function exception($content){
        $dir = LOG_PATH.DS .APP_NAME .DS."exception";
        $this->write($dir,$content);
    }

    function fatal($content){
        $dir = LOG_PATH.DS .APP_NAME .DS."fatal";
        $this->write($dir,$content);
    }

    function error($content){
        $dir = LOG_PATH.DS .APP_NAME .DS."error";
        $this->write($dir,$content);
    }

    function response($content){
        $dir = LOG_PATH.DS .APP_NAME .DS."response";
        $this->write($dir,$content);
    }

    //根据时间，HASH分散存储
    //实际上用了seasLog扩展，基本没用了这个方法
    function write($dir,$content){
        $str = "";
        if(is_array($content)){
            $str = json_encode($content);
        }elseif(is_object($content)){
            $str = serialize($content);
        }else{
            $str = $content;
        }
        //检查目录是否存在，不存在即创建之
        if(!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $br = get_br();
        if($this->_hashType){
            if(1 == $this->_hashType){
                $file = $dir .DS . date("YmdH");
            }elseif(2 == $this->_hashType){
                $file = $dir .DS . date("Ymd");
            }elseif(3 == $this->_hashType){
                $file = $dir .DS . date("Ym");
            }elseif(4 == $this->_hashType){
                $file = $dir .DS . date("Y");
            }else{
                exit(" log hash type error");
            }
        }else{
            $file = $dir;
        }



        $file .= $this->_extName;
        $date = "[".date("Y-m-d H:i:s").']';
        $content =$date. " [pid:".getmypid(). "]  ".$str.$br;

        $fd = fopen($file,"a+");
        fwrite($fd,$content);
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

}
