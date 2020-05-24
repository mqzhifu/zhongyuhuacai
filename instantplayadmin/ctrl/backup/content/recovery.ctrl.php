<?php
/**
 * Created by PhpStorm.
 * User: XiaHB
 * Date: 2019/3/18
 * Time: 20:56
 */

/**
 * 内容发布->回收站
 * Class recoveryCtrl
 */
class recoveryCtrl extends BaseCtrl{
    /**
     * 内容发布列表页;
     */
    public function index(){
        if(_g("getlist")){
            $this->getList();
        }
        // 获取文档所属类别;
        $roleList = DocCategoryModel::getCategoryInfo();
        $id = array_column($roleList, 'id');
        $name = array_column($roleList, 'name');
        $roleList = array_combine($id, $name);
        $this->assign("status_all",$roleList);
        $this->display("/content/recovery/index.html");
    }

    /**
     * Ajax动态获取列表页详情;
     */
    public function getList() {
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
                'admin_uid',
                'category',
                '',
                '',
                '',
                '',
                'sort',
                'a_time',
                '',
            );
            $order = " ORDER BY " .$sort[$order_column]." ".$order_dir;

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

            foreach ($doc_categorys as $cat) {
                foreach ($docs as $doc) {
                    if ($cat['id'] == $doc['category']) {
                        $row = array(
                            $doc['id'],
                            $doc['title'],
                            $cat['name'],
                            $cat['sort'],
                            get_default_date($doc['a_time']),
                            get_default_date($doc['dl_time']),
                            '<a href="/content/no/recovery/delDoc/id='.$doc['id'].'" class="btn btn-xs default red" data-id="'.$doc['id'].'" target="_blank"><i class="fa fa-file-text"></i> 彻底删除</a>
                             <a href="/content/no/recovery/recoverDoc/id='.$doc['id'].'" class="btn btn-xs default blue" data-id="'.$doc['id'].'" target="_blank"><i class="fa fa-file-text"></i> 恢复</a>',
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
        exit();
    }

    /**
     * @return string
     */
    public function getWhere() {
        $where = " is_show = 0 ";
        $title = _g("title");
        $category_id = _g("category_id");

        if (!is_null($category_id) && $category_id!='')
            $where .= " and category = '$category_id'";

        if (!is_null($title) && $title!='')
            $where .= " and title = '$title'";

        return $where;
    }

    function delDoc() {
        if ($id = _g('id')) {
            if (DocModel::db()->delById($id)) {
                echo "<script>alert('彻底删除成功！');location.href='".$_SERVER["HTTP_REFERER"]."';</script>";
            }else{
                echo "<script>alert('彻底删除失败！');location.href='".$_SERVER["HTTP_REFERER"]."';</script>";
            }
        }
    }

    /**
     * 文档恢复;
     */
    public function recoverDoc(){
        if ($id = _g('id')) {
            $updateSql = "UPDATE open_doc SET is_show = 1 where id = {$id} LIMIT 1 ;";
            DocCategoryModel::recoverDoc($updateSql);
            echo "<script>alert('恢复成功！');location.href='".$_SERVER["HTTP_REFERER"]."';</script>";
        }
        echo "<script>alert('恢复失败！');location.href='".$_SERVER["HTTP_REFERER"]."';</script>";
    }
}