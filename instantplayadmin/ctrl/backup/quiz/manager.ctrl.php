<?php
/**
 * Class managerCtrl
 */
class managerCtrl extends BaseCtrl{
    /**
     * APP统计->APP数据汇总->日数据（默认）;
     */
    public function index(){
        if(_g("getlist")){
            $this->getList();
        }
        $this->display("/quiz/manager/index.html");
    }

    public function getList()
    {
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        $result = goodsQuizModel::db()->getAll();
        $iTotalRecords = (count($result)) ? count($result) : 0;

        if (!empty($result) && is_array($result)) {
            $iDisplayLength = intval($_REQUEST['length']);
            if ('999999' == $iDisplayLength) {
                $iDisplayLength = $iTotalRecords;
            } else {
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }
            $iDisplayStart = intval($_REQUEST['start']);
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            foreach ($result as $k => $v) {
                $records["data"][] = array(
                    $v['id'],
                    get_default_date($v['a_time']),
                    get_default_date($v['u_time']),
                    $v['goods_name'],
                    $v['goods_code'],
                    $v['goods_value'],
                    $v['goods_describe'],
                    $v['is_show'] = (0 == $v['is_show'])?'无效':'有效',
                    '<a href="#" class="btn btn-xs default red delone" onclick="delone(this)" data-id="'.$v['id'].'"><i class="fa fa-trash-o"></i> 删除</a>'.'<a href="/quiz/no/manager/edit/?id='.$v['id'].'"class="btn btn-xs default green edit" onclick="edit(this)" data-id="'.$v['id'].'"><i class="fa fa-edit"></i> 修改</a>'.'<a href="/quiz/no/manager/addcard/?id='.$v['id'].'"class="btn btn-xs default blue add" onclick="edit(this)" data-id="'.$v['id'].'"><i class="fa fa-edit"></i> 添加兑换码</a>'
                );
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
        die();
    }

    /**
     * @return string
     */
    function getWhere()
    {
/*        $where = " opt = 2 ";
        if ($title = trim(_g("title"))) {
            if ($title == 1) {
                $title = '提现';
            } elseif ($title == 2) {
                $title = '金币欢乐送';
            }
            $where .= " and title = '{$title}' ";
        }
        return $where;*/
    }

    public function requestUrl ($url, $method = 'GET', $param = [])
    {
        $ch = curl_init ();

        curl_setopt ( $ch, CURLOPT_URL, $url );

        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );

        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 10 );

        curl_setopt ( $ch, CURLOPT_TIMEOUT , 2 );

        $query = http_build_query($param);

        curl_setopt( $ch, CURLOPT_POSTFIELDS, $query);

        curl_setopt ( $ch, CURLOPT_POST, 1 ); // 启用POST提交;

        $file_contents = curl_exec ( $ch );

        curl_close ( $ch );

        return $file_contents;

    }


    public function add(){
        $this->addCss("/assets/open/css/massage-set-create.css");
        $this->addCss("/assets/open/css/wickedpicker.min.css");
        $this->addJs("/assets/open/scripts/jquery.form.min.js");
        $this->addJs("/assets/open/scripts/wickedpicker.min.js");
        $this->display("/quiz/manager/add.html");
    }

    public function save(){
        $goods_name = _g('goods_name');
        $goods_value = _g('goods_value');
        $goods_describe = _g('goods_describe');
        $goods_code = _g('goods_code');
        if(_g('img') != 'undefined'){
            $uploadService = new UploadService();
            $imgtype = array('bmp','png','jpeg','jpg');
            $img_url = $uploadService->imageUpLoad("img", "/quiz", 5, $imgtype, "quiz");
        }
        $insertData = array(
            'goods_name' => $goods_name,
            'goods_value' => $goods_value,
            'goods_describe' => $goods_describe,
            'goods_code' => $goods_code,
            'goods_vectorgraph' => $img_url,
            'is_show' => 1,
            'a_time' => time(),
            'u_time' => time(),
        );
        $res = goodsShopModel::db()->add($insertData);
        if(!$res){
            $this->outputJson(2, 'update error');
        }
        $this->outputJson(200, 'succ');
    }

