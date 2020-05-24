<?php
set_time_limit(600);
header("Content-type:text/html;charset=utf-8");
class SocialFansCtrl extends BaseCtrl{
    function index(){
        $this->assign("categoryOption",GamesCategoryModel::getOptions());


//        $this->assign("recommendNewOption",GamesModel::getRecommendNewOption());
//        $this->assign("statusDesc",GamesModel::getStatusDesc());
//        $this->assign("onlineDesc",GamesModel::getOnlineDesc());
//        $this->assign("recommendImInviteDesc",GamesModel::getRecommendImInviteDesc());
//        $this->assign("recommendIndexDesc",GamesModel::getRecommendIndexDesc());
//        $this->assign("screenDesc",GamesModel::getScreenDesc());

        $this->display("social/fans_list.html");
    }

    function getList(){
        $this->getData();
    }


    function getWhere(){
        $where = " 1 ";
        if($id = _g("id"))
            $where .= " and id = $id ";

        if($uid = _g("uid"))
            $where .= " and uid = $uid ";

        if($to_uid = _g("to_uid"))
            $where .= " and to_uid = $to_uid ";


        if($from = _g("from")){
            $from .= ":00";
            $where .= " and a_time >= '".strtotime($from)."'";
        }

        if($to = _g("to")){
            $to .= ":59";
            $where .= " and a_time <= '".strtotime($to)."'";
        }

        return $where;
    }


