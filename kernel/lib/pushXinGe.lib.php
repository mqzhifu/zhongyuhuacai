<?php
include_once PLUGIN."/XingeApp.php";
//腾迅-信鸽PUSH
class PushXinGeLib{

    private $_appId = "2100327099";
    private $_secret_key = "960a1a069b12a439b3b2ec1fa2cf7635";

    private $_foreign_appId = "2100334169";
    private $_foreign_secret_key = "cc552045cd41cd82a09e23a07268a93b";

    private $_appId_new = "2100339375";
    private $_secret_key_new = "5b1ea9ea3b767e50e9d425edd42a176f";

    function __construct(){
        if(APP_NAME == 'instantplay_new'){
            $this->XingeApp =new XingeApp($this->_appId_new, $this->_secret_key_new);
            $this->userService = new UserService();
        }else{
            if(PCK_AREA == 'en'){
                $this->XingeApp =new XingeApp($this->_foreign_appId, $this->_foreign_secret_key);
                $this->userService = new UserService();
            }else{
                $this->XingeApp =new XingeApp($this->_appId, $this->_secret_key);
                $this->userService = new UserService();
            }
        }
    }

    //发送一条 安卓 透传 的消息，通过token
    function pushAndroidMsgOneMsgByToken($uid,$title,$content,$customData = '' ){
        LogLib::appWriteFileHash(["^^^^^^^^^^^^^^^^^^^^^^^^^pushAndroidMsgOneMsgByToken",$uid,$title,$content,$customData]);
        $token = $this->userService->getFieldById($uid,"push_xinge_touken");
        if(!$token){
            return out_pc(8049);
        }

        return $this->pushAndroid(1,Message::TYPE_MESSAGE,$token,$title,$content,$customData ,0  ,0 ,""  );
    }
    //$expireTime:离线保存时间，0实时，2147483647最大时间
    //$actionType:1打开APP，2打开浏览器，跳转URL,3打末intent
    //$customData:自定义的一个 结构体，可能移动端做类别区分处理，没有可以不写
    function pushAndroidNotifyOneMsgByToken($uid,$title,$content,$customData = null,$actionType =1 ,$expireTime = 72 * 60 * 60,$jumpUrl = 0){
        $token = $this->userService->getFieldById($uid,"push_xinge_touken");
        if(!$token){
            return out_pc(8049);
        }

//        var_dump($uid);
//        var_dump($token);
//        var_dump($title);
//        var_dump($content);

        return $this->pushAndroid(1,Message::TYPE_NOTIFICATION,$token,$title,$content,$customData ,$actionType  ,$expireTime ,$jumpUrl  );
    }
    //$type:1单个2全部
    function pushAndroid($type,$msgType,$token,$title,$content,$customData ,$actionType = 1 ,$expireTime ,$jumpUrl = ""){
        $data = array(
            'title'=>$title,
            'content'=>$content,
            'a_time'=>time(),
            'type'=>1,
            'catetory'=>$type,
            'device'=>1,
            'msg_type'=>$msgType,
            'third_token'=>$token,
            'uid'=>0,
            'custom_data'=>"",
        );

        $mess = new Message();
        $mess->setType($msgType);
        $mess->setTitle($title);
        $mess->setContent((string)$content);

        $mess->setExpireTime($expireTime);

        $action = new ClickAction();
        $action->setActionType($actionType);

        if($msgType == Message::TYPE_NOTIFICATION){
            if($actionType == ClickAction::TYPE_URL){
                $action->setUrl($jumpUrl);
                #打开url需要用户确认
                $action->setComfirmOnUrl(1);
            }

            //$style = new Style(0);
            #含义：样式编号0，响铃，震动，不可从通知栏清除，不影响先前通知
//        $style = new Style(0,1,1,0,0);
//        $mess->setStyle($style);
        }
        $custom = null;
//        $custom = array('key1'=>'value1', 'key2'=>'value2');
        if($customData){
            $data['custom_data'] = json_encode($customData);
            $custom = $customData;
            $mess->setCustom($custom);
        }

        $mess->setAction($action);

        $acceptTime1 = new TimeInterval(0, 0, 23, 59);
        $mess->addAcceptTime($acceptTime1);


        $aid = PushLogModel::db()->add($data);

        if($type == 1){
            $ret = $this->XingeApp->PushSingleDevice($token, $mess);
        }elseif($type == 2){
            $ret = $this->XingeApp->PushAllDevices(0, $mess);
        }

//        $argc = func_get_args();
        PushLogModel::db()->upById($aid,array("return_info"=>json_encode($ret)));

        return($ret);
    }

    function pushAll($msgType,$title,$content,$customData){
        return $this->pushAndroid(2,$msgType,null,$title,$content,$customData);
    }
}