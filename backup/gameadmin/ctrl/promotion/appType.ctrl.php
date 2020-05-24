<?php
/**
 * Class appTypeCtrl
 */
class appTypeCtrl extends BaseCtrl{
    //  `auto_open_room` tinyint(1) DEFAULT NULL COMMENT '报名自动创建房间1是2否',
//`ready` tinyint(1) DEFAULT NULL COMMENT '需要准备后才开始1是2否',
//`ai_robot` tinyint(1) DEFAULT NULL COMMENT '是否有机器人1是2否',
    function index(){
        $this->assign('status_all', ['1'=>'运行','2'=>'关闭']);
        $this->assign('auto_open_room', ['1'=>'自动创建','2'=>'手动创建']);
        $this->assign('ready', ['1'=>'需准备','2'=>'无需准备']);
        $this->assign('ai_robot', ['1'=>'有机器人','2'=>'无机器人']);
        $status_all = appModel::db()->getAll("status = 1");
        $id = array_column($status_all, 'id');
        $name = array_column($status_all, 'name');
        $app_all = array_combine($id, $name);
        $this->addCss("/assets/open/css/game-detail.css?1");
        $this->assign('app_all', $app_all);
        $this->display("promotion/app_type.html");
    }

    function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();
        $iTotalRecords = appModel::db()->getCount($where);
        if ($iTotalRecords){
            $iDisplayLength = intval($_REQUEST['length']);
            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }
            $iDisplayStart = intval($_REQUEST['start']);
            $sql = "SELECT * FROM app_type WHERE {$where} GROUP BY id ORDER BY id DESC LIMIT {$iDisplayStart}, {$iDisplayLength}";
            $data = appTypeModel::db()->getAllBySQL($sql);

            foreach($data as $k=>$v){
                $tmpInfo = appModel::db()->getById($v['app_id']);
                $records["data"][] = array(
                    $v['id'],
                    $v['name'],
                    $v['person'],
                    $tmpInfo['name'],
                    $auto_open_room = (1 == $v['auto_open_room'])?'自动创建':'手动创建',
                    $v['timeout_end_game'],
                    $ready = (1 == $v['ready'])?'需准备后开始':'直接开始',
                    $ai_robot = (1 == $v['ai_robot'])?'有机器人':'无机器人',
                    $status = (1 == $v['status'])?'运行':'关闭',
                    $v['game_timeout'],
                    '<a href="#" class="btn btn-circle red btn-sm" onclick="one_del(this)" data-id="'.$v['id'].'"><i class="fa fa-trash-o"></i> 删除</a>'.'<a href="#"  class="btn btn-circle blue btn-sm" onclick="edit(this)" data-id="'.$v['id'].'"><i class="fa fa-edit"></i> 修改</a>',
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);exit();
    }

    function delOne(){
        $id = _g('id');
        if(!$id || $id=='undefinded'){
            $this->outputJson(1, '参数不正确');
        }
        $res = appTypeModel::db()->delById($id);
        if(!$res){
            $this->outputJson(2, 'db error');
        }
        $this->outputJson(200, 'succ');
    }

    public function upAppOne(){
        $id = _g('id');
        $name = _g('name');
        $person = _g('person');
        $app_id = _g('app_id');
        $auto_open_room = _g('auto_open_room');
        $timeout_end_game = _g('timeout_end_game');
        $ready = _g('ready');
        $ai_robot = _g('ai_robot');
        $status = _g('status');
        $game_timeout = _g('game_timeout');

        $update = [];
        $update['name'] = $name;
        $update['person'] = $person;
        $update['app_id'] = $app_id;
        $update['auto_open_room'] = $auto_open_room;
        $update['timeout_end_game'] = $timeout_end_game;
        $update['ready'] = $ready;
        $update['ai_robot'] = $ai_robot;
        $update['status'] = $status;
        $update['game_timeout'] = $game_timeout;
        $res = appTypeModel::db()->update($update, "id = $id limit 1");
        if(!$res){
            $this->outputJson(2, '修改失败！');
        }
        $this->outputJson(200, '修改成功！');
    }


    function getOneApp(){
        $id = _g('id');
        if($this->isIllegal($id)){
            $this->outputJson(1, '缺少参数');
        }
        $sql = "select * from app";
        $items = appTypeModel::db()->getAllBySQL($sql);
        $res = appTypeModel::db()->getRowById($id);
        $this->outputJson(200, 'succ', ['data1'=>$res,'data2'=>$items]);
    }

    /**
     * app insert
     */
    public function addAppOne(){
        $name = _g('name');
        $app_id = _g('app_id');
        $person = _g('person');
        $timeout_end_game = _g('timeout_end_game');
        $game_timeout = _g('game_timeout');
        $auto_open_room = _g('auto_open_room');
        $ready = _g('ready');
        $ai_robot = _g('ai_robot');
        $status = _g('status');
        if(!$name || !$app_id || !$timeout_end_game || !$game_timeout || !$auto_open_room || !$ready || !$ai_robot ){
            $this->outputJson('-1001', '参数缺失！');
        }
        $insertData['name'] = $name;
        $insertData['person'] = $person;
        $insertData['app_id'] = $app_id;
        $insertData['timeout_end_game'] = $timeout_end_game;
        $insertData['game_timeout'] = $game_timeout;
        $insertData['auto_open_room'] = $auto_open_room;
        $insertData['ready'] = $ready;
        $insertData['ai_robot'] = $ai_robot;
        $insertData['status'] = $status;
        $res = appTypeModel::db()->add($insertData);
        if(!$res){
            $this->outputJson('-1002', '操作失败！');
        }else{
            $this->outputJson(200, '操作成功！');
        }
    }


    private function isIllegal($a){
        return (!$a || $a == 'undefinded');
    }

    private function getWhere(){
        $where = " 1 = 1 ";
        return $where;
    }

    public function outputJson ($code, $message, $data=[]){
        header("Content-Type: application/json");
        echo json_encode([
            "code" => $code,
            "message" => $message,
            "data" => $data,
        ]);
        exit(0);
    }

}