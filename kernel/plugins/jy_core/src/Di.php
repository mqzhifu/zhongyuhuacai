<?php
namespace Jy;

use Jy\Facade\Log;

class Di{
//    private $_traceInfo = "";
//    private $_debug = 2;

    //注解 类型，暂不使用。但建议注解的时候加上，方便以后处理
    private $_commentAutoWiredTypeKeyword = array("service",'model','controller','components');
    //注解 前缀
    private $_commentAutoWiredKeywordStart = "@AutoWired";
    //注解 后缀
    private $_commentAutoWiredKeywordEnd = "\n";

    private $_trace = null;
    private $_execCnt = 0;
    private $_execCntMax = 20;

    private $_getInstanceFuncName = "getInstance";

    function __construct(){

    }

    static function getInstance(){
        return new self();
    }

//    function setDebug($level){
//        $this->_debug = $level;
//    }
//
//    private function out($info){
//        if($this->_debug ){
//            $info .= "\n";
//            $this->_traceInfo .= $info;
//            if($this->_debug == 2)
//                echo $info;
//        }
//    }

    private function setFather($father){
        if($father){
            $this->_trace[] = $father;
        }
    }
    //检查：是否有 循环嵌套注入
    private function checkLoop($className){
        if(!$this->_trace){
            return 1;
        }

        foreach ($this->_trace as $k=>$v) {
            if($v == $className){
                $this->throwException(500);
            }
        }
    }

    private $_codeErrMessage = array(
        400=>'code is null',
        401=>'code not is key',
        500=>"loop create class",
        501=>"实例类最多层级为：{0}",
        502=>"class not exists:{0}",
        503=>'{0} 类不可实例化',
    );

    function throwException($code,$replace = ""){
        if(!$code){
            throw new \Exception($this->_codeErrMessage[400]);
        }

        if(!isset($this->_codeErrMessage[$code]) || !$this->_codeErrMessage[$code]){
            throw new \Exception($this->_codeErrMessage[401]);
        }
        if(!$replace){
            throw new \Exception($this->_codeErrMessage[$code]);
        }else{
            $message = $this->_codeErrMessage[$code];
            foreach ($replace as $key => $v) {
                $message = str_replace("{" . $key ."}",$v,$message);
            }

            throw new \Exception($message);
        }
    }
    //类里的 成员变量 引用了 其它类，需要注入
    function initMember($relClass,$instant,$className){
        //找 成员变量中 是否有 注解
        $paraComment = $this->getMemberComment($relClass);
        if($paraComment){
            $paraInstantList = null;
            foreach ($paraComment as $k=>$v) {
                $desc = explode(" ",$v);
                $newInstant  = $this->getClassInstance($desc[1],$className);
                $setFunc = $k;
                $instant->$setFunc = $newInstant;
            }
            return $instant;
        }
        return $instant;
    }
    //类里的函数的注解
    function initMethod($className,$methodName){
        $relClass = new \ReflectionClass($className);
        $method = $this->getClassMethods($relClass, $methodName);
        if(!$method){
            return null;
        }

        $dependencies = $method->getParameters();
        if(!$dependencies){
            return null;
        }

        foreach ($dependencies as $k=>$v) {
            if($v->hasType()){
                $paraClass = $v->getType();
                $paraClassName = $paraClass->getName();
            }else{
                $paraClassName = $v->getName();
            }
            if(!class_exists($className)){
                $this->throwException(502,array($paraClassName));
            }
            $dependInstantClass[$v->getPosition()] = $this->getClassInstance($paraClassName,$className);
        }

        return $dependInstantClass;
    }

