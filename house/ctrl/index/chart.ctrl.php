<?php
class chartCtrl extends BaseCtrl{

    function index(){

//        $this->setTitle('test');
//


        $yearList = get_year_list_range(2019,2021);
        $monthList = get_month_list();
        $this->assign("yearList",json_encode($yearList));
        $this->assign("monthList",json_encode($monthList));

        $this->addHookJS("/index/chart_hook.html");
        $this->display("/index/chart.html");

    }

    function insertUserTestData(){
//        UserModel::db()->delete(" id > 1 limit 100000");

        $year = _g("year");
        $month = _g("month");

        $rand = rand(1,31);
        $ym = $year . "-" .$month ."-";
        for($i=1;$i<=$rand;$i++){

            $r = rand(1,24);
            $ymd = $ym . $i . " $r:01:01";
            $unixTime = strtotime($ymd);
            for($j=1;$j<=100;$j++){
                $data = array('nickname'=>$i ."-".$j , 'a_time'=>$unixTime);
                UserModel::db()->add($data);
            }
        }
    }

    function getUserByYearMonth(){
        $type = _g("type");
        $year = _g("year");
        $month = _g("month");

        if(!$year){
            exit("year is null");
        }

        if(!$month){
            exit("month is null");
        }


        $lastDay = get_month_last_day($year,$month);
        $start = strtotime($year . "-". $month . "-1" );
        $end = strtotime($year . "-". $month . "-$lastDay" );


        $sql = " SELECT COUNT(a_time) as num, FROM_UNIXTIME(a_time,'%c-%e')  AS md  FROM USER where a_time >=  $start and a_time<= $end  GROUP BY  FROM_UNIXTIME(a_time,'%Y-%m-%d')   ";
        $data = UserModel::db()->getAllBySQL($sql);
        if(!$data){
            out_ajax(200,null);
        }

        $list = null;
        if(count($data) == $lastDay){
            foreach ($data as $k=>$v){
                $list[] = [$v['ymd'], $v['num']];
            }
            out_ajax(200,$data);
        }

//        var_dump($data);
//        echo "<br/>";

        for($i=1;$i<=$lastDay;$i++){
            $date = ($month . "-$i" );
//            $list[$date] = 0;
//            out($date);
            $f = 0;
            foreach ($data as $k=>$v){
                if($v['md'] == $date){
                    $f = [$v['md'], $v['num']];
//                    $list[$date] = $v['num'];
                    break;
                }
            }
            if(!$f){
                $list[] = [$date,0];
            }else{
                $list[] =  $f;
            }
        }
//        var_dump($list);exit;

        out_ajax(200,$list);

    }


    function loginuser(){
        $ps = _g("password");
        $uname = _g("username");
        $verify = _g("verify");
        if(!$ps)
            out_ajax(501,'ps null');

        if(!$uname)
            out_ajax(502,'uname null');

        if(!$verify)
            out_ajax(503,'verify null');

        $code = $this->_sess->getImgCode();
        if(strtolower($verify) != strtolower($code))
            out_ajax(501,'verify err');

        $islogin = $this->_acl->adminLogin($uname,$ps);
        if($islogin){
//            $uid = $this->_sess->getValue('id');
//            $str = "登陆：".$uname;
//            admin_db_log_writer($str,$uid,'login');
            out_ajax(200,'ok');
        }else{
            out_ajax(505,'login err');
        }
    }


    function login(){
        if(_g("opt")){

        }

        $this->addCss('/assets/global/google/font.css');
        $this->addCss('/assets/global/plugins/font-awesome/css/font-awesome.min.css');
        $this->addCss('/assets/global/plugins/simple-line-icons/simple-line-icons.min.css');
        $this->addCss('/assets/global/plugins/bootstrap/css/bootstrap.min.css');
        $this->addCss('/assets/global/plugins/uniform/css/uniform.default.css');
        $this->addCss('/assets/global/plugins/select2/select2.css');


        $this->addCss('/assets/admin/pages/css/login-soft.css');

        $this->addCss('/assets/global/css/components-md.css');
        $this->addCss('/assets/global/css/plugins-md.css');

        $this->addCss('/assets/admin/layout/css/layout.css');
        $this->addCss('/assets/admin/layout/css/themes/default.css');
        $this->addCss('/assets/admin/layout/css/custom.css');


//        $this->addJs('/assets/global/plugins/respond.min.js');
//        $this->addJs('/assets/global/plugins/excanvas.min.js');
//        $this->addJs('/assets/global/plugins/jquery.min.js');
        $this->addJs('/assets/global/plugins/jquery-migrate.min.js');
        $this->addJs('/assets/global/plugins/bootstrap/js/bootstrap.min.js');


        $this->addJs('/assets/global/plugins/jquery.blockui.min.js');
        $this->addJs('/assets/global/plugins/uniform/jquery.uniform.min.js');
        $this->addJs('/assets/global/plugins/jquery.cokie.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/backstretch/jquery.backstretch.min.js');
        $this->addJs('/assets/global/plugins/select2/select2.min.js');
        $this->addJs('/assets/global/scripts/metronic.js');
        $this->addJs('/assets/admin/layout/scripts/layout.js');
        $this->addJs('/assets/admin/layout/scripts/demo.js');
        $this->addJs('/assets/admin/pages/scripts/login-soft.js');

        $css = $this->initCss();
        $js = $this->initJS();
//var_dump($js);exit;
        $DOMAIN_URL = H_STATIC_URL;

        $html = $this->_st->compile("login.html");
        include $html;
    }

    function verifyImg(){
        $lib = get_instance_of("ImageAuthCodeLib");
        $lib->showImg();

        $this->_sess->setImgCode($lib->code);
    }


    function logout(){
        $this->_sess->none();
//        $data = array('up_time'=>time(),'is_online'=>0);
//        adminUserModel::db()->update($data," id = ".$this->_adminid. "  limit 1");
        Jump("/");
    }

    function upps(){

    }

//    function chart(){
//        $this->display("/index/chart.html");
//    }

}