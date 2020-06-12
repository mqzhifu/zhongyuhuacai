依赖注入说明 

有3种方式可以添加依赖注入

1注解-成员变量
>在类中，任意位置定义一个成员变量，如下：
```java
class{
    ...
    /**
    * @AutoWired \Rouchi\Jy\User service
    */
    private $_user ;
    ...
}
```
>@AutoWired:固定描述符<br/>
\Rouchi\Jy\User:需要依赖的<类>名。注：也可不写命名空间，在上方用use \Rouchi\Jy\User 引入，如都不写，默认在当前空间下寻找<br/>
service:该依赖的类 的类型，暂包括. service model controller  components resource .可忽略，后期使用。<br/>

注：注解的描述符以   /** */     关键字包含，非   /** */ 将不会解析

2构造函数，如下：
```java
class Test{
    function __construct(\Rouchi\Jy\User user){
    
    }
}
```

只需在构造函数的参数内写上  类名 变量名 即可。

注：
>不写变量名的限定类型也可以，那就以当前参数名做为类型。 
限定参数类型也可不写命名空间路径，具体引用同方法1（注解模式）。


3 action方法，如下：
```java
class Test{

    function 任意方法名(\Rouchi\Jy\User user){
        
        }
}

```
>具体规则，同2.唯一区别：是写在类中的方法，且是在初始化 controller类之后，再次获取依赖类

3类依赖注入的加载类顺序：1构造函数-》成员方法-》action方法


注：
>1、勿循环嵌套依赖类，程序会自动检查。<br/>
2、构造函数如果加了private，是不可实例化的。也就是，如果使用方法2，无法进行依赖注入<br/>
3、如果类中包含getInstance方法，构造 函数可以加上private<br/>
4、单次一个类实例化，依赖类，最多为20个(可自定义)，否则抛异常<br/>


