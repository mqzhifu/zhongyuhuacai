<?php
namespace Jy\Common\Valid\Test;
include_once "./../../../vendor/autoload.php";
use Jy\Common\Valid\Facades\Valid;

//$rule = array(
//    'cnt'=> array("int","numberMax:10","numberRange:2,15"),
//    'name'=> array("require","string","lengthMin:10"),
//    'price'=> array("require","float",),
//    'isLogin'=>array("require","bool"),
//    'myOb'=> array("require","object",),
//    'email'=>array('email','lengthRange:10,20'),
//    'dataArrOneNum'=> array("require","array"=>array("require","int","key_number")),
//    'dataArrTwoNum'=> array("require","array"=>array("require","key_number","array"=>
//        array("require","key_number",'int')
//    )),
//
//    'dataArrOneStr'=> array("require","array"=>array("key_hash","hash_config"=>
//        array(
//            "title"=>array("require","string"),
//            "id"=>array("require","int"),
//        ),
//    )
//    ),
//
//    'dataArrTwoStr'=> array("require","array"=>array("key_hash","hash_config"=>
//        array(
//            "company"=>array("require","array"=>array("key_hash","hash_config"=>array(
//                "name"=>array("require",'string'),
//                "age"=>array("require","int"),),
//            ),
//            ),
//            "id"=>array("require","int"),
//        ),
//    )
//    ),
//
//    'dataArrOneNumberOneStr'=>array("require","array"=>array("require","key_number","array"=>array("key_hash","hash_config"=>
//        array(
//            "school"=>array("require","string"),
//            "class"=>array("require","int"),
//        ),
//    )
//    )
//    ),
//
//    'dataArrOneStrOneNumber'=>array("require","array"=>array("key_hash","hash_config"=>
//        array(
//            "range"=>array("require","array"=>array("require","int","key_number")),
//            "id"=>array("require","int"),
//        ),
//    )
//    ),
//
//);


class MyOb{

}

$myOb = new MyOb();
//array('int','string','float','bool');
$data = array(
    'price'=>1.02,
//    'isLogin'=>false,
    'myOb'=>$myOb,
    'email'=>'mqzhifu@sina.com',
    'stream'=>2222,
    'dataArrOneNum'=>array(),
    'dataArrOneNumRequire'=>array(123,456),
    'dataArrTwoNum'=>array(
        array(1,6,9,10),
        array(2,4,6,8),
    ),
    'dataArrTwoNumRequire'=>array(
        array(1,6,9,10),
        array(2,4,6,8),
    ),
    'dataArrThreeNum'=>array(
        array( array(1,6,9,10),array(2,4,6,8)),
        array( array(1,3,5,7),array(2,4,6,12)),
    ),

//    'dataArrOneStr'=>array("aaaa"=>1,'id'=>2,'title'=>'last'),
    'dataArrOneStr'=>array('title'=>'last'),
    'dataArrOneStrRequire1'=>array('title'=>'last'),
    'dataArrOneStrRequire2'=>array('title'=>'last','id'=>111),


    'dataArrTwoStr'=>array(
        "company"=>array("name"=>'z','age'=>12),
        'id'=>2
    ),


    'dataArrTwoStrRequire'=>array(
        "company"=>array("name"=>'z','age'=>12),
        'id'=>2
    ),

    'dataArrOneNumberOneStr'=>array(
        array('class'=>1,'school'=>'Oxford'),
        array('class'=>2,'school'=>'Harvard'),
    ),
//    'dataArrOneNumberOneStr'=>null,
    'dataArrOneStrOneNumber'=>array(
        'id'=>99,
        'range'=>array(1,2,3,4,)
    ),
);


$rule = array(
    'cnt'=> "int|numberMax:10|numberRange:2,15",
    'name'=> "require|string|lengthMin:10",
    'price'=> "require|float",
//    'isLogin'=> "require|bool",
    'myOb'=> "object",
    'email'=> 'email|lengthRange:10,20',
    'dataArrOneNumRequire'=> array("int|require"),
    'dataArrOneNum'=> array("int"),
    'dataArrTwoNum'=> array(array("int")),
    'dataArrTwoNumRequire'=> array(array("require|int")),
    'dataArrThreeNum'=> array(array(array("int"))),

    'dataArrOneStr'=> array(
        "title"=>"string",
        "id"=>"int",
    ),
    'dataArrOneStrRequire1'=> array(
        "title"=>"string|require",
        "id"=>"int",
    ),
    'dataArrOneStrRequire2'=> array(
        "title"=>"string|require",
        "id"=>"int|require",
    ),

    'dataArrTwoStr'=> array(
        "company"=>array(
            "name"=>"string",
            "age"=>"require|int"),

        "id"=>"require|int",
    ),


    'dataArrTwoStrRequire'=> array(
        "company"=>array(
            "name"=>"require|string",
            "age"=>"require|int"),

        "id"=>"require|int",
    ),

    'dataArrOneNumberOneStr'=>array(
        array(
            "school"=>"string|require",
            "class"=>"int|require",
        ),
    ),

    'dataArrOneStrOneNumber'=>array(
        "range"=>array("require|int"),
        "id"=>"require|int",
    ),

);
//echo json_encode($rule,true);exit;
//$rule = json_encode($rule,true);
\Jy\Common\Valid\Facades\Valid::match($data,$rule);

//$class =new Valid();
//$message = array('require'=>'就TMD得填写');
//$class->setMessage($message);
//$rs = $class->match($data,$rule);
//var_dump($rs);exit;