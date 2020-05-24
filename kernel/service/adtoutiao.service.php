<?php

/**
 * 穿山甲广告数据
 * Class AdtoutiaoService
 */
class AdtoutiaoService
{
    //穿山甲的secureKey
    public static $SK = 'd7bdfe64fe2ceacc6748d75f51fcd43f';

    //穿山甲分配的userId
    public static $USER_ID = '5351';

    //http or https
    public static $REQUEST = 'http://';

    //请求方式
    public static $METHOD = 'GET';

    //穿山甲的api
    public static $API = [
        'user' => 'ad.toutiao.com/union/media/open/api/report/user',//账号数据接口
        'app' => 'ad.toutiao.com/union/media/open/api/report/app',//APP 数据接口
        'slot' => 'ad.toutiao.com/union/media/open/api/report/slot',//广告位数据接口
    ];
    //查询粒度
    public static $GRANULARITY = [
        'daily' => 'STAT_TIME_GRANULARITY_DAILY',//按天查询
    ];
    /**
     * 获取APi公共参数
     * @var array
     */
    public $commonParam = [];

    public function __construct ()
    {
        $this->commonParam = $this->getCommonParam();
    }

    //获取通用参数
    public function getCommonParam ()
    {
        $secureKey = self::$SK;
        $timestamp = time();
        $nonce = mt_rand(1000, 100000);
        $keys = [$secureKey, $timestamp, $nonce];
        sort($keys, 2);
        $keyStr = implode('', $keys);
        $sign = sha1($keyStr);
        $res = [
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'sign' => $sign,
            'user_id' => self::$USER_ID,
        ];
        return $res;
    }

    /**
     * 获取用户广告信息
     * @param $startDate
     * @param $endData
     * @return mixed
     */
    public function getUserAdList ($startDate, $endData)
    {
        $api = self::$API['user'];
        $privateParam = [
            'start_date' => $startDate,
            'end_date' => $endData,
            'time_granularity' => self::$GRANULARITY['daily'],
        ];
        $param = array_merge($this->commonParam, $privateParam);
        return $this->requestUrl($api, self::$METHOD, $param);
    }

    /**
     * 获取用户所有App广告数据
     * @param $startDate
     * @param $endData
     * @return bool|string
     */
    public function getAppAdList ($startDate, $endData)
    {
        $api = self::$API['app'];

        $privateParam = [
            'start_date' => $startDate,
            'end_date' => $endData,
            'time_granularity' => self::$GRANULARITY['daily'],
        ];
        $param = array_merge($this->commonParam, $privateParam);
        return $this->requestUrl($api, self::$METHOD, $param);
    }

    /**
     * 获取用户广告位数据
     * @param $startDate
     * @param $endData
     * @return bool|string
     */
    public function getSlotAdList ($startDate, $endData)
    {
        $api = self::$API['slot'];

        $privateParam = [
            'start_date' => $startDate,
            'end_date' => $endData,
            'time_granularity' => self::$GRANULARITY['daily'],
        ];
        $param = array_merge($this->commonParam, $privateParam);
        return $this->requestUrl($api, self::$METHOD, $param);
    }

    /**
     * 请求
     * @param $url
     * @param string $method
     * @param array $param
     * @return bool|string
     */
    public function requestUrl ($url, $method = 'GET', $param = [])
    {
        $query = http_build_query($param);
        $url = self::$REQUEST . $url;
        $ch = curl_init();
        $timeout = 200;
        $user_agent = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.146 Safari/537.36";
        if (strtolower($method) == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        } else {
            $url .= '?' . $query;
            curl_setopt($ch, CURLOPT_URL, $url);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    public function contabImportData(){
        //穿山甲当天数据获取不到  当天中午12点以后才能取到前一天的数据
        $startData = date("Y-m-d", strtotime('-1 day'));
        $endDate = date("Y-m-d",  time());
        $this->contabImportDataByInterval($startData, $endDate);
    }

    public function contabImportDataByInterval($startData, $endDate){
        try{

            //查询当天是否有数据进入  有的话不再查询新数据
            $newDayTotal = advertiseIncomeModel::db()->getCount("stat_datetime = '". $endDate ."'");

            if($newDayTotal > 0) {
                echo $endDate .' 数据已更新';
                return ;
            }

            $res = $this->getSlotAdList($startData, $endDate);

            $res = json_decode($res, true);

            if(!isset($res["code"]) || $res["code"] != 100) {
                throw new exception("innerface return error:". json_encode($res));
            }

            if(!isset($res["data"]) || empty($res["data"])) {
                throw new exception("innerface return empty");
            }

            //过滤返回值
            $charKey = ['ad_slot_id','appid','code_name','media_name','region','site_name','stat_datetime'];
            $liveKey = ['appid','ad_slot_id','click','click_rate','code_name','cost','ecpm','media_name','region','show','site_name','stat_datetime'];
            $allkeys = [];
            if(isset($res['data']) && count($res['data']) > 0){
                $allkeys = array_keys($res['data'][0]);
            }
            $diffset = array_diff($allkeys,$liveKey);
            foreach($res["data"] as $key => &$item) {
                $item['`show`'] = $item['show'];
                unset($item['show']);

                unset($item['currency']);  //暂时去掉他们新增的一个字段
                
                foreach ($diffset as $unsetKey) {
                    unset($item[$unsetKey]);
                }
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
                // LogLib::adImportLog('advertise import succ!'.date('%Y-%m-%d %H:%i:%s',time()));

            }

        } catch (Exception $e){
            // 报警
            // write log and send mail   TODO
            // LogLib::appErrorLog('Event----AdvertiseIncome---'. $e->getMessage());
            echo $e->getMessage();
        }
    }


}