<?php
class indexCtrl extends BaseCtrl{

    function index(){
        $this->setTitle('test');


        $yesterday = get_yesterday();
        $yesterdayTime = dayStartEndUnixtime($yesterday);
        $userYesterdayRegCnt = UserModel::db()->getCount("a_time >=  {$yesterdayTime['s_time']} and a_time <= {$yesterdayTime['e_time']}");

        $this->assign("userYesterdayRegCnt",$userYesterdayRegCnt);


        $yesterdayOrderTotalDb = OrderModel::db()->getRow("a_time >=  {$yesterdayTime['s_time']} and a_time <= {$yesterdayTime['e_time']}",null, " sum(total_price) as total");
        $yesterdayOrderTotal = 0;
        if($yesterdayOrderTotalDb['total']){
            $yesterdayOrderTotal = $yesterdayOrderTotalDb['total'];
        }
        $yesterdayOrderCnt = OrderModel::db()->getCount("a_time >=  {$yesterdayTime['s_time']} and a_time <= {$yesterdayTime['e_time']}");


        $this->assign("yesterdayOrderTotal",$yesterdayOrderTotal);
        $this->assign("yesterdayOrderCnt",$yesterdayOrderCnt);

        $this->addHookJS("/index/index_hook.html");
        $this->display("/index/index.html");

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

    function chart(){
        $this->display("/index/chart.html");
    }

}