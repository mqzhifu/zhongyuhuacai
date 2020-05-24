<?php

/**
 * @Author: Kir
 * @Date:   2019-03-20 14:41:51
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-03-25 15:48:16
 */


/**
 * 
 */
class PowerCtrl extends BaseCtrl
{
	
	function index()
	{
		$this->addCss('/assets/admin/pages/css/news.css');

		$this->assign('menus', MenuModel::getMenu());

		$roles = RolesModel::db()->getAll();
		$this->assign('roles', $roles);
		$this->display("/power/user/role_setting.html");
	}


	function search()
	{
		$id = _g('id');

		if (!$role = RolesModel::db()->getRowById($id)) {
        	exit(0);
		}
		if ($role['power'] == 'all') {
			$roles = MenuModel::db()->getAll();
			$role['power'] = array_column($roles, 'id');
		}
		else {
			$role['power'] = explode(",", $role['power']);
		}
		$this->assign('powers', $role['power']);
		$this->assign('select_id', $id);
		$this->index();
	}

	function save()
	{
		$roleId = _g('role_id');
		$ids = _g('ids');
		$ids = implode(",", $ids);

		if (!$role = RolesModel::db()->getRowById($roleId)) {
        	exit(0);
		}
		if (RolesModel::db()->upById($roleId, ['power'=>$ids])) {
			echo json_encode(200);
        	exit;
		}
		exit(0);
	}

	function newRole()
	{
		$this->addCss('/assets/admin/pages/css/news.css');

		$this->assign('menus', MenuModel::getMenu());

		$roles = RolesModel::db()->getAll();
		$this->assign('roles', $roles);
		$this->display("/power/user/new_role.html");
	}

	function newRoleSave()
	{
		$roleName = _g('role_name');
		$ids = _g('ids');
		$ids = implode(",", $ids);

		if (RolesModel::db()->getRow(" name='$roleName'")) {
        	exit(0);
		}
		if (RolesModel::db()->add(['name'=>$roleName, 'power'=>$ids])) {
			echo json_encode(200);
        	exit;
		}
		exit(0);
	}
}