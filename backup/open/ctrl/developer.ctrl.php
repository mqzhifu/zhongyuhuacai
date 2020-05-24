<?php
set_time_limit(600);
header("Content-type:text/html;charset=utf-8");

class developerCtrl extends BaseCtrl
{
	function company() 
	{
		$this->addJs("/assets/open/scripts/laydate/laydate.js");
		$this->addJs("/assets/open/scripts/area.js");

		$this->display("company.html",'new','isLogin');
	}

	function person() 
	{
		$this->addJs("/assets/open/scripts/laydate/laydate.js");

		$this->display("person.html",'new','isLogin');
	}

	// 开发者账号信息
	// 同时用作注册和更新
	function submit()
	{
		$uid = $this->_uid;
		$data = ['uid'=>$uid];

		if (_g("type")) {
			$data['type'] = _g("type");
		}
		if (_g("company")) {
			$data['company'] = _g("company");
		}
		if (_g("account_holder")) {
			$data['account_holder'] = _g("account_holder");
		}
		if (_g("license")) {
			$data['business_number'] = _g("license");
		}
		if (_g("province")) {
			$data['company_addr_province'] = _g("province");
		}
		if (_g("city")) {
			$data['company_addr_city'] = _g("city");
		}
		if (_g("district")) {
			$data['company_addr_district'] = _g("district");
		}
		if (_g("company_addr_detail")) {
			$data['company_addr_detail'] = _g("company_addr_detail");
		}

		if ($_FILES["idcard1"]) {
			$idcard1 = $this->uploadService->uploadFileByApp("idcard1", "idcard", "", 1);
			if ($idcard1['code'] != 200) {
				$this->outputJson($idcard1['code'], $idcard1['msg']);
			}
			$data['idcard_img'] = $idcard1['msg'];
		}

		
		if ($_FILES["idcard2"]) {
			$idcard2 = $this->uploadService->uploadFileByApp("idcard2", "idcard", "", 1);
			if ($idcard2['code'] != 200) {
				$this->outputJson($idcard2['code'], $idcard2['msg']);
			}
			$data['idcard2_img'] = $idcard2['msg'];
		}
		
		if ($_FILES["license_img"]) {
			$business = $this->uploadService->uploadFileByApp("license_img", "business", "", 1);
			if ($business['code'] != 200) {
				$this->outputJson($business['code'], $business['msg']);
			}
			$data['business'] = $business['msg'];
		}
		

		if (_g("legal_person")) {
			$data['legal_person'] = _g("legal_person");
		}
		if (_g("idcard_number")) {
			$data['idcard_number'] = _g("idcard_number");
		}
		if (_g("idcard_start_date")) {
			$data['idcard_start_date'] = strtotime(_g("idcard_start_date"));
		}
		if (_g("idcard_end_date")) {
			$data['idcard_end_date'] = strtotime(_g("idcard_end_date"));
		}
		if (_g("contact")) {
			$data['contact'] = _g("contact");
		}
		if (_g("contact_addr")) {
			$data['address'] = _g("contact_addr");
		}
		if (_g("contact_email")) {
			$data['email'] = _g("contact_email");
		}
		if (_g("contact_phone")) {
			$data['phone'] = _g("contact_phone");
		}

		$data['status'] = InformationModel::$_status_auditing;
		$data['u_time'] = time();

		if (InformationModel::db()->getCount( "uid=$uid") > 0){
			InformationModel::db()->update($data, " uid=$uid limit 1");
		} else {
			$data['a_time'] = time();
			InformationModel::db()->add($data);
			if (PCK_AREA != 'en') {
				// 推送一条新注册通知
				$notificationService = new openNotificationService();
				$title = "注册成功";
				$content = "开发者你好，你的开发者账号（用户ID：".$uid."）注册成功。";
		        $rs = $notificationService->sendNotifyMsg($uid,1,$title,$content);
				if ($rs['code'] != 1) {
					$this->outputJson(0, $rs['msg']);
				}
			}
		}

		$this->outputJson(200, "提交成功");
	}

