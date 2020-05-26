<?php
/**
 * @author jiaheng.wu@uthing.cn
 * @package 短信接口类
 * Time: 下午6:28
 */
class Sms {
	
	//基本配置信息，请勿修改
    protected $_serialno = '3SDK-EMY-0130-JFQOT';
    protected $_pwd = '200932';
    protected $_key = 'ut6453';
    protected $_type = 'soap';

    protected $gwUrl = 'http://sdkhttp.eucp.b2m.cn/sdk/SDKService?wsdl';
    protected $httpUrl = 'http://sdkhttp.eucp.b2m.cn/sdkproxy/sendsms.action';
    protected $connectTimeOut = 2;
    protected $readTimeOut = 10;
    protected $proxyhost = false;
    protected $proxyport = false;
    protected $proxyusername = false;
    protected $proxypassword = false;
    
    protected $charset = 'GBK';
    protected $priority = 5;
    
    protected $_client = null;
    protected $_log = null;	//日志文件地址

	public $_max_num = 50;//允许一个IP一次最多可发短信条数

	public $_delimiter = ";:";

    const MAX_MOBILES = 200;	//最多发送号码
    
    public static $_static = false;
    
    private $_pre = '【游心旅行】';
//    private $_pre = '';

//    private $_saleAccountHUAXIN = 'ACYX0087';       //华信营销账号  旧
//    private $_salePasswordHUAXIN = 'ACYX008765';    //华信营销密码  旧
//    private $_saleUidHUAXIN = '10513';              //华信营销uid  旧

    private $_saleAccountHUAXIN = 'acyx0087';     //华信营销账号  新
    private $_salePasswordHUAXIN = '352253';      //华信营销密码  新
    private $_saleUidHUAXIN = '10513';            //华信营销uid  新

    private $_sms_type = array('industry'=>1,'sale'=>2);       //短信类型

    /**
     * 设置短信签名
     * @param string $preStr
     */
    public function setPre($preStr){
        $this->_pre = $preStr ;
    }

    public function __construct($type="http") {
        $this->_log = LOG_PATH."/sms";
        $this->_type = $type;
    }
    
    public static function inst($type="http") {
    	if(false == self::$_static) {
    		self::$_static = new self($type);
    	}
    	return self::$_static;
    }
    
    /**
     * 发送短信息
     * @param string|array $mobiles
     * @param string $msg
     * @return boolean 
     */
    public function send($mobiles, $msg , $type = 'all') {
        $log = new Sys_Log();
        $log->append("sendsms", date("Y-m-d H:i:s") . PHP_EOL . var_export(array('mobiles'=>$mobiles, 'msg'=>$msg), 1) . PHP_EOL . PHP_EOL);

        if (!$this->check()) return array('status' => false);
    	if (!$mobiles = $this->filterNums($mobiles)) return array('status' => false);

		if(!is_array($mobiles))
			$mobiles = array($mobiles);
		//去重复
		array_unique($mobiles);
		if(count($mobiles) > $this->_max_num){
            $log->append("sendsms", date("Y-m-d H:i:s") . PHP_EOL . var_export(array('errmsg'=>' cnt one ip max '.$this->_max_num), 1) . PHP_EOL . PHP_EOL);
			echo "err:cnt one ip number more {$this->_max_num}.... \n";
			return array('err'=>1,'msg'=>' cnt one ip max '.$this->_max_num);
		}

		foreach($mobiles as $k=>$phone){
			//这个值要参与去重，所以不能无用的数据
			$queue_content = getIP().$this->_delimiter.$phone .$this->_delimiter .$type ;
			//往队列头尾部插入
			$queue_num = Sys_Redis::inst()->push(get_SMS_redis_key(),$queue_content);
//				$len = Sys_Redis::inst()->llen(get_SMS_redis_key());
			if(!$queue_num){
                $log->append("sendsms", date("Y-m-d H:i:s") . PHP_EOL . var_export(array('errmsg'=>"k:$k,phone:$phone".'set redis queue fail'), 1) . PHP_EOL . PHP_EOL);
                return array('status'=>1,'msg'=>"k:$k,phone:$phone".'set redis queue fail');
            }


			$rs  = Sys_Redis::inst()->set(get_SMS_redis_msg_key().$queue_content,$msg.$this->_delimiter.time().$this->_delimiter.$type);
		}
		return $arr = array('status'=>1);

    }