    function getClassMethods($refClass,$methodName){
        $methods = $refClass->getMethods();
        foreach ($methods as $k=>$v) {
            if($v->getName() == $methodName ){
                return $v;
            }
        }

        return 0;
    }
    //入口，获取一个类的实例化
    function getClassInstance($className,$father = null){
        if(!class_exists($className)){
            $this->throwException(502,array($className));
        }

        if($this->_execCnt > $this->_execCntMax){
            $this->throwException(501,array($this->_execCntMax));
        }

        $this->checkLoop($className);
        if(!$father){
            $this->_trace = null;
        }
        $this->setFather($className);

        //获取反射类
        $relClass = new \ReflectionClass($className);
        // 查看是否可以实例化
        $isInstantiable = $relClass->isInstantiable();
        if (!$isInstantiable ) {
            $method = $this->getClassMethods($relClass, $this->_getInstanceFuncName);
            if(!$method)
                $this->throwException(503,array($className));
        }
        // 查看是否有：构造函数
        $constructorMethod = $relClass->getConstructor();
        // 没有构造函数
        if (is_null($constructorMethod)) {
            $instant =  $this->instance($className,null,$isInstantiable);
            $finalInstant = $this->initMember($relClass,$instant,$className);
            return $finalInstant;
        }

        return $this->instanceByMethod($constructorMethod,$isInstantiable,$relClass,$className);

    }
    function instanceByMethod($method,$isInstantiable,$relClass,$className){
        //获取构造函数的-请求参数
        $dependencies = $method->getParameters();
        if(!$dependencies){
            //构造函数没有任何参数，不需要注入
            $instant =  $this->instance($className,null,$isInstantiable);
            $finalInstant = $this->initMember($relClass,$instant,$className);
            return $finalInstant;
        }
        //获取构造函数-注解
//        $constructorMethodComment = $constructorMethod->getDocComment();
//        if($constructorMethodComment){
//            $constructorMethodCommentContent = $this->filterCommentContent($constructorMethodComment);
//        }

        $dependInstantClass = [];
        foreach ($dependencies as $k=>$v) {
            if($v->hasType()){
                $paraClass = $v->getType();
                $paraClassName = $paraClass->getName();
            }else{
                $paraClassName = $v->getName();
            }
//            $paraClassName =  ucfirst($v->getName());
            if(!class_exists($className)){
                $this->throwException(502,array($paraClassName));
            }
            $dependInstantClass[$v->getPosition()] = $this->getClassInstance($paraClassName,$className);
        }
        $instant = $this->instance($className,$dependInstantClass,$isInstantiable);
        $finalInstant = $this->initMember($relClass,$instant,$className);
        return $finalInstant;
    }

    //实例化一个类
    private function instance($className,$paraInstantList = null,$isInstantiable = 1){
        if($isInstantiable){
            if($paraInstantList){
                $instance = new $className(...$paraInstantList);
            }else{
                $instance = new $className();
            }
        }else{
            if($paraInstantList){
                $instance = call_user_func_array(array($className,$this->_getInstanceFuncName),$paraInstantList);
            }else{
                $instance = call_user_func_array(array($className,$this->_getInstanceFuncName),array());
            }
        }

        $this->_execCnt++;
        return $instance;
    }
    //过滤注释内容
    private function filterCommentContent($content){
        $grep = "/{$this->_commentAutoWiredKeywordStart}(.*){$this->_commentAutoWiredKeywordEnd}/";
        preg_match($grep,$content, $matches);
        if($matches && is_array($matches) && $matches[1]){
            return trim($matches[1]);
        }
    }
    //获取函数的参数的定义类型
    function getParametersType($rel_method){
        $list = [];
        $dependencies = $rel_method->getParameters();
        foreach ($dependencies as $k=>$v) {
            if($v->hasType()){//证明该参数 定义了 类型
                $class = $v->getType();
                $list[$v->getName()] = array($class->getName());
            }
        }
    }
    //获取函数的全部成员变量的注解信息
    function getMemberComment($rel_class){
        $list = null;
        $properties = $rel_class->getProperties();
        foreach ($properties as $property) {
            $content = $property->getDocComment();
            if(!$content){
                continue;
            }
            $list[$property->getName()] = $this->filterCommentContent($content);
        }
        return $list;
    }
}