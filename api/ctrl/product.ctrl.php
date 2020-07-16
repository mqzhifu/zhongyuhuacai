<?php
class ProductCtrl extends BaseCtrl  {
    function __construct($request){
        parent::__construct($request);
    }

    //首页，分类ICON
    function getAllCategory(){
        $list = ProductCategoryModel::db()->getAll(" is_show_index = 1 ");
        if($list){
            foreach ($list as $k=>$v){
                if(arrKeyIssetAndExist($v,'pic')){
//                    $pic = explode(",",$v['pic']);
                    $list[$k]['pic'] = get_category_url($v['pic']);
                }else{
                    $list[$k]['pic'] = "";
                }
            }
        }
        return $this->out(200,$list);
    }
    //获取 后台 推荐的商品的列表
    function getRecommendList(){
        //type=1,推荐到详情页,type=2，首页
        $type = get_request_one( $this->request,'type',0);
        $page = get_request_one( $this->request,'page',1);
        $limit = get_request_one( $this->request,'limit',3);
        $rs = $this->productService->getRecommendList($page,$limit,$type);

        return $this->out($rs['code'],$rs['msg']);
//        if(!$rs['msg']){
//            out_ajax($rs['code'],$rs['msg']);
//        }
//        out_ajax(200,$this->productService->formatShow($rs['msg']));
    }

    function getUserHistoryPVList(){
        $id = get_request_one( $this->request,'id',0);
        $rs = $this->productService->getUserHistoryPVList($id,1);
        return $this->out($rs['code'],($rs['msg']));
    }

//    //获取一个分类下的所有商品列表
//    function getListByCategory(){
//        $categoryId =get_request_one( $this->request,'category_id',0);
//        $page = get_request_one( $this->request,'page',0);
//        $limit = get_request_one( $this->request,'limit',0);
//        $rs = $this->productService->getListByCategory($categoryId,$page,$limit);
//        if(!$rs['msg']){
//            out_ajax($rs['code'],$rs['msg']);
//        }
//        out_ajax(200,$this->productService->formatShow($rs['msg']));
//    }
    //产品详情
    function getOneDetail(){
        $id = get_request_one( $this->request,'id',0);
        $includeGoods = get_request_one( $this->request,'include_goods',1);

        $data = $this->productService->getOneDetail($id,$includeGoods,$this->uid);
//        echo json_encode($data['msg']);exit;
        return $this->out($data['code'],$data['msg']);
//        out_ajax($data['code'],$data['msg']);
    }

    //搜索
    function search(){
        $page = get_request_one( $this->request,'page',1);
        $limit = get_request_one( $this->request,'limit',3);

        $condition = array(
            'keyword'=>get_request_one( $this->request,'keyword',0),
            'category'=>get_request_one( $this->request,'category',0),
            'orderType'=> get_request_one( $this->request,'order_type',0),
            'orderUpDown'=> get_request_one( $this->request,'orderUpDown',0),
        );
        $rs = $this->productService->search($condition,$page,$limit,$this->uid);
        if($rs['code'] != 200){
            return out_ajax($rs['code'],$rs['msg']);
        }


        $rs['msg']['list'] = $this->productService->formatShow($rs['msg']['list']);
//        var_dump($rs['msg']);exit;
        return $this->out($rs['code'],$rs['msg']);

    }
    //点赞
    function up(){
        $id = get_request_one( $this->request,'id',0);
        $rs = $this->upService->add($this->uid,$id);
        return $this->out($rs['code'],$rs['msg']);
    }
    //收藏
    function collect(){
        $id = get_request_one( $this->request,'id',0);
        $rs = $this->collectService->add($this->uid,$id);
        return $this->out(200,$rs['msg']);
    }

    //点赞
    function cancelUp(){
        $id = get_request_one( $this->request,'id',0);
        $rs = $this->upService->cancel($this->uid,$id);
        return $this->out($rs['code'],$rs['msg']);
    }
    //收藏
    function cancelCollect(){
        $id = get_request_one( $this->request,'id',0);
        $rs = $this->collectService->cancel($this->uid,$id);
        return $this->out(200,$rs['msg']);
    }

    function uploadCommentVideo(){

    }

    function uploadCommentPic(){
        LogLib::inc()->debug(['uploadCommentPic',$_REQUEST]);
        LogLib::inc()->debug(["php fifle",$_FILES]);


        $oid = get_request_one( $this->request,'oid',0);
        $cid = get_request_one( $this->request,'cid',0);

        $uploadRs = $this->uploadService->comment('comment');
        if($uploadRs['code'] != 200){
            exit(" uploadService->comment error ".json_encode($uploadRs));
        }

        $comment = $this->commentService->getRowById($cid);

        $picUrl = $uploadRs['msg'];
        $upData = array(
            "pic"=>$comment['pic'] . ",".$picUrl
        );
        UserCommentModel::db()->upById($cid,$upData);
//        $avatarUrl = get_avatar_url( $data['avatar']);

        $url = get_comment_url($picUrl);
        out_ajax(200,$url);
    }

    //评论
    function comment(){
        $oid = get_request_one( $this->request,'oid',0);
        $title = get_request_one( $this->request,'title','');
        $content = get_request_one( $this->request,'content','');
        $star = get_request_one( $this->request,'star','');
        $pic = get_request_one( $this->request,'pic','');

        $rs = $this->commentService->add($this->uid,$oid,$title,$content,$pic,$star);
        out_ajax($rs['code'],$rs['msg']);
    }
    //获取产品 - 评论列表
    function getCommentList(){
        $pid = get_request_one( $this->request,'pid',0);
        $page = get_request_one( $this->request,'page',0);
        $limit = get_request_one( $this->request,'limit',0);

        $rs = $this->commentService->getListByPid($pid,$page,$limit);
        out_ajax($rs['code'],$rs['msg']);
    }
    //产品详情页的推荐
    function getDetailRecommend(){
        $pid = get_request_one( $this->request,'pid',0);
        $rs = $this->productService->getDetailRecommend($pid);
        return $this->out($rs['code'],$rs['msg']);
    }

    function getSearchAttr(){
        $category = ProductCategoryModel::db()->getAll(" is_show_search = 1 ");
        $orderType =ProductService::ORDER_TYPE;

        $rs = array('category'=>$category,'order_type'=>$orderType);
        return $this->out(200,$rs);
    }
}