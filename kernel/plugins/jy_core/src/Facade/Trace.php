<?php

namespace Jy\Facade;

use Jy\Contract\Trace\TraceAbstract;


/**
 * @method static array setClientSendTrace(string $to, array $data)
 * @method static array setClientReceiveTrace(array $traceData)
 * @method static array setServiceSendTrace(array $data)
 * @method static array setServiceReceiveTrace()
 * @method static array getClientSendTrace()
 * @method static array getClientReceiveTrace()
 * @method static array getServiceSendTrace()
 * @method static array getServiceReceiveTrace()
 */
class Trace extends TraceAbstract
{
	//..
}