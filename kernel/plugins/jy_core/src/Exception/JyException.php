<?php

namespace Jy\Exception;

use Jy\Facade\Log;
use Jy\Facade\Config;
use Jy\Common\RequestContext\RequestContext;
use Jy\Contract\Exception\JyExceptionInterface;
use Jy\Facade\Trace;

class JyException extends \ErrorException
{

    public function __construct($param)
    {
        try {

            $ret = [];
            $handle = Config::get('@app.Exception.Handle');

            if (!empty($handle) && class_exists($handle)
                && ( ( $handleObj = new $handle ) instanceof JyExceptionInterface) ) {
                $ret = call_user_func([$handleObj, 'deal'], $param['e']);
            }

            $fromType = $param['from'] ?? 'sys';
            unset($param['from']);

            $result = \Jy\App::$app->response->json($ret['data'] ?? (isDebug() ? $param : []),$ret['code'] ?? $param['type'],$ret['message'] ?? $param['message']);
            echo $result;
        } catch (\Throwable $e) {

            $result =  \Jy\App::$app->response->json(
                (isDebug() ? [
                        'trace' => $e->getTrace(),
                        'line' => $e->getLine(),
                    ] : []
                ),
                $e->getCode(),
                $e->getMessage()
            );

            echo $result;
        }

        Log::error($this->getLogContent());

        \Jy\Event::trigger('JY.REQUEST.END', $result);

        exit();
    }

    // log
    protected function getLogContent()
    {
        $logContent = "";

        ob_start();
        debug_print_backtrace();
        $content = ob_get_clean();

        if( strpos( PHP_OS ,"WIN" ) !== false){
            $logContent = "";
            $a = explode(",",$content);
            foreach ($a as $k=>$v) {
                $logContent .= $v .PHP_EOL;
            }
        }else{
            $contetArr = explode("\n", $content);
            array_shift($contetArr);
            $logContent = implode("\n", $contetArr);
        }

        return $logContent;
    }

}
