<?php
//后台管理员-权限控制
class AclLib{
	function __construct(){
		$this->sess = get_instance_of('SessionLib');
	}

	function isLogin(){
		$uname = $this->sess->getValue('uname');
		if(!$uname)
			return 0;
		return 1;
	}
	//初始化一级跟二级菜单
	function initMenu($menu_id){
		//获取用户已有（权限）的一级菜单
		$root = $this->initRootMenu();
		if(!$root)
			stop("用户~没有一级菜单");
		//当前已选中的一级菜单
		$default_menu = null;
		foreach($root as $k=>$v){
			if($v['menu_id'] == $menu_id){
				$default_menu = $v;
				break;
			}
		}
		//如果没有参数，默认选中第一个
		if(!$default_menu)
			$default_menu = $root[0];
		//获取用户已有（权限）的二级菜单
		$subMenu = $this->initSubMenu($default_menu['menu_id']);
		$rs['root'] = $root;
		$rs['second'] = $subMenu['second'];
		$rs['thrid'] = $subMenu['thrid'];
		return $rs;
	}
	//验证用户访问资源权限
	function authCtrl($ctrl,$ac){
		$resource = ResourceModel::getByCtrlAc($ctrl,$ac);
		if(!$resource)
			return 0;
		
		$auth = $this->authId($resource['resource_id']);
		return $auth;
	}
	//验证用户访问资源权限
	function authId($resouce_id){
		$role = $this->sess->getValue('role');
		if(!in_array($resouce_id, $role['ac_reso']))
			return 0;
		return 1;
	}
	//后台登陆
	function adminLogin($uname,$ps){
        $ps = md5($ps);
		$uinfo = adminUserModel::login($uname,$ps);
		if(!$uinfo)
			return 0;

//		$black = User_blackModel::db()->getRow(" uid = ".$uinfo['uid']);
//		if($black)
//			exit("用户在黑名单中");
//
//		$time = time() - 60 * 15;
//		$login_log = User_blackModel::db()->getAll(" uid = ".$uinfo['uid'] . " and add_time > $time ");
//		if(count($login_log) > 3)
//			exit('15分钟内只允许登陆3次...');
//
//		$role = RoleModel::db()->getRowById($uinfo['role_id']);
//		if($role){
//			$role['ac_reso'] = explode(",", $role['ac_reso']);
//			$role['menu_reso'] = explode(",", $role['menu_reso']);
//		}
//		$uinfo['role'] = $role;

//		$data = array('is_online'=>1,'up_time'=>time());
//		adminUserModel::db()->upById($uinfo['id'],$data);

		$this->sess->setSession($uinfo);
		return 1;
	}
	//前台登陆
	function login($uinfo){
		//$uinfo = UserModel::db()->getRow(" uname = '$uname' and ps = '$ps'");
		//if(!$uinfo)
		//	return 0;
		
		$this->sess->setSession($uinfo);
		return 1;
		
	}
	
	function getAllMenu(){
		$root = MenuModel::getRootMenu();
		if(!$root)
			exit('没有一级菜单');
		$rs = "";
		$s = "&nbsp;&nbsp;&nbsp;";
		$second = array();
		foreach($root as $k=>$v){
			$sub = MenuModel::getByPid($v['menu_id']);
			if($sub){
				$second[$v['menu_id']] = $sub;
			}
		}
		$rs = array('root'=>$root,'sub'=>$second);
		return $rs;
	}
	
	function getMenuSelectOption(){
		$root = MenuModel::getRootMenu();
		if(!$root)
			exit('没有一级菜单');
		$rs = "";
		$s = "&nbsp;&nbsp;&nbsp;";
		foreach($root as $k=>$v){
			$rs .= "<option value='{$v['menu_id']}'>{$v['title']}</option>";
			$sub = MenuModel::getByPid($v['menu_id']);
			if($sub){
				foreach($sub as $k2=>$v2){
					$rs .= "<option value='{$v2['menu_id']}'>$s{$v2['title']}</option>";
				}
			}
		}
		
		return $rs;
	}
	
