#数据验证<br/>
#包括：格式验证与类型验证两部分<br/>

#rule验证规则定义(json格式)<br/>
#<br/>
#参数名 : [验证类型1|验证类2|验证类型3...]<br/>

#验证类型：标量与复杂<br/>
#标量包括：整形、布尔、字符串等。还可以验证：长度、范围、邮箱等。<br/>
#复杂包括：数组（多维，递归），对象。<br/>
#具体支持类型，可参考Jy\Common\Valid\Valid\filter<br/>

#demo：参数名为 cnt ，规则为：整形   不能大于10   数字范围为2-15<br/>
```java
"cnt": "int|numberMax:10|numberRange:2,15"
```
#定义 KEY为数值 的数组<br/>
```java
   {"int|require"}
```

#定义 KEY为字符串(hashTable) 的数组<br/>
```java
	{"title":{"int|require"},"id"{{"int|require"}}}
```

```java
$rule = '{
{
	"cnt": "int|numberMax:10|numberRange:2,15",
	"name": "require|string|lengthMin:10",
	"price": "require|float",
	"myOb": "require|object",
	"email": "email|lengthRange:10,20",
	"dataArrOneNumRequire": ["int|require"],
	"dataArrOneNum": ["int"],
	"dataArrTwoNum": [
		["int"]
	],
	"dataArrTwoNumRequire": [
		["require|int"]
	],
	"dataArrThreeNum": [
		[
			["int"]
		]
	],
	"dataArrOneStr": {
		"title": "string",
		"id": "int"
	},
	"dataArrOneStrRequire1": {
		"title": "string|require",
		"id": "int"
	},
	"dataArrOneStrRequire2": {
		"title": "string|require",
		"id": "int|require"
	},
	"dataArrTwoStr": {
		"company": {
			"name": "string",
			"age": "require|int"
		},
		"id": "require|int"
	},
	"dataArrTwoStrRequire": {
		"company": {
			"name": "require|string",
			"age": "require|int"
		},
		"id": "require|int"
	},
	"dataArrOneNumberOneStr": [{
		"school": "string|require",
		"class": "int|require"
	}],
	"dataArrOneStrOneNumber": {
		"range": ["require|int"],
		"id": "require|int"
	}
}
//echo json_encode($rule);
//exit;

class MyOb{

}

$myOb = new MyOb();
//array('int','string','float','bool');
$data = array(
    'cnt'=>2,
    'name'=>'aaaaaaaaaaa',
    'price'=>1.02,
    'isLogin'=>false,
    'myOb'=>$myOb,
    'email'=>'mqzhifu@sina.com',
    'stream'=>2222,
    'dataArrOneNum'=>array(1,6,9,10),
    'dataArrTwoNum'=>array(
        array(1,6,9,10),
        array(2,4,6,8),
    ),
    'dataArrOneStr'=>array("aaaa"=>1,'id'=>2,'title'=>'last'),
    'dataArrTwoStr'=>array(
        "company"=>array("name"=>'z','age'=>12),
        'id'=>2),
    'dataArrOneNumberOneStr'=>array(
        array('class'=>1,'school'=>'Oxford'),
        array('class'=>2,'school'=>'Harvard'),
    ),
    'dataArrOneStrOneNumber'=>array(
        'id'=>99,
        'range'=>array(1,2,3,4,)
    ),
);

Valid::match($data,$rule);