<?php
class ProductCtrl extends BaseCtrl  {
    function __construct($request){
        parent::__construct($request);
    }

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
        $rs = $this->productService->getRecommendList();
        if(!$rs['msg']){
            out_ajax($rs['code'],$rs['msg']);
        }
        $data = null;
        foreach ($rs['msg'] as $k=>$v){
            $pic = "";
            if(arrKeyIssetAndExist($v,'pic')){
                $pic = explode(",",$v['pic']);
                $pic = get_product_url($pic[0]);
            }
            $data[] = array('goods_total'=>$v['lowest_price'],'goods_total'=>$v['goods_total'],'pic'=>$pic,'lowest_price'=>$v['lowest_price'],'title'=>$v['title']);
        }

        out_ajax(200,$data);

    }
    //获取一个分类下的所有商品列表
    function getListByCategory(){
        //分类>价格 销量
        $categoryId = $this->request['category_id'];
        $list = $this->productService->getListByCategory($categoryId);
        out_ajax($list['code'],$list['msg']);
    }
    //产品详情
    function getOneDetail(){
        $id = $this->request['id'];
        $includeGoods = $this->request['include_goods'];

        $data = $this->productService->getOneDetail($id,$includeGoods);
        out_ajax($data['code'],$data['msg']);
    }
    //获取所有分类下的，所有商品列表
    function getListAllCategory(){

    }
    //搜索
    function search(){
        $keyword = $this->request['keyword'];
        $data = $this->productService->search($keyword);
        out_ajax($data['code'],$data['msg']);
    }

    //点赞
    function up(){
        $pid = $this->request['pid'];
        $data = array(
            'a_time'=>time(),
            'pid'=>$pid,
            'uid'=>$this->uid,
        );
        $newId = UserLikedModel::db()->add($data);
        out_ajax(200,$newId);
    }
    //收藏
    function collect(){
        $pid = $this->request['pid'];
        $data = array(
            'a_time'=>time(),
            'pid'=>$pid,
            'uid'=>$this->uid,
        );
        $newId = UserCollectionModel::db()->add($data);
        out_ajax(200,$newId);
    }
    //评论
    function comment(){
        $pid = $this->request['pid'];
        $title = $this->request['title'];
        $content = $this->request['content'];

        $data = array(
            'title'=>$title,
            'content'=>$content,
            'a_time'=>time(),
            'pid'=>$pid,
            'uid'=>$this->uid,
            'pic'=>"",
        );
        $newId = UserCommentModel::db()->add($data);
        out_ajax(200,$newId);
    }
    //获取产品 - 评论列表
    function getCommentList(){
        $pid = $this->request['pid'];
        $data = UserCommentModel::getListByPid($pid);
        out_ajax(200,$data);
    }
}