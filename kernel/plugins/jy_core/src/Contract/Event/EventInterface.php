<?php

namespace Jy\Contract\Event;

use Jy\Componet\Event\EventParam;

interface EventInterface
{
    /**
     * 事件监听
     * @param string $name 事件名称
     * @param mixed $callback 回调
     * @param int $priority 优先级，越大越先执行
     * @return void
     */
    public function on($name, $callback, $priority = 0);

    /**
     * 取消事件监听
     * @param string $name 事件名称
     * @param mixed $callback 回调
     * @return void
     */
    public function off($name, $callback);

    /**
     * 触发事件
     * @param string $name 事件名称
     * @param array $data 数据
     * @param mixed $target 目标对象
     * @param string $paramClass 参数类
     * @return void
     */
    public function trigger($name, $data = [], $target = null, $paramClass = EventParam::class);

}
