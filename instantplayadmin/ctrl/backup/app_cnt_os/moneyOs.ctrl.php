<?php
/**
 * Class moneyOsCtrl
 */
class moneyOsCtrl extends BaseCtrl{

    function index(){
        $this->addCss("/assets/open/css/game-detail.css?1");
        $this->assign("statusDesc", BannerModel::getStatusDesc());
        if(_g('getList')){
            $this->getList();
        }
        $this->display("app_cnt_os/show.html");

    }

    function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();
        $sql = "select count(*) as cnt from money_os_config";

        $cntSql = moneyOsConfigModel::db()->getRowBySQL($sql);

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


            $sql = "select * from money_os_config where {$where} order by $order limit $iDisplayStart, $iDisplayLength ";

            $data = moneyOsConfigModel::db()->getAllBySQL($sql);
            foreach($data as $k=>$v){
                // 获取图片地址兼容新老版本;
                $imgurl = $this->getImgUrl($v['img_url']);
                $records["data"][] = array(
                    $v['id'],
                    $v['gift_card_name'],
                    $v['gift_card_value'],
                    $v['gift_desc'],
                    $v['change_gold'],
                    $imgurl,
                    '有效',
                    '<a href="#" class="btn btn-circle red btn-sm" onclick="one_del(this)" data-id="'.$v['id'].'"><i class="fa fa-trash-o"></i> 删除</a>'.'<a href="#"  class="btn btn-circle blue btn-sm" onclick="editNew(this)" data-id="'.$v['id'].'"><i class="fa fa-edit"></i> 修改</a>',
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

        $res = moneyOsConfigModel::db()->delById($id);
        if(!$res){
            $this->outputJson(2, 'db error');
        }

        $this->outputJson(200, 'succ');
    }

    function upOne(){
        $id = _g('id');
        if(!$id){
            $this->outputJson(2, 'update error');
        }
        $update = [];

        $uploadService = new UploadService();
        $imgs = $uploadService->uploadFileByApp("img1", "gift", "", 1);
        $img_url = $imgs['msg'];
        if($img_url && 200 == $imgs['code']){
            $update['img_url'] = $img_url;
        }
        $gift_card_name = _g('gift_card_name');
        $gift_card_value = _g('gift_card_value');
        $change_gold = _g('change_gold');
        $gift_desc = _g('gift_desc');
        if($gift_card_name){
            $update['gift_card_name'] = $gift_card_name;
        }
        if($gift_card_name){
            $update['gift_card_value'] = $gift_card_value;
        }
        if($gift_card_name){
            $update['change_gold'] = $change_gold;
        }
        if($gift_card_name){
            $update['gift_desc'] = $gift_desc;
        }
        $update['u_time'] = time();
        $res = moneyOsConfigModel::db()->update($update, "id=$id limit 1");
        if(!$res){
            $this->outputJson(2, 'update error');
        }
        $this->outputJson(200, 'succ');
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

    function getOne(){
        $id = _g('id');
        if($this->isIllegal($id)){
            $this->outputJson(1, '缺少参数');
        }

        $sql = "select * from money_os_config";
        $items = moneyOsConfigModel::db()->getAllBySQL($sql);

        $res = moneyOsConfigModel::db()->getRowById($id);
        $res['start_launch_time'] = date('Y-m-d H:i:s',$res['start_launch_time']);
        $res['end_launch_time'] = date('Y-m-d H:i:s',$res['end_launch_time']);

        $res['img'] = $this->getStaticFileUrl('banner', $res['img']);
        $this->outputJson(200, 'succ', ['data1'=>$res,'data2'=>$items]);
    }

    function getBannerColumn(){
        $sql = "select * from banner_column";
        $items = BannerModel::db()->getAllBySQL($sql);
        if(!$items){
            $this->outputJson(200,'');
        }

        $this->outputJson(200, 'succ', $items);
    }

    function getPopularizationList(){
        $all = BannerModel::db()->getAll();
        $this->outputJson(200, 'succ', $all);
    }

    function addPopularizationCol(){
        $add = [];
        $res = BannerModel::db()->add($add, 'banner_column');
        $this->outputJson(200, 'succ', $res);
    }

    function addOne(){
        $gift_card_name = _g('gift_card_name');
        $gift_card_value = _g('gift_card_value');
        $gift_desc = _g('gift_desc');
        $change_gold = _g('change_gold');
        if(_g('img') == 'undefined'){
            $this->outputJson(2, '缺少图片');
        }
        $uploadService = new UploadService();
        $imgs = $uploadService->uploadFileByApp("img", "gift", "", 1);
        $img_url = $imgs['msg'];

        $add = [];
        $add['gift_card_id'] = '';
        $add['gift_card_name'] = $gift_card_name;
        $add['gift_card_value'] = $gift_card_value;
        $add['gift_desc'] = $gift_desc;
        $add['change_gold'] = $change_gold;
        $add['img_url'] = $img_url;
        $add['status'] = 1;
        $add['a_time'] = time();
        $add['u_time'] = time();
        $res = moneyOsConfigModel::db()->add($add);
        if(!$res){
            $this->outputJson(2, 'add error');
        }

        $this->outputJson(200, 'succ');


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
        $this->outputJson(200, 'succ', $returnData);
    }

    private function isIllegal($a){
        return (!$a || $a == 'undefinded');
    }

    private function getWhere(){
        $where = " 1 ";
        if($game_name = _g("game_name"))
            $where .= " and game_name like '%$game_name%'";
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
        $uploadService = new UploadService();
        $result = substr($imgUrl,0,strrpos($imgUrl,"/"));
        $imgurl = '<img style="weight:40px;height:40px;" src="'.get_static_file_url_by_app('gift', $imgUrl).'"></img>';
        return $imgurl;
    }

}