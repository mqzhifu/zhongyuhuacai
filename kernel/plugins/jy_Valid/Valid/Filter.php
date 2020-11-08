<?php
namespace Jy\Common\Valid\Valid;
use Jy\Common\Valid\Contract\FilterInterface;
//各种变量的过滤
class Filter implements FilterInterface {
    private $_debug = 1;
    private $_delimiter = ":";
    private $_rangeDelimit = ",";

    private $_validate = array(
			'require'=> '/.+/',//不能为空,必须至少有一个字符(注：不严谨，处理整形。另外处理不了float类型)
			'email' => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
			'url' => '/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/',
			'currency' => '/^\d+(\.\d+)?$/',//货币
			'number' => '/^\d+$/',//非负整数（正整数 + 0）
			'int'=>"/^[0-9]*[1-9][0-9]*$/",//正整数
			'zip' => '/^[1-9]\d{5}$/',//邮编
			'chinese'=>"/^[\x7f-\xff]+$/",//是否为汉字
			'integer' => '/^[-\+]?\d+$/',//带正负号的数字
			'float' => '/^[-\+]?\d+(\.\d+)?$/',//正正负号的数字，包括小数点
			'english' => '/^[A-Za-z]+$/',//英语大小写字母
			'phone'=> "/^1[3,5,6,7,8]\d{9}$/",//手机
			'telphone'=>"/^(0[0-9]{2,3}\-)?([2-9][0-9]{6,7})+(\-[0-9]{1,4})?$/",//电话号码
			'tel'=>'/^((0\d{2,3})-)(\d{7,8})(-(\d{3,}))?$/',//电话
			'date'=>"/^(1|2)[0-9]{3}-[0-9]{2}-[0-9]{2}$/",//2012-12-12
			'time'=>"/^[0-5]{1}[0-9]{1}:[0-5]{1}[0-9]{1}:[0-5]{1}[0-9]{1}$/",
			'datetime'=>"/^(1|2)[0-9]{3}-[0-9]{2}-[0-9]{2} [0-5]{1}[0-9]{1}:[0-5]{1}[0-9]{1}:[0-5]{1}[0-9]{1}$/",
			'dateformat'=>"/^(1|2)[0-9]{3}[0-9]{2}[0-9]{2}$/",//20121229
			"uname"=>'/^[a-zA-z](\w+)$/',//以字母开头，数字与下划线组成
			'ip'=>'/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/',
			'qq'=>'/^[1-9]\d{4,9}$/',
			'uid'=>'/\w{12}/',
            'md5'=>'/^[a-z0-9]{32}$/',
	);
    //提现出错信息
    private $_validateDesc = array(
        'require'=>'必填写',
        'email'=>'邮箱格式错误',
        'url'=>'url地址有误',
        'number'=>'0+正整数',
        'int'=>'正整数(不包括0)',
        'zip'=>'邮编格式有误',
        'float'=>'双精度',
        'integer'=>'正负-整数',
        'string'=>'字符串',
        'bool'=>'布尔逻辑',
        'phone'=>'手机号格式错误',


        'numberMin'=>'（数值）不应该小于 :min',
        'numberMax'=>'（数值）不应该大于 :max',
        'numberRange'=>'（数值）范围 :start , :end',
        'lengthMin'=>'长度 不应该小于 :min',
        'lengthMax'=>'长度 不应该大于 :max',
        'lengthRange'=>'长度 范围 :start , :end',

    );

    public function out($info){
        if($this->_debug ){
            $info .= "\n";
            $this->_traceInfo .= $info;
            if($this->_debug == 2)
                echo $info;
        }
    }

	public function setMessage($message){
        foreach ($message as $k=>$v) {
            foreach ($this->_validateDesc as $k2=>$v2) {
                if($k == $k2){
                    $this->_validateDesc[$k2] = $v;
                    break;
                }
            }
        }
    }

    public function setDelimiter($delimiter){
        $this->_delimiter = $delimiter;
    }

    public function setRangeDelimit($rangeDelimiter){
        $this->_rangeDelimit = $rangeDelimiter;
    }

    public function setDebug($debug){
	    $this->_debug = $debug;
    }

