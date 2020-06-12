<?php

namespace Jy\Contract\Trace;


interface TraceInterface
{
	public static function getInitTrace();

    public static function setClientSendTrace(string $to, array $data);
    public static function setClientReceiveTrace(array $traceData);
    public static function setServiceSendTrace(array $data);
    public static function setServiceReceiveTrace();

    public static function getClientSendTrace():array;
    public static function getClientReceiveTrace():array;
    public static function getServiceSendTrace():array;
    public static function getServiceReceiveTrace():array;
}
