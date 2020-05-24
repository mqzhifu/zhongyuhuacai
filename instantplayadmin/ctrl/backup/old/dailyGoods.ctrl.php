<?php
set_time_limit(600);
header("Content-type:text/html;charset=utf-8");
class DailyGoodsCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }
        $typeOption = GoodsModel::getTypeDescOption();
        $this->assign("typeOption",$typeOption);

        $this->assign("onlineOption",GoodsModel::getOnlineDescOption());

        $this->display("daily/goods_list.html");
    }


    function getList(){
        $this->getData();
    }

    function getWhere(){
        $where = " 1 ";
        if($id = _g("id"))
            $where .= " and id = $id ";

        if($name = _g("name"))
            $where .= " and name like '%$name%'";

        if($type = _g("type"))
            $where .= " and type=$type";


        if($sort = _g("sort"))
            $where .= " and sort=$sort";

        if($is_online = _g("is_online"))
            $where .= " and is_online=$is_online";


        return $where;
    }


    function getData(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();

        $cnt = GoodsModel::db()->getCount($where);

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
                'stock',
                '',
                'type',
                '',
                'sort',
                'is_online'
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

            $data = GoodsModel::db()->getAll($where . $order);

            foreach($data as $k=>$v){
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['name'],
                    $v['summary'],
                    $v['point'],
                    $v['stock'],
                    "<img src='".get_img_url_by_app($v['img'])."' width=50 height=50 /> ",
                    GoodsModel::getTypeDescByKey($v['type']),
                    $v['dollar'],
                    $v['sort'],
                    GoodsModel::getOnlineDescByKey($v['is_online']),
                    '<a href="/dailyGoods/edit/id='.$v['id'].'" class="btn btn-xs default red" data-id="'.$v['id'].'" target="_blank"><i class="fa fa-pencil-square-o"></i> 编辑</a>',
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


    function add(){
        if(_g("opt")){
            $name = _g("name");
            $summary = _g("summary");
            $point = _g("point");
            $stock = _g("stock");
            $type = _g("type");
            $dollar = _g("dollar");
            $is_online = _g("is_online");
            $sort = _g("sort");

            if(!$name){
                exit("名称-不能为空");
            }

            if(!$point){
                exit("积分-不能为空");
            }

            if(!$stock){
                exit("库存-不能为空");
            }

            if(!$type){
                exit("类型-不能为空");
            }

            if(!$dollar){
                exit("美元-不能为空");
            }


            $imgLib = new ImageUpLoadLib();
            $imgLib->hash = 0;
            $imgLib->module = "games";

            $rs = $imgLib->upLoadOneFile('img');
            if($rs['code'] != 200){
                exit("图片-不能为空");
            }

            $img = $rs['msg'];


            $data = array(
                'name'=>$name,'summary'=>$summary,'point'=>$point,'stock'=>$stock, 'type'=>$type,'dollar'=>$dollar,'img'=>$img,
                'is_online'=>GoodsModel::$_online_false,
                'sort'=>$sort,
                'is_online'=>$is_online,
            );

            GoodsModel::db()->add($data);


            echo "<script>alert('ok');location.href='/dailyGoods/index/';</script>";



        }

        $this->assign("onlineOption",GoodsModel::getOnlineDescOption( ));

        $typeOption = GoodsModel::getTypeDescOption();
        $this->assign("typeOption",$typeOption);

        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->addHookJS("daily/goods_add_hook.html");
        $this->display("daily/goods_add.html");
    }

    function edit($id){
        if(!$id){
            exit("id为空");
        }

        $info = GoodsModel::db()->getById($id);
        if(!$info){
            exit("ID 不在DB中");
        }
        if(_g("opt")){
            $name = _g("name");
            $summary = _g("summary");
            $point = _g("point");
            $stock = _g("stock");
            $type = _g("type");
            $dollar = _g("dollar");
            $sort = _g("sort");
            $isOnline = _g("is_online");


            if(!$name){
                exit("名称-不能为空");
            }

            if(!$point){
                exit("积分-不能为空");
            }

            if(!$stock){
                exit("库存-不能为空");
            }

            if(!$type){
                exit("类型-不能为空");
            }

            if(!$dollar){
                exit("美元-不能为空");
            }


            $data = array(
                'name'=>$name,'summary'=>$summary,'point'=>$point,'stock'=>$stock,'type'=>$type,'dollar'=>$dollar,
                'sort'=>$sort,
                'is_online'=>$isOnline,
            );

            $imgLib = new ImageUpLoadLib();
            $imgLib->hash = 0;
            $imgLib->module = "games";

            $rs = $imgLib->upLoadOneFile('img');
            if($rs['code'] == 200){
                $data['img'] = $rs['msg'];
            }

            GoodsModel::db()->upById($info['id'],$data);


            echo "<script>alert('ok');location.href='/dailyGoods/index/';</script>";



        }

        $info['img'] = get_img_url_by_app($info['img']);

        $this->assign("onlineOption",GoodsModel::getOnlineDescOption($info['is_online']));

        $this->assign("info",$info);

        $typeOption = GoodsModel::getTypeDescOption($info['type']);
        $this->assign("typeOption",$typeOption);

        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->addHookJS("daily/goods_edit_hook.html");
        $this->display("daily/goods_edit.html");
    }


}