    public function match($value,$rule){
        if($rule == "string"){
            return is_string($value);
        }elseif($rule == 'bool'){
            return is_bool($value);
        }elseif($rule == "object"){
            return is_object($value);
        }
        else{
            if(strpos($rule,$this->_delimiter)){
                return $this->matchLength($value,$rule);
            }
            foreach ($this->_validate as $k=>$v) {
                if($k == $rule){
                    //对象类型，会引起preg_match函数第2个参数报错，做个容错
                    if($rule == 'require'  ){
                        if( is_object($value) && $value){
                            return 1;
                        }elseif(is_array($value) && $value){
                            return 1;
                        }elseif(is_bool($value)){
                            return 1;
                        }

                    }
                    return preg_match($v,$value)===1;
                }
            }
            $this->throwException("func pg:rule is err.".$value.json_encode($rule));
        }
    }

    public function matchLength($value,$rule){
        $ruleLimit = explode($this->_delimiter,$rule);
        switch ($ruleLimit[0]){
            case "numberMin":
                return $this->length("number",$value,$ruleLimit[1],"");
            case "numberMax":
                return $this->length("number",$value,"",$ruleLimit[1]);
            case "numberRange":
                $range = explode($this->_rangeDelimit,$ruleLimit[1]);
                return $this->length("number",$value,$range[0],$range[1]);
            case "lengthMin":
                return $this->length("length",$value,$ruleLimit[1],"");
            case "lengthMax":
                return $this->length("length",$value,"",$ruleLimit[1]);
            case "lengthRange":
                $range = explode($this->_rangeDelimit,$ruleLimit[1]);
                return $this->length("length",$value,$range[0],$range[1]);
            default:
                $this->throwException("minMaxLength func :case string is err");
        }
    }

    public function getMessage($rule){
        if(strpos($rule,$this->_delimiter)){
            $ruleLimit = explode($this->_delimiter,$rule);
            switch ($ruleLimit[0]){
                case "numberMin":
                    $msg = explode($this->_delimiter,$rule);
                    $msg =  str_replace(":min",$msg[1],$this->_validateDesc['numberMin']) ;
                    return $msg;
                case "numberMax":
                    $msg = explode($this->_delimiter,$rule);
                    $msg =  str_replace(":max",$msg[1],$this->_validateDesc['numberMax']) ;
                    return $msg;
                case "numberRange":
                    $msg = explode($this->_delimiter,$rule);
                    $msg =  str_replace(":start",$msg[1],$this->_validateDesc['numberRange']) ;
                    $msg =  str_replace(":end",$msg[2],$msg) ;
                    return $msg;
                case "lengthMin":
                    $msg = explode($this->_delimiter,$rule);
                    $msg =  str_replace(":min",$msg[1],$this->_validateDesc['lengthMin']) ;
                    return $msg;
                case "lengthMax":
                    $msg = explode($this->_delimiter,$rule);
                    $msg =  str_replace(":max",$msg[1],$this->_validateDesc['lengthMax']) ;
                    return $msg;
                case "lengthRange":
                    $delimit = explode($this->_delimiter,$rule);
                    $msg =  str_replace(":start",$delimit[0],$this->_validateDesc['lengthRange']) ;
                    $msg =  str_replace(":end",$delimit[1],$msg) ;
                    return $msg;
                default:
                    $this->throwException("getMessage func :case string is err");
            }
        }
        return $this->_validateDesc[$rule];
    }

