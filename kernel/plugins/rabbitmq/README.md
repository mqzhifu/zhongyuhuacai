准备工作
=  
php 依赖库
- 
1.composer 模式，"php-amqplib/php-amqplib"  
2.扩展模式，rabbitmq-c、amqp  

服务器 依赖
-
1.需要安装erlang  
2.rabbitmq-server  
 rabbitmq-server 依赖插件：rabbitmq_management、rabbitmq_delayed_message_exchange  

端口号：  
-
15672:rabbitmq web 可视化管理工具  
5672:PHP连接rabbitmq server  

名词说明  
=
生产者：生产消息，并发送给server  
消费者：接收<生产者>发送的消息，消费掉该消息  


DEMO
==

先定义ProductSms一个生产类，只需要继承一个基类(MessageQueue)即可
（ProductSms 类名就是 最后在rabbitmq创建的队列名。
```java
<?php
namespace php_base\MsgQueue\Test\Product;
use php_base\MsgQueue\MsgQueue\MessageQueue;

class SmsBean extends MessageQueue{
    public $_id = 1;
    public $_type = "";
    public $_msg = "";

    function __construct($conf = null,$provinder = 'rabbitmq',$debug = 0){
        parent::__construct($provinder,$conf,$debug);
    }

}

```
类中的成员变量，就是消息体中的键值   
接着，实例化类，定义消息体内，每个键对应的值

```java
$ProductSmsBean = new ProductSmsBean();
$ProductSmsBean->_id = 1;
$ProductSmsBean->_msg = "is finish.";
$ProductSmsBean->_type = "order";
```

这样一条消息，即初始化完成，接着，准备发送

#发送一条普通的消息
```javascript
$ProductSmsBean->send($ProductSmsBean);
```

#发送一条延迟的消息,3秒后发送
  ```javascript
 $ProductSmsBean->sendDelay(3000);
```

消息发送之后，想要知道，该消息是否被rabbitmq-server成功接受，是需要定义callback函数的  
（注：因为rabbitmq-server 是全异步网络模式，同步是无法获取发送结果的，只能注册callback）  

```java

    $callback = function($msg){
        out ("im simple ack callback");
    };
    $SmsBean->regUserCallbackAck($callback);
    $msgId = $SmsBean->send();
```

异常机制
=
因为，rabbitmq 是纯异步网络模式，同步是捕捉不到异步的。  
比如：发送一条消息，同步状态下，返回的永久是真。所以，异常的触发机制，依然是异步的。  
那就，需要类库来做 异常回调函数注册。一但有异常，类库会抛出。  

特殊异常情况  
-
当用户发送一条消息到server，rabbitmq 路由不到队列中（实际就是该队列不存在），类库给直接捕捉了，不报异常  
如果想报出来，需要 设置标识  

```java
$SmsBean->setIgnoreSendNoQueueReceive(false);
```

延迟消息
-
是用的rabbitmq 官方的插件  
机制是，当一条延迟消息过来后，插件会捕捉，并存于插件DB(mnesia)中  
然后由定时器维护一但到了时间，再把访消息发送到rabbitmq
优点：简单，效率高  
缺点： 
>一但rabbitmq server重启，插件也一并跟着重启，延迟消息将会丢失   
>因为是插件先捕获消息，并不做校验（该消息是否有队列接收），一但，队列不存在，该消息会丢失  
>基于上面，每发一条延迟消息，插件会报出一个错误，类库会帮助忽略掉  

消息体说明
==
正常发送一条消息到rabbitmq ，包括3个部分
>body:消息正文  
>arguments:描述消息的属性值  
>header:包含在arguments的header-Key中， 是对attr的进一步描述。  



消息arguments参数
-

参数名  | 说明  |
 ---- | ----- |
 expiration | 失效时间 |
content_type | MIME类型  | 
content_encoding | 传输格式 | 
Priority | 权限值 | 
correlation_id | 相互关联ID | 
application_headers | 头信息 | 
message_id | 扩展字段 | 
Timestamp | 时间 | 
Type | 扩展字段 | 
user_id | 扩展字段 | 
app_id | 扩展字段 | 
cluster_id | 扩展字段 | 
reply_to | 消息被发送者处理完后,返回回复时执行的回调(在rpc时会用到) | 

>消息参数除了日常的，还有很多扩展字段可以用上。比如：message_id用于可靠性。type:可以用做分类给consumer用.<br/>
>其中 message_id type Timestamp 基类已占用 ，send的时候，由基类自动生成 <br/>

消息参数的使用
=
使用者，基本上用不到，类库均已封闭好  
正常发送一条消息，类库会加上：

参数名  | 说明  |
 ---- | ----- |
content_type|1application/serialize 2application/json|
type|1confirm 2tx 3normal|
message_id|uniqid(time())|
timestamp|time()|
delivery_mode|2|


消费者
=

快速开启consumer 监听某一个事件队列  
```java

$SmsBean = new SmsBean();
$callback = function($msg){
    echo "im cousmer callback :processing...";
};

$SmsBean->groupSubscribe($callback,'consumer1');

```
非常简单，只要找到生产者定义的bean类，拿过来，直接实例化  
定义一个callback处理消息的回调函数  
最后groupSubscribe 开启守护模式监听  

