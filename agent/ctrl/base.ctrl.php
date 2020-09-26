<?php
class BaseCtrl {
    public $uid = 0;
    public $uinfo = null;

    public $request = null;
    public $bindUser = null;
    public $_st = null;
    public $_js = array();
    public $_css = array();
    public $_title = '';
    public $_subTitle = "";
    public $_hook_js = array();
    public $_assign = array();
    public $_adminid = "";
    public $_request = null;
    public $_backListUrl = "";
    public $_myLeader = null;
    //微服务 类
    public $userService = null;
    public $productService =null;
    public $systemService =null;
    public $orderService = null;
    public $uploadService = null;
    public $msgService = null;
    public $commentService = null;
    public $upService = null;
    public $collectService = null;
    public $payService = null;
    public $userAddressService = null;
    public $agentService = null;
    public $goodsService = null;
    public $cartService = null;
    public $withdrawMoneyService = null;
    public $session = null;
    public $shareService = null;
    function __construct($request)
    {
        LogLib::inc()->debug(['php_server', $_SERVER]);
        $this->request = $request;
//        $this->checkSign();

        $this->ctrl = $request['ctrl'];
        $this->ac = $request['ac'];

        //加载 配置文件 信息
        ConfigCenter::get(APP_NAME, "api");
        ConfigCenter::get(APP_NAME, "err_code");
        ConfigCenter::get(APP_NAME, "main");
        ConfigCenter::get(APP_NAME, "rediskey");

//        //实例化 用户 服务 控制器
        $this->userService = new UserService();
        $this->systemService = new SystemService();
        $this->productService = new ProductService();
        $this->orderService = new OrderService();
        $this->uploadService = new UploadService();
        $this->msgService = new MsgService();
        $this->commentService = new CommentService();
        $this->upService = new UpService();
        $this->collectService = new CollectService();
        $this->payService = new PayService();
        $this->userAddressService = new UserAddressService();
        $this->agentService = new AgentService();
        $this->goodsService = new GoodsService();
        $this->cartService = new CartService();
        $this->withdrawMoneyService = new WithdrawMoneyService();
        $this->shareService = new ShareService();

        $this->_st = getAppSmarty();
        $this->_sess = new SessionLib();
        $this->init_css_js();
        define("H_STATIC_URL",get_static_url());

        $this->initUserLoginInfoByToken();
        //有些接口必须，得登陆后，才能访问
        $isLogin = $this->loginAPIExcept($request['ctrl'], $request['ac']);
        if ($isLogin) {
            if (!$this->uinfo) {
                jump("/login/index/");
//                return out_ajax(5001);
            }
        }
        $ip = get_client_ip();
        $ipBaiduParserAddress = RedisOptLib::getBaiduIpParser($ip);
        if (!$ipBaiduParserAddress) {
            $ipBaiduParserAddress = AreaLib::getByIp($ip);
            LogLib::inc()->debug($ip);
            LogLib::inc()->debug($ipBaiduParserAddress);
            RedisOptLib::setBaiduIpParser($ip, $ipBaiduParserAddress);
        }

        $data = array(
            'a_time' => time(),
            'ctrl' => $request['ctrl'],
            'ac' => $request['ac'],
            'uid' => $this->uid,
            'client_info' => json_encode(get_client_info()),
            'ip_parser' => json_encode($ipBaiduParserAddress, JSON_UNESCAPED_UNICODE),
        );
        UserLogModel::db()->add($data);

        $this->session = new SessionLib();
    }

    function getUinfo(){

    }

    function init_css_js(){
//    <link rel="stylesheet" href="assets/css/app.css" />
//    <link rel="stylesheet" href="assets/libs/city/LArea.css" />
//
//    <script src="assets/libs/jquery.min.js"></script>
//    <script src="assets/js/amazeui.min.js"></script>
//    <script src="assets/libs/city/LAreaData1.js"></script>
//    <script src="assets/libs/city/LAreaData2.js"></script>
//    <script src="assets/libs/city/LArea.js"></script>


        $this->addCss('/agent/assets/css/amazeui.min.css');
        $this->addCss('/agent/assets/css/app.css');
        $this->addCss('/agent/assets/libs/city/LArea.css');


        $this->addJs('/agent/assets/libs/jquery.min.js');
        $this->addJs('/agent/assets/js/amazeui.min.js');
//        $this->addJs('/agent/assets/libs/city/LAreaData1.js');
//        $this->addJs('/agent/assets/libs/city/LAreaData2.js');
        $this->addJs('/agent/assets/libs/city/LArea.js');

    }

    function addJs($dir_file){
        if(!in_array($dir_file,$this->_js)){
            $this->_js[] = $dir_file;
        }
    }

