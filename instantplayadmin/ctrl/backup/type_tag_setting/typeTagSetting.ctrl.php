<?php

class TypeTagSettingCtrl extends BaseCtrl 
{

    function typeIndex()
    {
        if(_g("getTypeList")){
            $this->getTypeList();
        }
        $this->display('type_tag_setting/type.html');
    }


    function paintStyleIndex(){
        if(_g("getPaintStyleList")){
            $this->getPaintStyleList();
        }
        $this->display('type_tag_setting/paintingStyle.html');
    }

    function tagIndex(){
        if(_g("getTagList")){
            $this->getTagList();
        }
        $this->assign("tagTypeDesc", TagsDetailModel::getTagsTypeDesc());
        $this->display('type_tag_setting/tag.html');
    }


    // type
    function getTypeList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getTypeWhere();

        $sql = "select count(*) as cnt from games_category where $where";
        
        $cntSql = GamesCategoryModel::db()->getRowBySQL($sql);

        $cnt = 0;
        if(arrKeyIssetAndExist($cntSql,'cnt')){
            $cnt = $cntSql['cnt'];
        }

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";


            $sort = array(
                'id',
                '',
                '',
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

            $sql = "select * from games_category where $where GROUP BY id $order limit $iDisplayStart,$end ";

            $data = GamesCategoryModel::db()->getAllBySQL($sql);

            foreach($data as $k=>$v){
                $row = array(
                    $v['id'],
                    $v['name_cn'],
                    $v['name'],
                    '<a href="javascript:void(0)" class="btn btn-xs default red" data-id="'.$v['id'].'" onclick="edit('.$v['id'].')"><i class="fa fa-trash-o"></i>编辑</a>',
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

    function getTypeDetail(){
        $id = _g("id");

        $res = GamesCategoryModel::db()->getRow("id=$id");
        if(!$res){
            $this->outputJson(1, '添加失败');
        }
        $this->outputJson(200, 'succ', $res);
    }
    function addType(){

        $name_cn = _g("name_cn");
        $name = _g("name");

        $res = GamesCategoryModel::db()->add(['name'=>$name,'name_cn'=>$name_cn]);
        if(!$res){
            $this->outputJson(1, '添加失败');
        }
        $this->outputJson(200, 'succ');
    }

    function saveType(){
        $id = _g("id");
        $name_cn = _g("name_cn");
        $name = _g("name");

        $res = GamesCategoryModel::db()->update(['name'=>$name,'name_cn'=>$name_cn], "id=$id limit 1");
        if(!$res){
            $this->outputJson(1, '修改失败');
        }
        $this->outputJson(200, 'succ');
    }

    private function getTypeWhere(){
        $where = " 1 ";
        if($name = _g("name")){
            $where .= " and name like '%$name%'";
        }
        if($name_cn = _g("name_cn")){
            $where .= " and name_cn like '%$name_cn%'";
        }

        return $where;
    }



    function getPaintStyleList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getPaintStyleWhere();

        $sql = "select count(*) as cnt from game_paint_style where $where";
        
        $cntSql = GamePaintStyleModel::db()->getRowBySQL($sql);

        $cnt = 0;
        if(arrKeyIssetAndExist($cntSql,'cnt')){
            $cnt = $cntSql['cnt'];
        }

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";


            $sort = array(
                'style_id',
                '',
                '',
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

            $sql = "select * from game_paint_style where $where GROUP BY id $order limit $iDisplayStart,$end ";

            $data = GamePaintStyleModel::db()->getAllBySQL($sql);

            foreach($data as $k=>$v){
                $row = array(
                    $v['style_id'],
                    $v['name_cn'],
                    $v['name_en'],
                    '<a href="javascript:void(0)" class="btn btn-xs default red" data-id="'.$v['id'].'" onclick="edit('.$v['id'].')"><i class="fa fa-trash-o"></i>编辑</a>',
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

    function getPaintStyleDetail(){
        $id = _g("id");

        $res = GamePaintStyleModel::db()->getRow("id=$id");
        if(!$res){
            $this->outputJson(1, '添加失败');
        }
        $this->outputJson(200, 'succ', $res);
    }
    function addPaintStyle(){
        $styleid = _g("style_id");
        $name_cn = _g("name_cn");
        $name = _g("name");

        $res = GamePaintStyleModel::db()->add(['name_en'=>$name,'name_cn'=>$name_cn,'style_id'=>$styleid]);
        if(!$res){
            $this->outputJson(1, '添加失败');
        }
        $this->outputJson(200, 'succ');
    }

    function savePaintStyle(){
        $id = _g("id");
        $name_cn = _g("name_cn");
        $name = _g("name");

        $res = GamePaintStyleModel::db()->update(['name_en'=>$name,'name_cn'=>$name_cn], "id=$id limit 1");
        if(!$res){
            $this->outputJson(1, '修改失败');
        }
        $this->outputJson(200, 'succ');
    }

    private function getPaintStyleWhere(){
        $where = " 1 ";
        if($name = _g("name")){
            $where .= " and name_en like '%$name%'";
        }
        if($name_cn = _g("name_cn")){
            $where .= " and name_cn like '%$name_cn%'";
        }

        return $where;
    }



    function getTagsDetailList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getPaintStyleWhere();

        $sql = "select count(*) as cnt from tags_detail where $where";
        
        $cntSql = TagsDetailModel::db()->getRowBySQL($sql);

        $cnt = 0;
        if(arrKeyIssetAndExist($cntSql,'cnt')){
            $cnt = $cntSql['cnt'];
        }

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";


            $sort = array(
                'tag_id',
                '',
                '',
                '',
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

            $sql = "select * from tags_detail where $where GROUP BY id $order limit $iDisplayStart,$end ";

            $data = TagsDetailModel::db()->getAllBySQL($sql);

            foreach($data as $k=>$v){
                $row = array(
                    $v['tag_id'],
                    TagsDetailModel::getTagsTypeDesc()[$v['type']],
                    $v['tag_name'],
                    $v['name_en'],
                    '<a href="javascript:void(0)" class="btn btn-xs default red" data-id="'.$v['id'].'" onclick="edit('.$v['id'].')"><i class="fa fa-trash-o"></i>编辑</a>',
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

    function getTagsDetail(){
        $id = _g("id");

        $res = TagsDetailModel::db()->getRow("id=$id");
        if(!$res){
            $this->outputJson(1, '添加失败');
        }
        $this->outputJson(200, 'succ', $res);
    }
    function addTagsDetail(){
        $tagid = _g("tag_id");
        $name_cn = _g("name_cn");
        $name = _g("name");
        $type = _g("type");
        $res = TagsDetailModel::db()->add(['name_en'=>$name,'tag_name'=>$name_cn,'tag_id'=>$tagid,'type'=>$type]);
        if(!$res){
            $this->outputJson(1, '添加失败');
        }
        $this->outputJson(200, 'succ');
    }

    function saveTagsDetail(){
        $id = _g("id");
        $name_cn = _g("name_cn");
        $name = _g("name");
        $type = _g("type");
        $res = TagsDetailModel::db()->update(['name_en'=>$name,'tag_name'=>$name_cn,'type'=>$type], "id=$id limit 1");
        if(!$res){
            $this->outputJson(1, '修改失败');
        }
        $this->outputJson(200, 'succ');
    }

    private function getTagsDetailWhere(){
        $where = " 1 ";
        if($name = _g("name")){
            $where .= " and name_en like '%$name%'";
        }
        if($name_cn = _g("name_cn")){
            $where .= " and tag_name like '%$name_cn%'";
        }

        return $where;
    }
}