上面的关键点：consumer1 ，这是个标识。该标识作用：以该标识名为队列名，如果不存在 就会新建一个队列，接收product消息  


队列的维护
-
比如：创建、删除、绑定路由等，这些基操作 已由基类帮你完成  
业务人员其实不用关心 exchange 名，队列名，类库都已经帮你完成了  
只需要关注   tag值  


队列名的组成
-
bean类别名+tag名
比如：ProductSmsBean 类，最后生成的队列中即是：ProductSmsBean+tag  
tag:是由 消费者，启动守护进程时，赋值的  

特殊情况
-
如果两个进程，使用同一个bean类，使用相同的tagName，会怎样？  
就是两个守护进程，同时消费一个队列而以。  


以上是最简单最简单的一个DEMO，开启一个消费者守护进程，消费一个队列中的消息  
下面讲一些复杂的  

消费者-确认模式-多个consumer 监听
-

```javascript
class ConsumerManyBean extends  MessageQueue{
    function __construct($conf = "")
    {
        parent::__construct("rabbitmq", $conf, 3);

        $PaymentBean = new PaymentBean(null,null,3);
        $OrderBean = new OrderBean(null,null,3);
        $UserBean = new UserBean(null,null,3);
        $SmsBean = new SmsBean(null,null,3);

        $this->regListenerBean(array($OrderBean,$PaymentBean,$UserBean,$SmsBean));

    }
    //拒绝一条消息
    function handlePaymentBean($msg){
        echo "im handlePaymentBean , ConsumerManyBean\n";
        throw new RejectMsgException();
    }
    //回滚，重试
    function handleOrderBean($msg){
        echo "im handleOrderBean , ConsumerManyBean \n";
        throw new RetryException();
    }
    //运行时异常
    function handleUserBean($msg){
        echo "im handleUserBean\n , ConsumerManyBean";
        throw new \Exception();
    }
    //
    function handleSmsBean($msg){
        echo "im handleSmsBean , ConsumerManyBean \n";
        $RetryException=new RetryException();
        $retry = array(2,6);
        $RetryException->setRetry($retry);
        throw $RetryException;
    }
}
```

上面就是定义了4种类型的消息bean:PaymentBean OrderBean UserBean SmsBean  
然后，把4种消息类型添加 到监听中：regListenerBean  
最后，定义4个handle函数，处理callback  
这种模式，其实也只是开一个队列，但监听了4种类型的消息，如果有人发消息是这4种之一  
该队列，就会收到一条消息，然后，根据消息类型，最后分发到相应的handle中  


消息retry机制
-



#编程模式
>rabbitmq 是基于erlang模式，全并发模式(channel)。也就是全异步模式(基类里我规避了这种方式，但牺牲部分性能)<br/>
>大部分你的操作，比如：send 实际上并不是以同步方式拿到mq返回的值。<br/>
>很多业务都是基于callback function 方式，所以使用时请注意下.<br/>


#消息可靠性
>业务人员在投递/消费时，最好借助三方软件，如：redis|mysql ，持久化该消息状态。避免丢消息或重复消费，也方便跟踪<br/>
>理论上：事务模式更靠谱，但是跟确认模式差了10倍左右（官方给的是100倍左右）。<br/>
>建议：对一致性可靠性要求比较高的业务，如：订单业务考虑事务。次级重要的用确认模式，不是太重要的可以正常发送即可。<br/>
>注：事务模式与确认互斥

#简单压侧效果
>win7 PHP单进程 循环给rebbitmq发消息<br/>
>普通模式： 10000条，时间：0.32-0.4     100000条：4.1-3.59 . 官方说每秒10万条，可能LINUX下更快<br/>
>确认模式： 10000条，时间：0.42-0.5    100000条：5<br/>
>事务模式： 10000条，时间：2.4-2.7    100000条：好吧，我放弃了。。。超时状态<br/>

#重试机制
>当一条消息处理过程中发生异常，会被重新发送到队列，以阶梯的形式，可再次读取<br/>
>如：第一次发生异常该方向会重新回到到队列中但是会在5秒之后才出现，第2次是10秒，第3次是30....   <br/>
>具体阶梯的时间可设置，具体重试次数可设置。能很好的防止网络抖动或者LINUX假死<br/>

#各种配置注意：
>正常新开一个队列，系统默认最大值为10W条，超出即会丢掉（如配置死信队列会进到死信队列中）<br/>
>正常新建队列不建议使用定义化参数，比如：开启自动ACK、autoDel(自动删除)、<br/>
>正常新建队列最好都设置持久化属性，投递消息也是。至少MQ挂了重启，还可以找回数据<br/>
>设置消息的TTL时，尽量要考虑一但消息堆积过多，还没处理过，部分数据就失效且丢失了<br/>
>编写consumer一定要做好异常捕获，不然进程一但挂了，消息可是无止境堆积。<br/>

#追踪
>mq不提供太多可追踪的工具，可以使用后台管理系统。但做不到100%<br/>
>建议，业务方，最好把发送的一些消息在自己业务上做持久化。<br/>

#集群
>目前暂时没有集群，到达量后，会开启镜像模式集群，防止单点故障<br/>
