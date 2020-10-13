<?php
set_time_limit(600);
header("Content-type:text/html;charset=utf-8");
class RoleCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $this->display("/system/role_list.html");
    }


    function getList(){
        $this->getData();
    }



    function getData(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getDataListTableWhere();

        $cnt = RolesModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";


            $sort = array(
                'id',
                'id',
                '',
                '',
                '',
                '',
                'add_time',
            );
            $order = " order by ". $sort[$order_column]." ".$order_dir;

            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始


            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            $data = RolesModel::db()->getAll($where . $order);

            foreach($data as $k=>$v){
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['name'],
                    $v['power'],
//                    get_default_date($v['a_time']),

                    "",
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

    function getDataListTableWhere(){
        $where = 1;
        $name = _g("name");
//        $sex = _g("sex");
//        $status = _g("status");
//
//        $nickname = _g('name');

//        $from = _g("from");
//        $to = _g("to");

        $id = _g("id");
        if($id)
            $where .=" and id = '$id' ";

        if($name)
            $where .=" and name like '%$name%' ";

//        if($sex)
//            $where .=" and sex = '$sex' ";
//
//        if($status)
//            $where .=" and status = '$status' ";
//
//        if($nickname)
//            $where .=" and nickname = '$nickname' ";
//
//        if($from)
//            $where .=" and a_time >=  ".strtotime($from);
//
//        if($to)
//            $where .=" and a_time <= ".strtotime($to);

        return $where;
    }

    function add(){
        $this->addCss('/assets/admin/pages/css/news.css');

        $this->assign('menus', MenuModel::getMenu());
        $roles = RolesModel::db()->getAll();
        $this->assign('roles', $roles);

        $this->display("/system/role_add.html");
    }

    function addSave()
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