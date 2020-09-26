<?php
class IndexCtrl extends BaseCtrl{
    function index(){
        $this->ucenter();
    }

    function ucenter(){
        $this->setTitle('用户中心');
        $this->setSubTitle('用户中心');


        if($this->bindUser){
            $bindUserInfo = $this->bindUser['id']."(".$this->bindUser['nickname'].")";
        }else{
            $bindUserInfo = "无";
        }

        //提现金额 = 分享出去用户已支付的订单金额 * 佣金  + 二级代理的提成
        $allowWithdrawMoneyTotal = $this->agentService->getFee($this->uinfo['id']);
        $allowWithdrawMoneyTotalNum = $allowWithdrawMoneyTotal['sub_fee'] + $allowWithdrawMoneyTotal['fee'];
        //分享产品次数
        $shareProductCnt = $this->shareService->getShareCnt($this->uinfo['id']);
        //下级代理数
        $subAgentCnt = $this->agentService->getSubAgentCnt($this->uinfo['id']);
        //分享给用户后，用户下单数
        $allOrderList = $this->agentService->getAllOrderList($this->uinfo['id']);
        $allOrderCnt = count($allOrderList);


        if($this->_myLeader){
            $myLeader = $this->_myLeader['real_name'];
        }else{
            $myLeader = "无";
        }

        $this->assign("bindUserInfo",$bindUserInfo);
        $this->assign("uinfo",$this->uinfo);

        $this->assign("allowWithdrawMoneyTotalNum",$allowWithdrawMoneyTotalNum);
        $this->assign("shareProductCnt",$shareProductCnt);
        $this->assign("subAgentCnt",$subAgentCnt);

        $this->assign("allOrderCnt",$allOrderCnt);
        $this->assign("myLeader",$myLeader);


        $this->display("ucenter.html");
    }


}