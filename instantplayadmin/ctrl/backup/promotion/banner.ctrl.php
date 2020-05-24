<?php

/**
 * @Author: xuren
 * @Date:   2019-03-25 11:15:33
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-05-24 10:38:14
 */
class BannerCtrl extends BaseCtrl{


    function index(){
        $this->addCss("/assets/open/css/game-detail.css?1");
        $this->assign("statusDesc", BannerModel::getStatusDesc());
        if(_g('getList')){
            $this->getList();
        }
        $status_all = bannerLocationModel::db()->getAll();
        $id = array_column($status_all, 'id');
        $name = array_column($status_all, 'name');
        $status_all = array_combine($id, $name);
        $status_skip_all = bannerSkipModel::db()->getAll();
        $id1 = array_column($status_skip_all, 'id');
        $name1 = array_column($status_skip_all, 'name');
        $status_skip_all = array_combine($id1, $name1);
        $this->assign('status_all', $status_all);
        $this->assign('status_skip', $status_skip_all);
        $this->display("promotion/banner.html");

    }

    function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();
        $sql = "select count(*) as cnt from banner";
        $cntSql = BannerModel::db()->getRowBySQL($sql);

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
                'id',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            );
            $order = $sort[$order_column]." ".$order_dir;

            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始


            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;


            $sql = "select * from banner where $where GROUP BY id order by $order limit $iDisplayStart,$end ";

            $data = BannerModel::db()->getAllBySQL($sql);
            foreach($data as $k=>$v){
                $game_name = ($v['game_name'])?$v['game_name']:'-';
                $game_id = ($v['game_id'])?$v['game_id']:'-';
                // 获取图片地址兼容新老版本;
                $imgurl = $this->getImgUrl($v['img']);
                $locationInfo = bannerLocationModel::db()->getById($v['banner_location']);
                $skipInfo = bannerSkipModel::db()->getById($v['banner_skip']);
                $records["data"][] = array(
                    $v['id'],
                    $v['name'],
                    $game_name,
                    $game_id,
                    $locationInfo['name'],
                    $skipInfo['name'],
                    $v['weight'],
                    $imgurl,
                    $v['app_version'],
                    date("Y-m-d", $v['start_launch_time']),
                    date("Y-m-d", $v['end_launch_time']),
                    BannerModel::getDescByStatus($this->checkLaunchStatus($v['start_launch_time'], $v['end_launch_time'])),
                    '<a href="#" class="btn btn-circle red btn-sm" onclick="one_del(this)" data-id="'.$v['id'].'"><i class="fa fa-trash-o"></i> 删除</a>'.'<a href="#"  class="btn btn-circle blue btn-sm" onclick="edit(this)" data-id="'.$v['id'].'"><i class="fa fa-edit"></i> 修改</a>',
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
    }

    function delone(){
        $id = _g('id');
        if(!$id || $id=='undefinded'){
            $this->outputJson(1, '参数不正确');
        }

        $res = BannerModel::db()->delById($id);
        if(!$res){
            $this->outputJson(2, 'db error');
        }

        $this->outputJson(200, '操作成功！');
    }

    function updateOne(){
        $id = _g('id');
        $game_id = _g('game_id');
        $name = _g('name');
        $app_version = _g('app_version');
        $start_launch_time = _g('start_launch_time');
        $end_launch_time = _g('end_launch_time');
        $banner_column = 0;// 暂时去掉轮播图逻辑，给定默认值为0;
        $banner_location = _g('banner_location');
        $banner_skip = _g('banner_skip');
        $weight = _g('weight');
        $img_link = '';



        $update = [];
        $uploadService = new UploadService();
        $imgs = $uploadService->uploadFileByApp("img", "banner", "", 1);
        if(200 == $imgs['code']){
            $update['img'] = $imgs['msg'];
        }

        if(isset($game_id) && !empty($game_id)){
            $row = GamesModel::db()->getRow('id="'.$game_id.'"');
            if(!$row){
                $this->outputJson(4,'game_id非法');
            }
        }else{
            $row['id'] = 0;
            $row['name'] = '';
        }

        if($banner_location){
            $update['banner_location'] = $banner_location;
        }

        if($banner_skip){
            $update['banner_skip'] = $banner_skip;
        }

        if($banner_skip){
            $update['app_version'] = $app_version;
        }

        $update['name'] = $name;
        $update['game_id'] = $row['id'];
        $update['img_link'] = $img_link;
        $update['b_id'] = $banner_column;
        $update['weight'] = $weight;
        $update['game_name'] = $row['name'];
        $update['start_launch_time'] = strtotime($start_launch_time);
        $update['end_launch_time'] = strtotime($end_launch_time);
        $update['is_relative'] = '';
        $update['relative_path'] = '';
        $update['status'] = $this->checkLaunchStatus($update['start_launch_time'], $update['end_launch_time']);
        $res = BannerModel::db()->update($update, "id=$id limit 1");
        if(!$res){
            $this->outputJson(2, '操作失败！');
        }

        $this->outputJson(200, '操作成功！');
    }

    private function checkLaunchStatus($startTime, $endTime){
        $now  = time();
        if($now < $startTime){
            return BannerModel::$status_launching;
        }else if($now >= $startTime && $now <= $endTime){
            return BannerModel::$status_launching;
        }else{
            return BannerModel::$status_unlaunched;
        }
        return BannerModel::$status_unlaunched;
    }

    function getOneBanner(){
        $id = _g('id');
        if($this->isIllegal($id)){
            $this->outputJson(1, '缺少参数');
        }

        $sql = "select * from banner_column";
        $items = BannerModel::db()->getAllBySQL($sql);

        $res = BannerModel::db()->getRowById($id);
        $res['start_launch_time'] = date('Y-m-d',$res['start_launch_time']);
        $res['end_launch_time'] = date('Y-m-d',$res['end_launch_time']);

        $res['img'] = $this->getStaticFileUrl('banner', $res['img']);
        $this->outputJson(200, '操作成功！', ['data1'=>$res,'data2'=>$items]);
    }

    function getBannerColumn(){
        $sql = "select * from banner_column";
        $items = BannerModel::db()->getAllBySQL($sql);
        if(!$items){
            $this->outputJson(200,'');
        }

        $this->outputJson(200, '操作成功！', $items);
    }

    function getPopularizationList(){
        $all = BannerModel::db()->getAll();
        $this->outputJson(200, '操作成功！', $all);
    }

    function addPopularizationCol(){
        $add = [];
        $res = BannerModel::db()->add($add, 'banner_column');
        $this->outputJson(200, '操作成功！', $res);
    }

    function addOne(){
        $game_id = _g('game_id');
        $name = _g('name');
        $banner_location = _g('banner_location');
        $banner_skip = _g('banner_skip');
        $start_launch_time = _g('start_launch_time');
        $end_launch_time = _g('end_launch_time');
        $app_version = _g('app_version');
        //$banner_column = _g('banner_column');
        $banner_column = 0;// 暂时去掉推广位逻辑,默认值0;
        $weight = 0;
        $img_link = '';

        $is_relative = '';
        $relative_path = '';
        if(_g('img') == 'undefined'){
            $this->outputJson(2, '缺少图片');
        }
        $img_url = '';
        $uploadService = new UploadService();
//			$imgtype = array('bmp','png','jpeg','jpg');
        $imgs = $uploadService->uploadFileByApp("img", "banner", "", 1);
        $img_url = $imgs['msg'];

        if(isset($game_id) && !empty($game_id)){
            $row = GamesModel::db()->getRow('id="'.$game_id.'"');
            if(!$row){
                $this->outputJson(4,'game_id非法');
            }
        }else{
            $row['id'] = 0;
            $row['name'] = null;
        }
        $relative_path = (!empty($relative_path))?$relative_path:0;
        if(2 == $is_relative){
            $relative_path = 0;
        }

        $add = [];
        $add['name'] = $name;
        $add['game_id'] = $row['id'];
        $add['img_link'] = $img_link;
        $add['img'] = $img_url;
        $add['b_id'] = $banner_column;
        $add['weight'] = $weight;
        $add['game_name'] = $row['name'];
        $add['start_launch_time'] = strtotime($start_launch_time);
        $add['end_launch_time'] = strtotime($end_launch_time);
        $add['is_relative'] = $is_relative;
        $add['relative_path'] = $relative_path;
        $add['status'] = $this->checkLaunchStatus($add['start_launch_time'], $add['end_launch_time']);
        $add['banner_location'] = $banner_location;
        $add['banner_skip'] = $banner_skip;
        $add['app_version'] = $app_version;
        $res = BannerModel::db()->add($add);
        if(!$res){
            $this->outputJson(2, 'add error');
        }

        $this->outputJson(200, '操作成功！');


    }

    function getGames(){
        $where = 'limit 10';
        if($a = _g('where')){
            $where = "name like '%".$a."%' ".$where;
        }

        $list = GamesModel::db()->getAll($where);
        $returnData = [];
        foreach ($list as $value) {
            $returnData[] = ['id'=>$value['id'],'name'=>$value['name']];
        }
        $this->outputJson(200, '操作成功！', $returnData);
    }

    private function isIllegal($a){
        return (!$a || $a == 'undefinded');
    }

    private function getWhere(){
        $where = " 1 ";
        if($game_name = _g("game_name"))
            $where .= " and game_name like '%$game_name%'";

        if($banner_location = _g("banner_location"))
            $where .= " and banner_location = $banner_location";

        if($banner_skip = _g("banner_skip"))
            $where .= " and banner_skip = $banner_skip ";
        // if($a_time_from = _g("a_time_from")){
        //           $a_time_from .= ":00";
        //           $where .= " and a_time >= '".strtotime($a_time_from)."'";
        //       }

        //       if($a_time_to = _g("a_time_to")){
        //           $a_time_to .= ":59";
        //           $where .= " and a_time <= '".strtotime($a_time_to)."'";
        //       }

        return $where;
    }

    public function outputJson ($code, $message, $data=[])
    {
        header("Content-Type: application/json");
        echo json_encode([
            "code" => $code,
            "message" => $message,
            "data" => $data,
        ]);
        exit(0);
    }

    /**
     * @param $imgUrl
     * @return string
     */
    private function getImgUrl($imgUrl){
        //weight:40px;height:40px;
        $uploadService = new UploadService();
        $result = substr($imgUrl,0,strrpos($imgUrl,"/"));
        if('/banner' == $result){
            $imgurl = '<img id="img-bottom" class= "img-bottom" style="weight:40px;height:40px;"'.$uploadService->getStaticBaseUrl() .$imgUrl.'"></img>';
        }else{
            $imgurl = '<img id="img-bottom" class= "img-bottom" style="weight:40px;height:40px;" src="'.get_static_file_url_by_app('banner', $imgUrl).'"></img>';
        }
        return $imgurl;
    }

    public function getDetails(){
        $items = bannerLocationModel::db()->getAll();
        $returnData = [];
        $returnData['base'] = $items;
        $returnData['real_ad_data'] = $items;
        $this->outputJson(200, "成功", $returnData);
    }

    public function getDetailsSkip(){
        $items = bannerSkipModel::db()->getAll();
        $returnData = [];
        $returnData['base'] = $items;
        $returnData['real_ad_data'] = $items;
        $this->outputJson(200, "成功", $returnData);
    }


    public function delLocationOne(){
        $id = _g('id');
        $rs = bannerLocationModel::db()->delById($id);
        if(!$rs){
            $this->outputJson(3, '删除失败！');
        }else{
            $this->outputJson(200, '操作成功！');
        }
    }

    public function delSkipOne(){
        $id = _g('id');
        $rs = bannerSkipModel::db()->delById($id);
        if(!$rs){
            $this->outputJson(3, '删除失败');
        }else{
            $this->outputJson(200, '操作成功！');
        }
    }

    public function upLocationOne(){
        $id = _g('id');
        $res = bannerLocationModel::db()->getById($id);
        // 当前值如果存在进行更新操作;
        if($res){
            $name = _g('name');
            $res = bannerLocationModel::db()->upById($id, array('name'=>$name));
            if(!$res){
                $this->outputJson(3, '更新失败！');
            }else{
                $this->outputJson(200, '操作成功！');
            }
        }else{
            // 添加操作;
            $insertArray = array(
                'id' => _g('id'),
                'name' => _g('name'),
                'is_show' => 1
            );
            $rs = bannerLocationModel::db()->add($insertArray);
            if(!$rs){
                $this->outputJson(3, '创建失败！');
            }else{
                $this->outputJson(200, '操作成功！');
            }
        }
    }

    public function upSkipOne(){
        $id = _g('id');
        $res = bannerSkipModel::db()->getById($id);
        // 当前值如果存在进行更新操作;
        if($res){
            $name = _g('name');
            $res = bannerSkipModel::db()->upById($id, array('name'=>$name));
            if(!$res){
                $this->outputJson(3, '更新失败！');
            }else{
                $this->outputJson(200, '操作成功！');
            }
        }else{
            // 添加操作;
            $insertArray = array(
                'id' => _g('id'),
                'name' => _g('name'),
                'is_show' => 1
            );
            $rs = bannerSkipModel::db()->add($insertArray);
            if(!$rs){
                $this->outputJson(3, '创建失败！');
            }else{
                $this->outputJson(200, '操作成功！');
            }
        }
    }

}