<?php
/**
 * Class exchangeCtrl
 */
class exchangeCtrl extends BaseCtrl{
    public function index(){
        if(_g("getlist")){
            $this->getList();
        }
        $this->display("/quiz/exchange/index.html");
    }

    public function getList()
    {
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        $result = exchangeGiftRecordQuizModel::db()->getAll();
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

            $data = $result;
            foreach ($data as $k => $v) {
                $records["data"][] = array(
                    $v['id'],
                    $v['uid'],
                    $v['goods_name'],
                    $v['goods_id'],
                    $v['goods_value'],
                    $v['goods_num'],
                    $v['email'],
                    $v['goods_card'],
                    $v['goods_card_pass'],
                    $v['status'],
                    '<a href="/quiz/no/exchange/edit/?id='.$v['id'].'"class="btn btn-xs default green add" onclick="edit(this)" data-id="'.$v['id'].'"><i class="fa fa-edit"></i> 点击兑换</a>'
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

    public function edit(){
        $this->addCss("/assets/open/css/massage-set-create.css");
        $this->addCss("/assets/open/css/wickedpicker.min.css");
        $this->addJs("/assets/open/scripts/jquery.form.min.js");
        $this->addJs("/assets/open/scripts/wickedpicker.min.js");
        $id = _g('id');
        $info = exchangeGiftRecordModel::db()->getById($id);
        $this->assign('info', $info);
        $this->display("/quiz/exchange/edit.html");
    }

    public function save(){
        $id = _g('id');
        $email = _g('email');
        $goods_card = _g('goods_card');
        $goods_pass = _g('goods_pass');
        $updateData = array(
            'goods_card' => $goods_card,
            'goods_card_pass' => $goods_pass,
            'status' => 1,
            'u_time' => time(),
        );
        $res = exchangeGiftRecordModel::db()->upById($id, $updateData);
        if(!$res){
            $this->outputJson(2, 'update error');
        }
        // 发送邮件逻辑;
        $content = $goods_card;
        LogLib::wsWriteFileHash(["in AsynTaskCtrl",$email,'开心小游戏礼品兑换',$content]);
        $emailLib = new EmailLib();
        $emailLib->realSend($email,'开心小游戏礼品兑换',$content);
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
}