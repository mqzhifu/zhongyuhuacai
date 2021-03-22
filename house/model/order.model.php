<?php
class OrderModel {
	static $_table = 'orders';
	static $_pk = 'id';
	static $_db = null;
    static $_db_key = DB_CONN_DEFAULT;

    const TYPE_SALE = 1;
    const TYPE_TENANCY = 2;
    const TYPE_DESC = [
        self::TYPE_SALE=>"售卖",
        self::TYPE_TENANCY=>"租赁",
    ];

    const PAY_TYPE_MONTH = 1;
    const PAY_TYPE_QUARTER = 2;
    const PAY_TYPE_HALF_YEAR = 3;
    const PAY_TYPE_YEAR = 4;
    const PAY_TYPE_DESC = [
        self::PAY_TYPE_MONTH=>"月",
        self::PAY_TYPE_QUARTER=>"季",
        self::PAY_TYPE_HALF_YEAR=>"半年",
        self::PAY_TYPE_YEAR=>"整年",
    ];

    //支付类型 - 转换成具体的 天
    const PAY_TYPE_TURN_DAY = [
        self::PAY_TYPE_MONTH=>31,
        self::PAY_TYPE_QUARTER=>90,
        self::PAY_TYPE_HALF_YEAR=>183,
        self::PAY_TYPE_YEAR=>365,
    ];

    //支付类型 - 转换成具体的 月
    const PAY_TYPE_TURN_MONTH = [
        self::PAY_TYPE_MONTH=>1,
        self::PAY_TYPE_QUARTER=>3,
        self::PAY_TYPE_HALF_YEAR=>6,
        self::PAY_TYPE_YEAR=>12,
    ];

    const CATE_MASTER = 1;
    const CATE_USER = 2;
    const CATE_DESC = [
        self::CATE_MASTER=>"房主",
        self::CATE_USER=>"用户",
    ];

    const FINANCE_INCOME = 1;
    const FINANCE_EXPENSE = 2;
    const FINANCE_DESC = [
        self::FINANCE_INCOME=>"收入",
        self::FINANCE_EXPENSE=>"支出",
    ];

    const STATUS_WAIT = 1;
    const STATUS_OK = 2;
    const STATUS_FINISH = 3;
    const STATUS_DESC = [
        self::STATUS_WAIT=>"未处理",
        self::STATUS_OK=>"已确认",//已生成支付列表记录
        self::STATUS_FINISH=>"已完结",
    ];

    const FINISH_NORMAL = 1;
    const FINISH_FORCE = 2;
    const FINISH_DESC = [
        self::FINISH_NORMAL=>"正常结算",
        self::FINISH_FORCE=>"强制结算",
    ];


    const Property_Heat_Type_All = 1;
    const Property_Heat_Type_Half = 2;
    const Property_Heat_Type_No = 3;
    const Property_Heat_Type_Desc = [
        self::Property_Heat_Type_All=>"全包",
        self::Property_Heat_Type_Half=>"半包",
        self::Property_Heat_Type_No=>"双包",
    ];

    const Time_Cycle_IN = 1;
    const Time_Cycle_OUT = 2;
    const Time_Cycle_DESC = [
        self::Time_Cycle_IN=>"内置",
        self::Time_Cycle_OUT=>"外置",
    ];


//    const NOTIFY_WX = 1;
//    const NOTIFY_SMS = 2;
//    const NOTIFY_EMAIL = 3;
//    const NOTIFY_EMAIL = 3;
//
//    const NOTIFY_DESC = [
//        self::NOTIFY_WX=>"微信",
//        self::NOTIFY_SMS=>"短信",
//        self::NOTIFY_EMAIL=>"邮件",
//    ];

    //提醒通知 - 渠道
    const NOTIFY_WX = 1;
    const NOTIFY_WX_COMPANY = 2;
    const NOTIFY_DINGDING = 3;
    const NOTIFY_EMAIL = 3;
    const NOTIFY_SMS = 2;
    const NOTIFY_DESC = [
        self::NOTIFY_WX=>"微信",
        self::NOTIFY_WX_COMPANY=>"企业微信",
        self::NOTIFY_DINGDING=>"钉钉",
        self::NOTIFY_EMAIL=>"邮件",
        self::NOTIFY_SMS=>"短信",
    ];


