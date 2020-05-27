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

    }
    //用户反馈
    function feedback(){

    }

    function upInfo(){

    }

    function upAvatar(){

    }
    //已收藏的产品列表
    function getCollectList(){

    }
}