	function base()
	{
		$this->addJs("assets/open/scripts/laydate/laydate.js");
		$this->display("baseInfo.html", "new", "isLogin");
	}

	function account()
	{
		$this->addCss("assets/open/css/developerProfile.css");
		$this->addJs("assets/open/scripts/laydate/laydate.js");
		$this->addJs("assets/open/scripts/inforegist.js");
		$this->addJs("assets/open/scripts/area.js");
		$uid = $this->_uid;
		if($user = InformationModel::db()->getRow( "uid=".$uid)){
			$user['accountDesc'] = InformationModel::getAccountDescs()[$user['type']];

			$user['idcard_img'] = $this->getStaticFileUrl("idcard", $user['idcard_img']);
			$user['idcard2_img'] = $this->getStaticFileUrl("idcard", $user['idcard2_img']);
			$user['business'] = $this->getStaticFileUrl("business", $user['business']);
			$user['idcard_start_date'] = date("Y-m-d", $user['idcard_start_date']);
			$user['idcard_end_date'] = date("Y-m-d", $user['idcard_end_date']);
			$this->assign("user",$user);
			if ($user['type'] == 1) {
				$this->display("accountCompany.html", "new", "isLogin");
			} elseif ($user['type'] == 2) {
				$this->display("accountPerson.html", "new", "isLogin");
			}
		} elseif (PCK_AREA == 'en') {
			if (_g("type") == 2) {
				$this->display("/hiFun/person.html", "new", "isLogin");
			} else {
				$this->display("/hiFun/company.html", "new", "isLogin");
			}
		}
	}


	

	function finance()
	{
		$this->addCss("assets/open/css/developerProfile.css");
		$this->addJs("/assets/open/scripts/finance.js");
		$this->addJs("assets/open/scripts/laydate/laydate.js");
		$this->addJs("/assets/open/scripts/area.js");
		$uid = $this->_uid;
		$this->assign('tax_type', OpenFinanceModel::getTaxType());
		$this->assign('invoice_type', OpenFinanceModel::getInvoiceType());
		if($user = InformationModel::db()->getRow("uid=$uid")){
			
			$user['accountDesc'] = InformationModel::getAccountDescs()[$user['type']];
			
			$this->assign("user",$user);
			if ($finance = OpenFinanceModel::db()->getRow("uid=$uid")) {
				$uploadService = new UploadService();
				$finance['idcard_img'] = $this->getStaticFileUrl("idcard", $finance['idcard_img']);
				$finance['idcard2_img'] = $this->getStaticFileUrl("idcard", $finance['idcard2_img']);
				$finance['bank_img'] = $this->getStaticFileUrl("bank", $finance['bank_img']);
				$finance['idcard_start_date'] = date("Y-m-d", $finance['idcard_start_date']);
				$finance['idcard_end_date'] = date("Y-m-d", $finance['idcard_end_date']);
				$finance['tax_img'] = $this->getStaticFileUrl("tax", $finance['tax_img']);
				$this->assign("finance",$finance);
			}
			if ($user['type'] == 1) {
				$this->display("financeCompany.html", "new", "isLogin");
			} elseif ($user['type'] == 2) {
				$this->display("financePerson.html", "new", "isLogin");
			}
		} else {
			$this->display("financeCompany.html", "new", "isLogin");
		}
	}

