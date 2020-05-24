<?php
//验证码 操作类
class VerifierCodeLib{
    const TypeCellphone = 1;
    const TypeEmail = 2;

    function getTypeDesc(){
        return array(self::TypeCellphone=>'手机',self::TypeEmail=>'邮箱');
    }

    function keyInType($key){
        return in_array($key,array_flip($this->getTypeDesc()));
    }

    //$type:1手机2邮箱
    function authRule($addr,$ruleId,$type,$replaceInfo = null){
        if(!$type){
            return out_pc(8004);
        }

        if(!$this->keyInType($type)){
            return out_pc(8210);

        }

        if(!$addr){
            return out_pc(8015);
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

        $check = $this->check($type,$addr,$rule);
        if($check['code']!=200){
            return $check;
        }

        //判断之前有没有发送过，如果有，且没有失效的，要置一下状态位
//        $info = VerifiercodeModel::db()->getRow(" addr = '$addr' and rule_id = $ruleId and status = 1");
//        if($info){
//            if($info['expire_time'] > time()){//之前有发送过的，但是已失效
//                VerifiercodeModel::db()->upById($info['id'],array('status'=>3));
//            }
//        }


        $content = $rule['content'];
        if(!$content){
            return out_pc(5008);
        }
        $rule['send_content'] = "";
        //替换动态内容
        if($replaceInfo){
            foreach($replaceInfo as $k => $v){
                $content = str_replace($k,$v,$content);
            }
            $rule['send_content'] = $content;
        }
        return out_pc(200,$rule);
    }

    function sendCode($type,$addr,$ruleId,$uid = 0){
        if(!$type){
            return out_pc(8004);
        }

        if(!$this->keyInType($type)){
            return out_pc(8103);
        }

        if(!$ruleId){
            return out_pc(8005);

        }

        $replace_content = null;
        $code = rand(100000,999999);
        $replace_content = array("#code#"=>$code);
        $this->code = $code;

        $data = array('status'=>3);
        VerifiercodeModel::db()->update($data," addr = '$addr' and type = $type and rule_id = $ruleId and status = 1 limit 20 ");

        $rs = $this->send($type,$addr,$ruleId,$replace_content);
        if($rs['code']!= 200){
            return $rs;
        }

        $data = [
            'code' => $code,
            'expire_time' => time() + 300,
            'a_time' =>  time(),
            'status' => 0,
            'rule_id' => $ruleId,
            'type'=>$type,
            'addr'=>$addr,
            'status'=>1,
            'uid' => $uid,
        ];

        $id = VerifiercodeModel::db()->add($data);

        return out_pc(200,$id);
    }

    //$type:1手机2邮箱
    function send($type,$addr,$ruleId,$replace_content = "",$uid = 0){
        $rs = $this->authRule($addr,$ruleId,$type,$replace_content);
        if($rs['code'] != 200){
            return $rs;
        }

        $data = array(
            'rule_id' => $ruleId,
            'uid' => $uid,
            'content' => $rs['msg']['send_content'],
            'status' => 1,//待发送
            'a_time' => time(),
            'ip' => get_client_ip(),
            'err_info' => "",
            'title'=> $rs['msg']['title'],
        );

        if($type == self::TypeCellphone){
            $data['cellphone'] = $addr;
            $id = SmsLogModel::db()->add($data);
        }else{
            $data['email'] = $addr;
            $id = EmailLogModel::db()->add($data);
        }

        if($type == self::TypeCellphone){
//            $sms = new SmsLib();
//            $rs = $sms->realSend($addr,$rs['msg']['send_content']);
            $templateId = SmsRuleModel::db()->getById($ruleId)['third_id'];
            $sms = new TencentSMSLib();
            $rs = $sms->sendWithParam($addr,$this->code,$templateId);
            $rs['code'] = 200;
            $rs['msg'] = "ok";
//            var_dump($rs);exit;
        }else{
//            $email = new EmailLib();
//            $rs = $email->realSend($addr,$rs['msg']['title'],$rs['msg']['content']);
        }

        return out_pc($rs['code'],$rs['msg']);

    }

    function authCode($type,$addr,$code,$ruleId){
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
//        return out_pc(200);

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