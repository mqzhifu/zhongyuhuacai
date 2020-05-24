<?php

/**
 * Class changeNumCtrl
 */
class changeNumCtrl extends BaseCtrl{
    public function index(){
        if(_g("getlist")){
            $this->getList();
        }
        $this->display("/promotion/task_show.html");
    }

    public function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $result = taskConfigModel::db()->getCount();
        $iTotalRecords = ($result > 0)?$result:0;

        if ($result){
            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
            if('999999' == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            $selectSql = "SELECT * FROM task_config ORDER BY type ASC  LIMIT $iDisplayStart, $iDisplayLength ";
            $data = taskConfigModel::db()->query($selectSql);
            foreach($data as &$value){
                $records["data"][] = array(
                    $value['id'],
                    $value['type'] = (1 == $value['type'])?'日常任务':'新手任务',
                    $value['title'],
                    $value['content'],
                    $value['sort'],
                    '<button class="btn btn-sm default blue edit_btn" onclick="edit(this)" attr-id="'.$value['id'].'" attr-title="'.$value['title'].'" attr-sort="'.$value['sort'].'"attr-game-id="'.$value['game_id'].'">编辑</button>',
                );
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);exit();
    }

    /**
     * 更新排序;
     */
    public function upDate(){
        $id = _g('id');
        $info =  taskConfigModel::db()->getById($id);
        $sort = _g('sort');
        $game_id = _g('game_id');
        $title = _g('title');
        if(19 != $id && $info['title'] != $title){
            $this->outputJson(0, "当前任务类型不允许修改！");
        }
        if(19 != $id){
            $game_id = 0;
        }
        if (!$id || !is_numeric($sort)) {
            $this->outputJson(0, "参数有误！");
        }
        taskConfigModel::db()->upById($id, array('sort' => $sort, 'game_id' => $game_id, 'title'=>$title));
        $this->outputJson(200, "操作成功！");
    }
}