	function financeSubmit()
	{
		$uid = $this->_uid;
		$finance_data = ['uid'=>$uid];

		if (_g("province")) {
			$finance_data['bank_province'] = _g("province");
		}
		if (_g("city")) {
			$finance_data['bank_city'] = _g("city");
		}
		if (_g("district")) {
			$finance_data['bank_district'] = _g("district");
		}
		if (_g("bank_name")) {
			$finance_data['bank_name'] = _g("bank_name");
		}
		if (_g("branch_name")) {
			$finance_data['branch_name'] = _g("branch_name");
		}
		if (_g("bank_account")) {
			$finance_data['bank_account'] = _g("bank_account");
		}

		
		if ($_FILES["tax_img"]) {
			$tax_img = $this->uploadService->uploadFileByApp("tax_img", "tax", "", 1);
			if ($tax_img['code'] != 200) {
				$this->outputJson($tax_img['code'], $tax_img['msg']);
			}
			$finance_data['tax_img'] = $tax_img['msg'];
		}

		if ($_FILES["bank_img"]) {
			$bank_img = $this->uploadService->uploadFileByApp("bank_img", "bank", "", 1);
			if ($bank_img['code'] != 200) {
				$this->outputJson($bank_img['code'], $bank_img['msg']);
			}
			$finance_data['bank_img'] = $bank_img['msg'];
		}


		if (_g("tax_number")) {
			$finance_data['tax_number'] = _g("tax_number");
		}
		if (_g("tax_type")) {
			$finance_data['tax_type'] = _g("tax_type");
		}
		if (_g("invoice_type")) {
			$finance_data['invoice_type'] = _g("invoice_type");
		}

		$finance_data['status'] = OpenFinanceModel::$_status_auditing;

		$user_data = ['uid'=>$uid];

		if (_g("idcard_number")) {
			$finance_data['idcard_number'] = _g("idcard_number");
		}
		if (_g("idcard_start_date")) {
			$finance_data['idcard_start_date'] = strtotime(_g("idcard_start_date"));
		}
		if (_g("idcard_end_date")) {
			$finance_data['idcard_end_date'] = strtotime(_g("idcard_end_date"));
		}

		if ($_FILES["idcard1"]) {
			$idcard1 = $this->uploadService->uploadFileByApp("idcard1", "idcard", "", 1);
			if ($idcard1['code'] != 200) {
				$this->outputJson($idcard1['code'], $idcard1['msg']);
			}
			$finance_data['idcard_img'] = $idcard1['msg'];
		}

		
		if ($_FILES["idcard2"]) {
			$idcard2 = $this->uploadService->uploadFileByApp("idcard2", "idcard", "", 1);
			if ($idcard2['code'] != 200) {
				$this->outputJson($idcard2['code'], $idcard2['msg']);
			}
			$finance_data['idcard2_img'] = $idcard2['msg'];
		}

		
		if (_g("contact")) {
			$user_data['contact'] = _g("contact");
		}
		if (_g("contact_addr")) {
			$user_data['address'] = _g("contact_addr");
		}
		if (_g("contact_email")) {
			$user_data['email'] = _g("contact_email");
		}
		if (_g("contact_phone")) {
			$user_data['phone'] = _g("contact_phone");
		}

		if (OpenFinanceModel::db()->getCount( "uid=$uid") > 0){
			$finance_data['u_time'] = time();
			OpenFinanceModel::db()->update($finance_data, " uid=$uid limit 1");
		} else {
			$finance_data['a_time'] = time();
			OpenFinanceModel::db()->add($finance_data);
		}

		$user_data['u_time'] = time();

		if (InformationModel::db()->getCount( "uid=$uid") > 0){
			
			InformationModel::db()->update($user_data, " uid=$uid limit 1");
		} else {
			$user_data['a_time'] = time();
			InformationModel::db()->add($user_data);
		}

		$this->outputJson(200, "提交成功");
	}

	//开发者设置界面查询
	function getInfo(){
		//1.通過uid查詢用戶基本信息
		$uid = $this->_uid;
		// $baseInfo = [];
		//2.查询用户公司或者个人信息
		$arr = InformationModel::db()->getRow("uid=".$uid);
		$result = [];
		$result['type'] = InformationModel::getAccountDescs()[$arr['type']];
		if($arr['type'] == InformationModel::TYPE_COMPANY){
			$result['company'] = $arr['company'];
		}
		if($arr['type'] == InformationModel::TYPE_PERSON){
			$result['company'] = $arr['account_holder'];
		}
		$result['address'] = $arr['address'];
		$result['contact'] = $arr['contact'];
		$result['phone'] = $arr['phone'];

		$user2 = $this->userService->getUinfoById($uid, "instantplay");

		$result['nickname'] = $user2['nickname'];
		
		$result['avatar'] = $user2['avatar'];

		$result['sex'] = UserModel::getSexDesc()[$user2['sex']];//$user['sex'];
		$result['uid'] = $uid;

		$this->outputJson(1, "成功", $result);
	}

