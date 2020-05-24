<?php

class BaseCtrl
{
    public $_st = null;
    public $_js = array();
    public $_css = array();
    public $_title = '';
    public $_hook_js = '';
    public $_assign = array();
    public $_uid = "";
    public $userService = null;
    public $openGamesService = null;
    public $openAdvertiseService = null;
    public $uploadService = null;
    public $countsGamesService = null;
    public $advertiseService = null;
    public $discountCost = 0.5;

    public $notLoginUri = [
        '/index/index',
        '/index/login',
        '/index/login',
        '/index/logout',
        '/index/sendsms',
        '/index/codeLogin',
        '/index/passLogin',
        '/index/thirdLogin',
        '/index/thirdReg',
        '/register/index',
        '/register/register',
        '/register/sendSms',
        '/doc/index',
        '/doc/getContent',
        '/doc/getDocs',
        '/developer/openProtocolIndex',
        '/forgetPs/index',
        '/forgetPs/verify',
        '/forgetPs/reset',
        '/forgetPs/verifyUser',
        '/forgetPs/sendSms',
        '/forgetPs/authCode',
        '/forgetPs/resetPs',
        '/forgetPs/success',
        '/publish/home',
    ];

    function __construct ($frame = null, $ctrl, $ac)
    {
        //接口配置信息
        include_once CONFIG_DIR . DS . IS_NAME."/api.php";

        $this->ctrl = $ctrl;
        $this->ac = $ac;
        $this->uri = '/'.$this->ctrl.'/'.$this->ac;
        $this->_st = getAppSmarty();
        $this->_acl = get_instance_of('AclLib');
        $this->_sess = get_instance_of('SessionLib');
        if (!in_array($this->uri, $this->notLoginUri)) {
            if (!$this->isLogin()) {
                jump("/");
                exit;
            }
        }

        //实例化 用户 服务 控制器
        $this->userService = new UserService();
        $this->openGamesService = new OpenGamesService();
        $this->openAdvertiseService = new OpenAdvertiseService();
        $this->uploadService = new UploadService();
        $this->countsGamesService = new countsGamesService();
        $this->advertiseService = new advertiseService();

        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https' : 'http';
        define("H_STATIC_URL", get_static_url($http_type).get_tmp_status_dir());

        $this->_user = $this->_sess->getValue('user');

        $this->_uid = $this->_user['uid'];

        if ($this->_uid)
        {
            $openUser = InformationModel::db()->getRow("uid=".$this->_uid);
            if ($openUser){
                $this->_openuser = $openUser;
            }
        }

        define("LOGIN_UID", $this->_uid);

        $GLOBALS['jsonindex'] = [];
        if (file_exists(APP_CONFIG . "index.json")) {
            $jsonindex = file_get_contents(APP_CONFIG . "index.json");
            $GLOBALS['jsonindex'] = json_decode($jsonindex, true);
        }

        include_once APP_CONFIG . "code.php";
        include_once APP_CONFIG . DS . "rediskey.php";
        include_once APP_CONFIG . DS . "apiVersion.php";


        $this->init_css_js();
        $this->assign("uid", $this->_uid);
        $this->assign("nickname", $this->_user['nickname']);
        if(isset($this->_user['avatar'])){
            $this->assign("avatar", $this->_user['avatar']);

        }else{
            $this->assign("avatar","https://axhub.im/pro/173d447b0792252d/images/%E6%96%87%E6%A1%A3/u5675.png");
        }

        if(isset($this->_user['cellphone'])){
            $this->assign("cellphone", $this->_user['cellphone']);
        }

        if(PCK_AREA == 'en'){
            $this->assign("foreign", true);
        }
        $this->assign("env",ENV);
    }



    /**
     * 先用session做简单登录状态判断，后期需把session放到db，或者只用cookie维护状态
     * @return boolean
     */
    function isLogin ()
    {
        $user = $this->_sess->getValue('user');
        if (!$user)
            return 0;
        return 1;
    }

    function addJs ($dir_file)
    {
        if (!in_array($dir_file, $this->_js)) {
            $this->_js[] = $dir_file;
        }
    }

    function addHookJS ($js)
    {
        $this->_hook_js = $js;
    }

    function addCss ($dir_file)
    {
        if (!in_array($dir_file, $this->_css)) {
            $this->_css[] = $dir_file;
        }
    }

    function setTitle ($title)
    {
        $this->_title = $title;
    }

    function initCss ()
    {
        $css = "";
        if ($this->_css) {
            foreach ($this->_css as $k => $v) {
                $css .= '<link href="' . H_STATIC_URL . $v . '" rel="stylesheet" type="text/css"/>';
            }
        }
        return $css;
    }

    function initJS ()
    {
        $js = "";
        if ($this->_js) {
            foreach ($this->_js as $k => $v) {
                $js .= '<script src="' . H_STATIC_URL . $v . '" type="text/javascript"></script>';
            }
        }
        return $js;
    }

    function assign ($k, $v)
    {
        $this->_assign[$k] = $v;
    }

