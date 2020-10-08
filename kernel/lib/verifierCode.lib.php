<?php
//验证码 操作类
class VerifierCodeLib{
    const TypeCellphone = 1;
    const TypeEmail = 2;
    const EXPIRE_TIME = 300;
//    private $_code = 0;
    function getTypeDesc(){
        return array(self::TypeCellphone=>'手机',self::TypeEmail=>'邮箱');
    }

    function keyInType($key){
        return in_array($key,array_flip($this->getTypeDesc()));
    }
    //发送一条验证码信息
    function sendCode($type,$addr,$ruleId,$uid = 0){
        $checkRuleRs = $this->checkRule($addr,$ruleId,$type);
        if($checkRuleRs['code'] != 200){
            return out_pc($checkRuleRs['code'],$checkRuleRs['msg']);
        }
//        $ruleInfo = $checkRuleRs['msg'];
        //生成6位随机数字验证码
        $code = rand(100000,999999);
//        $replace_content = array('${code}'=>$code  );
        $replace_content = array('code'=>$code  );
        //开始发送短信
        $rs = $this->send($type,$addr,$ruleId,$replace_content);
        if($rs['code'] !=  200){
            return $rs;
        }

        $data = [
            'code' => $code,
            'expire_time' => time() + self::EXPIRE_TIME,
            'a_time' =>  time(),
            'status' => VerifiercodeModel::STATUS_NORMAL,
            'rule_id' => $ruleId,
            'type'=>$type,
            'addr'=>$addr,
            'uid' => $uid,
        ];

        $id = VerifiercodeModel::db()->add($data);

        return out_pc(200,$id);
    }
    //检查是否有：之前已经发送过的短信，但是未失效的，重新发送需要将之前的短信变更为失效状态
    //避免DB中有多条记录，干扰结果
    function checkHasSendAndUpExpire($addr,$type,$ruleId){
        $data = array(
            'status'=>VerifiercodeModel::STATUS_REPEAT_EXPIRE,
            'u_time'=>time(),
        );
        $rs = VerifiercodeModel::db()->update($data," addr = '$addr' and type = $type and rule_id = $ruleId and status = ".VerifiercodeModel::STATUS_NORMAL." limit 20 ");
        return $rs;
    }
    //检查是否有：已经过期/失效的验证码，更新下状态
    function checkExpireRecord(){
        //判断之前有没有发送过，如果有，且没有失效的，要置一下状态位
//        $info = VerifiercodeModel::db()->getRow(" addr = '$addr' and rule_id = $ruleId and status = 1");
//        if($info){
//            if($info['expire_time'] > time()){//之前有发送过的，但是已失效
//                VerifiercodeModel::db()->upById($info['id'],array('status'=>3));
//            }
//        }

        //判断
        $data = array('status'=>3);
        VerifiercodeModel::db()->update($data," addr = '$addr' and type = $type and rule_id = $ruleId and status = 1 limit 20 ");
    }
    //$type:1手机2邮箱
    function checkRule($addr,$ruleId,$type){
        if(!$type){
            return out_pc(3100);
        }

        if(! $this->keyInType($type) ){
            return out_pc(3101);
        }

        if(!$addr){
            return out_pc(3102);
        }

        if(!$ruleId){
            return out_pc(8005);
        }

        if($type == self::TypeCellphone){
            if(!FilterLib::regex($addr,"phone")){
                return out_pc(8119);
            }
            $rule = SmsRuleModel::db()->getById($ruleId);
        }else{
            if(!FilterLib::regex($addr,"email")){
                return out_pc(8120);
            }
            $rule = EmailRuleModel::db()->getById($ruleId);
        }

        if(!$rule){
            return out_pc(1001);
        }

//        $check = $this->check($type,$addr,$rule);
//        if($check['code']!=200){
//            return $check;
//        }

        $content = $rule['content'];
        if(!$content){
            return out_pc(5008);
        }

        return out_pc(200,$rule);
    }

