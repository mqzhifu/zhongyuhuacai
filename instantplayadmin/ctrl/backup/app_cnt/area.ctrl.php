<?php
/**
 * Created by PhpStorm.
 * User: xiahongbo
 * Date: 2019/3/20
 * Time: 9:44
 */

/**
 * Class areaCtrl
 */
class areaCtrl extends BaseCtrl{
    public function index(){
        // 一定用到的js/css分配;
        $this->addJs('/assets/isadmin/js/jquery.js');
        $this->addJs('/assets/isadmin/js/chinaMapConfigHigh.js');
        $this->addJs('/assets/isadmin/js/chinaMapSettings.xml');
        $this->addJs('/assets/isadmin/js/map.js');
        $this->addJs('/assets/isadmin/js/raphael-min.js');
        $this->addJs('/assets/isadmin/js/SyntaxHighlighter.js');
        $this->addJs('/assets/isadmin/js/worldMapConfig.js');
        $this->addJs('/assets/isadmin/js/chinaMapConfig.js');
        $this->addCss('/assets/isadmin/css/SyntaxHighlighter-set-create.css');
        // 可能用到的js/css的分配;
        /*$this->addJs('/assets/global/plugins/jquery.min.js');
        $this->addJs('/assets/global/plugins/jquery-migrate.min.js');
        $this->addJs('/assets/global/plugins/jquery-ui/jquery-ui.min.js');
        $this->addJs('/assets/global/plugins/bootstrap/js/bootstrap.min.js');
        $this->addJs('/assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js');
        $this->addJs('/assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js');
        $this->addJs('/assets/global/plugins/jquery.blockui.min.js');
        $this->addJs('/assets/global/plugins/jquery.cokie.min.js');
        $this->addJs('/assets/global/plugins/uniform/jquery.uniform.min.js');
        $this->addJs('/assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js');
        $this->addJs('/assets/global/plugins/select2/select2.min.js');
        $this->addJs('/assets/global/plugins/datatables/media/js/jquery.dataTables.min.js');
        $this->addJs('/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js');
        $this->addJs('/assets/global/scripts/metronic.js');
        $this->addJs('/assets/admin/layout/scripts/layout.js');
        $this->addJs('/assets/admin/layout/scripts/quick-sidebar.js');
        $this->addJs('/assets/admin/layout/scripts/demo.js');
        $this->addJs('/assets/admin/pages/scripts/table-managed.js');*/
        $data[0]['aa'] = 11;
        $data[0]['bb'] = 22;
        $data[0]['cc'] = 33;
        $data[0]['cc'] = 33;
        $data[0]['dd'] = 33;
        $data[0]['ee'] = 11;

        $data[1]['aa'] = 22;
        $data[1]['bb'] = 33;
        $data[1]['cc'] = 33;
        $data[1]['dd'] = 33;
        $data[1]['ee'] = 11;

        $data[2]['aa'] = 22;
        $data[2]['bb'] = 22;
        $data[2]['cc'] = 33;
        $data[2]['dd'] = 33;
        $data[2]['ee'] = 33;
        //$a = "{'jiangsu':{'value':'30.05%','index':'1','stateInitColor':'0'},'henan':{'value':'19.77%','index':'2','stateInitColor':'0'},'anhui':{'value':'10.85%','index':'3','stateInitColor':'0'},'zhejiang':{'value':'10.02%','index':'4','stateInitColor':'0'},'liaoning':{'value':'8.46%','index':'5','stateInitColor':'0'},'beijing':{'value':'4.04%','index':'6','stateInitColor':'1'},'hubei':{'value':'3.66%','index':'7','stateInitColor':'1'},'jilin':{'value':'2.56%','index':'8','stateInitColor':'1'},'shanghai':{'value':'2.47%','index':'9','stateInitColor':'1'},'guangxi':{'value':'2.3%','index':'10','stateInitColor':'1'},'sichuan':{'value':'1.48%','index':'11','stateInitColor':'2'},'guizhou':{'value':'0.99%','index':'12','stateInitColor':'2'},'hunan':{'value':'0.78%','index':'13','stateInitColor':'2'},'shandong':{'value':'0.7%','index':'14','stateInitColor':'2'},'guangdong':{'value':'0.44%','index':'15','stateInitColor':'2'},'jiangxi':{'value':'0.34%','index':'16','stateInitColor':'3'},'fujian':{'value':'0.27%','index':'17','stateInitColor':'3'},'yunnan':{'value':'0.23%','index':'18','stateInitColor':'3'},'hainan':{'value':'0.21%','index':'19','stateInitColor':'3'},'shanxi':{'value':'0.11%','index':'20','stateInitColor':'3'},'hebei':{'value':'0.11%','index':'21','stateInitColor':'4'},'neimongol':{'value':'0.04%','index':'22','stateInitColor':'4'},'tianjin':{'value':'0.04%','index':'23','stateInitColor':'4'},'gansu':{'value':'0.04%','index':'24','stateInitColor':'4'},'shaanxi':{'value':'0.02%','index':'25','stateInitColor':'4'},'macau':{'value':'0.0%','index':'26','stateInitColor':'7'},'hongkong':{'value':'0.0%','index':'27','stateInitColor':'7'},'taiwan':{'value':'0.0%','index':'28','stateInitColor':'7'},'qinghai':{'value':'0.0%','index':'29','stateInitColor':'7'},'xizang':{'value':'0.0%','index':'30','stateInitColor':'7'},'ningxia':{'value':'0.0%','index':'31','stateInitColor':'7'},'xinjiang':{'value':'0.0%','index':'32','stateInitColor':'7'},'heilongjiang':{'value':'0.0%','index':'33','stateInitColor':'7'},'chongqing':{'value':'0.0%','index':'34','stateInitColor':'7'}};";
        if(_g("getlist")){
            $this->getList();
        }
        $this->assign('data', $data);
        $this->display("/app_cnt/area/index.html");
    }

