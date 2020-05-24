<?php

/**
 * @Author: xuren
 * @Date:   2019-03-18 15:45:02
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-03-18 16:46:56
 */
set_time_limit(600);
header("Content-type:text/html;charset=utf-8");
class PlayedGamesCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $this->display("log/played_games.html");
    }


    function getList(){
        $this->getData();
    }


    function getData(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getDataListTableWhere();

        $cnt = PlayedGamesModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "asc";


            $sort = array(
            	'',
                'id',
                'game_id',
                'uid',
                'a_time',
                'e_time',
                '',
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

            $sql = "select * from `played_games` where $where GROUP BY id order by $order limit $iDisplayStart,$end ";

            
            $data = PlayedGamesModel::db()->getAllBySQL($sql);
            foreach($data as $k=>$v){
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['game_id'],
                    $v['uid'],
                    get_default_date($v['a_time']),
                    get_default_date($v['e_time']),
                    '',
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
        $id = _g("id");
        $uid = _g("uid");
        $ip = _g("ip");
        $a_from = _g("a_from");
        $a_to = _g("a_to");

        $e_from = _g("e_from");
        $e_to = _g("e_to");
        if($id){
        	$where .=" and id = '$id' ";
        }

        if($ip)
            $where .=" and ip = '$ip' ";

        if($uid)
            $where .=" and uid = '$uid' ";

        if($a_from = _g("a_from")){
            $a_from .= ":00";
            $where .= " and a_time >= '".strtotime($a_from)."'";
        }

        if($a_to = _g("a_to")){
            $a_to .= ":59";
            $where .= " and a_time <= '".strtotime($a_to)."'";
        }

        if($e_from = _g("e_from")){
            $e_from .= ":00";
            $where .= " and e_time >= '".strtotime($e_from)."'";
        }

        if($e_to = _g("e_to")){
            $e_to .= ":59";
            $where .= " and e_time <= '".strtotime($e_to)."'";
        }

        return $where;
    }

    
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

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="01simple.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    function export(){
        $where = $this->getDataListTableWhere();
        // $type = _g("type");
        
        $str = "导出excel:登录日志";
        $sql = "select * from played_games where $where GROUP BY id order by id desc";
        $items = PlayedGamesModel::db()->getAllBySQL($sql);


        if(!$items)
            exit('数据为空，不需要导出');

        $uid = $this->_sess->getValue('id');
        $str .= count($items);
        // admin_db_log_writer($str,$uid,'export_excel');

        

        $first = array(
            'ID',
            'uid',
            'GameID',
            '添加时间',
            '结束时间',
        );

        $newdatas = [];
        foreach ($items as $item) {
            $newdata = [];
            $newdata[] = $item['id'];
            $newdata[] = $item['uid'];
            $newdata[] = $item['game_id'];
            $newdata[] = date("Y-m-d H:i:s",$item['a_time']);
            $newdata[] = date("Y-m-d H:i:s",$item['e_time']);
            $newdatas[] = $newdata;
        }
        
        $this->export_data_as_excel($first, $newdatas);
        
    }


}