    function replaceContent($content , $replaceInfo = 0){
        $value = null;
        //替换动态内容
        if($replaceInfo){
            foreach($replaceInfo as $k => $v){
                $content = str_replace($k,$v,$content);
            }
        }
        return $content;
    }
    //$type:1手机2邮箱
    function send($type,$addr,$ruleId,$replace_content = "",$uid = 0){
        $checkRuleRs = $this->checkRule($addr,$ruleId,$type);
        if($checkRuleRs['code'] != 200){
            return out_pc($checkRuleRs['code'],$checkRuleRs['msg']);
        }

        $ruleInfo = $checkRuleRs['msg'];
//        $sendContent = $this->replaceContent($ruleInfo['content'],$replace_content);
        $sendContent = $replace_content;

        $this->checkHasSendAndUpExpire($addr,$type,$ruleId);

        $channel = SmsLogModel::CHANNEL_ALI;
        $out_no = get_rand_uniq_str(14);
        $data = array(
            'rule_id' => $ruleId,
            'uid' => $uid,
            'content' =>json_encode($sendContent),
            'status' => SmsLogModel::STATUS_SENDING,//待发送
            'a_time' => time(),
            'ip' => get_client_ip(),
            'type'=>0,//保留字段
            'title'=>$ruleInfo['title'],
            'channel'=>$channel,
            'out_no'=>$out_no,
        );
        $templateId = $ruleInfo['third_template_id'];
        if($type == self::TypeCellphone){
            $data['cellphone'] = $addr;
            $newDbIncId = SmsLogModel::db()->add($data);
        }else{
//            $data['email'] = $addr;
//            $id = EmailLogModel::db()->add($data);
        }

        if($type == self::TypeCellphone){
            $realSendSmsRuntimeBackInfo = $this->realSendSmsRuntime($channel,$templateId,$addr,$sendContent,$out_no);
        }else{
//            $email = new EmailLib();
//            $rs = $email->realSend($addr,$rs['msg']['title'],$rs['msg']['content']);
        }

//        var_dump($realSendSmsRuntimeBackInfo);

        $upData = array(
            'thrid_back_info'=>json_encode($realSendSmsRuntimeBackInfo['third_back_info']),
            'u_time'=>time(),
        );
        if( $realSendSmsRuntimeBackInfo['code'] == 200 ){
            $upData['status'] = SmsLogModel::STATUS_OK;
            $code = 200;
        }else{
            $code = 3200;
            $upData['status'] = SmsLogModel::STATUS_FAILED;
        }

        SmsLogModel::db()->upById($newDbIncId,$upData);

        $returnData = array('code'=>$code,'msg'=>json_encode($realSendSmsRuntimeBackInfo['third_back_info']) );
        return $returnData;
    }
    //真的发送，且是实时的
    function realSendSmsRuntime($channel,$templateId,$cellphoneNumber ,$content,$outNo = 0){
        $code = 500;
        if($channel == SmsLogModel::CHANNEL_ALI){
            $aliSms = new AliSmsLib();
            $backInfo = $aliSms->SendSms($cellphoneNumber,$templateId,$content,$outNo);
            if( $backInfo['back_code'] == 200){
                $code = 200;
            }
        }elseif($channel == SmsLogModel::CHANNEL_TENCENT){
//            $sms = new TencentSMSLib();
//            $rs = $sms->sendWithParam($addr,$this->code,$templateId);
        }else{
//            $sms = new SmsLib();
//            $rs = $sms->realSend($addr,$rs['msg']['send_content']);
        }
        return array('code'=>$code,'third_back_info'=>($backInfo));
    }

    function authCode($type,$addr,$code,$ruleId){
        if($code == 123321){//用于测试，万能验证码
            LogLib::inc()->debug(" sms code test pass , 123321:$type , $addr , $code , $ruleId ");
            return out_pc(200);
        }
        if(!$type){
            return out_pc(8004);
        }

        if(!$this->keyInType($type)){
            return out_pc(8103);
        }

        if(!$ruleId){
            return out_pc(8005);
        }

        if(!$addr){
            return out_pc(8015);
        }
//        echo " addr = '$addr' and rule_id = $ruleId and status = 1";

        $info = VerifiercodeModel::db()->getRow(" addr = '$addr' and rule_id = $ruleId and status = 1");
//        LogLib::appWriteFileHash($info);
        if(!$info){
            return out_pc(1004);
        }

        if($info['expire_time'] < time()){//已失效
            VerifiercodeModel::db()->upById($info['id'],array('status'=>3));
            return out_pc(1005);
        }
        if(!$code){
            return out_pc(8014);
        }

        if($info['code'] != $code){
            return out_pc(8110);
        }

        VerifiercodeModel::db()->upById($info['id'],array('status'=>2));

         return out_pc(200);
    }

    function check($type,$addr,$rule){
        //检查多少秒内，允许发送几次
        if($rule['period_times'] && $rule['period']){
            if($type == self::TypeCellphone){
                $times = SmsLogModel::getPeriodTimes($addr,$rule['id'],$rule['period']);
            }else{
                $times = EmailLogModel::getPeriodTimes($addr,$rule['id'],$rule['period']);
            }

            if($times){
                if( $times >= $rule['period_times']){
                    return out_pc(5006,$rule['period']."秒内只允许发送{$rule['period_times']}次");
                }
            }

        }
        //检查一天可发送次数
        if($rule['day_times']){
            if($type == self::TypeCellphone){
                $times = SmsLogModel::getDayMobileSendTimes($addr,$rule['id']);
            }else{
                $times = EmailLogModel::getDayMobileSendTimes($addr,$rule['id']);
            }

            if($times >= $rule['day_times']){
                return out_pc(5007,"一天之内最多允许发送{$rule['day_times']}次");
            }
        }

        return out_pc(200);

    }
}