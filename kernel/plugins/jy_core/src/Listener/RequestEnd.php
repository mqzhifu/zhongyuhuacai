<?php

namespace Jy\Listener;

use Jy\Contract\Event\EventListenerInterface;
use Jy\Component\Event\EventParam;
use Jy\Common\RequestContext\RequestContext;
use Jy\Facade\Trace;
use Jy\Facade\Log;

class RequestEnd implements EventListenerInterface
{

    public function handle(EventParam $p)
    {

        RequestContext::put('sys_data.error_code', is_object($p->getData()) ? $p->getData()->getCode() : 200);
        RequestContext::put('sys_data.duration', microtime(true) - RequestContext::get('sys_data.start_time', microtime(true)));

        Trace::setServiceSendTrace(is_object($p->getData()) ? $p->getData()->getData() : ['none']);

        // tracing
        Log::info(Trace::getServiceReceiveTrace());
        Log::info(Trace::getServiceSendTrace());
        Log::info(Trace::getClientSendTrace());
        Log::info(Trace::getClientReceiveTrace());

        Log::buffFlushFile();

        RequestContext::destroy();

    }
}
