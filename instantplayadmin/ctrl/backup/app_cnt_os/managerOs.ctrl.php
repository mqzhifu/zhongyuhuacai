<?php
/**
 * Class managerOsCtrl
 */
class managerOsCtrl extends BaseCtrl{
    public function index(){
        if(_g("getlist")){
            $this->getList();
        }
        $this->assign('audit_status', [1=>'未审核',2=>'通过',3=>'不通过',4=>'已发货']);
        $this->display("app_cnt_os/index.html");

    }

    public function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        $where = $this->getWhere();
        $iTotalRecords = MoneyOrderModel::db()->getCount($where);

        if ($iTotalRecords){
            $iDisplayLength = intval($_REQUEST['length']);
            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }
            $iDisplayStart = intval($_REQUEST['start']);
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $data = MoneyOrderModel::db()->getAll(" $where limit $iDisplayStart, $iDisplayLength");

            foreach($data as $k=>$v){
                $userInfo = UserModel::db()->getById($v['uid']);
                $status_name = '未审核';
                if(2 == $v['status']){
                    $status_name = '通过';
                }elseif (3 == $v['status']){
                    $status_name = '不通过';
                }elseif (4 == $v['status']){
                    $status_name = '已确认';
                }
                $bank = new BankService();
                $bankInfo = $bank->getGoldcoinInfo($v['uid']);
                $bankInfo = $bankInfo['msg'];
                $totalRmb = sprintf("%.2f",$bankInfo['total'] * 0.00001);
                // $GLOBALS['main']['goldcoinExchangeUSD'];

                if($v['status'] < 4){
                    $str = '<a href="#"  class="btn btn-xs default green edit" onclick="edit(this)" data-id="'.$v['id'].'"><i class="fa fa-edit"></i> 操作 </a>'.'<a href="#"  class="btn btn-xs default blue" onclick="affrimDo(this)" data-id="'.$v['id'].'"><i class="fa fa-edit"></i> 确认发货</a>';
                }else{
                    $str = '-';
                }
                $records["data"][] = array(
                    $v['in_trade_no'],
                    $v['uid'],
                    $userInfo['nickname'],
                    $v['pay_pal_address'],
                    '$'.$totalRmb,
                    '$'.$v['num'],
                    '金币',
                    $status_name,
                    get_default_date($v['a_time']),
                    get_default_date($v['u_time']),
                    // '<a href="/app_cnt_os/no/managerOs/id='.$v['id'].'" class="btn btn-xs default green" data-id="'.$v['id'].'" target="_blank"><i class="fa fa-file-text"></i> 操作</a>',
                    $str,
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
    }

    function makeRedisToken($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['token']['key'],$uid,IS_NAME);
        $token = TokenLib::create($uid);
        $rs = RedisPHPLib::set($key,$token,$GLOBALS['rediskey']['token']['expire']);
        var_dump($rs);
        echo "-ok";
    }

    function clearUserMatchStatus($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['serverMatching']['key'],$uid,'game');
        $userStatus = RedisPHPLib::getServerConnFD()->del($key);


        echo "ok";
    }

    function searchAllDel(){
        $where = $this->getWhere();

        if($where == ' 1 ')
            out_err("没有搜索条件，那么就是清空全表，禁止这样操作!",500,'ajax');

        $type = _g("type");
        if(!$type || (  $type != 'in' && $type != 'out' ) ){
            out_err("para type:error....!",500,'ajax');
        }

        if($type == 'in'){
            $cnt = Sms_logModel::db()->getCount($where);
            if(!$cnt)
                out_ok('删除了0条记录',200,'ajax');

            $sql = "delete from sms_log where ".$where . "  limit 100000";
            Sms_logModel::db()->execute($sql);
            out_ok("删除了{$cnt}条记录",200,'ajax');
        }else{
            $cnt = Send_logModel::db()->getCount($where);
            if(!$cnt)
                out_ok('删除了0条记录',200,'ajax');

            $sql = "delete from send_log where ".$where. "  limit 100000";
            Send_logModel::db()->execute($sql);
            out_ok("删除了{$cnt}条记录",200,'ajax');

        }
    }

    function delOne($uid){
        if(!(int)$uid){
            exit("uid is null");
        }

        $user = UserModel::db()->getById($uid);
        if(!$user){
            exit("uid not in db ");
        }
        var_dump($user);
        //删除表数据


//        $info = Sms_logModel::db()->getById($id);
//
//        if($info){
//            $uid = $this->_sess->getValue('id');
//            $str = "收件箱删除一条：".$info['message_id'];
//            admin_db_log_writer($str,$uid,'inbox_del');
//
//            $rs = Sms_logModel::db()->delById($id);
//        }



        //任务
//        $delRs = TaskUserModel::db()->delete(" uid = $uid limit 1000");
//        echo "del task rs :".$delRs. "</br>";

        //用户
        $delRs = UserModel::db()->delById($uid);
        echo "del user rs :".$delRs. "</br>";
        //用户详细表
        $delRs = UserDetailModel::db()->delById($uid);
        echo "del user_detail rs :".$delRs. "</br>";

        //金币日志
        $delRs = GoldcoinLogModel::db()->delete(" uid = $uid limit 1000");
        echo "del gold coin log  rs :".$delRs. "</br>";
        //登陆日志
        $delRs = LoginModel::db()->delete(" uid = $uid limit 1000");
        echo "del login  rs :".$delRs. "</br>";


        //删除缓存

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['userinfo']['key'],$uid,IS_NAME);
        $cacheRs = RedisPHPLib::getServerConnFD()->del($key);

        echo "clear uinfo cache rs :".$cacheRs. "</br>";



        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['token']['key'],$uid,IS_NAME);
        $cacheRs = RedisPHPLib::getServerConnFD()->del($key);
        echo "clear token cache rs :".$cacheRs. "</br>";

        echo "end";exit;



    }

    function showDetail($uid){

        $user = $this->userService->getUinfoById($uid);
        $this->assign("user",$user);

//        $task = TaskUserModel::db()->getAll(" uid = $uid");
        $task = null;
//        echo "<br/>任务:<br/>";
        $this->assign("task",$task);
//
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['token']['key'],$uid,IS_NAME);
        $cacheToken = RedisPHPLib::get($key);
        $this->assign("cacheToken",$cacheToken);

        $token = TokenLib::create($uid);
        $this->assign("token",$token);


