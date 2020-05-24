<?php
//客服 - 调度
class ScheduleLib{
	public $openid = "";
	public $timeout = 0;

	function __construct($openid = ""){
		if($openid){
			$this->openid = $openid;
		}

		$this->timeout = 48 * 60 * 60;
	}

	static function inst(){
		return new self();
	}

	function assign(){
		$servicing_sess = serverSessionModel::db()->getRow(" openid = '{$this->openid}' and status = 3" );
		//进行中的
		if($servicing_sess){
			//是否已失效
			if( $this->isTimeOut($servicing_sess['a_time']) ){
				$this->closeSess($servicing_sess['id']);
				return $this->assignServer();
			}

			$this->upLastTime($servicing_sess['id']);
//			$uname = getAdminUnameByid($servicing_sess['id']);
//			return out_ok("客服：".$uname.",正在服务中....",201,'pc');
			return out_ok("",201,'pc');
		}else{
			//等待接入中
			$wait_session = serverSessionModel::db()->getRow(" openid = '{$this->openid}' and  status = 2");
			if($wait_session){
				$this->upLastTime($wait_session['id']);

				$uname = getAdminUnameByid($servicing_sess['id']);
				return out_ok(_lang("servicing","#nickname#",$uname),202,'pc');
			}else{
				//未分配中
				$no_session = serverSessionModel::db()->getRow(" openid = '{$this->openid}' and  status = 1");
				if($no_session){
					$this->upLastTime($no_session['id']);

//					return out_ok("会话已建立，但是所有客服均在忙，未分配客服,请耐心等待....",203,'pc');
					return out_ok(_lang('server_busy'),203,'pc');
				}else{
					return $this->assignServer();
				}
			}
		}
	}

	function closeSess($sid){
		$sess = serverSessionModel::db()->getById($sid);
		if(!$sess)
			return false;


		if($sess['status'] == 4)
			return false;

		$data = array('up_time'=>time(),'status'=>4);
		serverSessionModel::db()->update($data," id =  ".$sid);


		if(isset($sess['admin_id']) && $sess['admin_id']){
			$data = array('servicing_sess_num'=>array(-1),'close_sess_num'=>array(1),'up_time'=>time());
			adminUserModel::db()->update($data," id = {$sess['admin_id']} limit 1");
		}

		return true;
	}
	//用户自己接入
	function servicingSess($sid){
		$sess = serverSessionModel::db()->getById($sid);
		if(!$sess)
			return false;

		if($sess['status'] == 3)
			return false;


		$data = array('up_time'=>time(),'status'=>3);
        $rs = serverSessionModel::db()->update($data," id = ".$sess['id'] . " limit 1 ");



		if(isset($sess['admin_id']) && $sess['admin_id']){
			$up_data = array('wating_sess_num'=>array(-1),'servicing_sess_num'=>array(1),'up_time'=>time());
			adminUserModel::db()->update($up_data," id = ".$sess['admin_id']. " limit 1");
		}else{
			return false;
		}


	}
	//管理员调度
	function adminAssign($sid,$admin_id){
		$sess = serverSessionModel::db()->getById($sid);
		if(!$sess)
			return out_err('sid not in db...',700,'pc');

		if($sess['status'] != 1 && $sess['status'] != 2)
			return out_err('此会话不是<未分配>或<等待接入状态>',701,'pc');


//		if(isset($sess['admin_id']) && $sess['admin_id'])
//			return out_err('此会话已经分配给了 adminid:'.$sess['admin_id'],703,'pc');

		$admin = adminUserModel::db()->getById($admin_id);
		if(!$admin)
			return out_err('admin id not in db...',704,'pc');


		$data = array('up_time'=>time(),'admin_id'=>$admin_id,'status'=>2);
		serverSessionModel::db()->update($data," id = $sid limit 1");


		$up_data = array('wating_sess_num'=>array(1), 'up_time'=>time());
		adminUserModel::db()->update($up_data," id = $admin_id limit 1");




		return out_ok('ok',200,'pc');

	}

	function upLastTime($sid){
		$data = array('up_time'=>time());
		serverSessionModel::db()->update($data,"  id = ". $sid . " limit 1");
		return true;
	}

	function isTimeOut($time){
		return false;
		if($time + $this->timeout <=  time())
			return true;

		return false;
	}
	//分配客服
	function assignServer(){
		$server_online = adminUserModel::db()->getAll(" is_online = 1 order by id asc ");
		$data = array(
			'openid'=>$this->openid,
			'a_time'=>time(),
			'up_time'=>time(),
			'admin_id'=>0,
			'receive_log_id'=>0,
			'status'=>0,
			'receive_num'=>0,
			'reply_num'=>0,
		);
		if(!$server_online) {
			$data['status'] = 1;
			serverSessionModel::db()->add($data);
//			return out_ok('会话已创建，但客服均未上线，请等待',301,'pc');
			return out_ok(_lang('server_offline'),301,'pc');


		}else{
			//随机分配一个
			$r = rand(0,count($server_online) - 1);
			$admin_id = $server_online[$r]['id'];

//			$admin_id = 0;
//			foreach($server_online as $k=>$v){
//				if($v['servicing_sess_num'] > getServiceMax()){
//					continue;
//				}
//
//				$admin_id = $v['id'];
//				break;
//			}

//			if($admin_id){
				$data['status'] = 2;
				$data['admin_id'] = $admin_id;

				serverSessionModel::db()->add($data);

				$up_data = array('wating_sess_num'=>array(1));
				adminUserModel::db()->update($up_data," id = ".$data['admin_id']. " limit 1");

				$uname = getAdminUnameByid($admin_id);
//				return out_ok("客服：".$uname.",正在忙~等待接入中....",302,'pc');
				return out_ok(_lang("servicing","#nickname#",$uname),302,'pc');
//			}else{
//				return out_ok("会话已建立，但是所有客服均在忙，未分配客服,请耐心等待....",303,'pc');
//			}


		}
	}
}
