<?php
class BaseCtrl {
    public $uid = 0;
    public $uinfo = null;
    public $request = null;
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
    public $shareService = null;

    public $_config = null;
    function __construct($request){
        $prefix = "BaseCtrl->__construct";
        LogLib::inc()->debug([$prefix,$request]);
        $this->request = $request;
//        //实例化 用户 服务 控制器
        $this->initService();
//        $this->tracing();

//        ConfigCenter::get(APP_NAME,"constant");
        $config = ConfigCenter::get(APP_NAME,"main");
        $this->_config = $config['common'];

    }

    function tracing($localEndpoint = 'baseService',$remoteEndpoint = 'userService'){
        TraceLib::getInc()->tracing($localEndpoint,$remoteEndpoint);
    }

    function initService(){
        LogLib::inc()->debug(["init new service"]);

        $this->userService = new UserService();
        $this->systemService = new SystemService();
        $this->productService = new ProductService();
        $this->orderService = new OrderService();
        $this->uploadService = new UploadService();
        $this->msgService = new MsgService();
        $this->commentService =  new CommentService();
        $this->upService = new UpService();
        $this->collectService =  new CollectService();
        $this->payService = new PayService();
        $this->userAddressService = new UserAddressService();
        $this->agentService = new AgentService();
        $this->goodsService = new GoodsService();
        $this->cartService = new CartService();
        $this->shareService = new ShareService();
    }

}
