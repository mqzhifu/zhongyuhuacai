<?php
class ExamineCtrl extends BaseCtrl 
{
	function ok()
	{
		$this->assign('auditStatus', OpenUserModel::$_status_passed);
		$this->assign('developerType', OpenUserModel::getAccountDescs());
		
		$this->display("developer/developer.html");
	}

	function audit()
	{
		$this->assign('auditStatus', OpenUserModel::$_status_auditing);
		$this->assign('developerType', OpenUserModel::getAccountDescs());

		$this->display("developer/developer.html");
	}

	function wait()
	{
		$this->assign('auditStatus', OpenUserModel::$_status_uncommitted);
		$this->assign('developerType', OpenUserModel::getAccountDescs());

		$this->display("developer/developer.html");
	}


	function detail()
	{
		$id = _g('id');
		$developer = OpenUserModel::db()->getRowById($id);
        $developer['idcard_start_date'] = date('Y-m-d',$developer['idcard_start_date']);
        $developer['idcard_end_date'] = date('Y-m-d',$developer['idcard_end_date']);
		$developer['business'] = $this->getStaticFileUrl('business', $developer['business'], "open");
		$developer['idcard_img'] = $this->getStaticFileUrl('idcard', $developer['idcard_img'], "open");
		$developer['idcard2_img'] = $this->getStaticFileUrl('idcard', $developer['idcard2_img'], "open");

		$this->assign('developerType', OpenUserModel::getAccountDescs());
		$this->assign('developerDetail', $developer);

		$this->display("developer/detail.html");
	}


    function pass()
    {
    	$uid=_g('uid');
    	$developer = OpenUserModel::db()->getRow(" uid=$uid ");
    	if ($developer['status'] == OpenUserModel::$_status_auditing) {
    		$newStatus = OpenUserModel::$_status_passed;
    		if (OpenUserModel::db()->upById($developer['id'], ['status'=>$newStatus, 'u_time'=>time()])) {
                // 推送一条审核通知
                $notificationService = new openNotificationService();
                $title = "账号主体资质审核结果";
                $content = "开发者你好，经平台审核，账号主体资质信息已通过审核。";
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
    	$developer = OpenUserModel::db()->getRow(" uid=$uid ");
    	if ($developer['status'] == OpenUserModel::$_status_auditing) {
    		$newStatus = OpenUserModel::$_status_rejected;
    		if (OpenUserModel::db()->upById($developer['id'], ['status'=>$newStatus, 'detail'=>$detail, 'u_time'=>time()])) {
                // 推送一条审核通知
                $notificationService = new openNotificationService();
                $title = "账号主体资质审核结果";
                $content = "开发者你好，经平台审核，账号主体资质信息未通过审核，具体原因如下：$detail";
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

        $where = $this->getWhere()." and ou.status=$status ";

        // $cnt = OpenUserModel::db()->getCount($where);
        $res = OpenUserModel::db()->getRowBySQL("select count(*) as total from open_user ou inner join user u on u.id=ou.uid where $where");
        $cnt = $res['total'];
        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "asc";

            if(PCK_AREA == 'cn'){
                $sort = array(
                    '',
                    'ou.uid',
                    'ou.type',
                    'ou.company',
                    'ou.contact',
                    'ou.phone',
                    'ou.a_time',
                    'ou.u_time',
                    '',
                );
            }else{
                $sort = array(
                    '',
                    'ou.uid',
                    'ou.type',
                    '',
                    '',
                    'ou.company',
                    'ou.contact',
                    'ou.phone',
                    'ou.a_time',
                    'ou.u_time',
                    '',
                );
            }
            

            $order = " order by " .$sort[$order_column]." ".$order_dir;

            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录

            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始

            $order.= " limit $iDisplayStart, $iDisplayLength ";

            $developers = OpenUserModel::db()->getAllBySQL("select ou.id,ou.uid,ou.type,ou.company,ou.account_holder,ou.contact,ou.phone,ou.a_time,ou.u_time,u.facebook_uid,u.google_uid from open_user ou inner join user u on u.id=ou.uid where ".$where . $order);

            $typeDesc = OpenUserModel::getAccountDescs();

            if(PCK_AREA == 'cn'){
                foreach ($developers as $dp) {
                    $row = array(
                        '<input type="checkbox" name="id[]" value="'.$dp['id'].'">',
                        $dp['uid'],
                        $typeDesc[$dp['type']],
                        $this->getDeveloperName($dp),
                        $dp['contact'],
                        $dp['phone'],
                        get_default_date($dp['a_time']),
                        get_default_date($dp['u_time']),
                        '<a href="/developer/no/examine/detail/id='.$dp['id'].'" class="btn btn-xs default blue" data-id="'.$dp['id'].'"><i class="fa fa-file-text"></i> 详情</a>',
                    );
                    $records["data"][] = $row;
                }
            }else{
                foreach ($developers as $dp) {
                    $accountSrc = "";
                    $thirdUid = "";
                    if($dp['facebook_uid']){
                        $accountSrc = "facebook";
                        $thirdUid = $dp['facebook_uid'];
                    }
                    if($dp['google_uid']){
                        $accountSrc = "google";
                        $thirdUid = $dp['google_uid'];
                    }
                    $row = array(
                        '<input type="checkbox" name="id[]" value="'.$dp['id'].'">',
                        $dp['uid'],
                        $typeDesc[$dp['type']],
                        $this->getDeveloperName($dp),
                        $accountSrc,
                        $thirdUid,
                        $dp['contact'],
                        $dp['phone'],
                        get_default_date($dp['a_time']),
                        get_default_date($dp['u_time']),
                        '<a href="/developer/no/examine/detail/id='.$dp['id'].'" class="btn btn-xs default blue" data-id="'.$dp['id'].'"><i class="fa fa-file-text"></i> 详情</a>',
                    );
                    $records["data"][] = $row;
                }
            }
            

        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
	}


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
        $from_a = _g("from_a");
        $to_a = _g("to_a");
        $from_u = _g("from_u");
        $to_u = _g("to_u");
		$type = _g("type");
		$company = _g("company");
		$contact = _g("contact");
        $phone = _g("pnone");

        if (!is_null($uid) && $uid!='')
            $where .= " and ou.uid = '$uid'";

        if (!is_null($from_a) && $from_a!='') {
            $where .= " and ou.a_time >= '".strtotime($from_a)."'";
        }

        if (!is_null($to_a) && $to_a!='') {
            $where .= " and ou.a_time <= '".strtotime("$to_a +1 day")."'";
        }

        if (!is_null($from_u) && $from_u!='') {
            $where .= " and ou.u_time >= '".strtotime($from_u)."'";
        }

        if (!is_null($to_u) && $to_u!='') {
            $where .= " and ou.u_time <= '".strtotime("$to_u +1 day")."'";
        }

        if (!is_null($type) && $type!='')
            $where .= " and ou.type = '$type'";

        if (!is_null($company) && $company!='')
            $where .= " and (ou.company = '$company' or ou.account_holder = '$company')";

        if (!is_null($contact) && $contact!='')
            $where .= " and ou.contact = '$contact'";

        if (!is_null($phone) && $phone!='')
            $where .= " and ou.phone = '$phone'";

        return $where;
    }
}