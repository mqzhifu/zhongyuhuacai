<?php
Class HouseService {
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

//    function getRowById($id,$default = '--'){
//        $info = HouseModel::db()->getById($id);
//        if(!$info){
//            return $default;
//        }
//
//        $info['province_cn'] =  $this->getProvinceByCode($info['province_code']) ;
//        $info['city_cn'] =  $this->getProvinceByCode($info['city_code']) ;
//        $info['county_cn'] =  $this->getProvinceByCode($info['county_code']) ;
//        $info['town_cn'] =   $this->getProvinceByCode($info['town_code']);
//
//        return out_pc(200,$info);
//    }
//
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
//

    function getGuessRoi($payList){
        $guessRoi = 0;
        foreach ($payList as $k=>$v){
            if($v['type'] == OrderModel::FINANCE_INCOME){
                $guessRoi += $v['price'];
            }else{
                $guessRoi -= $v['price'];
            }
        }
        return $guessRoi;
    }

}