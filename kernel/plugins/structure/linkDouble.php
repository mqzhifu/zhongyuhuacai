<?php
//双向-链表结构
class LinkDouble{
    public $head = null;//链表头部指针
    public $foot = null;//链表尾部指针
    public $nodePool = null;//内存池，保存结点
    public $incr = 1;//累加器
    public $nodePoolLength = 0;//记录，一共有多少个节点
    //单向 双向 循环
    public $nodePoolAddress = 0;
    public $debug = 0;
    function p($info,$br = 1){
        if($this->debug){
            _p($info,$br);
        }
    }

    //生成一个结点
    function makeNode(){
        $arr = array(
            'last'=>null,//上一个元素的地址
            'next'=>null,//下一个元素的地址
            'data'=>null,//数据
            'rand'=>null,//随机指向某一个节点
            'current'=>null,//当前元素的地址
        );
        return $this->addNodePool($arr);
    }
    //申请内存地址，将一个节点加入到内存池中，持久化
    function addNodePool($node){
//        $index = count($this->nodePool) ;//这里按说应该要减去1，但为了下面
        $index = $this->nodePoolAddress++;
        $this->p("new node index:".$index);
        $node['current'] = $index;
        $this->nodePool[$index] = $node;

        $this->nodePoolLength++;

        return $index;
    }
    //判断当前列表是否为空
    function empty(){
        if($this->head !== null){
            return false;
        }

        return true;
    }

    function addGroup($arrData,$location = 0,$direction = 1){
        foreach ($arrData as $k=>$v) {
            $this->add($v,$location,$direction);
        }
    }

    function setHeadIndex($index){
        $this->p("setHeadIndex:$index");
        $this->head = $index;
    }

    function setFootIndex($index){
        $this->p("setFootIndex:$index");
        $this->foot = $index;
    }

    function setNodeData($index,$data){
        if($this->getNodeByIndex($index) == -1){
            $this->p("err setNodeData index is unset");
            return false;
        }

        $this->p("setNodeData ".json_encode($data));
        $this->nodePool[$index]['data'] = $data;
    }

    function unsetNodeByIndex($index){
        $this->p("unsetNodeByIndex:$index");
        unset($this->nodePool[$index]);
    }

    //在尾部-添加一个结点
    //$location:位置,sort:在这个元素的,1前面插入一个，2后面插入一个
    function add($data,$location = 0,$direction = 1){
        $this->p(" func add ,data: ".json_encode($data)."  ,location:$location,direction=$direction");
        $location = (int)$location;

        if($this->empty()){
            $this->p(" link is empty");
            $newNodeIndex = $this->makeNode();
            $this->setNodeData($newNodeIndex,$data);
            $this->setHeadIndex($newNodeIndex);
            $this->setFootIndex($newNodeIndex);
            return $newNodeIndex;
        }

        //插入位置，不能大于链表元素总长度
        if($location > $this->nodePoolLength){
            $this->p("error : location > nodePoolLength");
            return -2;
        }

        if(!$location){
            //在末尾的后面加一个元素
            //新建一个元素，保存DATA
            $newNodeIndex = $this->addNodeBySomeWhere($this->foot,2,$data);
            $this->setFootIndex($newNodeIndex);
        }else{//根据 位置，向前 或 向后插入
            //在头部,前面 ,加一个
            if($location == 1 && $direction == 1){

                $newNodeIndex = $this->makeNode();
                $this->setNodeData($newNodeIndex,$data);
                //在头部元素之前再加一个元素，这个元素的上元素地址应该为空
                $this->nodePool[$newNodeIndex]['last'] = null;
                $this->nodePool[$newNodeIndex]['next'] = $this->head;
                $this->nodePool[$this->head]['last'] = $newNodeIndex;

                $this->setHeadIndex($newNodeIndex);

            }elseif($location == $this->nodePoolLength && $direction == 2){
                //在末尾的后面加一个元素
                //新建一个元素，保存DATA
                $newNodeIndex = $this->addNodeBySomeWhere($this->foot,$direction,$data);
                $this->setFootIndex($newNodeIndex);
            }else{
                //找到 当前 位置的元素，在它之前插入一个元素
                $node = $this->findOne($location,2);
                if(is_int($node))
                    return -3;

                $newNodeIndex = $this->addNodeBySomeWhere($node['current'],$direction,$data);
            }
        }

//        var_dump($this->nodePool);
        return $newNodeIndex;
    }
    //在某一个元素 插入一个元素
    //$direction  ：1前面 2后面
    //$keyIndex：某一个元素
    function addNodeBySomeWhere($keyIndex,$direction = 2,$data){
        $newNodeIndex = $this->makeNode();
        $this->setNodeData($newNodeIndex,$data);


        if($direction == 1){
            $this->nodePool[$keyIndex]['last'] = $newNodeIndex;
            $this->nodePool[$newNodeIndex]['next'] = $newNodeIndex;
        }else{
            $this->nodePool[$keyIndex]['next'] = $newNodeIndex;
            $this->nodePool[$newNodeIndex]['last'] = $keyIndex;
        }

        return $newNodeIndex;
    }
    //删除中位数，那个位置的元素
    function delMiddle(){
        $location = $this->nodePoolLength / 2;
        if(!is_int($location)){//证明是偶数
            $location = (int)$location + 1;
        }

        $node = $this->getNodeByIndex($location);
        $this->p("delMiddle index:$location,data:".$node['data']);

        $this->delOne($location,2,1);
    }

