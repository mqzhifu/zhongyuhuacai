<?php
class BaseCtrl{
    public $_st = null;
    public $_js = array();
    public $_css = array();
    public $_title = '';
    public $_hook_js = '';
    public $_assign = array();
    public $_adminid = "";

    function __construct($frame = null,$ctrl,$ac,$cate,$sub){
        //接口配置信息
        include_once CONFIG_DIR.DS.IS_NAME."/api.php";

        $this->ctrl = $ctrl;
        $this->ac = $ac;
        $this->cate = $cate;
        $this->sub = $sub;


        $this->_st = getAppSmarty();
        $this->_acl = get_instance_of('AclLib');
        $this->_sess = get_instance_of('SessionLib');

        if($ac != 'login' && $ac !='sms' && $ac !='logout'  && $ac != 'loginuser' && $ac != 'verifyImg' && $ac != 'verify'){
            if(!$this->_acl->isLogin()){
                jump("/no/no/index/login/");
                exit;
            }
        }

        //实例化 用户 服务 控制器
        $this->userService = new UserService();
        $this->msgService = new MsgService();
        $this->friendService = new FriendService();
        $this->fansService = new FansService();
        $this->gamesService = new GamesService();
        $this->imSevice = new ImService();
        $this->advertiseService = new AdvertiseService();
        $this->systemService = new SystemService();


        define("H_STATIC_URL",get_static_url().get_tmp_status_dir());

        $this->_adminid = $this->_sess->getValue('id');

        define("LOGIN_UID",$this->_adminid);
        //记录请求日志
        $id = AdminLogModel::addReq($this->_adminid,$this->cate,$this->sub,$this->ctrl,$this->ac);
        define("ACCESS_ID",$id);

        $GLOBALS['jsonindex'] = [];
        if(file_exists(APP_CONFIG."index.json")){
            $jsonindex = file_get_contents(APP_CONFIG."index.json");
            $GLOBALS['jsonindex'] = json_decode($jsonindex,true);
        }

        include_once APP_CONFIG."code.php";
        include_once APP_CONFIG.DS."rediskey.php";
        include_once APP_CONFIG.DS."apiVersion.php";

        if ($this->_adminid) {
            $role_id = AdminUserModel::db()->getRowById($this->_adminid)['role_id'];
            $ids = RolesModel::db()->getRowById($role_id)['power'];
            $menu = MenuModel::getMenu($ids);
            $this->assign("menu",$menu);
        }
        
        $this->init_css_js();
        $this->assign("uname",$this->_sess->getValue('uname'));

        if(PCK_AREA == 'en'){
            $this->assign("foreign", true);
        }

    }

    function getStaticBaseUrl($locate='instantplayadmin'){
        $baseUrl = "https://mgres.kaixin001.com.cn/xyx";
        if(ENV == 'release'){
            $baseUrl .= DIRECTORY_SEPARATOR."pro".DIRECTORY_SEPARATOR;
        } else {
            $baseUrl .= DIRECTORY_SEPARATOR."dev".DIRECTORY_SEPARATOR;
        }

        $baseUrl .= "upload".DIRECTORY_SEPARATOR.$locate;
        return $baseUrl;
    }

    function addJs($dir_file){
        if(!in_array($dir_file,$this->_js)){
            $this->_js[] = $dir_file;
        }
    }

    function addHookJS($js){
        $this->_hook_js = $js;
    }

    function addCss($dir_file){
        if(!in_array($dir_file,$this->_css)){
            $this->_css[] = $dir_file;
        }
    }

    function setTitle($title){
        $this->_title = $title;
    }

    function initCss(){
        $css = "";
        if($this->_css){
            foreach($this->_css as $k=>$v){
                $css .= '<link href="'.H_STATIC_URL.$v.'" rel="stylesheet" type="text/css"/>';
            }
        }
        return $css;
    }

    function initJS(){
        $js = "";
        if($this->_js){
            foreach($this->_js as $k=>$v){
                $js .= '<script src="'.H_STATIC_URL.$v.'" type="text/javascript"></script>';
            }
        }
        return $js;
    }

    function assign($k,$v){
        $this->_assign[$k] = $v;
    }

