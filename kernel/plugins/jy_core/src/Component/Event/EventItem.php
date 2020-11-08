<?php

namespace Jy\Component\Event;


class EventItem
{
    /**
     * 事件回调
     * 或者事件类名
     */
    public $callback;

    /**
     * 优先级
     * 越大越先执行
     */
    public $priority;

    public function __construct($callback, int $priority = 0)
    {
        $this->callback = $callback;
        $this->priority = $priority;
    }

}
