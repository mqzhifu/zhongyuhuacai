<?php
class NotifyCallBack extends WxPayNotify
{

    public $wx_callback_data = null;

    //查询订单
    public function Queryorder($transaction_id)
    {
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);

        $config = new WxPayConfig();
        $result = WxPayApi::orderQuery($config, $input);
        LogLib::inc()->debug("Queryorder:" . json_encode($result));
        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            return true;
        }
        return false;
    }

    /**
     *
     * 回包前的回调方法
     * 业务可以继承该方法，打印日志方便定位
     * @param string $xmlData 返回的xml参数
     *
     **/
    public function LogAfterProcess($xmlData)
    {
        LogLib::inc()->debug(["LogAfterProcess",$xmlData]);
        return;
    }

    //重写回调处理函数
    /**
     * @param WxPayNotifyResults $data 回调解释出的参数
     * @param WxPayConfigInterface $config
     * @param string $msg 如果回调处理失败，可以将错误信息输出到该方法
     * @return true回调出来完成不需要继续回调，false回调处理未完成需要继续回调
     */
    public function NotifyProcess($objData, $config, &$msg)
    {
        $data = $objData->GetValues();
        $this->wx_callback_data = $data;
        LogLib::inc()->debug(["NotifyProcess objData->GetValues()",$data]);
        //TODO 1、进行参数校验
        if(!array_key_exists("return_code", $data) || (array_key_exists("return_code", $data) && $data['return_code'] != "SUCCESS")) {
            //TODO失败,不是支付成功的通知
            //如果有需要可以做失败时候的一些清理处理，并且做一些监控
            $msg = "NotifyProcess return_code err";
            LogLib::inc()->debug(["NotifyProcess return_code err",$data]);
            return false;
        }
        if(!array_key_exists("transaction_id", $data)){
            $msg = "NotifyProcess transaction_id err";
            LogLib::inc()->debug([" NotifyProcess transaction_id err",$data]);
            return false;
        }

        //TODO 2、进行签名验证
        try {
            $checkResult = $objData->CheckSign($config);
            if($checkResult == false){
                $msg = "NotifyProcess 签名错误 err";
                //签名错误
                LogLib::inc()->debug(" NotifyProcess 签名错误...");
                return false;
            }
        } catch(Exception $e) {
            LogLib::inc()->debug(['NotifyProcess exception',json_encode($e)]);
            return false;
        }

        //TODO 3、处理业务逻辑
        $notfiyOutput = array();


        //查询订单，判断订单真实性
        if(!$this->Queryorder($data["transaction_id"])){
            $msg = "NotifyProcess Queryorder err";
//            $msg = "订单查询失败";
            LogLib::inc()->debug(['NotifyProcess Queryorder err']);

            return false;
        }
        return true;
    }
}