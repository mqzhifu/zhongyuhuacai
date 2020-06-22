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

    function addOne($uid,$data){

        $this->checkArea($data);

        if(!arrKeyIssetAndExist($data,'uid')){
            return out_pc(8002);
        }

        if(!arrKeyIssetAndExist($data,'mobile')){
            return out_pc(8364);
        }

        if(!arrKeyIssetAndExist($data,'name')){
            return out_pc(8363);
        }

        if(!arrKeyIssetAndExist($data,'address')){
            return out_pc(8365);
        }

        $addData = array(
            'province'=>$data['province'],
            'city'=>$data['city'],
            'county'=>$data['county'],
            'town'=>$data['town'],
            'mobile'=>$data['mobile'],
            'uid'=>$data['uid'],
            'name'=>$data['name'],
            'address'=>$data['address'],
            'uid'=>$uid,
            'a_time'=>time(),
        );

        if(arrKeyIssetAndExist($data,'is_default')){
            $addData['is_default'] = self::IS_DEFAULT_TRUE;
        }

        $newId = UserAddressModel::db()->add($data);
        return out_pc(200,$newId);
    }

    function checkArea($data){
        if(!arrKeyIssetAndExist($data,'province')){
            return out_pc(8359);
        }

        if(!arrKeyIssetAndExist($data,'city')){
            return out_pc(8360);
        }

        if(!arrKeyIssetAndExist($data,'county')){
            return out_pc(8361);
        }

        if(!arrKeyIssetAndExist($data,'town')){
            return out_pc(8362);
        }

        if(!$this->getProvinceByCode($data['province'])){
            return out_pc(1030);
        }

        if(!$this->getProvinceByCode($data['city'])){
            return out_pc(1031);
        }

        if(!$this->getProvinceByCode($data['county'])){
            return out_pc(1032);
        }

        if(!$this->getProvinceByCode($data['town'])){
            return out_pc(1033);
        }
    }

    function delOne($uid,$id){
        $row = UserAddressModel::db()->getById($id);
        if(!$row){

        }

        if($row['uid'] != $uid){

        }
        $rs =  UserAddressModel::db()->delById($id);
        return out_pc(200,$rs);
    }

    function editOne(){

    }

    function getProvinceByCode($code){
        return AreaProvinceModel::db()->getRow(" code = '$code'");
    }

    function getCityByCode($code){
        return AreaCityModel::db()->getRow(" code = '$code'");
    }

    function getCountyByCode($code){
        return AreaCountyModel::db()->getRow(" code = '$code'");
    }

    function getTownByCode($code){
        return AreaTownModel::db()->getRow(" code = '$code'");
    }


}