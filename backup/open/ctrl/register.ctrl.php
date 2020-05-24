<?php

/**
 * @Author: Kir
 * @Date:   2019-04-18 17:33:37
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-05-24 13:48:05
 */

/**
 * 
 */
class RegisterCtrl extends BaseCtrl
{
	
	function index()
	{
		$this->addCss('assets/open/css/register.css');
		$this->addJs('assets/open/scripts/register.js');
        $this->display("register.html",'new','noLogin');
	}

	function register()
	{
		$phone = _g("phone");
		$verifyCode = _g("verifyCode");
		$ps = _g("ps");

		if(!preg_match("/^1[35678]\d{9}$/", $phone)){
			$this->outputJson(1, "手机名称格式不正确");
		}
		if(!preg_match('/^([_a-zA-Z0-9]){6,20}$/', $ps)){
			$this->outputJson(2 , "密码格式需要6-20位数字或字母或_");
		}

		if(UserModel::db()->getRow("cellphone=$phone")){
			$this->outputJson(0, "该手机已注册");
		}
		// 验证验证码
		$VerifierCodeClass = new VerifierCodeLib();
		$VerifierCode = $VerifierCodeClass->authCode(VerifierCodeLib::TypeCellphone, $phone, $verifyCode, SmsRuleModel::$_type_reg);

		if ($VerifierCode['code'] != 200) {
            $this->outputJson($VerifierCode['code'], "验证码错误");
        }

		$pcInfo = [];
		$pcInfo['nickname'] = "";
		$pcInfo['sex'] = "";
		$pcInfo['avatar'] = "";
		// 注册
		$ps = md5($ps);
		$rs = $this->userService->register($phone, $ps, UserModel::$_type_cellphone_ps,null, $pcInfo);
		if($rs['code'] != 200){
			$this->outputJson($rs['code'], "注册失败");
		}
		LogLib::appWriteFileHash(["im in register  ~~~~~~~~~",$rs['msg']['uid'],IS_NAME]);
		//这行代码不能删除，不然，缓存会出错
        $this->userService->getUinfoById($rs['msg']['uid'],IS_NAME);

		// 同步cdn
		$this->openGamesService->rsyncToServer();
		// 自登陆
		$rs2 = $this->userService->selfLogin($phone,$ps,UserModel::$_type_pc_cellphone_ps);
		if($rs2['code'] != 200){
			$this->outputJson($rs2['code'], "");
		}
		// 存储session
		$uinfo = ['uid'=>$rs2['msg']['id'], 'nickname'=>$rs2['msg']['nickname'],'avatar'=>getUserAvatar($rs2['msg']),'cellphone'=>$rs2['msg']['cellphone']];
        $this->_sess->setSession(['user'=>$uinfo]);

		$this->outputJson(200, "注册成功", ['uid'=>$rs2['msg']['id']]);
	}

	function sendSms()
	{
		$cellphone = _g('phone');
        $class = new VerifierCodeLib();
        $rs = $class->sendCode(VerifierCodeLib::TypeCellphone,$cellphone, SmsRuleModel::$_type_reg);
        $this->outputJson($rs['code'], $rs['msg']);
    }


}