    public function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        $select_sql = "SELECT *,COUNT(id) AS ids, 
                       COUNT(CASE WHEN sex = 1 THEN sex END ) AS maleCount,
                       COUNT(CASE WHEN sex = 2 THEN sex END ) AS femaleCount,
                       DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') AS date_new
                       FROM user GROUP BY date_new ORDER BY date_new DESC;";
        $result = GoldcoinLogModel::getAll($select_sql);
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
            $end = $end > $iTotalRecords ? $iTotalRecords : 300;

            $selectSql = " SELECT *,COUNT(id) AS ids, 
                           COUNT(CASE WHEN sex = 1 THEN sex END ) AS maleCount,
                           COUNT(CASE WHEN sex = 2 THEN sex END ) AS femaleCount,
                           DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') AS date_new
                           FROM user GROUP BY date_new ORDER BY date_new DESC
                           LIMIT $iDisplayStart, $end ";
            $data = GoldcoinLogModel::getAll($selectSql);
            foreach ($data as &$value){
                $sql1 = "SELECT COUNT(access_log.id) AS cnt,
                     COUNT(user.id) AS users_cnt,
                     FROM_UNIXTIME(access_log.a_time,'%Y-%m-%d') AS tt,
                     COUNT(CASE WHEN user.sex = 1 THEN user.sex END ) AS maleCounthuoyue,
                     COUNT(CASE WHEN user.sex = 2 THEN user.sex END ) AS femaleCounthuoyue
                     FROM access_log LEFT JOIN user ON access_log.uid = user.id 
                     WHERE FROM_UNIXTIME(access_log.a_time,'%Y-%m-%d') = '{$value['date_new']}';";
                $tmp_val = GoldcoinLogModel::getAll($sql1);
                $value['huoyue'] = $tmp_val[0]['cnt'];// 总活跃
                $value['maleCounthuoyue'] = $tmp_val[0]['maleCounthuoyue'];// 男活跃;
                $value['femaleCounthuoyue'] = $tmp_val[0]['femaleCounthuoyue'];// 女活跃;
                $value['users_cnt'] = $tmp_val[0]['users_cnt'];// 当月总数;
                $sql2 = "SELECT count(id) AS zongrenshu,COUNT(CASE WHEN sex = 1 THEN sex END ) AS nanrenshu,COUNT(CASE WHEN sex = 1 THEN sex END ) AS nvrenshu FROM user WHERE DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') <= '{$value['date_new']}'";
                $tmp_val1 = GoldcoinLogModel::getAll($sql2);
                $value['zongrenshu'] = $tmp_val1[0]['zongrenshu'];
                $value['nanrenshu'] = $tmp_val1[0]['nanrenshu'];
                $value['nvrenshu'] = $tmp_val1[0]['nvrenshu'];
                $sql3 = "SELECT count(id) AS zl,COUNT(CASE WHEN sex = 1 THEN sex END ) AS nanl,COUNT(CASE WHEN sex = 1 THEN sex END ) AS nvl FROM user WHERE DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') < '{$value['date_new']}'";
                $tmp_val2 = GoldcoinLogModel::getAll($sql3);
                $value['zl'] = $tmp_val2[0]['zl'];
                $value['nanl'] = $tmp_val2[0]['nanl'];
                $value['nvl'] = $tmp_val2[0]['nvl'];
            }
            foreach ($data as $k => $v) {
                $records["data"][] = array(
                    '1',
                    '1',
                    '1',
                    '1',
                    '1',
                    '1',
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
     * 查看省趋势详情
     */
    public function provinceView(){
        $this->addCss("/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css");
        $this->addCss("/assets/admin/pages/css/profile.css");
        $this->addCss("/assets/admin/pages/css/tasks.css");

        $this->addCss("/assets/global/plugins/datatables/extensions/ColReorder/css/dataTables.colReorder.min.css");
        $this->addCss("/assets/global/plugins/datatables/extensions/Scroller/css/dataTables.scroller.min.css");
        $this->addCss("/assets/global/plugins/select2/select2.css");
        $this->addCss("/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css");
        $this->addCss("/assets/global/css/plugins.css");
        $this->display("/app_cnt/area/provinceView.html");
    }

    public function getGameActiveLine(){
        $type = _g("type");
        switch($type){
            case 1://按日
                $sql = "select count(g.id) as active,g.id from games g left join played_games pg on g.id=pg.game_id where to_days(from_unixtime(pg.a_time,'%Y-%m-%d %H:%i:%s')) = to_days(now()) group by g.id order by count(g.id) desc limit 10";
                break;
            case 2://按周
                $sql = "select count(g.id) as active,g.id,YEARWEEK(now()) from games g left join played_games pg on g.id=pg.game_id where YEARWEEK(date_format(from_unixtime(pg.a_time,'%Y-%m-%d %H:%i:%s'),'%Y-%m-%d')) = YEARWEEK(now()) group by g.id order by count(g.id) desc limit 10";
                break;
            case 3://按月
                $sql = "select count(g.id) as active,g.id from games g left join played_games pg on g.id=pg.game_id where date_format(from_unixtime(pg.a_time,'%Y-%m-%d %H:%i:%s'),'%Y%m') = date_format(curdate(),'%Y%m') group by g.id order by count(g.id) desc limit 10";
                break;
                default:
                $sql = "select count(g.id) as active,g.id from games g left join played_games pg on g.id=pg.game_id group by g.id order by count(g.id) desc limit 10";

        }
        $data = UserModel::db()->getAllBySQL($sql);
        $resultData = [];
        foreach ($data as $value) {
            switch ($type) {
                case '1'://按日
                    $sql2 = "select count(game_id) active,game_id,from_unixtime(a_time,'%d') as tt from played_games where game_id=".$value['id']." group by from_unixtime(a_time,'%Y-%m-%d')  order by tt  desc limit 7";
                    break;
                case '2'://按周
                    $sql2 = "select count(game_id) active,game_id,from_unixtime(a_time,'%u') as tt from played_games where game_id=".$value['id']." group by from_unixtime(a_time,'%Y-%u')  order by tt  desc limit 7";
                    break;
                case '3'://按月
                    $sql2 = "select count(game_id) active,game_id,from_unixtime(a_time,'%m') as tt from played_games where game_id=".$value['id']." group by from_unixtime(a_time,'%Y-%m')  order by tt  desc limit 7";
                    break;
                default://按日
                    $sql2 = "select count(game_id) active,game_id,from_unixtime(a_time,'%d') as tt from played_games where game_id=".$value['id']." group by from_unixtime(a_time,'%Y-%m-%d')  order by tt  desc limit 7";
                    break;
            }
            $res = UserModel::db()->getAllBySQL($sql2);
            if($res){
                $newData = [];
                foreach ($res as $value) {
                    $newData[] = [$value['tt'], $value['active']];
                }
                $obj = new Result();
                $obj->data = $newData;
                $obj->label = $res[0]['game_id'];

                $resultData[] = $obj;
            }
        }
        $this->outputJson(200, "succ", $resultData);

    }
}