	function realSend(){

		$queue = Sys_Redis::inst()->lrange(get_SMS_redis_key(),0,-1);
		if(!$queue)
			return 0;
		Sys_Redis::inst()->delete(get_SMS_redis_key());			//删除key
		$cnt = count($queue);
		echo "start_cnt:$cnt\n";
        if($cnt > 300){
            echo "warning:sms attacked!~";
            $email = new Email();
            $email->SetFrom("admin@uthing.cn","游心旅行");
            $email->sendMail(array('78878296@qq.com','xiaoz'),'sms被刷','sms已被刷，请赶快处理,cnt:$cnt',array("admin@uthing.com","uthing"));

            return 0;
        }

//        echo " start out...\n";
//        foreach($queue as $k=>$v){
//            var_dump($v);
//            echo "\n";
//        }
//
//        echo " end out...\n";


		//去掉重复
		$queue = array_unique($queue);
        echo "array_unique_cnt:".count($queue)."\n";
		$ori_queue = $queue;
		//统计一个IP发送了多少量
		sort($queue);
        echo "sort_cnt:".count($queue)."\n";
		$final = array();
		if($cnt > 1){
			for($i=0;$i<count($queue) - 1;$i++){
				$member_info = explode($this->_delimiter,$queue[$i]);
				$ip = $member_info[0];


				$next_member_info = explode($this->_delimiter,$queue[$i+1]);
				$next_ip = $next_member_info[0];

				$key = $ip;
				if( $ip == $next_ip){
					if(!isset($final[$key])){
						$final[$key] = 1;
					}
					$final[$key] ++;
				}
			}
		}


		$black_ip = array();
		foreach($final as $k=>$v){
			if($v > $this->_max_num){
				$black_ip[] = $k;
			}
		}
        echo "ori_queue_cnt:".count($ori_queue)."\n";
		foreach($ori_queue as $k=>$v){
			echo $k.":";
			$ip_phone = explode($this->_delimiter,$v);
			$ip = $ip_phone[0];
			if(!in_array($ip,$black_ip)){
				echo  $ip."-".$ip_phone[1]."-".$ip_phone[2].",";
				$key = get_SMS_redis_msg_key().$v;
				$msg = Sys_Redis::inst()->get($key);
				$msg_add_time = explode(";:",$msg);

				//释放redis
				Sys_Redis::inst()->delete($key);

				$data =  array(
					'phone'=>$ip_phone[1],
					'ip'=>$ip_phone[0],
					'addtime'=>time(),
					'type'=>$msg_add_time[2],
					'content'=>$msg_add_time[0],
					'send_addtime'=>$msg_add_time[1],
				);
				Table_SMS_log::inst()->addOne($data);
				//只能正式环境才发送短信-测试自己去log_newut.sms_log表里去查
				if(PLATFORM_IS_PRODUCT){
                    if(1){
//                    if( 'findps' == $msg_add_time[2] ||  'bindtel' == $msg_add_time[2] ||  'inform_steward' == $msg_add_time[2] || 'inform_user' == $msg_add_time[2] || 'reg' == $msg_add_time[2] || 'sub_order' == $msg_add_time[2]
//                        || 'mod_bindtel' == $msg_add_time[2]
//                        || 'ub_optonspwd' == $msg_add_time[2]
//                        || 'ub_findps' == $msg_add_time[2]
//                        || 'all' == $msg_add_time[2]
//                        || 'admin_ub_success' == $msg_add_time[2]
//                        || 'again_confirm_resource' == $msg_add_time[2]
//                        || 'again_confirm' == $msg_add_time[2]
//                        || 'tourdiy_invite' == $msg_add_time[2]
//                        || 'counsel' == $msg_add_time[2]
//                        || 'ub_pay_win' == $msg_add_time[2]
//                        || 'steward_apply' == $msg_add_time[2]
//                        || 'ub_reset_win' == $msg_add_time[2]
//                        || 'ub_options_win' == $msg_add_time[2]
//                        || 'alter_price' == $msg_add_time[2]
//                        || 'no_resource' == $msg_add_time[2]
//                        || 'own_resource' == $msg_add_time[2]
//                        || 'cmz_private' == $msg_add_time[2]
//                        || 'cmz_firm' == $msg_add_time[2]
//                        || 'hongbao_coupon' == $msg_add_time[2]
//                        || 'less_ub_succ' == $msg_add_time[2]
//                        || 'counsel' == $msg_add_time[2]
//                    ){
                        $send_status = $this->newSendSms($ip_phone[1],$msg_add_time[0]);
                        if($send_status != 200){
                            echo "new sms :exception_fail...".$send_status;
                            $return = array('status' => false, 'error' => '500');
                        }
                    }else{
                        try {
                            $rs = null;
                            if ($this->_type == 'http') {
                                echo "http:";
                                $rs = $this->httpSend(array($ip_phone[1]), $msg_add_time[0]);
                            } elseif ($this->_type == 'soap') {
                                echo "soap:";
                                $rs = $this->soapSend(array($ip_phone[1]), $msg_add_time[0]);
                            }
                            if ($rs['error'] != '0') {
                                echo "err:".Sms_Code::$_errors[$rs['error']]."-".$rs['error'];
                                throw new Exception(Sms_Code::$_errors[$rs['error']], $rs['error']);
                            }else{
                                echo "ok";
                            }
                            $return = array('status' => true);
                        } catch (Exception $ex) {
                            echo "exception_fail...";
                            $return = array('status' => false, 'error' => $ex);
                        }
                    }
				}else{
					echo " in test send...\n";
					$return = array('status' => true);
				}

			}else{
				echo " in black_ip<br/>";
				$return = array('status' => false, 'error' => 'in_black');
			}
			echo "\n";
		}

		return $return;
	}
    
