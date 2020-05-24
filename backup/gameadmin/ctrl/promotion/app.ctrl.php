<?php

/**
 * Class BannerCtrl
 */
class appCtrl extends BaseCtrl{

    function index(){
        $this->assign('status_all', ['1'=>'运行','2'=>'关闭']);
        $this->addCss("/assets/open/css/game-detail.css?1");
        $this->display("promotion/app.html");
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
            $sql = "SELECT * FROM app WHERE {$where} GROUP BY id ORDER BY id DESC LIMIT {$iDisplayStart}, {$iDisplayLength}";
            $data = appModel::db()->getAllBySQL($sql);

            foreach($data as $k=>$v){
                $records["data"][] = array(
                    $v['id'],
                    $v['name'],
                    $status = (1 == $v['status'])?'运行':'关闭',
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
        $res = appModel::db()->delById($id);
        if(!$res){
            $this->outputJson(2, 'db error');
        }
        $this->outputJson(200, 'succ');
    }

    public function upAppOne(){
        $id = _g('id');
        $name = _g('name');
        $status = _g('status');
        $pass = _g('pass');
        $update = [];
        $update['name'] = $name;
        $update['status'] = $status;
        $update['ps'] = md5($pass);
        $res = appModel::db()->update($update, "id = $id limit 1");
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
        $items = appModel::db()->getAllBySQL($sql);
        $res = appModel::db()->getRowById($id);
        $this->outputJson(200, 'succ', ['data1'=>$res,'data2'=>$items]);
    }

    /**
     * app insert
     */
    public function addAppOne(){
        $name = (string)_g('name');
        $status = _g('status');
        $pass = _g('pass');
        if(!$name || !$status || !$pass){
            $this->outputJson('-1001', '参数缺失！');
        }
        $insertData['name'] = $name;
        $insertData['status'] = $status;
        $insertData['ps'] = md5($pass);
        $res = appModel::db()->add($insertData);
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