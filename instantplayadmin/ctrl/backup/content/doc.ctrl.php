<?php

/**
 * @Author: Kir
 * @Date:   2019-02-25 15:05:41
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-05-24 09:45:59
 */


class DocCtrl extends BaseCtrl
{
    function index() {
        if(_g("getlist")){
            $this->getList();
        }

        $this->display("/content/doc/doc_view.html");

    }

    function docEdit() {
        $this->assign('categoryDesc', DocCategoryModel::getCategoryDesc());
    	if ($docId = _g('id')) {
    		$doc = DocModel::db()->getRow(" id=$docId ");
    		if ($doc) {
    			$category = $doc['category'];
    			$cat = DocCategoryModel::db()->getRow(" id=$category ");
    			$this->assign('doc', $doc);
    			$this->assign('cat', $cat);
    			$this->assign('docId', $docId);
    		}
    		
    	}
    	if(!empty($doc['img_url'])){
            $imgUrl = $doc['img_url'];
            $arr = explode("|",$imgUrl);
            if(!empty($arr[1])){
                $img_url2 = $this->getStaticFileUrl("content", $arr[1]);
                $this->assign("img_url2", $img_url2);
            }
            if(!empty($arr[2])){
                $img_url3 = $this->getStaticFileUrl("content", $arr[2]);
                $this->assign("img_url3", $img_url3);
            }
            if(!empty($arr[3])){
                $img_url4 = $this->getStaticFileUrl("content", $arr[3]);
                $this->assign("img_url4", $img_url4);
            }
            if(!empty($arr[4])){
                $img_url5 = $this->getStaticFileUrl("content", $arr[4]);
                $this->assign("img_url5", $img_url5);
            }
            if(!empty($arr[5])){
                $img_url6 = $this->getStaticFileUrl("content", $arr[5]);
                $this->assign("img_url6", $img_url6);
            }
        }
    	$this->display("/content/doc/doc_edit.html");
    }

    function docEditSubmit() {
    	if (!$admin_uid = $this->_adminid) {
    		return;
    	}
        $sort = _g('sort');
		$cat = _g('cat');
		$title = _g('title');
		$content = _g('content');

		$doc = [
			'category'=>$cat,
			'admin_uid'=>$admin_uid,
			'title'=>$title,
			'content'=>$content,
            'sort'=>$sort,
			'a_time'=>time(),
		];
		if (!$oldDoc = DocModel::db()->getRow(" title = '$title' and category = $cat ")) {
			// 新增doc
			DocModel::db()->add($doc);
		} else {
			DocModel::editDoc($oldDoc['id'], $doc, 1);
		}
    }


    public function delDocs() {
        // 之前郑天做的物理删除功能，仍保存,现改为逻辑删除modify by XiaHB;
        /*if ($docIds = _g('ids')) {
            if (DocModel::db()->delByIds($docIds)) {
                echo "1";
                exit;
            }
        }
        echo "0";*/
        if(isAjax()){
            $docIds = _g('ids');
            if (!empty($docIds) && is_string($docIds)) {
                $res = $this->delByIdsNew($docIds);
                if($res){
                    echo '1';exit();
                }else{
                    echo '0';exit();
                }
            }
        }else{
            echo "0";exit();
        }

    }

    function cateEdit() {
        $this->assign('categoryDesc', DocCategoryModel::getCategoryDesc());
        $this->display("/content/doc/doc_category_edit.html");
    }

    function cateEditSubmit() {
        $id = _g('id');
        $sort = _g('sort');
        $name = _g('name');

        $category = [
            'id'=>$id,
            'name'=>$name,
            'sort'=>$sort,
            'a_time'=>time(),
        ];
        if (!$catData = DocCategoryModel::db()->getRow(" id=$id ")) {
            // 新增category
            DocCategoryModel::db()->add($category);
        } else {
            DocCategoryModel::db()->upById($catData['id'], $category);
        }
    }

    public function delCate() {
        if ($cate = _g('docCate')) {
            if (DocCategoryModel::db()->delById($cate)) {
                echo "1";
                exit;
            }
        }
        echo "0";
    }


    function getList() {
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();

        $cnt = DocModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "asc";


            $sort = array(
                '',
                'id',
                '',
                'name',
                'sort',
                'a_time',
                'u_time',
            );

            $order = " order by " .$sort[$order_column]." ".$order_dir;

            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录

            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始

            $order.= " limit $iDisplayStart, $iDisplayLength ";

            $docs = DocModel::db()->getAll($where . $order);
            $doc_categorys = DocCategoryModel::db()->getAll();
//            '<textarea wrap="logical" disabled="disabled">'.$doc['content'].'</textarea>',
            foreach ($doc_categorys as $cat) {
            	foreach ($docs as $doc) {
            		if ($cat['id'] == $doc['category']) {
            			$row = array(
		                    '<input type="checkbox" name="id[]" value="'.$doc['id'].'">',
		                    $doc['id'],
                            $doc['title'],
                            $cat['name'],
                            $doc['sort'],
		                    get_default_date($doc['a_time']),
		                    get_default_date($doc['u_time']),
		                    '<a href="/content/no/doc/docEdit/?id='.$doc['id'].'" class="btn btn-xs default blue" data-id="'.$doc['id'].'" target=""><i class="fa fa-file-text"></i> 编辑</a>',
		                );
		                $records["data"][] = $row;
            		}
            	}

            }
            
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
    }

