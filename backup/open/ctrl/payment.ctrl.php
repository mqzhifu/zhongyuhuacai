<?php

/**
 * @Author: xuren
 * @Date:   2019-03-13 10:22:20
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-05-10 15:47:44
 */
 class PaymentCtrl extends BaseCtrl{
 	/**
     * 支付接入index
     */
    public function index()
    {
        $this->checkGame();

        $this->display("game/gamePayment.html", "new", "isLogin");
    }


    public function getGoods()
    {
        $gameInfo = $this->checkGame();
        $gameid = $gameInfo["id"];
        $list = PropsPriceModel::getList($gameid);

        $this->outputJson(200,"succ",$list['data']);
    }

    /**
     * 添加商品
     */
    public function addGoodsItem(){
        $gameInfo = $this->checkGame();
        $gameid = $gameInfo["id"];
        $iosType = _g("ios_type");
        $price = _g("price");
        $goodsName = _g("goods_name");

        $res = PropsPriceModel::addItem($gameid, $price, $goodsName, $iosType);
        if(!$res){
            $this->outputJson(0, "添加失败", []);
        }

        $this->outputJson(1, "添加成功", $res);
    }


 }