<?php
set_time_limit(600);
header("Content-type:text/html;charset=utf-8");
class DailyOrderGoodsCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        

        $goodsOption = "";
        $allGoods = GoodsModel::db()->getAll(1);
        foreach($allGoods as $k=>$v){
            $goodsOption .= "<option value='{$v['id']}'>{$v['name']}</option>";
        }

        $adminUserOption = AdminUserModel::getOption();

        $statusDesc = OrderGoodsModel::getStatusDesc();

        $this->assign("statusDesc",$statusDesc);
        $this->assign("goodsOption",$goodsOption);
        $this->assign("adminUserOption",$adminUserOption);


        $this->display("daily/order_goods_list.html");
    }

    function getWhere(){
        $where = " 1 ";
        if($id = _g("id"))
            $where .= " and id = $id ";

        if($goods_id = _g("goods_id"))
            $where .= " and goods_id = $goods_id";

        if($admin_uid = _g("admin_uid"))
            $where .= " and admin_uid=$admin_uid";


        if($status = _g("status"))
            $where .= " and status=$status";


        if($from = _g("from")){
            $from .= ":00";
            $where .= " and add_time >= '".strtotime($from)."'";
        }

        if($to = _g("to")){
            $to .= ":59";
            $where .= " and add_time <= '".strtotime($to)."'";
        }


        return $where;
    }


    function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();


        $cnt = OrderGoodsModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";


            $sort = array(
                '',
                'id',
                '',
                '',
                '',
                '',
                'status',
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

            $data = OrderGoodsModel::db()->getAll($where . $order);


            foreach($data as $k=>$v){
                $goods = GoodsModel::db()->getById($v['goods_id']);
                $nickname = $this->userService->getFieldById($v['uid'],'nickname');

                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $goods['name']."(".$v['goods_id'].")",
                    $nickname."(".$v['uid'].")",
                    $v['point'],
                    $v['email'],
                    OrderGoodsModel::getStatusDescByKey($v['status']),
                    $v['memo'],
                    AdminUserModel::getFieldById($v['admin_uid'],'nickname'),
                    get_default_date($v['a_time']),
                    '<a href="/dailyOrderGoods/edit/id='.$v['id'].'" class="btn btn-xs default red" data-id="'.$v['id'].'" target="_blank"><i class="fa fa-pencil-square-o"></i> 编辑</a>',
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

    function edit($id){
        if(!$id){
            exit("id为空");
        }

        $info = OrderGoodsModel::db()->getById($id);
        if(!$info){
            exit("ID 不在DB中");
        }

        $opt = _g("opt");
        if($opt == 'ok'){
            $data =array(
                'status'=>OrderGoodsModel::$_status_wait_send,
                'admin_uid'=>$this->_adminid,
            );
//
            OrderGoodsModel::db()->upById($info['id'],$data);
//
            out_ajax(200,$data);
        }elseif($opt == 'deny' || $opt == "send"){
            $memo = _g('memo');
            if(!$memo){
                out_ajax(500,'memo is null');
            }

            $status = OrderGoodsModel::$_status_deny;
            if( $opt == "send"){
                $status = OrderGoodsModel::$_status_finish;
            }

            $data =array(
                'status'=>$status,
                'admin_uid'=>$this->_adminid,
                'memo'=>$memo,
            );

            OrderGoodsModel::db()->upById($info['id'],$data);

            out_ajax(200);
        }

        $goods = GoodsModel::db()->getById($info['goods_id']);


        $info['goods_info'] = $goods['name'] ."-".$goods['point'] ."-" .$goods['dollar'];

        $info['status_desc'] = OrderGoodsModel::getStatusDescByKey($info['status']);

        $info['a_time'] = get_default_date($info['a_time']);

        $this->assign("info",$info);

        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->addHookJS("daily/order_goods_edit_hook.html");

        $this->display("daily/order_goods_edit.html");

    }


}