	function getCateSelectOption(){
		$root = CategoryModel::db()->getAll(" level = 1 ");
		if(!$root)
			exit('没有一级菜单');
		$rs = "";
		$s = "&nbsp;&nbsp;&nbsp;";
		foreach($root as $k=>$v){
			if( $v['category_id'] == 1 || $v['category_id'] == 4 ){
				continue;
			}
			$rs .= "<option value='{$v['category_id']}'>{$v['title']}</option>";
			$sub = CategoryModel::db()->getAll( " pid = " . $v['category_id']);
			if($sub){
				foreach($sub as $k2=>$v2){
					$rs .= "<option value='{$v2['category_id']}'>$s{$v2['title']}</option>";
				}
			}
		}
	
		return $rs;
	}
	
	function getRoleSelectOption(){
		$role = RoleModel::db()->getAll();
		if(!$role)
			exit('没有角色');
		$rs ="";
		foreach($role as $k=>$v){
			$rs .= "<option value='{$v['role_id']}'>{$v['title']}</option>";
		}
		
		return $rs;
	}
	//根据ROLE_ID转换成角色名
	function mapRoleName($user){
		foreach($user as $k=>$v){
			$role = RoleModel::db()->getRowById($v['role_id']);
			$user[$k]['role'] = $role['title'];
		}
		return $user;
	}
	//获取用户已有（权限）的一菜菜单
	function initRootMenu(){
		$rootMenu = MenuModel::getRootMenu();
		if(!$rootMenu)
			exit('没有一级菜单');
	
		$role = $this->sess->getValue('role');
		$rs = array();
		foreach($rootMenu as $k=>$v){
			if( in_array( $v['menu_id'] , $role['menu_reso'] ) ){
				$rs[] = $v;
			}
		}
		return $rs;
	}
	//获取用户已有（权限）的二级/三级菜单
	function initSubMenu($root_id){
		//获取该一级菜单下的，所有 二级/三级菜单 DB数据
		$subMenu = $this->getSubMenu($root_id);
		$second_menu = null;//二级
		$thrid_menu = null;//三级
		//开始对用户有哪些菜单~权限验证
		$role = $this->sess->getValue('role');
		foreach($subMenu['second'] as $k=>$v){
			if(in_array($v['menu_id'], $role['menu_reso'])){
				$second_menu[] = $v;
				if($subMenu['thrid'][$v['menu_id']]){
					foreach($subMenu['thrid'][$v['menu_id']] as $k2=>$v2){
						if(in_array($v['menu_id'], $role['menu_reso'])){
							$thrid_menu[$v['menu_id']][] = $v2;
						}
					}
				}
			}
		}
		if($second_menu)
		foreach($second_menu as $k=>$v){
			$url = $this->getMenuUrl($v);
			$second_menu[$k]['url'] = $url;
		}
			
		if($thrid_menu)
		foreach($thrid_menu as $k=>$v){
			$url = $this->getMenuUrl($v);
			$thrid_menu[$k]['url'] = $url;
		}
	
	
		$rs['second'] = $second_menu;
		$rs['thrid'] = $thrid_menu;
	
	
		return $rs;
	}
	//获取菜单的URL地址~
	function getMenuUrl($arr){
		if($arr['link']){
			return $arr['link'];
		}else{
			if($arr['ctrl'] && $arr['ac']){
				$url = "/admin.php?ctrl=".$arr['ctrl']."&ac=".$arr['ac'];
				if($arr['para']){
					$url = $url."&".$arr['para'];
				}
				$url .= "&menu_id=".$arr['menu_id'];
				return $url;
			}
		}
	}
	
	function getSubMenu($root_id){
		$second_menu = MenuModel::getByPid($root_id);
		if(!$second_menu)
			stop("没有二级菜单");
		$thrid_menu = null;
		foreach($second_menu as $k=>$v){
			$thrid = MenuModel::getByPid($v['menu_id']);
			if($thrid){
				foreach($thrid as $k2=>$v2){
					$thrid_menu[$v['menu_id']][] = $v2 ;
				}
	
			}
		}
		$rs['second'] = $second_menu;
		$rs['thrid'] = $thrid_menu;
		return $rs;
	}
	
}
?>