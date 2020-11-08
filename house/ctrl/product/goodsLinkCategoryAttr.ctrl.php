<?php
class GoodsLinkCategoryAttrCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $this->display("/product/goods_link_capa_list.html");
    }


    function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getDataListTableWhere();

        $cnt = GoodsLinkCategoryAttrModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";


            $sort = array(
                'id',
                'id',
                'gid',
                'pc_id',
                'pca_id',
                'pcap_id',
                '',
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

            $limit = " limit $iDisplayStart,$end";
            $data = GoodsLinkCategoryAttrModel::db()->getAll($where . $order .$limit);


            foreach($data as $k=>$v){
//                $paraStr = "";
//                if(arrKeyIssetAndExist($v,'product_attr_ids')){
//                    $paraIds = explode(",",$v['product_attr_ids']);
//                    foreach ($paraIds as $k2=>$v2) {
//                        $tmp = explode("-",$v2);
//                        $attrName = ProductCategoryAttrModel::db()->getOneFieldValueById($tmp[0],'name');
//                        $paraName = ProductCategoryAttrParaModel::db()->getOneFieldValueById($tmp[1],'name');
//                        $paraStr .= $attrName . " : ". $paraName . "<br/>";
//                    }
//                }

//                $payTypeArr = OrderModel::getSomePayTypeDesc($v['pay_type']);

                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
//                    GoodsLinkCategoryAttrModel::db()->getOneFieldValueById($v['pid'],'title'),
//                    $v['type'],
//                    GoodsLinkCategoryAttrModel::STATUS[$v['status']],
//                    $paraStr,
                    $v['gid'],
                    $v['pc_id']."-(".ProductCategoryModel::db()->getOneFieldValueById($v['pc_id'],'name') .")",
//                    json_encode($payTypeArr,JSON_UNESCAPED_UNICODE),
//                    AdminUserModel::db()->getOneFieldValueById($v['admin_id'],'uname'),
                    $v['pca_id']."-(".ProductCategoryAttrModel::db()->getOneFieldValueById($v['pca_id'],'name') .")",
                    $v['pcap_id']."-(".ProductCategoryAttrParaModel::db()->getOneFieldValueById($v['pcap_id'],'name') .")",
//                    $v['order_total'],
//                    get_default_date($v['a_time']),
//                    '<a target="_blank" href="/product/no/goods/makeQrcode/id='.$v['id'].'" class="btn blue btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 二维码 </a>',
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


        $pc_id = _g("pc_id");
        $pca_id = _g("pca_id");
        $pcap_id = _g("pcap_id");
        $id = _g("id");
        if($id)
            $where .=" and id = '$id' ";

        if($pc_id)
            $where .=" and pc_id = '$pc_id' ";

        if($pca_id)
            $where .=" and pca_id = '$pca_id' ";

        if($pcap_id)
            $where .=" and pcap_id = '$pcap_id' ";

        return $where;
    }


}