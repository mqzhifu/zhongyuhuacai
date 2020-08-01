<?php
class UserCtrl extends BaseCtrl  {

    function reg($request){

        //        $userInfo = $request['userInfo'];
        $userInfo = null;
        $type = get_request_one($request,'type',0);

        $ps = get_request_one($request,'ps',"") ;
        $name =  get_request_one($request,'name','') ;

        $rs = $this->userService->register($name,$ps,$type,$userInfo);
        out_ajax($rs['code'],$rs['msg']);
    }

    //普通微信注册的用户 绑定  代理账号
    function bindAgent($request){
        $mobile = $agentUid =get_request_one( $this->request,'mobile',"");
        $code = $agentUid =get_request_one( $this->request,'code',0);
        $this->agentService->userBindAgent($this->uid,$mobile,$code);
    }
    //用户详细信息
    function getOneDetail(){
        $userRs  = $this->userService->getUinfoById($this->uid);
        if($userRs['code'] != 200){
            return out_ajax($userRs['code'],$userRs['msg']);
        }

//        $user = $userRs['msg'];
//        $view_product_history_cnt = 0;
//        $viewList = UserProductLogModel::db()->getAll(" uid = {$this->uid} group by pid");
//        if($viewList){
//            $view_product_history_cnt = count($viewList);
//        }
//        $user['view_product_history_cnt'] = $view_product_history_cnt;
//        $user['collect_cnt'] = UserCollectionModel::db()->getCount(" uid =  {$this->uid}");
//        $user['coupon_cnt'] = CouponModel::db()->getCount(" uid = {$this->uid} and status = 1");

        $user = $userRs['msg'];

        $user['view_product_history_cnt'] = $this->productService->getUserViewProductCnt($this->uid);
        $user['collect_cnt'] =      $this->collectService->getUserCnt($this->uid);
        $user['comment_cnt'] =      $this->commentService->getUserCnt($this->uid);
        $user['up_cnt'] =           $this->upService->getUserCnt($this->uid);

        out_ajax(200,$user);

    }
    //删除一个收货地址
    function delAddress($request){
        $id = get_request_one($request,'id',0);
        $rs =  $this->userAddressService->addOne($this->uid,$id);
        out_ajax($rs['code'],$rs['msg']);
    }
    //添加一个收货地址
    function addAddress($request){
        $edit_id =  get_request_one($request,'edit_id',0);
        $rs =  $this->userAddressService->addOne($this->uid,$request,$edit_id);
        out_ajax($rs['code'],$rs['msg']);
    }
    //用户反馈
    function feedback(){
        $title = get_request_one($this->request,'title',0);
        $content = get_request_one($this->request,'content',0);
        $mobile = get_request_one($this->request,'mobile',0);
        $pic = get_request_one($this->request,'pic',0);

        $data = array(
            'title'=>$this,
            'content'=>$content,
            'pic'=>$mobile,
            'mobile'=>$pic,
            'uid'=>$this->uid,
            'status'=>UserFeedbackModel::STATUS_WAIT,
        );

        $newId = UserFeedbackModel::db()->add($data);
        out_ajax(200,$newId);
    }


    function wxUserInfoBind(){
//        $sex = get_request_one($this->request,'sex',0);
//        $nickname = get_request_one($this->request,'nickname',0);
        LogLib::inc()->debug(["wxUserInfoBind",$_REQUEST]);

        $rawData = get_request_one($this->request,'rawData',0);
        $rawData = json_decode($rawData,true);

        $data = array(
            'sex'=>$rawData['gender'],
            'nickname'=>$rawData['nickName'],
            'avatar'=>$rawData['avatarUrl'],
        );

        $rs = $this->userService->upUserInfo($this->uid,$data);

        return out_ajax($rs['code'],$rs['msg']);
    }

    //更新资料
    function upInfo(){
//        $sex = get_request_one($this->request,'sex',0);
        $nickname = get_request_one($this->request,'nickname',0);
//        $avatar = get_request_one($this->request,'avatar',0);

        $data = array(
//            'sex'=>$sex,
            'nickname'=>$nickname,
//            'avatar'=>$avatar,
        );

        $rs = $this->userService->upUserInfo($this->uid,$data);
        return out_ajax($rs['code'],$rs['msg']);
    }
    //更新头像
    function upAvatar(){
//        LogLib::inc()->debug(['up avatar',$_REQUEST]);
        LogLib::inc()->debug(['up avatar php $_FILES ',$_FILES]);

        $uploadRs = $this->uploadService->avatar('avatar');
        if($uploadRs['code'] != 200){
            exit(" uploadService->product error ".json_encode($uploadRs));
        }

        $data['avatar'] = $uploadRs['msg'];
        $this->userService->upUserInfo($this->uid,$data);

        $avatarUrl = get_avatar_url( $data['avatar']);

        out_ajax(200,$avatarUrl);
    }

    function viewProductHistoryCnt(){
        $cnt = $this->productService->getUserViewProduct($this->uid);
        out_ajax($cnt['code'],$cnt['msg']);
    }

    //用户收获产品 统计
    function getCollectListCnt(){
        $cnt = $this->collectService->getUserCnt($this->uid);
        out_ajax(200,$cnt);
    }
    //已收藏的产品列表
    function getCollectList(){
        $list = $this->collectService->getUserList($this->uid);
        out_ajax(200,$list['msg']);
    }

    //浏览产品 - 历史 记录
    function viewProductHistory(){
//        $uid = $this->uid;
//        $list = UserProductLogModel::db()->getAll(" uid = $uid group by pid order by id desc limit 30");
//        $list = $this->productService->getUserViewProduct($this->uid);
//        $productList = null;
//        if($list){
//            $productList = null;
//            foreach ($list as $k=>$v){
//                $productList[] = ProductModel::db()->getById($v['pid']);
//            }
//            $productList = $this->productService->formatShow($productList);
//        }
//        out_ajax(200,$productList);
        $list = $this->productService->getUserViewProduct($this->uid);
        out_ajax(200,$list['msg']);
    }

    function getAddressList(){
        $list = $this->userAddressService->getList($this->uid);
        out_ajax(200,$list['msg']);
    }

    function getUserAddressDefault(){
        $list = $this->userAddressService->getUserAddressDefault($this->uid,1);
        out_ajax($list['code'],$list['msg']);
    }

    function getAddressById(){
        $id = get_request_one($this->request,'id',0);
        $row = $this->userAddressService->getById($id);

        out_ajax($row['code'],$row['msg']);
    }
}