    function init_css_js(){
        $this->addCss('/assets/global/google/font.css');


        $this->addCss('/assets/global/plugins/font-awesome/css/font-awesome.min.css');
        $this->addCss('/assets/global/plugins/simple-line-icons/simple-line-icons.min.css');
        $this->addCss('/assets/global/plugins/bootstrap/css/bootstrap.min.css');
        $this->addCss('/assets/global/plugins/uniform/css/uniform.default.css');
        $this->addCss('/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css');
        $this->addCss('/assets/global/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css');
        $this->addCss('/assets/global/plugins/fullcalendar/fullcalendar.min.css');
        $this->addCss('/assets/global/plugins/jqvmap/jqvmap/jqvmap.css');
        $this->addCss('/assets/admin/pages/css/tasks.css" rel="stylesheet');
        $this->addCss('/assets/global/css/components.css');
        $this->addCss('/assets/global/css/plugins.css');
        $this->addCss('/assets/admin/layout/css/layout.css');
        $this->addCss('/assets/admin/layout/css/themes/darkblue.css');
        $this->addCss('/assets/admin/layout/css/custom.css');

        $this->addJs('/assets/global/plugins/respond.min.js');
        $this->addJs('/assets/global/plugins/excanvas.min.js');

        $this->addJs('/assets/global/plugins/jquery-migrate.min.js');
        $this->addJs('/assets/global/plugins/jquery-ui/jquery-ui.min.js');
        $this->addJs('/assets/global/plugins/bootstrap/js/bootstrap.min.js');
        $this->addJs('/assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js');
        $this->addJs('/assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js');
        $this->addJs('/assets/global/plugins/jquery.blockui.min.js');
        $this->addJs('/assets/global/plugins/jquery.cokie.min.js');
        $this->addJs('/assets/global/plugins/uniform/jquery.uniform.min.js');
        $this->addJs('/assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js');
        $this->addJs('/assets/global/plugins/jqvmap/jqvmap/jquery.vmap.js');
        $this->addJs('/assets/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.russia.js');
        $this->addJs('/assets/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.world.js');
        $this->addJs('/assets/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.europe.js');
        $this->addJs('/assets/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.germany.js');
        $this->addJs('/assets/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.usa.js');
        $this->addJs('/assets/global/plugins/jqvmap/jqvmap/data/jquery.vmap.sampledata.js');
        $this->addJs('/assets/global/plugins/flot/jquery.flot.min.js');
        $this->addJs('/assets/global/plugins/flot/jquery.flot.resize.min.js');
        $this->addJs('/assets/global/plugins/flot/jquery.flot.categories.min.js');
        $this->addJs('/assets/global/plugins/jquery.pulsate.min.js');
        $this->addJs('/assets/global/plugins/bootstrap-daterangepicker/moment.min.js');
        $this->addJs('/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.js');
        $this->addJs('/assets/global/plugins/fullcalendar/fullcalendar.min.js');
        $this->addJs('/assets/global/plugins/jquery-easypiechart/jquery.easypiechart.min.js');
        $this->addJs('/assets/global/plugins/jquery.sparkline.min.js');
        $this->addJs('/assets/global/scripts/metronic.js');
        $this->addJs('/assets/admin/layout/scripts/layout.js');
        $this->addJs('/assets/admin/layout/scripts/quick-sidebar.js');
        $this->addJs('/assets/admin/layout/scripts/demo.js');
        $this->addJs('/assets/admin/pages/scripts/index.js');
        $this->addJs('/assets/admin/pages/scripts/tasks.js');

        //=====================================上面都是公共的==========================
        $this->addCss('/assets/global/plugins/select2/select2.css');
        $this->addCss('/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css');
        $this->addCss('/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css');
//        $this->addCss('/assets/admin/layout4/css/themes/light.css');
        $this->addCss('/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css');


        $this->addJs('/assets/global/plugins/select2/select2.min.js');
        $this->addJs('/assets/global/plugins/datatables/media/js/jquery.dataTables.min.js');
        $this->addJs('/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js');
//        $this->addJs('/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js');

        $this->addJs('/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js');

        $this->addJs('/assets/global/scripts/datatable.js');
        $this->addJs('/assets/global/plugins/bootbox/bootbox.min.js');
    }


    function display($file){
        $ac = $this->ac;
        $ctrl = $this->ctrl;
        $css = $this->initCss();
        $js = $this->initJS();

        if($this->_assign){
            foreach($this->_assign as $k=>$v){
                $$k = $v;
            }
        }


        $header_html = $this->_st->compile("layout/header.html");
        $index_html = $this->_st->compile($file);
        $footer_html = $this->_st->compile("layout/footer.html");


        $hook_js = $this->_hook_js;

        include $header_html;
        include $index_html;
        include $footer_html;
        exit;

    }

    public function outputJson ($code, $message, $data = [])
    {
        header("Content-Type: application/json");
        echo json_encode([
            "code" => $code,
            "message" => $message,
            "data" => $data,
        ]);
        exit(0);
    }


    /**
     * 静态资源路径（对老路径作兼容）
     * @param  [string] $module [模块名]
     * @param  [string] $path   [详细路径]
     * @param  [string] $appName   [图片所在项目名]
     * @param  [bool] $admin   老路径是否在admin
     * @return [string] 完整路径
     */
    public function getStaticFileUrl($module, $path, $appName = APP_NAME, $admin=false)
    {   
        if (strpos($path, $module) === false) {
            # 新路径
            return get_static_file_url_by_app($module, $path, $appName);
        } else {
            # 老路径
            if ($admin) {
                $appName = "instantplayadmin";
            }
            $openGamesService = new OpenGamesService();
            return $openGamesService->getOldStaticImageUrl($module, $path, $appName);
        }
    }
}