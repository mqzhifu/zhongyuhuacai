<?php

namespace Jy\Contract\Event;

use Jy\Component\Event\EventParam;

interface EventListenerInterface
{
    /**
     * 事件处理方法
     * @param EventParam $e
     * @return void
     */
    public function handle(EventParam $p);
}