    function length($type,$value,$min = 0,$max = 0 ){
        if(!$max && !$min)
            $this->throwException("内容长度为：0");

        if($type == 'length'){
            $length = mb_strlen($value,"UTF8");
        }else{
            $length = $value;
        }

        if($max && $min){
//            var_dump($max);var_dump($min);
            if($length >= $min && $length <=$max){
                return 1;
            }
            return 0;
        }

        if($max){
            if($length > $max )
                return 0;
            return 1;
        }else{
            if($length < $min )
                return 0;
            return 1;
        }
    }
    //统一抛异常
    function throwException($info){
        throw new \Exception($info);
    }

/*
	公民身份号码是由：17位数字码和1位校验码组成。
	6位地址码，8位出生日期码，3位顺序码和1位校验码。
	顺序码表示在同一地址码所标识的区域范围内，对同年同月同日出生的人编定的顺序号，顺序码的奇数分配给男性，偶数分配给女性。
	身份证最后一位校验码算法如下：
	1. 将身份证号码前17位数分别乘以不同的系数，从第1位到第17位的系数分别为：7 9 10 5 8 4 2 1 6 3 7 9 10 5 8 4 2
	2. 将得到的17个乘积相加。
	3. 将相加后的和除以11并得到余数。
	4. 余数可能为0 1 2 3 4 5 6 7 8 9 10这些个数字，其对应的身份证最后一位校验码为1 0 X 9 8 7 6 5 4 3 2。
	*/
	 function idAuth($number , $date = '' , $sex = ''){
		$rs['number'] = $number;
// 		if( 15 == strlen($number) ){
// 			$number = self::idcard_15to18($number);
// 		}
		
		if( 18 != strlen($number)  ){
			return array( 'error' => 1 , 'data'=> '身份证号为:18位(如最后一位为X,就写X)' );
		}
		$lastNum = substr($number, -1);
// 		if($lastNum == 'x' || $lastNum == 'X'){
			
// 		}
// 		6位地址码,先取前2位,判断省份
		$aCity=array(
					11=>"北京",12=>"天津",13=>"河北",14=>"山西",15=>"内蒙古",21=>"辽宁",22=>"吉林",23=>"黑龙江",31=>"上海",32=>"江苏",
					33=>"浙江",34=>"安徽",35=>"福建",36=>"江西",37=>"山东",41=>"河南",42=>"湖北",43=>"湖南",44=>"广东",45=>"广西",46=>"海南",
					50=>"重庆",51=>"四川",52=>"贵州",53=>"云南",54=>"西藏",61=>"陕西",62=>"甘肃",63=>"青海",64=>"宁夏",65=>"新疆",71=>"台湾",
					81=>"香港",82=>"澳门",91=>"国外");
		$twnNum = substr($number,0,2);
		$f= 0;
		foreach($aCity as $k=>$v){
			if($k == $twnNum){
				$f= 1;
			}
		}
		if(!$f){
			return array( 'error' => 1 , 'data'=> '省份所对应的ID错误' );
		}
		$rs['province'] = $aCity[$twnNum];
		//判断出生日期
		$date = substr($number,6,8);
		$rs['date'] = $date;
		if( ! self::regex($date, 'dateFormat') ){
			return array( 'error' => 1 , 'data'=> '日期格式错误' );
		}
		//性别
		$sex = substr($number,16,1);
		if($sex % 2 == 0)$rs['sex'] = "女";
		else $rs['sex'] = "男";
		//验证第18位
		//将身份证号码前17位数分别乘以不同的系数
		$code = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
		//将得到的17个乘积相加。将相加后的和除以11并得到余数,如下 
		$szVerCode =array('1','0','X','9','8','7','6','5','4','3','2');
		$total = 0;
		for ($i = 0;$i < 17;$i++) {
			$total += $number[$i] * $code[$i];
		}
		if($szVerCode[$total % 11] != $lastNum ){
			return array( 'error' => 1 , 'data'=> '第18位验证码错误' );
		}
		// 		6位地址码
		$data = file(KERNEL_PATH."class/ID6number.txt");
		$addrNumber = substr($number,0,6);
		$f = 0 ;
		foreach($data as $k=>$v){
			$tmp = explode(' ', $v);
			if($addrNumber == $tmp[0]){
				$f = 1;
				$rs['city'] = $tmp[1];
				break;
			}
		}

		if(!$f)return array( 'error' => 1 , 'data'=> '省份详细信息错误' );
		return array( 'error' => 0 , 'data'=> $rs );
	}
	
// 	将15位身份证升级到18位
	 function idcard_15to18($idcard){
		// 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
		if (array_search(substr($idcard, 12, 3), array('996', '997', '998', '999')) !== false){
			$idcard = substr($idcard, 0, 6) . '18'. substr($idcard, 6, 9);
		}else{
			$idcard = substr($idcard, 0, 6) . '19'. substr($idcard, 6, 9);
		}
// 	　	return $idcard;
	}


}




?>