<?php
class UserAddressService{
    const IS_DEFAULT_TRUE = 1;
    const IS_DEFAULT_FALSE = 2;
    const IS_DEFAULT_DESC = [
        self::IS_DEFAULT_TRUE =>"是",
        self::IS_DEFAULT_TRUE =>"否",
    ];
    function getList($uid,$is_default = 0){
        $where = "uid = {$uid}";
        if($is_default){
            $where .= "  and is_default = ".UserAddressService::IS_DEFAULT_TRUE;
        }

        $list = UserAddressModel::db()->getAll($where);
        if($list){
            foreach ($list as $k=>$v){
                $list[$k] = $this->formatRow($v);
            }
        }
        return out_pc(200,$list);
    }
    //获取用户的，一个默认收货地址，如果没有，就选择随便选择一个
    function getUserAddressDefault($uid){
        $list = UserAddressModel::db()->getAll(" uid = $uid ");
        if(!$list){
            return out_pc(200,$list);
        }

        foreach ($list as $k=>$v){
            if($v['is_default'] == UserAddressService::IS_DEFAULT_TRUE){
                $v = $this->formatRow($v);
                return out_pc(200,$v);
            }
        }

        return out_pc(200,$list[0]);
    }

    function getRowById($id){
        $info = UserAddressService::getById($id);
        if($info['code'] != 200){
            return out_pc(1000);
        }

        $info = $info['msg'];

        $info['province_cn'] =  $this->getProvinceByCode($info['province_code']) ;
        $info['city_cn'] =  $this->getProvinceByCode($info['city_code']) ;
        $info['county_cn'] =  $this->getProvinceByCode($info['county_code']) ;
        $info['town_cn'] =   $this->getProvinceByCode($info['town_code']);

        return out_pc(200,$info);
    }

    function formatRow($row){
        $row['province_cn'] = $this->coverProvinceCn( $row['province_code']);
        $row['county_cn'] = $this->coverCountyCn( $row['county_code']);
        $row['city_cn'] = $this->coverCityCn( $row['city_code']);
        $row['town_cn'] = $this->coverTownCn( $row['town_code']);
        return $row;
    }

    function coverProvinceCn($code,$default = "--"){
        $province = $this->getProvinceByCode($code) ;
        if(!$province){
            return $default;
        }

        return $province['name'];
    }

    function coverCityCn($code,$default = "--"){
        $province = $this->getCityByCode($code) ;
        if(!$province){
            return $default;
        }

        return $province['name'];
    }

    function coverCountyCn($code,$default = "--"){
        $province = $this->getCountyByCode($code) ;
        if(!$province){
            return $default;
        }

        return $province['name'];
    }

    function coverTownCn($code,$default = "--"){
        $province = $this->getTownByCode($code) ;
        if(!$province){
            return $default;
        }

        return $province['name'];
    }


    function editOne($uid,$id,$data){
        if(!$id){
            return out_pc(8381);
        }

        $address = $this->getById($id);
        if(!$address){
            return out_pc(1038);
        }

        if($address['uid'] != $uid){
            return out_pc(8382,array($uid));
        }

        $this->addOne($uid,$data,$id);
    }

    function addOne($uid,$data,$editId = 0){
        if(!$uid){
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
        //检查  省市县镇
//        $this->checkArea($data);

        $addData = array(
            'province_code'=>$data['province_code'],
            'city_code'=>$data['city_code'],
            'county_code'=>$data['county_code'],
//            'town_code'=>$data['town_code'],
            'town_code'=>0,
            'mobile'=>$data['mobile'],
            'uid'=>$uid,
            'name'=>$data['name'],
            'address'=>$data['address'],
            'uid'=>$uid,
            'a_time'=>time(),
        );

        if(arrKeyIssetAndExist($data,'is_default')){
            //先把之前已经 设置成默认收货地址 置0
            $data = array("is_default"=>self::IS_DEFAULT_FALSE);
            UserAddressModel::db()->update($data,"uid = {$uid} limit 100" );
            $addData['is_default'] = self::IS_DEFAULT_TRUE;
        }

        if(!$editId){
            $newId = UserAddressModel::db()->add($addData);
        }else{
            $newId = UserAddressModel::db()->upById($editId,$addData);
        }

        return out_pc(200,$newId);
    }

    function checkArea($data){
        if(!arrKeyIssetAndExist($data,'province_code')){
            return out_pc(8359);
        }

        if(!arrKeyIssetAndExist($data,'city_code')){
            return out_pc(8360);
        }

        if(!arrKeyIssetAndExist($data,'county_code')){
            return out_pc(8361);
        }

//        if(!arrKeyIssetAndExist($data,'town_code')){
//            return out_pc(8362);
//        }

        if(!$this->getProvinceByCode($data['province_code'])){
            return out_pc(1030);
        }

        if(!$this->getCityByCode($data['city_code'])){
            return out_pc(1031);
        }

        if(!$this->getCountyByCode($data['county_code'])){
            return out_pc(1032);
        }

//        if(!$this->getTownByCode($data['town_code'])){
//            return out_pc(1033);
//        }

        return out_pc(200);
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

    function parserAddressByStr($str){
        if(!$str){
            return out_pc(8383);
        }
        $delimiter = "";
        $delimiterArr = array('，',',','\n','<br/>','<br />','<br>');
        foreach ($delimiterArr as $k=>$v){
            if (strpos($str,$v) !== false) {
                $delimiter = $v;
            }
        }

        if(!$delimiter){
            out_ajax(8380);
        }

        $arr = explode($delimiter,$str);
        $strArr = [];
        foreach ($arr as $k=>$v){
            $x = trim($v);
            foreach ($delimiterArr as $k2=>$v2){
                $x = str_replace($v2,"",$x);
            }
            $strArr[] = $x;
        }

        $rs = array(
            'name'=>'',
            'province'=>'',
            'city'=>'',
            'county'=>'',
            'town'=>'',
            'village'=>'',
            'mobile'=>'',
        );

        if(count($strArr) == 6){
            $rs = array(
                'name'=>$strArr[0],
                'province'=>$strArr[1],
                'city'=>$strArr[2],
                'county'=>$strArr[3],
                'town'=>$strArr[4],
                'village'=>$strArr[5],
                'mobile'=>$strArr[6],
            );
        }else{
            return out_pc(200,$rs);
        }

//        foreach ($strArr as $k=>$v){
//            if (strpos($str,"省") !== false) {
//
//            }
//        }
    }

    function getAreaProvinceCity(){
        $provinceList = AreaProvinceModel::db()->getAll(1,null,"code,short_name");
        $provinceData = null;
        foreach ($provinceList as $k=>$v){
            $provinceData[$k] = $v['short_name'];
        }

        $cityList = AreaCityModel::db()->getAll(1,null,"code,short_name");
        $cityData = null;
        foreach ($cityList as $k=>$v){
            $cityData[$k] = $v['short_name'];
        }

        $final = array('province_data'=>$provinceData,'city_data'=>$cityData);
        var_dump($final);exit;

        return out_pc(200,$final);
    }

    function getById($id){
        $row = UserAddressModel::db()->getRow($id);
        if(!$row){
            return out_pc(200,$row);
        }

        $row = $this->formatRow($row);

        return out_pc(200,$row);
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