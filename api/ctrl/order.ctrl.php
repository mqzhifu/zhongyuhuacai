<?php
class OrderCtrl extends BaseCtrl  {
    function getListByUser(){
        $this->orderService->getUserList($this->uid);
    }
    function doing(){
        $gid = $this->request['gid'];
        $num = $this->request['num'];

        $this->orderService->doing($this->uid,$gid,$num);
    }

    function getOneDetail(){
        $id = $this->request['id'];
        $this->orderService->getOneDetail($id);
    }

    function refund(){
        $id = $this->request['id'];
        $this->orderService->refund($id);
    }


}