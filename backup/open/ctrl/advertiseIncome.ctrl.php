<?php

/**
 * Class AdvertiseIncomeCtrl
 * 获取广告收入数据
 */
class AdvertiseIncomeCtrl
{

    /**
     * Date: 2019/2/28
     * author: haopeng
     * doc:获取广告收入数据
     */
    public function index() {
        try{

            //穿山甲当天数据获取不到  当天中午12点以后才能取到前一天的数据
            $startData = date("Y-m-d", strtotime('-2 day'));
            $endDate = date("Y-m-d",  time());

            //查询当天是否有数据进入  有的话不再查询新数据
            $newDayTotal = advertiseIncomeModel::db()->getCount("stat_datetime = '". $endDate ."'");

            if($newDayTotal > 0) {
                echo $endDate .' 数据已更新';
                return ;
            }

            $res = (new AdtoutiaoService())->getSlotAdList($startData, $endDate);

            $res = json_decode($res, true);

            if(!isset($res["code"]) || $res["code"] != 100) {
                throw new exception("innerface return error:". json_encode($res));
            }

            if(!isset($res["data"]) || empty($res["data"])) {
                throw new exception("innerface return empty");
            }

            //过滤返回值
            $charKey = ['ad_slot_id','appid','code_name','media_name','region','site_name','stat_datetime'];
            foreach($res["data"] as $key => &$item) {
                $item['`show`'] = $item['show'];
                unset($item['show']);

                unset($item['currency']);  //暂时去掉他们新增的一个字段

                foreach($charKey as $v) {
                    $item[$v] = '\'' .$item[$v]. '\'';
                }

            }

            foreach ($res["data"] as $key => $val) {

                $val['last_update_time'] = time();
                $fieldValue = implode(', ', $val);
                $fieldName = implode(', ', array_keys($val));

                $updateData = ' ';
                unset($val['appid'], $val['ad_slot_id'], $val['stat_datetime']);    //update不更新唯一索引
                foreach($val as $k => $v) {
                    $updateData .= $k .' = '. $v .' and ';
                }
                $updateData = substr($updateData, 0, -4);

                //  最先考虑的是数据增量返回的  所以使用on update方法  后来才知道是一次性返回全部  不是增量  也不是实时
                //  虽然on update方法用不到了  但也还是留着吧  不会影响太大性能 以后改成实时增量返回的话sql也不用改了
                $sql = 'insert into open_advertise_income('. $fieldName .') values('. $fieldValue .') on  DUPLICATE key update '. $updateData;

                $res = advertiseIncomeModel::db()->execute($sql);
                print_r($res);
                echo '-';

            }

        } catch (Exception $e){
            // 报警
            // write log and send mail   TODO
            LogLib::appErrorLog('Event----AdvertiseIncome---'. $e->getMessage());
            echo $e->getMessage();
        }
    }

}