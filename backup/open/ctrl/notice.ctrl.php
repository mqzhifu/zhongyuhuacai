<?php

/**
 * @Author: Kir
 * @Date:   2019-02-21 10:47:23
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-05-10 14:25:04
 */

class NoticeCtrl extends BaseCtrl
{
	public function index()
	{
		$this->addJs("/assets/open/scripts/layui.js");
        
        $this->display("notice.html", "new", "isLogin");
    }

    public function getCount()
    {
        $uid = $this->_uid;
        $count = NotificationModel::db()->getCount(" uid = $uid ");
        $this->outputJson(200, "succ", ['count'=>$count]);
    }

    public function getNoticeList()
    {
    	$uid = $this->_uid;
        $page = _g("page");
        $length = _g("length");
        $start = ($page-1) * $length;

    	$noticeList = NotificationModel::db()->getAll(" uid = $uid order by id DESC limit $start,$length");

    	// 格式化时间
    	foreach ($noticeList as &$notice) {
    		$notice['a_time'] = date('Y-m-d', $notice['a_time']);
    	}

		$this->outputJson(200, "succ", $noticeList);
    }


    /**
     * 已读处理;
     */
    public function setReadStatus(){
        $id = _g('id');
        NotificationModel::db()->upById($id, ["is_read"=>1]);
    }
}