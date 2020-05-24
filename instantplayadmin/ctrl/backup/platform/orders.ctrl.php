<?php
/**
 * Created by PhpStorm.
 * User: XiaHB
 * Date: 2019/3/18
 * Time: 17:45
 */

/**
 * 平台数据->订单查询
 * Class ordersCtrl
 */
class ordersCtrl extends BaseCtrl{
    public function index(){
        if(_g("getlist")){
            $this->getList();
        }
        // 游戏名称的下拉框数据处理;
        $roleList = GamesModel::getGamesNameList();
        $id = array_column($roleList, 'id');
        $name = array_column($roleList, 'name');
        $roleList = array_combine($id, $name);
        $this->assign("status_all",$roleList);
        $onlineDesc = ['1'=>"待处理 / 预订",'2'=>"成功",'3'=>"失败"];
        $this->assign('onlineDesc', $onlineDesc);
        $this->display("platform/orders/index.html");
    }

    /**
     * 获取数据详情;
     */
    public function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        $where = $this->getWhere();

        $sql = "SELECT go.*,ga.name FROM games_goods_order AS go LEFT JOIN games AS ga ON go.game_id = ga.id WHERE {$where}";
        $result = FeedbackModel::getAll($sql);
        $iTotalRecords = (count($result))?count($result):0;

        if (!empty($result) && is_array($result)){
            $iDisplayLength = intval($_REQUEST['length']);
            if('999999' == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            $select_sql = "SELECT go.*,ga.name FROM games_goods_order AS go LEFT JOIN games AS ga ON go.game_id = ga.id WHERE {$where}  ORDER BY go.id DESC LIMIT $iDisplayStart, $end ";
            $data = FeedbackModel::getAll($select_sql);

            foreach($data as $k=>$v){
                switch ($v["status"]){
                    case 1:
                        $v["status"] = '待处理 / 预订';
                        break;
                    case 2:
                        $v["status"] = '成功';
                        break;
                    case 3:
                        $v["status"] = '失败';
                        break;
                    default:
                        $v['status'] = '-';
                }
                $userService = new UserService();
                $name_nick = $userService->getUinfoById($v['uid']);
                $nickName = ($name_nick["nickname"])?$name_nick["nickname"]:'-';
                $records["data"][] = array(
                    $v['id'],
                    $v['game_id'],// 游戏ID
                    $v['name'],// 游戏名称;
                    $v['uid'],// 用户ID
                    $v['nick_name'] = $nickName,// 昵称;
                    $v['in_trade_no'],// 内部订单号;
                    $v['out_trade_no'],// 部订单号;
                    $v['goods_id'],// 商品ID；
                    $v['money'],// 充值金额RMB;
                    $v['goldcoin'],// 消耗金币数;
                    $v['a_time'] = date('Y-m-d H:i:s', $v['a_time']),// 下单时间;
                    $v['status'],
                    "<span style='font-weight: bold'>-</span>",
                );
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
        exit();
    }

    /**
     * where条件拼接;
     * @return string
     */
    function getWhere(){
        $where = " 1 = 1";
        if($name = _g("id"))
            $where .= " and ga.id = $name";

        if($uid = _g("uid"))
            $where .= " AND go.uid = $uid";

        if($game_id = _g("game_id"))
            $where .= " AND go.game_id = $game_id";

        if($goods_id = _g("goods_id"))
            $where .= " AND go.goods_id = $goods_id";

        if($money = _g("money"))
            $where .= " AND go.money = $money";

        if($goldcoin = _g("goldcoin"))
            $where .= " AND go.goldcoin = $goldcoin";

        if($game_name = trim(_g("game_name")))
            $where .= " AND ga.name like '%$game_name%'";

        if($out_trade_no = trim(_g("out_trade_no")))
            $where .= " AND go.out_trade_no = $out_trade_no";

        if($in_trade_no = trim(_g("in_trade_no")))
            $where .= " AND go.in_trade_no = $in_trade_no";

        if($from = _g("from")){
            $from .= ":00";
            $where .= " AND go.a_time >= '".strtotime($from)."'";
        }

        if($to = _g("to")){
            $to .= ":59";
            $where .= " AND go.a_time <= '".strtotime($to)."'";
        }

        if($status = _g("status")){
            $where .= " AND go.status = $status";
        }

        return $where;
    }

}