    function getData(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();

        $cnt = FansModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";


            $sort = array(
                '',
                'id',
                'uid',
                'to_uid',
                'a_time',
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

//            var_dump($where);exit;
            $data = FansModel::db()->getAll($where . $order);

            foreach($data as $k=>$v){
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['uid'],
                    $v['to_uid'],
                    get_default_date($v['a_time']),
                    '',
//                    '<a href="/user/showDetail/uid='.$v['id'].'" class="btn btn-xs default yellow" data-id="'.$v['id'].'" target="_blank"><i class="fa fa-folder-open"></i> 详情</a>'.
//                    '<a href="/dailyGame/edit/id='.$v['id'].'" class="btn btn-xs default red" data-id="'.$v['id'].'" target="_blank"><i class="fa fa-pencil-square-o"></i> 编辑</a>',

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
            $imgLib = new ImageUpLoadLib();
            $imgLib->hash = 0;
            $imgLib->module = "games";

            $rs = $imgLib->upLoadOneFile('small_img');
            if($rs['code'] != 200){
                exit($rs['msg']);
            }
            $small_img = $rs['msg'];

            $rs = $imgLib->upLoadOneFile('list_img');
            if($rs['code'] != 200){
                exit($rs['msg']);
            }
            $list_img = $rs['msg'];

            $rs = $imgLib->upLoadOneFile('index_reco_img');
            if($rs['code'] != 200){
                exit($rs['msg']);
            }
            $index_reco_img = $rs['msg'];

            $name = _g("name");
            $category = _g("category");


            $recommend_index = _g("recommend_index");
            $recommend_new = _g("recommend_new");
            $recommend_im_invite = _g("recommend_im_invite");
            $screen = _g("screen");
            $sort = _g("sort");
            $summary = _g("summary");
            $background_color = _g("background_color");
            $play_url = _g("play_url");
            $open_method = _g("open_method");
            $is_online = _g("is_online");

            if(!$name){
                exit("名称不能为空");
            }

            if(!$category){
                exit("分类不能为空");
            }

            if(!$screen){
                exit("屏幕不能为空");
            }

            if(!$sort){
                exit("排序不能为空");
            }

//            if(!$summary){
//                exit("简介不能为空");
//            }

            if(!$background_color){
                exit("背景色不能为空");
            }

            if(!$is_online){
                exit("在线状态不能为空");
            }

            if(!$play_url){
                exit("游戏地址不能为空");
            }

            if($recommend_index){
                $recommend_index = 1;
            }else{
                $recommend_index = 2;
            }

            if($recommend_im_invite){
                $recommend_im_invite = 1;
            }else{
                $recommend_im_invite = 2;
            }

            if($recommend_new){
                $recommend_new = 1;
            }else{
                $recommend_new = 2;
            }



            $data = array(
                'name'=>$name,
                'category'=>$category,
                'small_img'=>$small_img,
                'list_img'=>$list_img,
                'index_reco_img'=>$index_reco_img,
                'recommend_index'=>$recommend_index,
                'recommend_im_invite'=>$recommend_im_invite,
                'recommend_new'=>$recommend_new,
                'screen'=>$screen,
                'sort'=>$sort,
                'summary'=>$summary,
                'background_color'=>$background_color,
                'play_url'=>$play_url,
                'open_method'=>$open_method,
                'is_online'=>$is_online,
            );

//            var_dump($data);

            $rs = GamesModel::db()->add($data);
//            var_dump($rs);exit;


            $uid = $this->_sess->getValue('id');

//            $user = AdminUserModel::db()->getRow(" id = $uid");
//            if($user['ps'] != md5($old_ps) ){
//                exit('原始密码错误');
//            }
//            AdminUserModel::db()->update(array('ps'=>md5($ps) )," id = $uid limit 1 ");
//            $this->_sess->none();
            echo "<script>alert('ok');location.href='/dailyGame/index/';</script>";
//            jump("/");
        }
        $this->assign("statusDesc",GamesModel::getStatusDesc());
        $this->assign("onlineDesc",GamesModel::getOnlineDesc());
        $this->assign("recommendImInviteDesc",GamesModel::getRecommendImInviteDesc());
        $this->assign("recommendIndexDesc",GamesModel::getRecommendIndexDesc());
        $this->assign("screenDesc",GamesModel::getScreenDesc());

//        kaixinContext

        $str = GamesCategoryModel::getOptions();

        $this->assign("categoryOption",$str);

        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->addHookJS("daily/game_add_hook.html");

        $this->display("daily/game_add.html");

    }

    function edit($id){
        if(!$id){
            exit("id为空");
        }

        $info = GamesModel::db()->getById($id);
        if(!$info){
            exit("ID 不在DB中");
        }

        if(_g("opt")){
            $name = _g("name");
            $category = _g("category");

            $recommend_index = _g("recommend_index");
            $recommend_new = _g('recommend_new');
            $recommend_im_invite = _g("recommend_im_invite");
            $screen = _g("screen");
            $sort = _g("sort");
            $summary = _g("summary");
            $background_color = _g("background_color");
            $play_url = _g("play_url");
            $open_method = _g("open_method");
            $is_online = _g("is_online");

            $data = array();


            $imgLib = new ImageUpLoadLib();
            $imgLib->hash = 0;
            $imgLib->module = "games";

            $rs = $imgLib->upLoadOneFile('small_img');
            if($rs['code'] == 200){
                $data['small_img'] = $rs['msg'];
            }

            $rs = $imgLib->upLoadOneFile('list_img');
            if($rs['code'] == 200){
                $data['list_img'] = $rs['msg'];
            }

            $rs = $imgLib->upLoadOneFile('index_reco_img');
            if($rs['code'] == 200){
                $data['index_reco_img'] = $rs['msg'];
            }


            if($name){
                $data['name'] = $name;
            }

            if($category){
                $data['category'] = $category;
            }

            if($screen){
                $data['screen'] = $screen;
            }

            if($sort){
                $data['sort'] = $sort;
            }

            if($background_color){
                $data['background_color'] = $background_color;
            }


            if($summary){
                $data['summary'] = $summary;
            }

            if($is_online){
                $data['is_online'] = $is_online;
            }

            if($play_url){
                $data['play_url'] = $play_url;
            }

            if($open_method){
                $data['open_method'] = $open_method;
            }

            if($recommend_index){
                $recommend_index = 1;
            }else{
                $recommend_index = 2;
            }

            $data['recommend_index'] = $recommend_index;

            if($recommend_im_invite){
                $recommend_im_invite = 1;
            }else{
                $recommend_im_invite = 2;
            }

            $data['recommend_im_invite'] = $recommend_im_invite;


            if($recommend_new){
                $recommend_new = 1;
            }else{
                $recommend_new = 2;
            }
            $data['recommend_new'] = $recommend_new;

//            var_dump($data);exit;

            $rs = GamesModel::db()->upById($info['id'],$data);
//            var_dump($rs);exit;


            $uid = $this->_sess->getValue('id');

//            $user = AdminUserModel::db()->getRow(" id = $uid");
//            if($user['ps'] != md5($old_ps) ){
//                exit('原始密码错误');
//            }
//            AdminUserModel::db()->update(array('ps'=>md5($ps) )," id = $uid limit 1 ");
//            $this->_sess->none();
            echo "<script>alert('ok');location.href='/dailyGame/index/';</script>";
//            jump("/");
        }

        $info['small_img'] = get_img_url_by_app($info['small_img']);
        $info['list_img'] = get_img_url_by_app($info['list_img']);
        $info['index_reco_img'] = get_img_url_by_app($info['index_reco_img']);


        $this->assign("info",$info);

        $this->assign("statusDesc",GamesModel::getStatusDesc());
        $this->assign("onlineDesc",GamesModel::getOnlineDesc());
        $this->assign("recommendImInviteDesc",GamesModel::getRecommendImInviteDesc());
        $this->assign("recommendIndexDesc",GamesModel::getRecommendIndexDesc());
        $this->assign("screenDesc",GamesModel::getScreenDesc());

//        kaixinContext

        $str = GamesCategoryModel::getOptions($info['category']);

        $this->assign("categoryOption",$str);

        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->addHookJS("daily/game_edit_hook.html");

        $this->display("daily/game_edit.html");

    }

}