<?php

/**
 * @Author: Kir
 * @Date:   2019-02-14 16:01:28
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-05-08 18:28:14
 */


/**
 * 
 */
class DocCtrl extends BaseCtrl
{

	public function index()
	{
		if ($this->isLogin()) {
            $header = 'isLogin';
        } else {
            $header = 'noLogin';
        }
        $this->display("document.html", "new", $header);
    }
	
	public function getDocs()
	{	
		$order = " ORDER BY `sort` ";
		$categoryList = DocCategoryModel::db()->getAll(' 1 '. $order);
		$docList = DocModel::db()->getAllBySQL(' select `id`,`category`,`title` from open_doc where 1=1 AND is_show = 1 '.$order);

		echo json_encode(['categoryList'=>$categoryList, 'docList'=>$docList]);
	}


	public function getContent()
	{
		$docId = _g('docId');
		if ($doc = DocModel::db()->getRowById($docId)) {
			$doc['a_time'] = date('Y-m-d', $doc['a_time']);
			echo json_encode($doc);
		}
		return;
	}


}