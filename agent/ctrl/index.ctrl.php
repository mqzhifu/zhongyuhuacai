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

        //提现金额 = 分享用户已支付的订单金额(未提现过的) * 佣金  + 二级代理的提成
        $allowWithdrawMoneyTotal = $this->agentService->getFee($this->uinfo['id']);
        $allowWithdrawMoneyTotalNum = $allowWithdrawMoneyTotal['sub_fee'] + $allowWithdrawMoneyTotal['fee'];
        //已提金额
        $hasFee = $this->agentService->getHasFee($this->uinfo['id']);

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

        $this->assign("hasFee",$hasFee);
        $this->assign("bindUserInfo",$bindUserInfo);
        $this->assign("uinfo",$this->uinfo);

        $this->assign("allowWithdrawMoneyTotalNum",$allowWithdrawMoneyTotalNum);
        $this->assign("shareProductCnt",$shareProductCnt);
        $this->assign("subAgentCnt",$subAgentCnt);

        $this->assign("allOrderCnt",$allOrderCnt);
        $this->assign("myLeader",$myLeader);


        $this->display("ucenter.html");
    }

    function enterQrCode(){
//        $url = get_domain_url() ."agent/apply/type=2&aid=".$aid ;
//        $url = "http://agent-dev.xlsyfx.cn/";
        $url = get_domain_url() ;

        require_once PLUGIN . '/phpqrcode/qrlib.php';

        $value = $url;					//二维码内容
        $errorCorrectionLevel = 'L';	//容错级别
        $matrixPointSize = 11;			//生成图片大小

        $service = new UploadService();
        //生成二维码图片
        $filename = $service->getApplyAgentUploadPath(999);
        QRcode::png($value,$filename , $errorCorrectionLevel, $matrixPointSize, 0);

        $original_pic_path = get_agent_apply_original_pic_path(1 );
        $this->mergePic($original_pic_path,$filename);
    }

    function mergePic($src,$qrCode){
        header('Content-type: image/jpg');

        $srcImg = imagecreatefromjpeg($src);
        $qrCodeImg = imagecreatefrompng($qrCode);

        imagecopymerge($srcImg, $qrCodeImg, 235,697, 0,0, imagesx($qrCodeImg), imagesy($qrCodeImg), 100);
        imagejpeg($srcImg);
        exit;
//        $merge = 'merge.png';
//        var_dump(imagepng($srcImg,'./merge.png'));//bool(true)
    }

}