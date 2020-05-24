<?php
class IndexCtrl extends BaseCtrl  {

    function index(){
        //接口统计

        //小程序里

        //首页/分类页
            //获取banner
            //获取分类列表
            //获取推荐列表
            //一个分类下的所有商品
            //所有产品 列表 -  支持各种维度搜索条件   分类>价格 销量
            //搜索商品


        //产品/商品
            //产品点赞
            //产品收藏
            //产品评论
            //产品详情页  基础信息调取   商品属性调取

        //用户相关

        //用户进入小程序,获取OPENID，传给后端注册/登陆，换取token
        //反馈问题
        //下单页，汇总信息
        //调取支付
        //个人中心 - 订单列表
        //个人中心 - 编辑信息
        //站内信 消息 列表
        //退款


        //代理
        //密码登陆
        //商品，获取二维码
        //短信登陆
        //短信找回密码
        //发送短信
        //退出


        //订单列表
        //编辑个人信息
        //提现列表
        //发起提现



        return $this->out(200,"PING OK !");
    }

    function getBannerList(){
        $data = BannerModel::db()->getAll();
        out_ajax(200,$data);
    }

    function getCategoryList(){}

    function getRecommendProductList(){
        $data = ProductModel::getRecommendList();
        out_ajax(200,$data);
    }
    //    $keyword:目前仅支持UID
    function search($keyword){
        $data = ProductModel::search($keyword);
        out_ajax(200,$data);
    }

//    function getAppVersionInfo($versionCode = 0){
//
//        if($versionCode){
//            $appInfo = AppVersionModel::db()->getRow(" version_code =  $versionCode ");
//            if(!$appInfo){
//                return $this->out(1023);
//            }
//        }else{
//            $appInfo = AppVersionModel::db()->getRow(" 1 order by version_code desc limit 1");
//        }
//        return $this->out(200,$appInfo);
//    }
//
//    function cntLog($category,$type,$memo){
//        if (!in_array($category, CntActionLogModel::getCategories())) {
//            return $this->out(8311,$GLOBALS['code'][8311]);
//        }
//
//        if (!in_array($type, CntActionLogModel::getTypes())) {
//            return $this->out(8312,$GLOBALS['code'][8312]);
//        }
//        $data = array(
//            'category'=>$category,
//            'type'=>$type,
//            'memo'=>$memo,
//            'a_time'=>time(),
//            'uid'=>$this->uid,
//        );
//
//        $rs = CntActionLogModel::db()->add($data);
//        return $this->out(200,$rs);
//    }
//
//
//    //获取APP_UI配置（3级）;
//    public function getUiShowConfig(){
//        $root = AppUiConfigModel::db()->getAll(" pid = 0 ");
//        foreach ($root as $k=>$v) {
//            $sub = AppUiConfigModel::db()->getAll( " pid = {$v['id']} ");
//            foreach ($sub as $k2=>$v2) {
//                $three = AppUiConfigModel::db()->getAll( " pid = {$v2['id']} ");
//                $sub[$k2]['sub'] = $three;
//                if(empty($sub[$k2]['sub'])){
//                    unset($sub[$k2]['sub']);
//                }
//                foreach ($three as $k3=>$v3){
//                    if(arrKeyIssetAndExist($v3,'dir_name')){
//                        $four = AppUiConfigModel::db()->getAll( " pid = {$v3['id']} ");
//                        $sub[$k2][$k3]['sub'] = $four;
//                    }
//                }
//            }
//            $root[$k]['sub'] = $sub;
//        }
//        return $this->out(200, $root);
//    }


}