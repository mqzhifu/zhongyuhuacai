<?php

namespace Jy\Listener;

use Jy\Contract\Event\EventListenerInterface;
use Jy\Component\Event\EventParam;
use Jy\Common\RequestContext\RequestContext;
use Jy\Facade\Trace;

class InitAfter implements EventListenerInterface
{

    public function handle(EventParam $p)
    {
        // tracing
        Trace::setServiceReceiveTrace();

        // 上下文数据的初始化
        static::initRequestContext();
    }

    public static function initRequestContext()
    {

        if (!RequestContext::isInSwooleCoroutine()) {
            // data pre deal
            RequestContext::put('request_user_data', \Jy\App::$app->request->getUserData());
            RequestContext::put('request_data', \Jy\App::$app->request->getRequestParams());
            RequestContext::put('sys_data', \Jy\App::$app->request->getRequestSysParams());
            RequestContext::put('request_trace_cs_data', \Jy\App::$app->request->getRequestTraceParams());
            RequestContext::put('request_header_data', \Jy\App::$app->request->getRequestHeaderParams());

            //unset($_GET);
            //unset($_POST);
            //unset($_REQUEST);
        }

        // 初始化所有场景的$_SERVER变量
    }


}
