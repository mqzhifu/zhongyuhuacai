<?php
set_time_limit(600);
header("Content-type:text/html;charset=utf-8");
class InviteCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        
        $this->display("user/invite.html");

    }


    function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();

        $sql = "select count(*) as cnt from invite";
        

        $cntSql = UserModel::db()->getRowBySQL($sql);
        //array(4) { ["id"]=> string(1) "4" ["name"]=> string(5) "xuren" ["level"]=> string(2) "10" ["sex"]=> string(1) "0" }
//        echo $sql;
//        var_dump($cntSql);exit;

        $cnt = 0;
        if(arrKeyIssetAndExist($cntSql,'cnt')){
            $cnt = $cntSql['cnt'];
        }

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "asc";


            $sort = array(
                '',
                'id',
                'uid',
                'to_uid',
                'a_time',
            );
            $order = $sort[$order_column]." ".$order_dir;

            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始


            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;


            // $sql = "SELECT COUNT(b.`uid`) AS login_cnt,b.`a_time` AS last_login_time,
            //         a.id,a.name,a.nickname,a.birthday,a.country,a.province,a.city,a.avatar,a.a_time,a.sex,a.is_online,a.cellphone,a.point,a.goldcoin,a.diamond,a.`type` 
            //         FROM user AS a  LEFT JOIN login AS b  ON a.id = b.uid where $where GROUP BY b.uid order by $order limit $iDisplayStart,$end ";
            $sql = "select * from invite where $where GROUP BY id order by $order limit $iDisplayStart,$end ";

            // echo $sql;
            // exit;
//            echo $sql;exit;
            $data = UserModel::db()->getAllBySQL($sql);
            foreach($data as $k=>$v){
                $avatarImg = "";
                if(arrKeyIssetAndExist($v,'avatar')){
                    $avatarImg = "<img width='50' height='50' src='{$v['avatar']}' />";
                }

                $loginCnt = LoginModel::db()->getCount(" uid = ".$v['id']);
                $lastLogin = LoginModel::db()->getRow(" uid = ".$v['id'] ." order by a_time desc");
                $lastLoginTime = 0;
                if($lastLogin){
                    $lastLoginTime = $lastLogin['a_time'];
                }
                $records["data"][] = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['uid'],
                    $v['to_uid'],
                    get_default_date($v['a_time']),

//                    '<a href="#" class="btn btn-xs default red delone" data-id="'.$v['id'].'"><i class="fa fa-trash-o"></i> 删除</a>'.
                    '',
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


    function getWhere(){
        $where = " 1 ";
        if($id = _g("id"))
            $where .= " and id=$id";

        if($uid = _g("uid")){
            $where .= " and uid like '%$uid%'";

        }

        if($to_uid = _g("to_uid"))
            $where .= " and to_uid like '%$to_uid%'";

		// if($a_time = _g("a_time")){

  //           $where .= " and a_time=$a_time";
		// }

		if($a_time_from = _g("a_time_from")){
            $a_time_from .= ":00";
            $where .= " and a_time >= '".strtotime($a_time_from)."'";
        }

        if($a_time_to = _g("a_time_to")){
            $a_time_to .= ":59";
            $where .= " and a_time <= '".strtotime($a_time_to)."'";
        }

        return $where;
    }


}