    /**
     * HTTP方式发送
     * @param array $mobiles
     * @param string $msg
     */
    public function httpSend($mobiles, $msg) {

        //$msg = $this->_pre.$msg;
    	$rs = '';
    	while (true) {
    		if (!$mobiles) break;
    		$nums = array_splice($mobiles, 0, self::MAX_MOBILES);

	    	$params = array(
	    		'cdkey'	=> $this->_serialno,
	    		'password' => $this->_key,
	    		'phone'	=> join(',', $nums),
	    		'message' => $msg		
	    	);

	    	$url = $this->httpUrl . '?' . http_build_query($params);
	    	$ch = curl_init($url);
	    	curl_setopt($ch, CURLOPT_HTTPGET, true);
	    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    	curl_setopt($ch, CURLOPT_HEADER, false);
	    	$rs = curl_exec($ch);
	    	$info = curl_getinfo($ch);
	    	curl_close($ch);
	    	$this->writeLog(array_merge(array('return' => $rs,'info' => $info, 'u' => php_uname('u')), $params));
            
    	}
    	return $this->parseReturnData($rs);
    }
	//新的SMS供应商，创世华信
//	public function httpSendHUAXIN($mobiles, $msg) {
//
//		$url = "http://125.208.3.91:8888/sms.aspx";
//		//$url = "http://125.208.3.91:8888/smsGBK.aspx";  GBK字符集
//		$msg = $this->_pre.$msg;
//		$rs = '';
//		while (true) {
//			if (!$mobiles) break;
//			$nums = array_splice($mobiles, 0, self::MAX_MOBILES);
//
//			$params = array(
//				'userid'	=> $this->_serialno,
//				'password' => $this->_key,
//				'phone'	=> join(',', $nums),
//				'message' => $msg
//			);
//
//			$url = $this->httpUrl . '?' . http_build_query($params);
//			$ch = curl_init($url);
//			curl_setopt($ch, CURLOPT_HTTPGET, true);
//			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//			curl_setopt($ch, CURLOPT_HEADER, false);
//			$rs = curl_exec($ch);
//			$info = curl_getinfo($ch);
//			curl_close($ch);
//			$this->writeLog(array_merge(array('return' => $rs,'info' => $info, 'u' => php_uname('u')), $params));
//
//		}
//		return $this->parseReturnData($rs);
//	}

