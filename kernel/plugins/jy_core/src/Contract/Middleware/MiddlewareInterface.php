<?php

namespace Jy\Contract\Middleware;


interface MiddlewareInterface
{
    /**
     * 事件处理方法
     * @param EventParam $e
     * @return void
     */
    public function handle();
}