    function init_css_js ()
    {
        $this->addCss('assets/open/css/public.css');
        $this->addCss('assets/open/css/bootstrap.min.css');
        $this->addJs('assets/open/scripts/jquery-1.12.4.min.js');
        $this->addJs('assets/open/scripts/layer/layer.js');
        $this->addJs('assets/open/scripts/common.js');
        $this->addJs('assets/open/scripts/bootstrap.min.js');
    }


    function display ($file, $type = '', $header='')
    {
        $ac = $this->ac;
        $ctrl = $this->ctrl;
        $css = $this->initCss();
        $js = $this->initJS();

        if ($this->_assign) {
            foreach ($this->_assign as $k => $v) {
                $$k = $v;
            }
        }

        if ($type == 'new') {
            if ($header == 'isLogin') {
                $header_html = $this->_st->compile("layout/header_login.html");
            } else if ($header == 'noLogin') {
                $header_html = $this->_st->compile("layout/header_nologin.html");
            }
            $index_html = $this->_st->compile($file);
        } else {
            if ($type == 'index') {
                $header_html = $this->_st->compile("layout/header_index.html");
            } elseif ($type == 'regist') {
                $header_html = $this->_st->compile("layout/header_regist.html");
            } else {
                if ($this->isLogin()) {
                    $header_html = $this->_st->compile("layout/header.html");
                } else {
                    $header_html = $this->_st->compile("layout/header_index.html");
                }       
            }
            
            $index_html = $this->_st->compile($file);
            $footer_html = $this->_st->compile("layout/footer.html");
        }
        if (PCK_AREA != 'en') {
            $warn_html = $this->_st->compile("layout/warn.html");
        }
        
        $hook_js = $this->_hook_js;
        include "$header_html";
        include "$index_html";
        include "$footer_html";
        include "$warn_html";
        exit;

    }

    /**
     *  过滤参数
     * @param array $param
     * @param bool $empty false 不排除控制 true排除空值
     * @return array
     */
    public function filterParam ($param = [], $empty = false)
    {
        $info = [];
        foreach ($param as $val => $func) {
            if ($func) {
                $info[$val] = $func(_g($val));
            } else {
                $info[$val] = _g($val);
            }
            if ($empty && !$info[$val]) {
                unset($info[$val]);
            }
        }
        return $info;
    }

    /**
     * 封装返回数据的格式（json）
     *
     * @param  array $data
     * @param string $error_id
     * @param string $message
     * 当没有数据的时候  根据类型返回
     */
    protected function dataOut($data = array(), $error_id = 200, $message = 'success', $option = array())
    {
        $return = array(
            'error_id' => $error_id,
            'message' => $message,

        );
        if (!empty ($data)) {
            $return ['data'] = $data;
        }
        $this->ajaxreturn($return);
    }

    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @param int $json_option 传递给json_encode的option参数
     * @return void
     */
    protected function ajaxReturn($data, $type = 'JSON', $json_option = 0) {
        switch (strtoupper($type)){
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data,$json_option));
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler  =   isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
                exit($handler.'('.json_encode($data,$json_option).');');
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
            default     :
                // 用于扩展其他返回格式数据

                exit($data);
        }
    }

    // 检验游戏信息，若不存在跳转到指定页面
    public function checkGame ()
    {
        $gameid = _g("gameid");
        $gameInfo = $this->openGamesService->getGameInfo($this->_uid, $gameid);
        if (empty($gameInfo)) {
            if (isAjax()) {
                $this->outputJson(99, "无此游戏权限", []);
            } else {
                jump("/game/show/");
                exit(0);
            }
        }

        // 获取URL
        $gameInfo["icon_256_url"] = "";
        $gameInfo["icon_128_url"] = "";
        $gameInfo["startup_url"] = "";
        if ($gameInfo["list_img"] != "") {
            $gameInfo["icon_256_url"] = $this->getStaticFileUrl("games", $gameInfo["list_img"], "instantplayadmin");
        }
        if ($gameInfo["small_img"] != "") {
            $gameInfo["icon_128_url"] = $this->getStaticFileUrl("games", $gameInfo["small_img"], "instantplayadmin");
        }
        if ($gameInfo["index_reco_img"] != "") {
            $gameInfo["startup_url"] = $this->getStaticFileUrl("games", $gameInfo["index_reco_img"], "instantplayadmin");
        }

        $this->assign("gameid", $gameInfo["id"]);
        $this->assign("gameInfo", $gameInfo);

        return $gameInfo;
    }

    // json输出
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
     * @param  [string] $appName   [新路径不传，某些老路径走instantplayadmin]
     * @return [string] 完整路径
     */
    public function getStaticFileUrl($module, $path, $appName = APP_NAME)
    {   
        if (strpos($path, $module) === false) {
            # 新路径
            return get_static_file_url_by_app($module, $path);
        } else {
            # 老路径
            return $this->openGamesService->getOldStaticImageUrl($module,$path,$appName);
        }
    }

}
