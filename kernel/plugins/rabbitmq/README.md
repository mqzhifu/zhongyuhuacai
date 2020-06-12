#rabbitMq 队列<br/>
#注意：依赖 "php-amqplib/php-amqplib":<br/>
#具体可参考TEST目录下的client.php server.php

#先定义ProductSms一个生产类，只需要继承一个基类即可。ProductSms 该类名即是 绑定队列的名，也就是接收消息的队列名
```java
$productSms = new ProductSms();
```
##再定义一个通信协议类
```java
$ProductSmsBean = new ProductSmsBean();
```

#初始化要发送的数据
```javascript
$ProductSmsBean->_id = 1;
$ProductSmsBean->_msg = "is finish.";
$ProductSmsBean->_type = "order";
```

#发送一条普通的消息
```javascript
$productSms->send($ProductSmsBean);
```

#消息参数

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

#发送一条延迟5秒的消息
```java
$arguments = array("expiration"=>5000);
$productSms->send($ProductSmsBean,$arguments);
```

#消费者<br/>
#快速开启consumer 监听某一个事件队列
```java

$productSms = new ProductSms();
$userCallback = function ($recall){
    echo "im in user callback func \n";
};
$productSms->groupSubscribe($userCallback,"dept_A");

```
>上面的关键点：dept_A ，这是个标识，如果以该标识为队列名，且存在~OK。如不存在 就会新建一个队列，接收product消息<br/>
>也就是说：创建队列/选择队列 ，绑定队列 这些基操作 已由基类帮你完成。<br/>
>业务人员可随意使用，随意创建<br/>

#消费者-确认模式-多个consumer 监听
```javascript
class ConsumerSms extends MessageQueue{
    function __construct()
    {
        parent::__construct();
    }

    function init(){
        $queueName = "test.header.delay.sms";

        $this->setBasicQos(1);
//        $durable = true;$autoDel = false;
//        $this->createQueue();

        $ProductSmsBean = new ProductSmsBean();
        $handleSmsBean = array($this,'handleSmsBean');
        $this->setListenerBean($ProductSmsBean->getBeanName(),$handleSmsBean);

        $ProductUserBean = new ProductUserBean();
        $handleUserBean = array($this,'handleUserBean');
        $this->setListenerBean($ProductUserBean->getBeanName(),$handleUserBean);

        $this->subscribe($queueName,null);
    }

    function handleSmsBean($data){
        echo "im sms bean handle \n ";
        return array("return"=>"ack");
    }

    function handleUserBean($data){
        echo "im user bean handle \n ";
        return array("return"=>"reject",'requeue'=>false);
    }
}
```

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