    function getWhere() {
        $where = " is_show = 1 ";
        $id = _g("id");
        $admin_uid = _g("admin_uid");
        $from = _g("from");
        $to = _g("to");
		$category = _g("category");
		$title = _g("title");

        if (!is_null($id) && $id!='')
            $where .= " and id = '$id'";

        if (!is_null($from) && $from!='') {
            $where .= " and a_time >= '".strtotime($from)."'";
        }

        if (!is_null($to) && $to!='') {
            $where .= " and a_time <= '".strtotime("$to +1 day")."'";
        }

        if (!is_null($category) && $category!='')
            $where .= " and category = '$category'";

        if (!is_null($admin_uid) && $admin_uid!='')
            $where .= " and admin_uid = '$admin_uid'";

        if (!is_null($title) && $title!='')
            $where .= " and title = '$title'";


        return $where;
    }

    /**
     * 文档逻辑删除;
     * @param $docIds
     * @return int
     */
    public function delByIdsNew($docIds){
        $retArr = explode(',', $docIds);
        $limits = count($retArr);
        $upData = [];
        $upData['dl_time'] = time();
        $upData['is_show'] = 0;
        $rs = DocModel::editDoc($docIds, $upData, $limits);
        if($rs){
            return 1;
        }else{
            return 0;
        }
    }

//    /**
//     * 图片上传;
//     */
//    public function updateFile(){
//        $doc_id = _g('doc_id');
//        $uploadService = new UploadService();
//        // $invoice_img = $uploadService->imageUpLoad("img", "/content", 5, $imgtype, "content");
//        $imgs = $uploadService->uploadFileByApp("img", "content", "", 1);
//        $invoice_img = $imgs['msg'];
//        $rs = DocModel::db()->getById($doc_id);
//        $invoice_img = $rs['img_url'].'|'.$invoice_img;
//        if($invoice_img){
//            DocModel::db()->upById($doc_id, array('img_url'=>$invoice_img));
//            echo json_encode(0);exit();
//        }else{
//            echo json_encode(1);exit();
//        }
//
//
//        // 获取文件信息;
//        /*$file = $_FILES["file"];
//        $doc_id = _g("doc_id");*/
//        // 加限制条件;
//        // 1.文件类型;
//        // 2.文件大小（需要落表）;
//        // 3.保存的文件名不重复;
//        /*if($arr["size"]<10241000 ) {
//            //临时文件的路径
//            $arr['size'] = round($arr['size']/10241000).'MB';
//            //$filename = iconv("UTF-8","gb2312", $arr["tmp_name"]);
//            $date = date("Ymd/");
//            $path = STATIC_RES."app_install_package/android/$date";
//            if(!file_exists($path)){
//                mkdir($path, 0777, true);
//            }
//            $uploadname = time().mt_rand(100,999).".".'apk';
//            $rs = move_uploaded_file($arr['tmp_name'],$path.$uploadname);
//            if(true === $rs){
//                echo json_encode(0);exit();
//            }
//        }else {
//            echo json_encode(1);exit();
//        }*/
//
//        /*$uploadService = new UploadService();
//        $imgtype = array('bmp','png','jpeg','jpg');
//        $img_url = $uploadService->imageUpLoad("img", "/banner", 5, $imgtype, "banner");*/
//
//        //$date = date("Ymd/");
//        /*$fullPath = STATIC_RES."/dev/upload/instantplayadmin/content/";
//        if(!file_exists($fullPath)){
//            mkdir($fullPath, 0777, true);
//        }
//        $uploadname = time().mt_rand(100,999).".".'jpg';
//        $rs = move_uploaded_file($file['tmp_name'],$fullPath.$uploadname);
//        $path = "/content/".$uploadname;
//        DocModel::db()->upById($doc_id, array('img_url'=>$path));
//        if($rs){
//            echo json_encode(0);exit();
//        }else{
//            echo json_encode(1);exit();
//        }*/
//    }

    /**
     * @param $imgUrl
     * @return string
     */
    private function getImgUrl($imgUrl){
        $uploadService = new UploadService();
        $result = substr($imgUrl,0,strrpos($imgUrl,"/"));
        if('/content' == $result){
            $imgurl = $uploadService->getStaticBaseUrl() .$imgUrl;
        }else{
            $imgurl = get_static_file_url_by_app('content', $imgUrl);
        }
        return $imgurl;
    }

    /**
     * 获取分类的详细信息;
     */
    public function getDetails(){
        $items = DocCategoryModel::db()->getAll();
        $returnData = [];
        $returnData['base'] = $items;
        $returnData['real_ad_data'] = $items;
        $this->outputJson(200, "成功", $returnData);
    }

    /**
     * 同时进行更新和添加的操作，依据分类id来实现;
     */
    public function doDoc(){
        $id = _g('id');
        $res = DocCategoryModel::db()->getById($id);
        // 当前值如果存在进行更新操作;
        if($res){
            $name = _g('name');
            $sort = _g('sort');
            $res = DocCategoryModel::db()->upById($id, array('name'=>$name, 'sort'=>$sort));
            if(!$res){
                $this->outputJson(3, '更新失败');
            }else{
                $this->outputJson(200, 'succ');
            }
        }else{
            // 添加操作;
            $insertArray = array(
                'id' => _g('id'),
                'name' => _g('name'),
                'sort' => _g('sort'),
                'a_time' => time()
            );
            $rs = DocCategoryModel::db()->add($insertArray);
            if(!$rs){
                $this->outputJson(3, '创建失败');
            }else{
                $this->outputJson(200, 'succ');
            }
        }
    }

    /**
     * 删除分类;
     */
    public function delOne(){
        $id = _g('id');
        $rs = DocCategoryModel::db()->delById($id);
        if(!$rs){
            $this->outputJson(3, '删除失败');
        }else{
            $this->outputJson(200, 'succ');
        }
    }
}