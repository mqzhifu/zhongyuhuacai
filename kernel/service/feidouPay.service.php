<?php
/**
 * Created by PhpStorm.
 * User: XiaHB.
 * Date: 2019/4/8
 * Time: 10:06
 */

/**
 * 飞豆游戏平台第三方支付接入;
 * Class feidouPayService
 */
class feidouPayService
{
    public static $SK = 'Q75qOZHRoYthQLb4cUh0';

    public static $METHOD = 'POST';

    public static $API = [
        'iospay' => 'http://api.feidou.com/local.iospay.php',
    ];

    /**
     * @var array
     */
    public $commonParam = [];

    public function __construct ()
    {
        $this->commonParam = $this->getCommonParam();
    }

    /**
     * 时间戳和ip地址;
     * @return array
     */
    public function getCommonParam ()
    {
        $timestamp = time();
        $res = [
            'timestamp' => $timestamp,// 时间戳;
            'ip' => get_client_ip(),// ip地址;
        ];
        return $res;
    }

    /**
     * @return bool|string
     */
    public function getiosPay($requestData){
        $api = self::$API['iospay'];
        $param = $this->commonParam;
        // md5(timestamp+userid+roleid+amount+coin+ip+gameid+serverid+key)注意顺序;
        $param['sign'] = md5($param['timestamp'].$requestData['userid'].$requestData['roleid'].$requestData['amount'].$requestData['coin'].$param['ip'].$requestData['gameid'].$requestData['serverid'].self::$SK);
        $param = array_merge($requestData, $param);// 参数归一;
        LogLib::appWriteFileHash(["==============feidou local.iospay.php begin==============",$param]);
        return $this->requestUrl($api, self::$METHOD, $param);
    }

    /**
     * php调用接口公共方法;
     * @param $url
     * @param string $method
     * @param array $param
     * @return mixed
     */
    public function requestUrl ($url, $method = 'GET', $param = [])
    {

        $ch = curl_init ();

        curl_setopt ( $ch, CURLOPT_URL, $url );

        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );

        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 10 );

        curl_setopt ( $ch, CURLOPT_TIMEOUT , 2 );

        $query = http_build_query($param);

        curl_setopt( $ch, CURLOPT_POSTFIELDS, $query);

        curl_setopt ( $ch, CURLOPT_POST, 1 ); // 启用POST提交;

        $file_contents = curl_exec ( $ch );

        curl_close ( $ch );

        return $file_contents;

    }

}