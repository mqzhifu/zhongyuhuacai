<?php

namespace Jy\Contract\Trace;

use Jy\Contract\Trace\TraceInterface;
use Jy\Common\RequestContext\RequestContext;
use Jy\Facade\Config;
use Jy\Facade\Redis;
use Jy\Common\Helpers\MachineHelper;

abstract class TraceAbstract implements TraceInterface
{


	// write log 。。。。

	public static function getInitTrace()
	{
		return [
			'traceid' 	=> RequestContext::getUniqKey(),
			'spanid'  	=> 0,
			'parentid'  => 0,
			'timestamp' => 0,

			'cs'		=> 0,
			'cr'		=> 0,
			'sr'		=> 0,
			'ss'		=> 0,

			'caller'	=> getJyAppName(),
			'callee'	=> "",
			'params'	=> "",
			'result'	=> '',
			'ip'		=> MachineHelper::getLocalIp(),
		];
	}

	public static function increProcessMax(array $traceData, $type = "process")
	{
		$ret = Redis::incr("trace:{$type}:".$traceData['traceid']);

		// expire
		Redis::expire("trace:{$type}:".$traceData['traceid'], 3600);

		return $ret;
	}

	// cs
    public static function setClientSendTrace(string $to, array $data)
    {
		$traceData = RequestContext::get('trace_cs_data');
		if (empty($traceData)) $traceData = RequestContext::get('trace_sr_data');
    	if (empty($traceData)) $traceData = static::getInitTrace();

		$traceData['cs'] = static::increProcessMax((array)$traceData);
		$traceData['spanid'] = static::increProcessMax((array)$traceData, "node");

		if (!RequestContext::has('trace_cs_data')) {
			$traceData['parentid'] = static::increProcessMax((array)$traceData, "level");
		}

		$traceData['timestamp'] = microtime(true);
		$traceData['caller'] = getJyAppName();
		$traceData['callee'] = $to;
		$traceData['params'] = $data;
		$traceData['result'] = '';
		$traceData['ip'] = MachineHelper::getLocalIp();

		RequestContext::put('trace_cs_data', $traceData);

		return $traceData;
    }

    // cr
    public static function setClientReceiveTrace(array $traceData)
    {
		$traceData['cr'] = static::increProcessMax((array)$traceData);
		$traceData['timestamp'] = microtime(true);
		$traceData['ip'] = MachineHelper::getLocalIp();

		RequestContext::put('trace_cr_data', $traceData);

		return $traceData;
    }

    // ss
    public static function setServiceSendTrace(array $data)
    {
    	$traceData = RequestContext::get('trace_sr_data');
		if (empty($traceData)) return [];

		$traceData['ss'] = static::increProcessMax((array)$traceData);
		$traceData['timestamp'] = microtime(true);
		$caller = $traceData['caller'];
		$callee = $traceData['callee'];
		$traceData['callee'] = $caller;
		$traceData['caller']  = $callee;
		$traceData['result']  = $data;

		RequestContext::put('trace_ss_data', $traceData);

		return $traceData;
    }

    // sr
    public static function setServiceReceiveTrace()
    {
    	$traceData = RequestContext::get('request_trace_cs_data');
		if (empty($traceData)) $traceData = static::getInitTrace();

		$traceData['sr'] = static::increProcessMax((array)$traceData);
		$traceData['timestamp'] = microtime(true);
		$traceData['params'] = RequestContext::get('request_data');
		$traceData['ip'] = MachineHelper::getLocalIp();
		$traceData['caller'] = getJyAppName();

		RequestContext::put('trace_sr_data', $traceData);

		return $traceData;
    }

    public static function getClientSendTrace():array
    {
    	return RequestContext::get('trace_cs_data', []); // currency
    }
    public static function getClientReceiveTrace():array
    {
    	return RequestContext::get('trace_cr_data', []); // currency
    }

    public static function getServiceSendTrace():array
    {
    	return RequestContext::get('trace_ss_data', []);
    }
    public static function getServiceReceiveTrace():array
    {
    	return RequestContext::get('trace_sr_data', []);
    }
}
