<?php
class UserAddressService{
    const IS_DEFAULT_TRUE = 1;
    const IS_DEFAULT_FALSE = 2;
    const IS_DEFAULT_DESC = [
        self::IS_DEFAULT_TRUE =>"是",
        self::IS_DEFAULT_TRUE =>"否",
    ];
    function getList($uid){
        $list = UserAddressModel::db()->getAll(" uid = {$uid} and is_default = ".UserAddressService::IS_DEFAULT_TRUE);
        return out_pc(200,$list);
    }

    function addOne(){

    }

    function delOne(){

    }

    function editOne(){

    }
}