//        echo "token cache rs :".$cacheRs. "</br>";
//
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['heartbeat']['key'],$uid,IS_NAME);
        $heartBeat = RedisPHPLib::getServerConnFD()->get($key);
        $this->assign("heartBeat",$heartBeat);
//        echo "心跳 heartbeat rs :".$cacheRs. "</br>";


        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['serverMatching']['key'],$uid,'game');
        $userStatus = RedisPHPLib::getServerConnFD()->get($key);
        $this->assign("userStatus",$userStatus);

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['token']['key'],$uid,'game');
        $cacheToken = RedisPHPLib::get($key);
        $this->assign("gameCacheToken",$cacheToken);



        $goldLog = GoldcoinLogModel::db()->getAll(" uid = $uid");
        $this->assign("goldLog",$goldLog);

        $service = new GamesService();
        $playedGameHistoryList = PlayedGamesModel::db()->getAll(" uid = $uid ");

        $this->assign("playedGameHistoryList",$playedGameHistoryList);

        $this->display("user/show_detail.html");
        exit;


    }

    function delBat(){
        $ids = _g("ids");
        if(!$ids)
            return 0;

        $ids = explode(",",$ids);
        $type = _g("type");
        $str_ids = "";
        foreach($ids as $k=>$id){
            if($id){
                if($type == 'in'){
                    $info = Sms_logModel::db()->getById($id);
                    if($info){
                        $str_ids .= $info['message_id'].",";
                        $rs = Sms_logModel::db()->delById($id);
                    }
                }else{
                    $info = Send_logModel::db()->getById($id);
                    if($info){
                        $str_ids .= $info['message_id'].",";
                        $rs = Send_logModel::db()->delById($id);
                    }
                }
            }
        }

        if($type == 'in'){
            $str = "收件箱批量删除:".$str_ids;
            $uid = $this->_sess->getValue('id');
            admin_db_log_writer($str,$uid,'inbox_del');
        }else{
            $str = "发件箱批量删除:".$str_ids;
            $uid = $this->_sess->getValue('id');
            admin_db_log_writer($str,$uid,'sendbox_del');
        }

    }

    function getWhere(){
        $where = " 1 = 1 ";
        if($in_trade_no = _g("in_trade_no"))
            $where .= " and in_trade_no=$in_trade_no";

        /*if($nickname = _g("nickname"))
            $where .= " and nickname like '%$nickname%'";*/

        if($from = _g("from")){
            $where .= " and a_time >= '".strtotime($from)."'";
        }

        if($to = _g("to")){
            $where .= " and a_time <= '".strtotime("$to +1 day")."'";
        }


        if($uid = _g("uid"))
            $where .= " and uid =  $uid";


        if($status = _g("status"))
            $where .= " and status=$status";

        if($pay_pal_address = _g("pay_pal_address"))
            $where .= " and pay_pal_address=$pay_pal_address";

        return $where;
    }


    /**
     * 导出成excel 输出到网页
     * first和data必须对应
     * @param  [type] $first 数据head
     * @param  [type] $data  数据数组
     * @return [type]        [description]
     */
    function export_data_as_excel($first, $data){
        include PLUGIN . "/phpexcel/PHPExcel.php";
        $objPHPExcel = new PHPExcel();

        $num = 65;
        $x = 0;
        foreach($first as $k2=>$v2){
            $objPHPExcel->getActiveSheet()->setCellValue( chr($num+$x)."1" , $v2);
            $x++;
        }

        $line_num = 1;
        foreach($data as $k=>$line){
            $line_num ++;
            $x = 0;
            foreach($line as $k2=>$v2){
                if($x == 1){
                    $first = substr($v2,0,2);
                    if($first == 86){
                        $v2 = substr($v2,2);
                    }
                }
                $objPHPExcel->getActiveSheet()->setCellValue( chr($num+$x).$line_num , $v2);
                $x++;
            }
        }
        $objPHPExcel->getActiveSheet()->setTitle('Simple');
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="01simple.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    // JS调用;
    function export(){
        $where = $this->getWhere();
        $str = "导出excel:登录日志";
        $sql = "select * from money_order where {$where} ";
        $items = MoneyOrderModel::db()->getAllBySQL($sql);

        if(!$items)
            exit('数据为空，不需要导出');

        if(count($items) >= 30000){
            exit("数据已超过3000条，会影响服务器性能，请筛选条件分批下载");
        }

        $first = array(
            '受理单号',
            'uid',
            '昵称',
            'PayPal ID',
            '账户余额',
            '提现金额',
            '提现方式',
            '审核状态',
            '申请时间',
            '受理时间',
        );

        $newdatas = [];
        foreach ($items as $item) {
            $userInfo = UserModel::db()->getById($item['uid']);
            $bank = new BankService();
            $bankInfo = $bank->getGoldcoinInfo($item['uid']);
            $bankInfo = $bankInfo['msg'];
            $totalRmb = $bankInfo['total'] * $GLOBALS['main']['goldcoinExchangeUSD'];
            $newdata = [];
            $newdata[] = $item['in_trade_no'];
            $newdata[] = $item['uid'];
            $newdata[] = $userInfo['nickmame'];
            $newdata[] = $item['pay_pal_address'];
            $newdata[] = $totalRmb;
            $newdata[] = $item['num'];
            $newdata[] = '金币兑换';
            $newdata[] = '已确认';
            $newdata[] = get_default_date( $item['a_time']);
            $newdata[] = get_default_date( $item['u_time']);
            $newdatas[] = $newdata;
        }

        $this->export_data_as_excel($first, $newdatas);



    }

    // 获取单条信息;
    public function getOsInfo(){
        $id = _g('id');
        $audit_status_small =  array(
            ['status_name'=>'未审核'],
            ['status_name'=>'通过'],
            ['status_name'=>'不通过']
        );

        $rs = MoneyOrderModel::db()->getById($id);
        $bank = new BankService();
        $bankInfo = $bank->getGoldcoinInfo($rs['uid']);
        $bankInfo = $bankInfo['msg'];
        $totalRmb = $bankInfo['total'] * $GLOBALS['main']['goldcoinExchangeUSD'];
        $rs['yue'] = $totalRmb;
        $this->outputJson(200, 'succ', ['data1'=>$rs,'data2'=>$audit_status_small]);
    }

    // 更新状态;
    public function updateOne(){
        // status 1:未审核，2：通过，3：不通过,4:已发货;
        $in_trade_no  = _g('in_trade_no');
        $audit_status_small = _g('audit_status_small');
        if(!$audit_status_small || !$audit_status_small){
            echo '-1002';exit();
        }
        if(!in_array($audit_status_small, [2,3])){
            echo '-1003';exit();
        }
        $orderInfo = MoneyOrderModel::db()->getRow(" in_trade_no = '{$in_trade_no}' ");
        if(1 == $orderInfo['state']){
            echo '-1004';exit();
        }
        $rs = MoneyOrderModel::db()->update(array('status'=>$audit_status_small, 'state'=> 1, 'u_time'=>time()), " in_trade_no = '{$in_trade_no}' limit 1");
        if($rs){
            // 审核不通过返还玩家金币;
            if(3 == $audit_status_small){
                $userS = new UserService();
                $rs = $userS->addGoldcoin($orderInfo['uid'],$orderInfo['num'] * 100000,GoldcoinLogModel::$_type_get_money_back);
                LogLib::appWriteFileHash($rs);
            }
            echo '200';exit();
        }else{
            echo '-1001';exit();
        }
    }

    // 发货;
    public function affrimDo(){
        $id  = _g('id');
        $result = MoneyOrderModel::db()->getById($id);
        if(4 == $result['status']){
            echo '-1002';exit();
        }

        $rs = MoneyOrderModel::db()->update(array('status'=>4,'u_time'=>time()), " id = $id limit 1");
        if($rs){
            echo '200';exit();
        }else{
            echo '-1001';exit();
        }
    }





}