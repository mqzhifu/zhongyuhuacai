<?php
class FactoryCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $this->assign("statusOptions",FactoryModel::getStatusOptions());
        $this->display("/people/factory_list.html");
    }

    function add(){
        if(_g('opt')){
            $data =array(
                'title'=> _g('title'),
                'real_name'=> _g('real_name'),
                'id_card_num'=> _g('id_card_num'),
                'mobile'=> _g('mobile'),
                'category'=>_g("category"),
                'status'=>FactoryModel::STATUS_WAIT,
                'a_time'=>time(),
            );

            if(!$data['title']){
                $this->notice("工厂名 不能为空 ");
            }

            if(!$data['real_name']){
                $this->notice("真实姓名 不能为空 ");
            }

            if(!$data['id_card_num']){
                $this->notice("身份证号 不能为空 ");
            }

            if(!$data['category']){
                $this->notice("类别 不能为空 ");
            }

            if(!$data['mobile']){
                $this->notice("手机号 不能为空 ");
            }

            if(!FilterLib::regex($data['mobile'],"phone")){
                $this->notice("手机号格式错误 ");
            }

            $uploadService = new UploadService();
            $uploadRs = $uploadService->factory('pic');
            if($uploadRs['code'] != 200){
                exit(" uploadService->product error ".json_encode($uploadRs));
            }

            $data['pic'] = $uploadRs['msg'];

            $newId = FactoryModel::add($data);
            $this->ok($newId,"",$this->_backListUrl);
        }

        $cityJs = json_encode(AreaCityModel::getJsSelectOptions());
        $countryJs = json_encode(AreaCountyModel::getJsSelectOptions());

        $this->assign("provinceOption",AreaProvinceModel::getSelectOptionsHtml());
        $this->assign("cityJs",$cityJs);
        $this->assign("countyJs",$countryJs);


//        $this->assign("sexOptions",FactoryModel::)

        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->addHookJS("/people/factory_add_hook.html");
        $this->addHookJS("/layout/file_upload.js.html");
        $this->display("/people/factory_add.html");
    }

    function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getDataListTableWhere();

        $cnt = FactoryModel::db()->getCount($where);

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
                'a_time',
                "status",
                "",
                "",
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

            $data = FactoryModel::db()->getAll($where . $order);

            foreach($data as $k=>$v){
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['title'],
                    $v['category'],
                    $v['real_name'],
                    $v['id_card_num'],
                    $v['status'],
                    get_default_date($v['a_time']),
                    $v['mobile'],
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
        $id = _g("id");
        $title = _g("title");
        $category = _g("category");
        $real_name = _g('real_name');
        $mobile = _g('mobile');
        $from = _g("from");
        $to = _g("to");

        if($id)
            $where .=" and id = '$id' ";

        if($title)
            $where .=" and title like '%$title%' ";

        if($category)
            $where .=" and category like '%$category%' ";

        if($real_name)
            $where .=" and real_name like '%$real_name%' ";

        if($mobile)
            $where .=" and mobile = '$mobile' ";

        if($from)
            $where .=" and a_time >=  ".strtotime($from);

        if($to)
            $where .=" and a_time <= ".strtotime($to);

        return $where;
    }


}