    public function edit(){
        $this->addCss("/assets/open/css/massage-set-create.css");
        $this->addCss("/assets/open/css/wickedpicker.min.css");
        $this->addJs("/assets/open/scripts/jquery.form.min.js");
        $this->addJs("/assets/open/scripts/wickedpicker.min.js");
        $id = _g('id');
        $res = goodsShopModel::db()->getById($id);
        $baseUrl = $this->getStaticBaseUrl();
        $res['goods_vectorgraph'] = $baseUrl.'/'.$res['goods_vectorgraph'];
        $this->assign('data', $res);
        $this->display("quiz/manager/edit.html");
    }

    public function addcard(){
        $this->addCss("/assets/open/css/massage-set-create.css");
        $this->addCss("/assets/open/css/wickedpicker.min.css");
        $this->addJs("/assets/open/scripts/jquery.form.min.js");
        $this->addJs("/assets/open/scripts/wickedpicker.min.js");
        $id = _g('id');
        $res = goodsShopModel::db()->getById($id);
        $result = goodsShopCardModel::db()->getAll( " goods_id = {$id} " );
        $this->assign('data', $res);
        $goodsCardStr = [];
        foreach ($result as $value){
            $isUse = (1 == $value['is_use'])?'已使用':'未使用';
            array_push($goodsCardStr, $value['goods_card'].'（'.$isUse.'）');
        }
        $this->assign('result', implode($goodsCardStr));
        $this->display("quiz/manager/addcard.html");
    }

    public function saveInfo(){
        $id = _g('id');
        $goods_name = _g('goods_name');
        $goods_value = _g('goods_value');
        $goods_describe = _g('goods_describe');
        $goods_code = _g('goods_code');
        if(_g('img') != 'undefined'){
            $uploadService = new UploadService();
            $imgtype = array('bmp','png','jpeg','jpg');
            $img_url = $uploadService->imageUpLoad("img", "/quiz", 5, $imgtype, "quiz");
        }
        $insertData = array(
            'goods_name' => $goods_name,
            'goods_value' => $goods_value,
            'goods_describe' => $goods_describe,
            'goods_code' => $goods_code,
            'goods_vectorgraph' => $img_url,
            'is_show' => 1,
            'a_time' => time(),
            'u_time' => time(),
        );
        $res = goodsShopModel::db()->upById($id, $insertData);
        if(!$res){
            $this->outputJson(2, 'update error');
        }
        $this->outputJson(200, 'succ');
    }

    public function saveCardInfo(){
        $id = _g('id');
        $goods_card = _g('goods_card');
        $goods_pass = _g('goods_pass');
        $insertData = array(
            'goods_id' => $id,
            'goods_card' => $goods_card,
            'goods_pass' => $goods_pass,
            'is_use' => 0,
            'a_time' => time(),
            'u_time' => time()
        );
        $res = goodsShopCardModel::db()->add($insertData);
        if(!$res){
            $this->outputJson(2, 'update error');
        }
        $this->outputJson(200, 'succ');
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

    public function delone(){
        $id = _g('id');
        $res = goodsShopModel::db()->delById($id);
        if(!$res){
            $this->outputJson(2, 'update error');
        }
        $this->outputJson(200, 'succ');
    }

    public function head(){
        $this->addCss("/assets/open/css/massage-set-create.css");
        $this->addCss("/assets/open/css/wickedpicker.min.css");
        $this->addJs("/assets/open/scripts/jquery.form.min.js");
        $this->addJs("/assets/open/scripts/wickedpicker.min.js");
        $this->display("/quiz/manager/head.html");
    }

    public function saveHead(){
        if(_g('img') != 'undefined'){
            $uploadService = new UploadService();
            $imgtype = array('bmp','png','jpeg','jpg');
            $img_url = $uploadService->imageUpLoad("img", "/quiz", 5, $imgtype, "quiz");
        }
        $insertData = array(
            'img'=> $img_url,
            'a_time' => time(),
            'u_time' => time(),
        );
        $res = headImgModel::db()->add($insertData);
        if(!$res){
            $this->outputJson(2, 'update error');
        }
        $this->outputJson(200, 'succ');
    }
}