    /**
     * 华信平台行业短信发送
     * @param $mobiles      被叫号码
     * @param $msg          发送内容
     * @param $url          请求地址
     * @param $account      请求账号
     * @param $pwd          请求密码
     * @param string $uid   请求uid
     * @param string $returnType    数据返回格式xml json
     * @param bool $type        短信状态  false 行业短信  true 营销短信
     * @return Ambigous|array|mixed|object
     */
    public function newHttpSendHUAXIN($mobiles, $msg, $url, $account, $pwd, $uid='', $returnType='xml', $type, $charset='utf8'){

        $msg = $this->_pre.$msg;

        //如果为营销短信就拼接退订提示
        if($type){
            $msg = $msg."回T退订";
        }
        while (true) {
            if (!$mobiles) break;
            $nums = array_splice($mobiles, 0, self::MAX_MOBILES);

            $params = array(
                'action'    => 'send',
                'userid'	=> $uid,
                'account'   => $account,
                'password'  => $pwd,
                'mobile'	=> join(',', $nums),
                'content'   => $msg
            );
//            $url = $url . '?' . http_build_query($params);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_POST, true);


            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

            $rs = curl_exec($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            $this->writeLog(array_merge(array('return' => $rs,'info' => $info, 'u' => php_uname('u')), $params));
        }

        //判断返回格式
        if($returnType=='xml'){
            $return = $this->parseReturnData($rs);
        }elseif($returnType=='json'){
            $return = json_decode($rs,true);
        }else{
            $return = $rs;
        }

        return $return;
    }


    /**
     * 华信平台行业短信发送【加密】
     * @param $url      请求链接
     * @param $userId   用户id
     * @param $userName 用户名称 供应商提供
     * @param $password 用户密码
     * @param $mobile   被叫手机号
     * @param $text     发送内容
     * @return array|mixed|object
     */

    public function newHttpSendEncryptionHUAXIN($url, $userId, $userName, $password, $mobile, $text){
        //生成认证密文
        $stamp = gmdate('mdHis', time() + 3600 * 8);
        $secret = $password.$stamp;
        $secret = $this->Md5Encrypt($secret);
        //拼接内容签名
        $text = $text.$this->_pre;

        //处理手机号（兼容多个手机号）
        $nums = array_splice($mobile, 0, self::MAX_MOBILES);


        //加密发送内容
        $jsonParam = array(
            'UserName'  =>  $userName,  //用户名
            'Secret'    =>  $secret,    //认证密文
            'Stamp'     =>  $stamp,     //时间戳
            'Moblie'    =>  join(',', $nums),    //手机号
            'Text'      =>  $text,      //发送内容
            'Ext'       =>  '',         //扩展号  （可选项）
            'SendTime'  =>  '',         //定时时间（可选项）
        );

        $jsonString = json_encode($jsonParam);
        $key = $password;
        $key = substr($key,0,8);
        $key = str_pad($key,8,"\0",STR_PAD_RIGHT);
        $data = $this->DESEncrypt($jsonString,$key);
        $text64 = base64_encode($data);


        //日志需要
        $params = array(
            'UserId'    =>  $userId,
            'Text64'    =>  urlencode($text64),
        );

        //定义发送内容
        $params_url = "UserId=".$userId."&Text64=".urlencode($text64);
        $url = $url."?".$params_url;

        //CURL请求
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $rs = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        //写日志
        $this->writeLog(array_merge(array('return' => $rs,'info' => $info, 'u' => php_uname('u')), $params));

        //处理返回的json内容
        $result = json_decode($rs,true);
        return $result;
    }

    /**
     * 返回大写MD5加密结果  华信加密
     * @param $text
     * @return string
     */
    public function Md5Encrypt($text){
        $ret = md5($text);
        return strtoupper($ret);
    }

    /**
     * 华信加密
     * @param $value
     * @param $key
     * @return string
     */
    public function DESEncrypt ($value,$key)
    {
        $iv = $key;
        $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
        $value = $this->PaddingPKCS7($value);
        mcrypt_generic_init($td, $key, $iv);
        $ret = mcrypt_generic($td, $value);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $ret;
    }

    /**
     * 华信加密
     * @param $data
     * @return string
     */
    public function PaddingPKCS7 ($data)
    {
        $block_size = mcrypt_get_block_size('tripledes', 'cbc');
        $padding_char = $block_size - (strlen($data) % $block_size);
        $data .= str_repeat(chr($padding_char), $padding_char);
        return $data;
    }

    /**
     * 新行业短信发送接口
     * @param $mobiles          被叫号码  数组形式 单个 13555555555  多个 $array = array( '13555555555', '13555555555', '13555555555');
     * @param string $msg       发送内容
     * @return int  成功 200  失败 500  退订 501  存在黑词 700
     */
    public function newSendSms($mobiles, $msg='', $type=''){

        //是否加密
        $encryption=0;

        if(!is_array($mobiles)){
            $mobiles = array($mobiles);
        }

        //拼接字符串  用户sms_log 存储
        $phones = '';
        if(is_array($mobiles)){
            $phones = implode(',',$mobiles);
        }
        $time = time();
        $data =  array(
            'phone'     =>  $phones,
            'ip'        =>  getIP(),
            'addtime'   =>  $time,
            'type'      =>  $type,
            'content'   =>  $msg,
            'send_addtime'  =>  $time,
            'send_status'   =>  '0',
            'sms_type'  =>  $this->_sms_type['industry'],
        );

        //检测黑词
        $black_keyword = Table_Blackkeyword::inst()->isHaveBlack($msg);
        if($black_keyword){
            $data['send_status'] = 3;
            Table_SMS_log::inst()->addOne($data);
            return 700;
        }


        //数据返回格式
        $returnType = 'xml';

        if($returnType=='xml'){
            $url = "http://114.113.154.5/sms.aspx";         //行业短信 返回xml
        }elseif($returnType=='json'){
            $url = "http://114.113.154.5/smsJson.aspx";     //行业短信 返回json
        }else{
            return 500;
        }

        //请求信息
        $account = "AC00265";
        $pwd = "AC0026545";
        $uid = "";  //没有调用加密的就不用uid




        //只能正式环境才发送短信-测试自己去log_newut.sms_log表里去查
        if(PLATFORM_IS_PRODUCT){
            //只有行业短信才有加密传输
            if($encryption){
                $url = "http://114.113.154.5/ensms.ashx";       //行业短信 返回json  加密传输
                $uid = '50024';
                //加密传输
                $return = $this->newHttpSendEncryptionHUAXIN($url, $uid, $account, $pwd, $mobiles, $msg);
                if($return['StatusCode']==1){
                    $data['send_status'] = 1;
                    Table_SMS_log::inst()->addOne($data);
                    return 200;
                }else{
                    $data['send_status'] = 2;
                    Table_SMS_log::inst()->addOne($data);
                    return 500;
                }
            }else{
                //非加密传输 可 xml json
                $return = $this->newHttpSendHUAXIN($mobiles, $msg, $url, $account, $pwd, $uid, $returnType ,false);
                if($return['returnstatus']=='Success'){
                    $data['send_status'] = 1;
                    Table_SMS_log::inst()->addOne($data);
                    return 200;
                }else{
                    $data['send_status'] = 2;
                    Table_SMS_log::inst()->addOne($data);
                    return 500;
                }
            }
        }else{
            $data['send_status'] = 1;
            Table_SMS_log::inst()->addOne($data);
            return 200;
        }

    }


    /**
     * 新营销短信发送接口
     * @param $mobiles          被叫号码  数组形式 单个 13555555555  多个 $array = array( '13555555555', '13555555555', '13555555555');
     * @param string $msg       发送内容
     * @return int  成功 200  失败 500  退订 501  存在黑词 700
     */
    public function newSendSaleSms($mobiles, $msg='', $title='', $type=''){
        if(!is_array($mobiles)){
            $mobiles = array($mobiles);
        }

        //拼接字符串  用户sms_log 存储
        $phones = '';
        if(is_array($mobiles)){
            $phones = implode(',',$mobiles);
        }
        $time = time();
        $data =  array(
            'phone'     =>  $phones,
            'ip'        =>  getIP(),
            'addtime'   =>  $time,
            'type'      =>  $type,
            'content'   =>  $msg,
            'send_addtime'  =>  $time,
            'send_status'   =>  '0',
            'sms_type'  =>  $this->_sms_type['sale'],
        );

        //检测黑词
        $black_keyword = Table_Blackkeyword::inst()->isHaveBlack($msg);
        if($black_keyword){
            $data['send_status'] = 3;
            Table_SMS_log::inst()->addOne($data);
            return 700;
        }


        //监控发送最小值
        $minCount = 100;
        $nowCount = count($mobiles);
        if($nowCount<$minCount){
            EDM::inst()->sendAlarmForSmsNumber($title);
        }

        //发送时间监控  未在 08:30 - 18:00 之间
        $nowHour = date("Gi");
        if($nowHour < 830 || $nowHour > 1800){
            EDM::inst()->sendAlarmForSmsTime($title);
        }

        //接口返回数据类型
        $returnType = 'xml';

        if($returnType=='xml'){
            $url = "http://114.113.154.110/sms.aspx";       //新营销短信 返回xml
        }elseif($returnType=='json'){
            $url = "http://114.113.154.110/smsJson.aspx";   //新营销短信 返回json
        }else{
            return 500;
        }

        //请求信息
        $account = $this->_saleAccountHUAXIN;
        $pwd = $this->_salePasswordHUAXIN;
        $uid = $this->_saleUidHUAXIN;



        //只能正式环境才发送短信-测试自己去log_newut.sms_log表里去查
        if(PLATFORM_IS_PRODUCT){
            // xml json
            $return = $this->newHttpSendHUAXIN($mobiles, $msg, $url, $account, $pwd, $uid, $returnType ,true);
            if($return['returnstatus']=='Success'){
                $data['send_status'] = 1;
                Table_SMS_log::inst()->addOne($data);
                return 200;
            }else{
                $data['send_status'] = 2;
                Table_SMS_log::inst()->addOne($data);
                return 500;
            }
        }else{
            $data['send_status'] = 1;
            Table_SMS_log::inst()->addOne($data);
            return 200;
        }
    }

    /**
     * 获取发送失败的手机号
     * @return array|bool
     */
    public function getFailHUAXIN(){

        $uid = $this->_saleUidHUAXIN;
        $account = $this->_saleAccountHUAXIN;
        $password = $this->_salePasswordHUAXIN;


//        $uid = "10513";
//        $account = "acyx0087";
//        $password = "352253";
        $url = "http://114.113.154.110/statusApi.aspx";

        $params = array(
            'action'    => 'query',
            'userid'	=> $uid,
            'account'   => $account,
            'password'  => $password,
        );
        $url = $url . '?' . http_build_query($params);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $rs = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        $this->writeLog(array_merge(array('return' => $rs,'info' => $info, 'u' => php_uname('u')), $params));
        $return = $this->parseReturnData($rs);
        $mobile_array = array();
        if(is_array($return)){
            if(isset($return['callbox'])){          //判断是否有手机号
                foreach ($return['callbox'] as $_k=>$_v) {
                    //兼容一维数组
                    if(is_array($_v)){
                        if($_v['status']==20){  //如果发送失败
                            $mobile_array[$_v['mobile']] = array(
                                'phone'  =>  $_v['mobile'],
                            );
                        }
                    }else{
                        if($return['callbox']['status']==20) {  //如果发送失败
                            $mobile_array[$return['callbox']['mobile']] = array(
                                'phone'  =>  $return['callbox']['mobile'],
                            );
                        }
                        break;
                    }
                }
            }elseif(isset($return['errorstatus'])){ //判断是否报错
                return false;
            }
        }

        return $mobile_array;
    }


    /**
     * 获取取消订阅的手机号
     */
    public function getCancelHUAXIN(){


//        $uid = "10513";
//        $account = "ACYX0087";
//        $password = "ACYX008765";


        $uid = $this->_saleUidHUAXIN;
        $account = $this->_saleAccountHUAXIN;
        $password = $this->_salePasswordHUAXIN;
        $url = "http://114.113.154.110/callApi.aspx";

        $params = array(
            'action'    => 'query',
            'userid'	=> $uid,
            'account'   => $account,
            'password'  => $password,
        );
        $url = $url . '?' . http_build_query($params);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $rs = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        $this->writeLog(array_merge(array('return' => $rs,'info' => $info, 'u' => php_uname('u')), $params));
        $return = $this->parseReturnData($rs);

        $rule = array('T','t');
        $mobile_array = array();
        if(is_array($return)){
            if(isset($return['callbox'])){          //判断是否有手机号
                foreach ($return['callbox'] as $_k=>$_v) {
                    //兼容一维数组
                    if(is_array($_v)){
                        if(in_array($_v['content'],$rule)){
                            $mobile_array[$_v['mobile']] = array(
                                'phone'         =>  $_v['mobile'],
                                'receive_time'  =>  $_v['receivetime'],
                            );
                        }
                    }else{
                        if(in_array($return['callbox']['content'],$rule)){
                            $mobile_array[$return['callbox']['mobile']] = array(
                                'phone'         =>  $return['callbox']['mobile'],
                                'receive_time'  =>  $return['callbox']['receivetime'],
                            );
                        }
                        break;
                    }
                }

            }elseif(isset($return['errorstatus'])){ //判断是否报错
                return false;
            }
        }

        //如果手机号不为空
        if(!empty($mobile_array)){
            //将手机号存储
            $phone_str = 0;
            foreach ($mobile_array as $_k=>$_v){
                if($phone_str==0){
                    $phone_str = $_v['phone'];
                }else{
                    $phone_str .= ",".$_v['phone'];
                }
            }

            //过滤已存手机号
            $phone_info = Table_Sms_Cancel::inst()->autoClearCache()->field('phone')->where(array("phone in($phone_str)"))->select();
            if($phone_info){
                foreach ($phone_info as $_k=>$_v){
                    //如果数据库存在该账号 则销毁相应数组
                    if(isset($mobile_array[$_v['phone']])){
                        unset($mobile_array[$_v['phone']]);
                    }
                }
            }

            //将退订信息存入表中
            if(!empty($mobile_array)){
                foreach ($mobile_array as $_k=>$_v){
                    $add_data = array(
                        'phone'         =>  $_v['phone'],
                        'receive_time'  =>  strtotime($_v['receive_time']),
                    );
                    Table_Sms_Cancel::inst()->autoClearCache()->addData($add_data)->add();
                }
            }
        }

        return true;
    }
    
    /**
     * SOPA方式发送
     * @param array $mobiles
     * @param string $msg
     */
    public function soapSend($mobiles,$msg) {

    	header("Content-Type: text/html; charset=" . $this->charset);
    	include_once dirname(__FILE__) . '/sms/client.class.php';
    	$client = $this->getClient();
    	while (true) {
    		if (!$mobiles) break;
    		$nums = array_splice($mobiles, 0, self::MAX_MOBILES);
    		if (isUtf8($msg)){
                $msg = iconv('UTF-8', $this->charset."//IGNORE", $msg);
            }
    		$rs = $client->sendSMS($nums, $msg, '', '', $this->charset, $this->priority);
    	}
        if($rs != '0'){
            return array('status'=>false,'error'=>$rs);
        }else{
            return array('status'=>true,'error'=>$rs);
        }
    	
    }
    
    /**
     * 过滤电话号码
     * @param string|array $phones
     */
    protected function filterNums($mobiles) {
    	if (!is_array($mobiles)) $mobiles = (array) $mobiles;
    	foreach ($mobiles as $key => $num) {
    		if (!preg_match('/^1\d{10}$/', $num)) {
    			unset($mobiles[$key]);
    		}
    	}
    	return $mobiles;
    }
    
    /**
     * 检查是否可 发信息扩展
     * @return boolean
     */
    protected function check() {
    	return true;
    }

    /**
     * 设置优先级
     * @param int $priority
     * @return Sms
     */
    protected function setPriority($priority) {
    	$this->priority = (int) $priority;
    	return $this;
    }
    
    /**
     * 设置字符集
     * @param string $charset
     * @return Sms
     */
    protected function setCharset($charset) {
    	$this->charset = $charset;
    	return $this;
    }
    
    /**
     * 解析返回数据
     * @param array|xml $data
     * @return Ambigous <string, multitype:, unknown>
     */
    protected function parseReturnData($data, $type = 'xml') {
    	if ($type == 'xml') {
    		return parseXMLString(trim($data));
    	}
    }
    
    /**
     * 返回client
     */
    protected function getClient() {
    	if  (!$this->_client) {
            include_once dirname(__FILE__) . '/sms/client.class.php';
    		$client = new SmsClient($this->gwUrl, $this->_serialno, $this->_pwd, $this->_key,
    				$this->proxyhost, $this->proxyport, $this->proxyusername, $this->proxypassword,
    				$this->connectTimeOut, $this->readTimeOut);
    		$client->setIncomingEncoding($this->charset);
    		$this->_client = $client;
    	}
    	return $this->_client;
    }
    
    /**
     * 更新密码
     * @param string $pwd
     * @return Ambigous
     */
    public function updatePwd($pwd) {
    	if (!preg_match('/^\d{6}$/', $pwd)) return false;
    	$client = $this->getClient();
    	return $client->updatePassword($pwd);
    }
    
    /**
     * 余额查询
     */
    public function getBalance() {
    	$client = $this->getClient();
    	return $client->getBalance();
    }
    
    /**
     * 充值
     * @param string $cardNo
     * @param string $cardPwd
     */
    public function chargeUp($cardNo, $cardPwd) {
    	$client = $this->getClient();
    	return $client->chargeUp($cardNo, $cardPwd);
    }
    
    /**
     * 注册登录
     */
    public function login() {
    	$client = $this->getClient();
    	//使用随机数设置key
    	$sessionKey = $client->generateKey();
        $sessionKey = $this->_key;
        
    	$result = $client->login($sessionKey);
    	if ($result != null && $result == '0') {
    		echo '登录成功！ session key：' . $client->getSessionKey();
    		exit;
    	}
    }
    
    /**
     * 注销登录
     */
    public function logout() {
    	$client = $this->getClient();
    	return $client->logout();
    }
    
    /**
     * 获得版本号
     */
    public function getVersion() {
    	$client = $this->getClient();
    	return $client->getVersion();
    }
    
    /**
     * 接口调用错误
     */
    public function chkError() {
    	$client = $this->getClient();
    	$err = $client->getError();
    	$result = array('status' => true);
    	if ($err) {
    		$result['status'] = false;
    		$result['error'] = new Exception($err);
    	}
    	return $result;
    }
    
    /**
     * 写日志
     * @param array	$data
     */
    private function writeLog($data) {
    	$logDir = $this->_log . '/' . date('Y') . '/' . date('m');
    	$logFile = $logDir . '/' . date('d') . '.log';
    	if (!is_dir($logDir)) {
    		mkdir($logDir, 0777, true);
    	}
    	return @file_put_contents($logFile, "\n======\n" . var_export($data, true) . "\n======\n", FILE_APPEND);
    }
    
    /**
     * 删除短信日志,每月最后一天执行,删除上个月的短信日志
     */
    public function clearLogs($dirName) {
    	$handle = opendir($dirName);
    	if ($handle) {
    		while (false !== ($item = readdir($handle))) {
    			if ($item != "." && $item != "..") {
    				if (is_dir($dirName . DIRECTORY_SEPARATOR . $item)) {
    					$this->clearLogs($dirName . DIRECTORY_SEPARATOR . $item);
    				} else {
    					unlink("$dirName/$item");
    				}
    			}
    		}
    		closedir($handle);
    		rmdir($dirName);
    	}
    }


}
