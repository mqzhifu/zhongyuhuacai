<?php

/**
 * Class bank
 * 用户银行卡信息
 */
class bankCtrl extends BaseCtrl
{

    public function editBank() {
        try{
            $bankParam = $_POST;
            if(empty($bankParam) || in_array('', $bankParam)) {
                $errorCode = '400';
                throw new exception("param is empty");
            }

            $bankParam = array_merge($bankParam, ['create_time' => time(),
                                                  'last_update_time' => time(),
                                                  'user_id' => $this->_uid]);

            if(isset($bankParam['do_type']) && $bankParam['do_type'] == 1) {    //add
                $res = bankModel::db()->add($bankParam);
            } elseif(isset($bankParam['do_type']) && $bankParam['do_type'] == 2 && isset($bankParam['bank_id'])) {  //update
                $res = bankModel::db()->upById($bankParam['bank_id'], $bankParam);
            } else {
                $errorCode = '401';
                throw new exception("missing param");
            }

            if($res === false) {
                $errorCode = '500';
                throw new exception("edit error");
            }

            $this->dataOut();
        } catch (Exception $e){
            $this->dataOut('', $errorCode, $e->getMessage());
        }

    }

    public function setDefault() {
        try{
            if(!isset($_POST["id"]) || empty(intval($_POST["id"]))) {
                $errorCode = '400';
                throw new exception("param is empty");
            }
            $bankId = $_POST["id"];

            $updateInfo = ['is_default' => 2];
            $updateWhere = ['user_id' => $this->_uid];

            $res = bankModel::db()->update($updateInfo, $updateWhere);

            if($res === false) {
                $errorCode = '500';
                throw new exception("update error");
            }

            $updateInfo = ['is_default' => 1, 'last_update_time' => time()];
            $updateWhere = ['id' => $bankId];

            $defaultRes = bankModel::db()->update($updateInfo, $updateWhere);

            if($defaultRes === false) {
                $errorCode = '501';
                throw new exception("update error");
            }

            $this->dataOut();
        } catch (Exception $e){
            $this->dataOut('', $errorCode, $e->getMessage());
        }
    }

}