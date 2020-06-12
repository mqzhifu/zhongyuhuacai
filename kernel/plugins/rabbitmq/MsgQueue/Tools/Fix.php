<?php
namespace php_base\MsgQueue\Tools;
use php_base\MsgQueue\Model\MsgRecordModel;
use php_base\MsgQueue\Facades\MsgQueue;


class Fix
{
    private $_mqProvider = null;

    function __construct(){
        $this->_mqProvider = MsgQueue::getInstance(null,null,3);
    }
    //生产者发关失败的
    function fixExceptionMsgAndRedelivery(){
        $cnt = 10;
        //一次拿100条，最多10次，每CLI执行一次修复，共计最多：1000条
        while ($cnt > 0) {
            //runtimeException retryReject userReject
            $msgList = MsgRecordModel::getSendFailListAndNoFix(100);
            if (!$msgList) {
                $this->out("notice:getSendFailListAndNoFix is null");
                break;
            }

            $cnt = count($msgList);
            $this->out("cnt:".$cnt);
            foreach ($msgList as $k=>$v) {
                $this->out("fix msg id:{$v['id']} msg_id:".$v['message_id']);
//                $rs = MsgRecordModel::upSendFailAndNoFixStatusByMsgId($v['id']);
//                $this->out("upSendFailAndNoFixStatusByMsgId: rs ".$rs);
                $attribute = json_decode($v['attribute'],true);
                $attribute['user_id'] = 99999 ;
                $this->_mqProvider->publish($v['content'],null,null,json_decode($v['events'],true),$attribute,1);
                $rs = MsgRecordModel::fixDoneByMsgId($v['id']);
                $this->out("fixDoneByMsgId: rs ".$rs);
                exit;
            }
            $this->out("fix done,cnt:".$cnt);
            $cnt-- ;
        }
    }

    function out($msg ,$br = 1){
        if(is_object($msg) || is_array($msg)){
            $msg = json_encode($msg);
        }
        if($br){
            if (preg_match("/cli/i", php_sapi_name())){
                echo $msg . "\n";
            }else{
                echo $msg . "<br/>";
            }
        }else{
            echo $msg;
        }
    }
}