	function modifyBaseInfo(){
		$uid = $this->_uid;
		$nickname = _g("nickname");
		$sex = _g("sex");
		$ps = _g("ps");
		
		$data = [];
		if(isset($_FILES["avatar"])){

			// $res = $this->uploadService->upAvatar($uid);
			$res = $this->uploadService->uploadFileByApp('avatar','avatar','user',1 , 'instantplay',$uid);
			if($res['code'] != 200){
				$this->outputJson($res['code'], $res['msg']);
			}
			$this->_user['avatar'] = $res['msg'];
			
		}

		if($nickname){
			$data['nickname'] = $nickname;
		}
		if($sex!=null){
			$data['sex'] = $sex;
		}
		if($ps!=null){
			if(!preg_match('/^([_a-zA-Z0-9]){6,20}$/', trim($ps))){
				$this->outputJson(5 , "密码格式不正确");
			}
			$data['ps'] = md5($ps);
		}

		if(isset($data['ps'])){
			$oldps = md5(_g("old_ps"));
			if($oldps == $data['ps']){
				$this->outputJson(3, "修改失败，新旧密码相同", []);
			}
			if($data['ps'] != md5(_g("confirm_ps"))){
				$this->outputJson(4, "两次密码不一致",[]);
			}
			$res1 = UserModel::db()->getRow("id=".$uid." and ps='".$oldps."'");
			if(!$res1){
				$this->outputJson(2, "密码不正确", []);
			}
		}
		
		$this->userService->upUserInfo($uid, $data);
		// 同步cdn
		$this->openGamesService->rsyncToServer();

		foreach($data as $k=>$v){
			if (isset($this->_user[$k])){
				if ($k == 'avatar'){
					// $this->_user[$k] = $this->uploadService->getStaticBaseUrl() .$v;
				}
				else{
					$this->_user[$k] = $v;
				}
			}
		}
		$this->_sess->setSession(['user'=>$this->_user]);
		
		$this->outputJson(1, "成功", $data);
	}

	function modifyInfo(){
		$uid = $this->_uid;
		// $type = _g("type");
		$company = _g("company");
		$address = _g("address");
		$contact = _g("contact");
		$phone = _g("phone");
		$data = [];
		// if($type){
		// 	$data['type'] = $type;
		// }
		if($company!=null){
			$data['company'] = $company;
		}
		if($address!=null){
			$data['address'] = $address;
		}
		if($contact!=null){
			$data['contact'] = $contact;
		}
		if($phone!=null){
			$data['phone'] = $phone;
			if(!FilterLib::regex($phone,'phone')){
				$this->outputJson(0, "手机格式不正确");
			}
		}
		InformationModel::db()->update($data, " uid=".$uid." limit 1");

		$this->outputJson(1, "成功");
	}


	// 开放平台协议
	function openProtocolIndex(){
		
		$this->addCss("assets/open/css/protocol.css");
		if ($this->isLogin()) {
			$this->display("proto-open.html",'new','isLogin');
		} else {
			$this->display("proto-open.html","new","noLogin");
		}
	}
	// 开心小游戏协议
	function kxgameProtocolIndex(){
		if ($this->isLogin()) {
			$this->display("proto-kxgame.html",'new','isLogin');
		} else {
			$this->display("proto-kxgame.html","new","noLogin");
		}
		
	}
	// 侵权引导协议
	function copyrightGuidelines(){
		$this->display("proto-qinquan.html","regist");
	}


}