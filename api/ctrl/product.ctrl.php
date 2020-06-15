<?php
class ProductCtrl extends BaseCtrl  {
    function __construct($request){
        parent::__construct($request);
    }

//    //获取所有分类下的，所有商品列表
//    function getListAllCategory(){
//
//    }

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
        out_ajax(200,$list);
    }
    //获取 后台 推荐的商品的列表
    function getRecommendList(){
        //type=1,推荐到详情页,type=2，首页
        $type = get_request_one( $this->request,'type',0);
        $page = get_request_one( $this->request,'page',0);
        $limit = get_request_one( $this->request,'limit',4);
        $rs = $this->productService->getRecommendList($page,$limit,$type);

//        if(!$rs['msg']){
            out_ajax($rs['code'],$rs['msg']);
//        }
//        out_ajax(200,$this->productService->formatShow($rs['msg']));
    }

    function getUserHistoryPVList(){
        $id = get_request_one( $this->request,'id',0);
        $rs = $this->productService->getUserHistoryPVList($id);
        out_ajax(200,($rs['msg']));
    }

    //获取一个分类下的所有商品列表
    function getListByCategory(){
        $categoryId =get_request_one( $this->request,'category_id',0);
        $page = get_request_one( $this->request,'page',0);
        $limit = get_request_one( $this->request,'limit',0);
        $rs = $this->productService->getListByCategory($categoryId,$page,$limit);
        if(!$rs['msg']){
            out_ajax($rs['code'],$rs['msg']);
        }
        out_ajax(200,$this->productService->formatShow($rs['msg']));
    }
    //产品详情
    function getOneDetail(){
        $id = get_request_one( $this->request,'id',0);
        $includeGoods = get_request_one( $this->request,'include_goods',0);

        $data = $this->productService->getOneDetail($id,$includeGoods,$this->uid);
        out_ajax($data['code'],$data['msg']);
    }

    //搜索
    function search(){
        $page = get_request_one( $this->request,'page',0);
        $limit = get_request_one( $this->request,'limit',10);

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

        $list = $this->productService->formatShow($rs['msg']);
        out_ajax($rs['code'],$list);

    }
    //点赞
    function up(){
        $id = get_request_one( $this->request,'id',0);
        $rs = $this->upService->add($this->uid,$id);
        out_ajax($rs['code'],$rs['msg']);
    }
    //收藏
    function collect(){
        $id = get_request_one( $this->request,'id',0);
        $rs = $this->collectService->add($this->uid,$id);
        out_ajax(200,$rs['msg']);
    }

    //点赞
    function cancelUp(){
        $id = get_request_one( $this->request,'id',0);
        $rs = $this->upService->cancel($this->uid,$id);
        out_ajax($rs['code'],$rs['msg']);
    }
    //收藏
    function cancelCollect(){
        $id = get_request_one( $this->request,'id',0);
        $rs = $this->collectService->cancel($this->uid,$id);
        out_ajax(200,$rs['msg']);
    }


    //评论
    function comment(){
        $id = get_request_one( $this->request,'id',0);
        $title = get_request_one( $this->request,'title','');
        $content = get_request_one( $this->request,'content','');

        $newId = $this->commentService->add($this->uid,$id,$title,$content);
        out_ajax(200,$newId);
    }
    //获取产品 - 评论列表
    function getCommentList(){
        $pid = get_request_one( $this->request,'pid',0);
        $page = get_request_one( $this->request,'page',0);
        $limit = get_request_one( $this->request,'limit',0);

        $rs = $this->commentService->getListByPid($pid,$page,$limit);
        out_ajax($rs['code'],$rs['msg']);
    }

    function getDetailRecommend(){
        $pid = get_request_one( $this->request,'pid',0);
        $rs = $this->productService->getDetailRecommend($pid);
        out_ajax($rs['code'],$rs['msg']);
    }

    function getSearchAttr(){
        $category = ProductCategoryModel::db()->getAll(" is_show_search = 1 ");
        $orderType =ProductService::ORDER_TYPE;

        $rs = array('category'=>$category,'order_type'=>$orderType);
        out_ajax(200,$rs);
    }
}