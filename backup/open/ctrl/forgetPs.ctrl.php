<?php

/**
 * @Author: Kir
 * @Date:   2019-04-18 14:07:37
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-05-24 14:35:20
 */

/**
 * 
 */
class ForgetPsCtrl extends BaseCtrl
{
	
	function index()
	{
        if ($this->isLogin()) {
            $header = 'isLogin';
        } else {
            $header = 'noLogin';
        }
		$this->addJs('assets/open/scripts/img_ver.js');
    	$this->display('retrieveIndex.html', 'new', $header);
	}

	function verify()
	{
        if ($this->isLogin()) {
            $header = 'isLogin';
        } else {
            $header = 'noLogin';
        }
		$this->display('retrieveVerify.html', 'new', $header);
	}

	function reset()
	{
        if ($this->isLogin()) {
            $header = 'isLogin';
        } else {
            $header = 'noLogin';
        }
		$this->display('retrieveReset.html', 'new', $header);
	}

	function success()
	{
        if ($this->isLogin()) {
            $header = 'isLogin';
        } else {
            $header = 'noLogin';
        }
		$this->display('retrieveSuccess.html', 'new', $header);
	}

    public function verifyUser()
    {
    	$phone = _g("phone");
    	$user = UserModel::db()->getRow(" cellphone = $phone");

    	if (!$user) {
    		$this->outputJson(0, "没有查询到该用户信息，请重新输入手机号");
    	}

    	$this->outputJson(200, "验证通过");
    }

    public function sendSms()
    {
    	$phone = _g("phone");

    	$class = new VerifierCodeLib();
        $rs = $class->sendCode(VerifierCodeLib::TypeCellphone, $phone, SmsRuleModel::$_type_findPs);

        $this->outputJson($rs['code'], $rs['msg']);
    }

    public function authCode()
    {
    	$phone = _g("phone");
    	$code = _g("code");

		$VerifierCodeClass = new VerifierCodeLib();
		$VerifierCode = $VerifierCodeClass->authCode(VerifierCodeLib::TypeCellphone, $phone, $code, SmsRuleModel::$_type_findPs);

		if ($VerifierCode['code'] != 200) {
            $this->outputJson($VerifierCode['code'], $VerifierCode['msg']);
        }

        $usersafe = new UsersafeService();
        $upCode = $usersafe->forgetPs($phone,VerifierCodeLib::TypeCellphone);
        
        $this->outputJson(200, 'verify success', ['upcode'=>$upCode]);
    }


    public function resetPs()
    {
        if(!preg_match('/^([_a-zA-Z0-9]){6,20}$/', _g("ps"))){
            $this->outputJson(0 , "密码至少包含字母和数字，最短6位字符，区分大小写");
        }
        
    	$ps = md5(_g("ps"));
    	$confirmPs = md5(_g("confirmPs"));
    	$upcode = _g("upcode");

    	$usersafe = new UsersafeService();
        $ret = $usersafe->resetPS($ps,$confirmPs,$upcode);

        $this->outputJson($ret['code'], $ret['msg']);
    }
}