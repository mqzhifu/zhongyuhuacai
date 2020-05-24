<?php
/**
 * Created by PhpStorm.
 * User: xiahongbo
 * Date: 2019/3/13
 * Time: 11:18
 */
class printBillCtrl extends BaseCtrl{
    /**
     * 打印电子对账单;
     */
    public function printElectronicBills($partnerName, $settlementBs, $settlementTime, $settlementNo, $settlementMonth, $settlementMoney){
        //二期参数固定，三期中，此方法可看做一个接口，无需改动，只需要传入对应的参数即可，如下:
        $partnerName = '北京123科技有限公司';
        $settlementBs = '连连看 服务/内购 收入结算';
        $settlementTime = '2019-03-01  至  2019-03-31';
        $settlementNo = 'KX-AD-123456789';
        $settlementMonth = '2019-03';
        $settlementMoney = '1000.00';
        require_once(PLUGIN."tcpdf/tcpdf.php");
        $pdf = new tcpdf();
        /*$pdf->AddPage();
        $txt = "您好！";
        $pdf->Write(20, $txt);
        $pdf->Output('minimal.pdf', 'I');
        $url = "http://isop-test.feidou.com/game/aa/";
        $html = file_get_contents($url);*/
        $time = date("Y-m-d");
        $pdf->SetCreator('Victor');
        $pdf->SetAuthor('Victor');
        $pdf->SetTitle("开心网结算对账单".'（'.$time.'）');
        $pdf->SetSubject('');
        $pdf->SetKeywords('');
        //设置页眉信息 参数分别是LOGO地址，LOGO大小，两行标题，标题颜色，分割线颜色。。颜色是RGB
        $pdf->SetHeaderData('', 30, '', '', array(0,0,0), array(0,0,0));
        //设置页脚信息
        $pdf->setFooterData(array(0,0,0), array(0,0,0));
        // 设置页眉和页脚字体
        $pdf->setHeaderFont(Array('stsongstdlight', '', '12'));
        $pdf->setFooterFont(Array('helvetica', '', '8'));
        //设置默认等宽字体
        $pdf->SetDefaultMonospacedFont('courier');
        //设置间距
        $pdf->SetMargins(15, 0, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        //设置分页
        $pdf->SetAutoPageBreak(TRUE, 15);
        //设置图片比例
        $pdf->setImageScale(1.25);
        //将页眉页脚的信息输出出来。
        $pdf->AddPage();
        //设置字体 - 正文标题的哦。B是加粗，15是大小
        $pdf->SetFont('stsongstdlight', 'B', 15);
        $pdf->Write(20, '', '', 0, 'C', true, 0, false, false, 0);
        //设置字体 - 正文内容的哦。B是加粗，15是大小
        $pdf->SetFont('stsongstdlight', '', 11.2);
        $html = $this->getElectronicBillsTemplate($partnerName, $settlementBs, $settlementTime, $settlementNo, $settlementMonth, $settlementMoney);
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        $pdf->Output('electronic_bills.pdf','I');
    }

    /**
     * 获取打印模板;
     * @return string
     */
    protected function getElectronicBillsTemplate($partnerName, $settlementBs, $settlementTime, $settlementNo, $settlementMonth, $settlementMoney){
        //理论上附件页是无需打印的，获取附件中table中的数据，用动态拼接table表单的形式，二期此功能点尚未完善，故暂且认为其会传入个数组，并随意赋值，功能点已实现，注意三期时候，改为传入数组的形式；
        $str_table = $this->getTableInfo();
        $strings = "<html><head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <meta http-equiv=\"X-UA-Compatible\" content=\"ie=edge\">
    <style>
        body {
            background: #98A0B2;
        }
        .happyBill {
            width:210mm;
            margin: 0 auto;
            padding-left: 52px;
            padding-top: 132px;  
            padding-right: 52px;
            background:rgba(255,255,255,1);
            box-sizing: border-box;
        }
        .happyBill h3 {
            font-size: 33px;
            font-weight: 400;
            color:rgba(0,0,0,1);
            line-height: 36px;
        }
        .information {
            width: 100px;
            height: 124px;
            margin-top: 61px;
        
            font-weight: bold;
            color:rgba(0,0,0,1);
            line-height: 36px;
        }
        .information p {
            font-weight: bold;
            font-size: 14px;
            line-height: 40px;
        }
        table{
            margin-top: 60px;
            width: 100%;
            border-right: 1px solid #000000;
            border-bottom: 1px solid #000000;
        }
        table td{
            width: 50%;  
            border-left: 1px solid #000000;
            border-top: 1px solid #000000;
            height: 30px;
            line-height: 30px;
            text-align: center;
        }
        table tr:nth-child(1){
           background:rgba(243,243,245,1);
        }
        .rule {
            width: 100%;
            height: 0.5px;
            background:rgba(0,0,0,1);
            margin-top: 104px;
        }
        .discribe {
            height: 15px;
            font-size: 15px;
            font-weight: 400;
            color:rgba(0,0,0,1);
            line-height: 36px;
        }
        .news {
            margin-top: 131px;
            width: 106px;
            height: 88px;
            font-size: 15px;
            font-weight: bold;
            color:rgba(0,0,0,1);
            line-height: 56px;
            text-align: left;
        }
        .news span{
            display: block;
            text-align: right;
        }
        .cardInfo {
            margin-top: 304px;
            margin-bottom: 131px;
        }
        .headTitle {
            width: 90px;
            height: 22px;
            font-size: 22px;
            font-weight: bold;
            color:rgba(0,0,0,1);
            line-height: 36px;
        }
        .cardInfo ol {
            margin-top: 24px;
        }
        .cardInfo ol li {
            font-size: 15px;
            font-weight: 400;
            color:rgba(0,0,0,1);
        }
        .Enclosure {
            margin-top: 131px;
        }
        .Enclosure span:nth-child(1) {
            width: 64px;
            height: 31px;
            font-size: 33px;
            font-weight: 400;
            color:rgba(0,0,0,1);
            line-height: 36px;
        }
        .Enclosure span:nth-child(2) {
            font-size: 15px;
            font-weight: 400;
            color:rgba(0,0,0,1);
            line-height: 36px;
            margin-left: 16px;
        }
        .cardDetail {
            margin-top: 85px;
            padding-bottom: 937px;
            background:rgba(255,255,255,1);
        }
        .cardDetail b{
            font-size: 15px;
            font-weight: bold;
            color:rgba(0,0,0,1);
            line-height: 36px;
        }
        .cardDetail span {
            font-size: 15px;
            font-weight: bold;
            color:rgba(0,0,0,1);
            line-height: 36px;
            margin-top: 104px;
            margin-left: 600px;
            display: inline-block;
         
        }
        .table_d {
            margin-top: 23px;
        }
        .table_d td {
            width: 33.3%;
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            color:rgba(0,0,0,1);
            line-height: 61px;
            height: 61px;
        }
        table tr:nth-child(1){
           background:#F3F3F5;
        }
    </style>
</head>
                     <body>
                        <div class='happyBill'>
                        <h1>开心网结算对账单</h1>
                        <div class='information'>
                            <p>合作伙伴名称 :$partnerName</p>
                            <p>结算业务名称 :$settlementBs</p>
                            <p>结算时段 :$settlementTime</p>
                            <p>结算单号 :$settlementNo</p>
                        </div>
                        <!-- 表格 -->
                        <table width=\"600\" cellspacing=\"0\" cellpadding=\"1px\" border='1'>
                            <tr>
                                <td >结算月份</td>
                                <td >分成金额（元）</td>
                            </tr>
                            <tr>
                                <td>$settlementMonth</td>
                                <td>$settlementMoney</td>
                            </tr>
                        </table>
                        <!-- 分割线部分 -->
                        <p class='rule'></p>
                        
                        <p class='discribe'>以下为企业操作栏（个人主体无需操作）</p>
                        <!-- 公司信息 -->
                        <div class='news'>
                            <span style='font-weight:bold'>公司（盖章）：</span>
                        </div>
                        <div> 
                            <span style='font-weight:bold'>日期：</span>
                        </div>
                        <!-- 发票信息 -->
                        <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
                        <div class='cardInfo'>
                            <h3>开票信息</h3>
                            <ol>
                                <li>发票信息：增值税专用发票</li>
                                <li>公司名称：北京开心人信息技术有限公司</li>
                                <li>纳税人识别号：91110108672848792T</li>
                                <li>开票地址及电话：北京市朝阳区北三环东路8号1幢-3至26层101内12层1272房间，010-57978378</li>
                                <li>开户行及账号：招商银行北京海淀支行，110906303810302</li>
                                <li>货物或应税劳务名称：可选择“信息服务费” “服务费” “技术服务费”</li>
                                <li>注意：请根据对账单的实际金额开票，不要将多张对账单金额开在同一张发票内。</li>                      
                            </ol>
                        </div>
                        
                        <div class='Enclosure'><br/><br/><br/><br/>
            <span>附件</span><span>（该附件页无需打印邮寄）</span>
        </div>
         <div class='cardDetail'>
            <b>收款主体下广告收入明细</b><br>
            $str_table
            <span>合计：1000.00</span>
        </div>
                    </div>
                     </body>
                     </html>";
        return $strings;
    }

    /**
     * 获取附件中的表单数据（html拼接）
     * @return string
     */
    protected function getTableInfo(){
        $orderList [0]['aa'] = '2019-03 ';
        $orderList [0]['bb'] = '连连看';
        $orderList [0]['cc'] = '200.00';
        $orderList [1]['aa'] = '2019-04';
        $orderList [1]['bb'] = '国际象棋';
        $orderList [1]['cc'] = '800.00';
        $strTable = '<table width="300" cellspacing="0" cellpadding="10" border=\'1\' class=\'table_d\'>' .
            '<tr>' .
            '<td style="text-align:center;">结算月份</td>' .
            '<td style="text-align:center;">项目名称</td>' .
            '<td style="text-align:center;">分成金额（元）</td>' .
            '</tr>';

        //循环出数据
        foreach ($orderList as $k => $val) {
            $strTable .= '<tr>' .
                '<td style="text-align:center;">&nbsp;' . $val['aa'] . '</td>' .
                '<td style="text-align:center;">&nbsp;' . $val['bb'] . '</td>' .
                '<td style="text-align:center;">&nbsp;' . $val['cc'] . '</td>' .
                '</tr>';
        }
        //补上最后的</table>
        $strTable .= '</table>';
        return $strTable;
    }
}