	static function db(){
		if(self::$_db)
			return self::$_db;

		self::$_db = new DbLib(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}


    static function getById($id){
        $row = self::db()->getById($id);
        if(!$row){
            return $row;
        }

        return self::format($row);
    }

    static function format($row){
        $row['dt'] = get_default_date($row['a_time']);
        $row['contract_end_time_dt'] = get_default_date($row['contract_end_time']);
        $row['contract_start_time_dt'] = get_default_date($row['contract_start_time']);

        $row['pay_mode_turn_day'] = OrderModel::PAY_TYPE_TURN_DAY[$row['pay_mode']];
        $row['pay_mode_desc'] = OrderModel::PAY_TYPE_DESC[$row['pay_mode']];
        $row['type_desc'] = OrderModel::TYPE_DESC[$row['type']];
        $row['status_desc'] = OrderModel::STATUS_DESC[$row['status']];
        $row['category_desc'] = OrderModel::CATE_DESC[$row['category']];
        $row['admin_name'] = AdminUserModel::getFieldById($row['admin_id'],'uname');

        if (arrKeyIssetAndExist($row,"saler_id")){
            $saler = AdminUserModel::db()->getById($row['saler_id']);
            if($saler){
                $row['saler_name'] = $saler['uname'];
            }
        }else{
            $row['saler_name'] = "";
        }

        if(arrKeyIssetAndExist($row,"property_heat_type")){
            $row['property_heat_type_desc'] = OrderModel::Property_Heat_Type_Desc[$row['property_heat_type']];
        }

        if(arrKeyIssetAndExist($row,"time_cycle")){
            $row['time_cycle_desc'] = OrderModel::Time_Cycle_DESC[$row['time_cycle']];
        }

        //附件
        $contract_attachment_link = "";
        if($row['contract_attachment']){
            $url = get_contract_url($row['contract_attachment'] );
            $contract_attachment_link =  $url;
//                "<a href='$url' target='_blank'>".'<i class="fa fa-file-text"></i></a>';
        }
        $row['contract_attachment_link'] = $contract_attachment_link;

        return $row;
    }

	static function getStatusOptions(){
	    $html = "";
	    foreach (self::STATUS_DESC as $k=>$v) {
            $html .= "<option value={$k}>{$v}</option>";
	    }
        return $html;
    }

    static function getFinishTypeOptions(){
        $html = "";
        foreach (self::FINISH_DESC as $k=>$v) {
            $html .= "<option value={$k}>{$v}</option>";
        }
        return $html;
    }

    static function getFinanceDescOption(){
        $html = "";
        foreach (self::FINANCE_DESC as $k=>$v) {
            $html .= "<option value={$k}>{$v}</option>";
        }
        return $html;
    }

    static function getPayTypeOptions(){
        $html = "";
        foreach (self::PAY_TYPE_DESC as $k=>$v) {
            $html .= "<option value={$k}>{$v}</option>";
        }
        return $html;
    }

    static function getTypeOptions(){
        $html = "";
        foreach (self::TYPE_DESC as $k=>$v) {
            $html .= "<option value={$k}>{$v}</option>";
        }
        return $html;
    }

    static function getCateTypeOptions(){
        $html = "";
        foreach (self::CATE_DESC as $k=>$v) {
            $html .= "<option value={$k}>{$v}</option>";
        }
        return $html;
    }
    static function upStatus($oid,$status,$data = []){
	    $upData = array("status"=>$status,'u_time'=>time());
	    if ($data){
            $upData = array_merge($upData,$data);
        }
	    return self::db()->upById($oid,$upData);
    }

    static function getSomePayTypeDesc($payTypeIds ){
	    if(!is_array($payTypeIds)){
            $payTypeIds = explode(",",$payTypeIds);
        }

        $rs = array();
        foreach ($payTypeIds as $k=>$v) {
            $rs[$v] = self::PAY_TYPE_DESC[$v];
        }
        return $rs;
    }
    //获取一个房源的付款信息
    static function getPayListByStatus($oid,$status){
        return OrderPayListModel::db()->getAll(" status = $status and oid = $oid ");
    }

    //获取一个房源的付款信息 - 汇总金额
    static function totalPayListByTypeStatus($oid,$status){
        return OrderPayListModel::db()->getRow(" status = $status and oid = $oid ",null," sum(price) as total ");
    }

    static function getListByUid($uid){
        $list = self::db()->getAll(" uid = $uid");
        return $list;
    }

}