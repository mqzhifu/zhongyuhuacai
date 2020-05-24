<?php
include_once KERNEL_DIR.DS ."config".DS ."memcache.php";
//memcache
class McacheLib{
    public $memcache = null;
    public $node = null;
    function __construct()
    {
        $this->memcache = new Memcache();
        $this->imitation();


    }

    function addServer(){
        foreach ($GLOBALS[KERNEL_NAME]['memcache']  as $k=>$v) {
            $this->memcache->addServer($v['h'],$v['p'],$v['weight']);
        }

    }

    function imitation(){
        foreach ($GLOBALS[KERNEL_NAME]['memcache'] as $k=>$v) {
            for ($i=0 ; $i < 5; $i++) {
                $key = sprintf("%u", crc32($v['h'].$v['p'].'_'.$i));
                $this->node[$key] = $v['h'].":".$v['p'];
            }
        }

        ksort($this->node);

        var_dump($this->node);
        exit;

        exit;



        $cnt = count($GLOBALS[KERNEL_NAME]['memcache']);
//        $numberEnd = pow(2,32) - 1;
        $numberEnd = 24;
        $every = (int)($numberEnd / $cnt);

        _p("2^32:$numberEnd, cnt:$cnt , every:$every");

        $start = 0;
        $end = $every;
        foreach ($GLOBALS[KERNEL_NAME]['memcache'] as $k=>$v) {
            $this->node[$k] = array('s'=>$start,'e'=>$end);

            $start = $end;
            $end += $every;

            _p(crc32($v['h'].$v['p']));
        }

        var_dump($this->node);exit;
    }
}