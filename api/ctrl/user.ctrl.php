<?php
class UserCtrl extends BaseCtrl  {

    function reg($request){

        $type = get_request_one($request,'type',0);
//        $userInfo = $request['userInfo'];
        $userInfo = null;
        $ps = get_request_one($request,'ps',"") ;
        $name =  get_request_one($request,'name','') ;

        $rs = $this->userService->register($name,$ps,$type,$userInfo);
        out_ajax($rs['code'],$rs['msg']);
    }

    function getOneDetail(){
        $userRs  = $this->userService->getUinfoById($this->uid);
        if($userRs['code'] != 200){
            return out_ajax($userRs['code'],$userRs['msg']);
        }

        $user = $userRs['msg'];
        $user['view_product_history_cnt'] = UserProductLogModel::db()->getCount(" uid = {$this->uid}");
        $user['collect_cnt'] = UserCollectionModel::db()->getCount(" uid =  {$this->uid}");
        $user['coupon_cnt'] = CouponModel::db()->getCount(" uid = {$this->uid} and status = 1");
        out_ajax(200,$user);

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

    function upInfo(){
        $sex = get_request_one($this->request,'sex',0);
        $nickname = get_request_one($this->request,'nickname',0);

        $data = array(
            'sex'=>$sex,
            'nickname'=>$nickname,
        );

        $this->userService->upUserInfo($this->uid,$data);
    }

    function upAvatar(){
        LogLib::inc()->debug(['up avatar',$_REQUEST]);

        LogLib::inc()->debug(["php fifle",$_FILES]);


        var_dump($_FILES);
//        $userInfo = $this->userService->getUinfoById($this->uid);

//        $uploadRs = $this->uploadService->avatar('avatar');
//        if($uploadRs['code'] != 200){
//            exit(" uploadService->product error ".json_encode($uploadRs));
//        }
//
//        $data['avatar'] = $uploadRs['msg'];
//        $this->userService->upUserInfo($this->uid,$data);
//
//        if(arrKeyIssetAndExist($userInfo,'avatar')){
//            $this->uploadService->delAvatar($userInfo['avatar']);
//        }

    }
    //已收藏的产品列表
    function getCollectList(){
        $list = UserCollectionModel::getListByUid($this->uid);
        if(!$list){
            out_ajax(200,$list);
        }

        $rs = null;
        foreach ($list as $k=>$v){
            $row = $v;
            $product = $this->productService->getOneDetail($v['pid']);
            $row['price'] = $product['price'];
            $row['title'] = $product['title'];
            $rs[] = $row;
        }
        out_ajax(200,$rs);
    }
    //浏览产品 - 历史 记录
    function viewProductHistory(){
        $uid = $this->uid;
        $list = UserProductLogModel::db()->getAll(" uid = $uid");
        out_ajax(200,$list);
    }

    function getAddress(){
        $list = $this->userAddressService->getList($this->uid);
        out_ajax(200,$list['msg']);

    }
}