<?php

class ManagerCtrl extends BaseCtrl
{
	function ok()
	{
		$this->assign('auditStatus', OpenFinanceModel::$_status_passed);
		$this->assign('developerType', OpenUserModel::getAccountDescs());
		
		$this->display("finance/manager/index.html");
	}

	function audit()
	{
		$this->assign('auditStatus', OpenFinanceModel::$_status_auditing);
		$this->assign('developerType', OpenUserModel::getAccountDescs());

		$this->display("finance/manager/index.html");
	}

	function wait()
	{
		$this->assign('auditStatus', OpenFinanceModel::$_status_uncommitted);
		$this->assign('developerType', OpenUserModel::getAccountDescs());

		$this->display("finance/manager/index.html");
	}


	function loginOpen()
	{
		header('Access-Control-Allow-Origin:*');
		$uid = _g("uid");
		if (!$user = UserModel::db()->getRowById($uid)) {
			exit(0);
		};

		$userService = new UserService();
		$key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['token']['key'],$uid,'open');
		if (!$token = RedisPHPLib::get($key)) {
			$token = $userService->createToken($uid);
		}
			
        echo( "<script>location.href='http://isop-test.feidou.com/?token=$token';</script>");

	}


	function detail()
	{
		if (!$id = _g('id')) {
			exit(0);
		}
		if (!$finance = OpenFinanceModel::db()->getRowById($id)) {
			exit(0);
		};

        $finance['tax_rate'] = OpenFinanceModel::getTaxRate($finance['tax_type'], $finance['invoice_type']);
        $finance['tax_img'] = $this->getStaticFileUrl('tax', $finance['tax_img'], "open");
        $finance['tax_type'] = OpenFinanceModel::getTaxType()[$finance['tax_type']];
        $finance['invoice_type'] = OpenFinanceModel::getInvoiceType()[$finance['invoice_type']];
        $finance['idcard_img'] = $this->getStaticFileUrl('idcard', $finance['idcard_img'], "open");
        $finance['idcard2_img'] = $this->getStaticFileUrl('idcard', $finance['idcard2_img'], "open");

        $finance['bank_img'] = $this->getStaticFileUrl('bank', $finance['bank_img'], "open");


		$this->assign("openFinance", $finance);
        
		$developer = OpenUserModel::db()->getRow("uid = ".$finance['uid']);
		$developer['business'] = $this->getStaticFileUrl('business', $developer['business'], "open");
        $developer['idcard_img'] = $this->getStaticFileUrl('idcard', $developer['idcard_img'], "open");
        $developer['idcard2_img'] = $this->getStaticFileUrl('idcard', $developer['idcard2_img'], "open");
        $developer['idcard_start_date'] = date("Y-m-d", $developer['idcard_start_date']);
        $developer['idcard_end_date'] = date("Y-m-d", $developer['idcard_end_date']);
		$this->assign('developer', $developer);

		$this->assign("accountType", OpenUserModel::getAccountDescs());
		$this->display("/finance/manager/detail.html");
	}


    function pass()
    {
    	$uid=_g('uid');
    	$developer = OpenFinanceModel::db()->getRow(" uid=$uid ");
    	if ($developer['status'] == OpenFinanceModel::$_status_auditing) {
    		$newStatus = OpenFinanceModel::$_status_passed;
    		if (OpenFinanceModel::db()->upById($developer['id'], ['status'=>$newStatus, 'u_time'=>time()])) {
                // 推送一条审核通知
                $notificationService = new openNotificationService();
                $title = "账号主体财务审核结果";
                $content = "开发者你好，经平台审核，账号主体财务信息已通过审核。";
                $rs = $notificationService->sendNotifyMsg($uid,1,$title,$content);
    			echo json_encode(200);
        		exit;
    		}
    	}
    	echo json_encode(0);
    }

    function reject()
    {
    	$uid=_g('uid');
    	$detail = _g('detail');
    	$developer = OpenFinanceModel::db()->getRow(" uid=$uid ");
    	if ($developer['status'] == OpenFinanceModel::$_status_auditing) {
    		$newStatus = OpenFinanceModel::$_status_rejected;
    		if (OpenFinanceModel::db()->upById($developer['id'], ['status'=>$newStatus, 'detail'=>$detail, 'u_time'=>time()])) {
                // 推送一条审核通知
                $notificationService = new openNotificationService();
                $title = "账号主体财务审核结果";
                $content = "开发者你好，经平台审核，账号主体财务信息未通过审核，具体原因如下：$detail";
                $rs = $notificationService->sendNotifyMsg($uid,1,$title,$content);
    			echo json_encode(200);
        		exit;
    		}
    	}
    	echo json_encode(0);
    }

	function getDevelopers()
	{
		$status = _g('status');
		$records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere()." and status=$status ";

        $sql = "select count(*) as cnt from (select b.id,u.uid,u.type,u.company,u.contact,u.phone,b.status,b.u_time from open_finance as b left join open_user as u on u.uid=b.uid) as c where $where";

        $cntSql = OpenUserModel::db()->getRowBySQL($sql);
		$cnt = 0;
        if(arrKeyIssetAndExist($cntSql,'cnt')){
            $cnt = $cntSql['cnt'];
        }
        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "asc";


            $sort = array(
                '',
                'id',
                'uid',
                '',
                '',
                '',
                '',
                '',
                'u_time',
                '',
            );

            $order = " order by " .$sort[$order_column]." ".$order_dir;

            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录

            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始

            $order.= " limit $iDisplayStart, $iDisplayLength ";

            $sql = "select * from(select b.id,u.uid,u.type,u.company,u.account_holder,u.contact,u.phone,u.email,b.status,b.u_time from open_finance as b inner join open_user as u on u.uid=b.uid) as c where $where";

            $developers = OpenUserModel::db()->getAllBySQL($sql . $order);
            // $typeDesc = OpenUserModel::getAccountDescs();
            foreach ($developers as $dp) {
    			$row = array(
                    '<input type="checkbox" name="id[]" value="'.$dp['id'].'">',
                    $dp['id'],
                    $dp['uid'],
                    $typeDesc[$dp['type']],
                    $this->getDeveloperName($dp),
                    $dp['contact'],
                    $dp['phone'],
                    $dp['email'],
                    get_default_date($dp['u_time']),
                    '<a href="/finance/manager/manager/detail/id='.$dp['id'].'" class="btn btn-xs default blue" data-id="'.$dp['id'].'" ><i class="fa fa-file-text"></i> 详情</a>',
                );
                $records["data"][] = $row;
        	}

        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
	}
    // 传入包含type字段和company、account_holder map
    function getDeveloperName($dp){
        if($dp['type'] == OpenUserModel::TYPE_PERSON){
            return $dp['account_holder'];
        }

        if($dp['type'] == OpenUserModel::TYPE_COMPANY){
            return $dp['company'];
        }
        return "";
    }
	function getWhere() 
	{
        $where = " 1 ";
        $uid = _g("uid");
        $from = _g("from_u");
        $to = _g("to_u");
		$type = _g("type");
		$company = _g("company");
		$contact = _g("contact");
		$pnone = _g("pnone");
        $email = _g("email");

        if (!is_null($uid) && $uid!='')
            $where .= " and uid = '$uid'";


        if (!is_null($from) && $from!='') {
            $where .= " and u_time >= '".strtotime($from)."'";
        }

        if (!is_null($to) && $to!='') {
            $where .= " and u_time <= '".strtotime("+1 day $to")."'";
        }

        if (!is_null($type) && $type!='')
            $where .= " and type = '$type'";

        if (!is_null($company) && $company!='')
            $where .= " and company = '$company'";

        if (!is_null($contact) && $contact!='')
            $where .= " and contact = '$contact'";

        if (!is_null($phone) && $phone!='')
            $where .= " and phone = '$phone'";

        if (!is_null($email) && $email!='')
            $where .= " and email = '$email'";

        return $where;
    }
}