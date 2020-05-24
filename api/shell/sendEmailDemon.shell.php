<?php
class SendEmailDemon{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
        $data = EmailLogModel::db()->getAll(" status = 1 limit 100");
        if(!$data){
            LogLib::sendmailWriteFileHash("no data!");
            exit;
            sleep(1);
        }

        $cnt = count($data);
        LogLib::sendmailWriteFileHash(["cnt:",$cnt]);
        $emailLib = new EmailLib(1);
        foreach($data as $k=>$v){
            $rs = $emailLib->realSend($v['email'],$v['title'],$v['content']);
            if($rs == true){
                EmailLogModel::db()->upById($v['id'],array('status'=>2));
            }else{
                EmailLogModel::db()->upById($v['id'],array('status'=>3,'err_info'=>$rs));
            }
        }
    }


}

function o($str){
//    $encode = mb_detect_encoding($str, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
//    var_dump($encode);
//    var_dump($str);
//    var_dump(iconv("UTF-8","gbk//TRANSLIT",$str));
    if(PHP_OS == 'WINNT'){
        $str = iconv("UTF-8","GBK//IGNORE",$str)."\r\n";
    }

    echo $str."\n";
}