    function delAll(){
        $this->setHeadIndex(null);
        $this->setFootIndex(null);
         $this->nodePool = null;//内存池，保存结点
         $this->incr = 1;//累加器
         $this->nodePoolLength = 0;//记录，一共有多少个节点

        return 1;
    }

    //删除一个节点,,type:1 值 2位置 ，sort:1 正序  2反序
    function delOne($key,$type = 1,$sort = 1){
        $this->p("func delOne key:$key,type:$type,sort:$sort ");
        if($this->empty())
            return false;

        $node = $this->findOne($key,$type,$sort);
        $this->p("find one:".json_encode($node));
        if($node < 0)
            return $node;

        if($node['index'] == $this->head && $node['index'] == $this->foot){
            //当前链表就只有一个元素了，当再删除后，列表即为空
            $this->p("deleted node,link is empty");
            $this->delAll();
        }elseif($node['index'] == $this->foot) {//删除的元素为末尾元素
            if ($this->nodePool[$node['last']]['last'] === null) {
                //证明，上一个元素就是头，也就是 当上一个元素的（上地址）为空
                $this->setFootIndex( $this->head);
            } else {
                $this->setFootIndex($this->nodePool[$node['last']]['current']);
            }
            $this->nodePool[$node['last']]['next'] = null;
            $this->unsetNodeByIndex($node['index']);

        }elseif($node['index'] == $this->head){
            $this->nodePool[$node['next']]['last'] = null;
            $this->setHeadIndex($node['next']);
            $this->unsetNodeByIndex($node['index']);
        }else{
            $this->nodePool[$node['last']] ['next'] = $node['next'];
            $this->nodePool[$node['next']] ['last'] = $this->nodePool[$node['last']]['current'];
            $this->unsetNodeByIndex($node['index']);
        }
        //将总元素数量-1
        $this->nodePoolLength--;

        return 1;
    }

    function getAllByHeader(){
        return $this->getAll('asc');
    }

    function getAllByFooter(){
        return $this->getAll('desc');
    }

    //迭代，获取整个链表
    function getAll($sort = 'asc'){
        $list = [];
        if($this->empty())
            return -1;

        if($sort == 'asc'){
            $sortIndex = "next";
            $first = $this->head;
        }else{
            $sortIndex = "last";
            $first = $this->foot;
        }

//        var_dump($this->nodePool);
        while(1){

            if(  $this->nodePool[$first][$sortIndex] === null){
                //最后一个
//                $list[] = $this->nodePool[$fist]['data'];
                $list[] = $this->nodePool[$first];
                break;
            }
//            $list[] = $this->nodePool[$fist]['data'];
            $list[] = $this->nodePool[$first];
            $first = $this->nodePool[$first][$sortIndex];
        }

        return $list;
    }

    //获取某一个位置的节点,type:1 值 2位置 ，sort:1 正序  2反序
    function findOne($key,$type = 1,$sort = 1){
        if($this->empty())
            return -1;

        if($sort == 1){
            $location = $this->head;
            $indexKey = "next";
        }else{
            $location = $this->foot;
            $indexKey = "last";
        }

        $this->incr = 1;
        while(1){
            $node = $this->getNodeByIndex($location);
            if($node[$indexKey] === null){
                //最后一个
                $rs = $this->compare($key,$node,$type);
                if($rs){
                    $node['index'] = $location;
                    return $node;
                }
                break;
            }

            $rs = $this->compare($key,$node,$type);
            if($rs){
                $node['index'] = $location;
                return $node;
            }

            $location = $node[$indexKey];
        }

        return -3;
    }

    function getNodeByIndex($nodeIndex){
        if(!isset($this->nodePool[$nodeIndex])){
            return -1;
        }
        return $this->nodePool[$nodeIndex];
    }

    function getOneByFooter(){
        if($this->empty())
            return -1;

        return $this->nodePool[$this->foot];
    }

    function getOneByHeader(){
        if($this->empty())
            return -1;

        return $this->nodePool[$this->head];
    }

    function compare($key,$node,$type = 1){
        if($type == 1){
            if($node['data'] == $key){
                return true;
            }

        }else{
            if($this->incr == $key){
                return true;
            }
            $this->incr++;

        }
        return false;
    }
}




