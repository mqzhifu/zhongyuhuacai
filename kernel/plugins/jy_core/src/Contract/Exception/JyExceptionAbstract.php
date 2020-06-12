<?php

namespace Jy\Contract\Exception;

use Jy\Contract\Exception\JyExceptionInterface;

abstract class JyExceptionAbstract implements JyExceptionInterface 
{
	// 不需要上报的exception
	protected $dontReport = [
		// namespace::class
	];

	/**
	 * 用户自动以接口抽象
	 * @Author   jingzhiheng
	 * @DateTime 2020-02-03T15:15:48+0800
	 * @param    [obj]    $exception 
	 * @return   [array]  [$code, $message, $data]  type : [int, string, array] 用户自定义返回值，用于覆盖系统自带的返回值
	 */
    abstract public function handle($exception):array;

    abstract public function report($exception);

    final public function deal(\Throwable $e):array
    {
    	$ret = $this->handle($e);

    	if (!$this->shouldntReport($e)) {
    		$this->report($e);
    	}

    	return $ret;
    }

    final protected function shouldntReport(\Throwable $e)
    {
        foreach ($this->dontReport as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }

        return false;
    }
}