    function addCss($dir_file){
        if(!in_array($dir_file,$this->_css)){
            $this->_css[] = $dir_file;
        }
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



        $backListUrl = $this->_backListUrl;

        include $header_html;
        include $index_html;
        include $footer_html;
        exit;

    }

    function setTitle($title){
        $this->_title = $title;
    }

    function setSubTitle($title){
        $this->_subTitle = $title;
    }

    function checkSign(){
        $sign = _g('sign');
        if(!$sign){
            return $this->out(3000);
        }

        $checkSign = TokenLib::checkSign($request , $sign,$this->app['apiSecret']);
        if(!$checkSign){
            return $this->out(3001);
        }

    }
    //返回的数据，1检查格式2如果弱类型要转移成前端想要的类型
    function checkDataAndFormat($data){
        $api = ConfigCenter::get(APP_NAME,"api");
        if(!arrKeyIssetAndExist($api,$this->request['ctrl'])){
            return $this->out(7060);
        }

        if(!arrKeyIssetAndExist($api[$this->request['ctrl']],$this->request['ac'])){
            return $this->out(7061);
        }
        if(!arrKeyIssetAndExist($api[$this->request['ctrl']][$this->request['ac']],'return')){
            return $this->out(7062);
        }

        $apiMethodReturn = $api[$this->request['ctrl']][$this->request['ac']]['return'];
        $data = FilterLib::apiReturnDataCheckInit($apiMethodReturn,$data);
        return $data;
    }


    function out($code,$msg = ""){
        if($code == 200){
//            if(!$msg){
//                if($msg === ""){
//                    $msg = $GLOBALS['code'][$code];
//                }elseif($msg === 0){
//                    $msg = 0;
//                }else{
//                    $msg = [];
//                }
//            }
//
//            if(is_string($msg) && $msg == 'space_string'){
//                $msg = "";
//            }
//
//            $apiMethod = null;
//            if(isset($GLOBALS[APP_NAME]['api'][$this->request['ctrl']][$this->request['ac']])){
//                $apiMethod =$GLOBALS[APP_NAME]['api'][$this->request['ctrl']][$this->request['ac']];
//            }
            $msg = $this->checkDataAndFormat($msg);
            if(arrKeyIssetAndExist($msg,'code')){
                $data = $msg;
            }else{
                $data = array('code'=>$code,"msg"=>$msg);
            }

            return $data;
        }else{
            $data = array('code'=>$code,"msg"=>$msg);
            return $data;
        }
    }

    //有些接口，必须是登陆后，才能访问~有些不需要
    function loginAPIExcept($ctrl ,$ac ){
        $arr =  ConfigCenter::get(APP_NAME,"main")['loginAPIExcept'];
        if(!$arr){
            return 1;
        }
        foreach($arr as $k=>$v){
            if($v[0] == $ctrl && $v[1] == $ac){
                return 0;
            }
        }

        return 1;
    }
    //判断登陆，初始化用户信息
    function initUserLoginInfoByToken(){
        $uinfo = $this->_sess->getValue("uinfo");
//        echo "uinfo:";
//        var_dump($uinfo);
        if(!$uinfo){
            return false;
        }

//        if(!arrKeyIssetAndExist($sessRootData,'uid')){
//            return false;
//        }


        if(arrKeyIssetAndExist($uinfo,'province_code')){
            $province = AreaProvinceModel::getNameByCode($uinfo['province_code']);
            $uinfo['province'] = $province;
        }

        if(arrKeyIssetAndExist($uinfo,'city_code')){
            $city = AreaCityModel::getNameByCode($uinfo['city_code']);
            $uinfo['city'] =  $city;
        }

        if(arrKeyIssetAndExist($uinfo,'county_code')){
            $county = AreaCountyModel::getNameByCode($uinfo['county_code']);
            $uinfo['county'] =  $county;
        }

        $uinfo['avatar_url'] = get_agent_url($uinfo['avatar']);
//        var_dump($uinfo['avatar_url'] );exit;
        $this->uinfo = $uinfo;
//        $uinfoRs = $this->userService->getUinfoById($uid);
//        $uinfo = AgentModel::db()->getById($uid);


        //处理绑定用户的ID
        if(arrKeyIssetAndExist($uinfo,'uid')){
            $user = UserModel::db()->getById($uinfo['uid']);
            $this->bindUser = $user;
        }

        if(arrKeyIssetAndExist($uinfo,'invite_agent_uid')){
            $user = UserModel::db()->getById($uinfo['uid']);
            $this->_myLeader = $user;
        }


        return out_pc(200);
    }

}