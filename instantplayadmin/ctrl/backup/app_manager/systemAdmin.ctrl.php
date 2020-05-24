<?php
/**
 * Created by PhpStorm.
 * User: xiahongbo
 * Date: 2019/3/18
 * Time: 18:50
 */

/**
 * Class systemAdminCtrl
 */
class systemAdminCtrl extends BaseCtrl{
    function upPs(){
        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');
        $this->addHookJS("system/upps_hook.html");
        $this->display("app_manager/systemAdmin/upps.html");
    }

    public function upPsedit(){
            $old_ps = _g("old_ps");
            if(!$old_ps){
                exit("原密码不能为空");
            }

            $ps = _g("ps");
            if(!$ps){
                exit("新密码不能为空");
            }

            $ps_sure = _g("ps_sure");
            if(!$ps_sure){
                exit("确认密码不能为空");
            }

            if($ps_sure != $ps){
                exit("两次密码不一致");
            }

            if(strlen($ps)<6)
                exit("新密码至少6个字符");

            $uid = $this->_sess->getValue('id');

            $user = AdminUserModel::db()->getRow(" id = $uid");
            if($user['ps'] != md5($old_ps) ){
                exit('原始密码错误');
            }
            AdminUserModel::db()->update(array('ps'=>md5($ps) )," id = $uid limit 1 ");
            $this->_sess->none();
            echo "<script>alert('新密码设置成功，请您重新登陆');location.href='/';</script>";
    }
}
