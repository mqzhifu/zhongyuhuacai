<?php
set_time_limit(600);
header("Content-type:text/html;charset=utf-8");
class LoginCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $this->display("log/in_list.html");
    }


    function getList(){
        $this->getData();
    }

    function getWhere(){
        $where = " 1 ";
        if($mobile = _g("mobile"))
            $where .= " and mobile = '$mobile'";

        if($message = _g("message"))
            $where .= " and mobile like '%$message%'";

        if($from = _g("from")){
            $from .= ":00";
            $where .= " and add_time >= '".strtotime($from)."'";
        }

        if($to = _g("to")){
            $to .= ":59";
            $where .= " and add_time <= '".strtotime($to)."'";
        }


        return $where;
    }


    function getData(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getDataListTableWhere();

        $cnt = LoginModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "asc";


            $sort = array(
                'id',
                'id',
                '',
                '',
                '',
                '',
                'add_time',
            );
            $order = $sort[$order_column]." ".$order_dir;

            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始


            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            $sql = "select * from `login` where $where GROUP BY id order by id desc limit $iDisplayStart,$end ";

            
            $data = LoginModel::db()->getAllBySQL($sql);
            foreach($data as $k=>$v){
                $type = "登入";
                if($v['type'] == 2){
                    $type = "登出";
                }
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['uid'],
                    $v['ip'],
                    $v['dpi'],
                    $v['cellphone'],
                    $v['sim_imsi'],

                    $type,
                    UserModel::getTypeDescByKey($v['login_type']),
                    $v['cate'],


                    $v['os']."/".$v['os_version'],
                    $v['device_model']."/".$v['device_version'],
                    $v['browser_model']."/".$v['browser_version'],

                    get_default_date($v['a_time']),


                    "",
                );

                $records["data"][] = $row;
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
    }

    function getDataListTableWhere(){
        $where = 1;
        $uid = _g("uid");
        $ip = _g("ip");


        if($ip)
            $where .=" and ip = '$ip' ";

        if($uid)
            $where .=" and uid = '$uid' ";

        if($from = _g("from")){
            $from .= ":00";
            $where .= " and a_time >= '".strtotime($from)."'";
        }

        if($to = _g("to")){
            $to .= ":59";
            $where .= " and a_time <= '".strtotime($to)."'";
        }

        return $where;
    }

    // function export(){
    //     $objPHPExcel = new PHPExcel();
    //     $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    //     $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
    //     header("Pragma: public");
    //     header("Expires: 0");
    //     header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
    //     header("Content-Type:application/force-download");
    //     header("Content-Type:application/vnd.ms-execl");
    //     header("Content-Type:application/octet-stream");
    //     header("Content-Type:application/download");;
    //     header('Content-Disposition:attachment;filename="resume.xls"');
    //     header("Content-Transfer-Encoding:binary");
    //     $objWriter->save('php://output');
    //     //设置excel的属性：
    //     //创建人
    //     $objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
    //     //最后修改人
    //     $objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
    //     //标题
    //     $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
    //     //题目
    //     $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
    //     //描述
    //     $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
    //     //关键字
    //     $objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");
    //     //种类
    //     $objPHPExcel->getProperties()->setCategory("Test result file");

    //     //设置当前的sheet
    //     $objPHPExcel->setActiveSheetIndex(0);
    //     //设置sheet的name
    //     $objPHPExcel->getActiveSheet()->setTitle('Simple');
    //     //设置单元格的值
    //     $data = LoginModel::db()->getAll();
    //     $count = count($data);
    //     for($i=0; $i<$count; $i++){
    //             $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $this->convertUTF8($data[$i]['id']));
    //             $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $this->convertUTF8($data[$i]['uid']));
    //             $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $this->convertUTF8($data[$i]['ip']));
    //             $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $this->convertUTF8($data[$i]['dpi']));
    //             $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $this->convertUTF8($data[$i]['cellphone']));
    //             $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $this->convertUTF8($data[$i]['sim_imsi']));
    //             $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $this->convertUTF8($data[$i]['type']));
    //             $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, $this->convertUTF8($data[$i]['login_type']));
    //             $objPHPExcel->getActiveSheet()->setCellValue('I' . $i, $this->convertUTF8($data[$i]['cate']));
    //             $objPHPExcel->getActiveSheet()->setCellValue('J' . $i, $this->convertUTF8($data[$i]['os']."/".$data[$i]['os_version']));
    //             $objPHPExcel->getActiveSheet()->setCellValue('K' . $i, $this->convertUTF8($data[$i]['device_model']."/".$data[$i]['device_version']));
    //             $objPHPExcel->getActiveSheet()->setCellValue('L' . $i, $this->convertUTF8(date($data[$i]['a_time'])));

    //             echo date('H:i:s') . " Create new Worksheet object\n";
    //             $objPHPExcel->createSheet();
    //             $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
    //             $objWriter-save('php://output');
    //     }

    //     function convertUTF8($str)
    //     {
    //        if(empty($str)) return '';
    //        return  iconv('gb2312', 'utf-8', $str);
    //     }
    
    /**
     * 导出成excel 输出到网页
     * first和data必须对应
     * @param  [type] $first 数据head
     * @param  [type] $data  数据数组
     * @return [type]        [description]
     */
    function export_data_as_excel($first, $data){
        include PLUGIN . "/phpexcel/PHPExcel.php";
        $objPHPExcel = new PHPExcel();

        $num = 65;
        $x = 0;
        foreach($first as $k2=>$v2){
            $objPHPExcel->getActiveSheet()->setCellValue( chr($num+$x)."1" , $v2);
            $x++;
        }

        $line_num = 1;
        foreach($data as $k=>$line){
            $line_num ++;
            $x = 0;
            foreach($line as $k2=>$v2){
                if($x == 1){
                    $first = substr($v2,0,2);
                    if($first == 86){
                        $v2 = substr($v2,2);
                    }
                }
                $objPHPExcel->getActiveSheet()->setCellValue( chr($num+$x).$line_num , $v2);
                $x++;
            }
        }
        $objPHPExcel->getActiveSheet()->setTitle('Simple');
        $objPHPExcel->setActiveSheetIndex(0);


//        echo 55;exit;
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="01simple.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
//echo 222;exit;
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    
    function export(){

        $where = $this->getDataListTableWhere();
        $type = _g("type");
        
        $str = "导出excel:登录日志";
        $sql = "select * from login where $where order by id desc";
        $items = LoginModel::db()->getAllBySQL($sql);


        if(!$items)
            exit('数据为空，不需要导出');

        $uid = $this->_sess->getValue('id');
        $str .= count($items);
        // admin_db_log_writer($str,$uid,'export_excel');


        if(count($items) >= 10000){
            exit("数据已超过1000条，会影响服务器性能，请筛选条件分批下载");
        }

        $first = array(
            'ID',
            'uid',
            'IP',
            '分辨率',
            '手机号',
            'imsi',
            '类型',
            '登录方式',
            '请求方式',
            'OS/OS版本',
            '设备/设备版本',
            '浏览器/浏览器版本',
            '时间',
        );

        $newdatas = [];
        foreach ($items as $item) {
            $type = "登入";
            if($item['type'] == 2){
                $type = "退出";
            }
            $newdata = [];
            $newdata[] = $item['id'];
            $newdata[] = $item['uid'];
            $newdata[] = $item['ip'];
            $newdata[] = $item['dpi'];
            $newdata[] = $item['cellphone'];
            $newdata[] = $item['sim_imsi'];
            $newdata[] = $type;
            $newdata[] = UserModel::getTypeDescByKey($item['login_type']);
            $newdata[] = $item['cate'];
            $newdata[] = $item['os']."/".$item['os_version'];
            $newdata[] = $item['device_model']."/".$item['device_version'];
            $newdata[] = $item['browser_model']."/".$item['browser_version'];
            $newdata[] = get_default_date($item['a_time']);
            $newdatas[] = $newdata;
        }
        
        $this->export_data_as_excel($first, $newdatas);
        
    }

}