<?php
class AppDBrestAPI {
    private $base;
    function  __construct($version) {
        $this->base = "http://appdb-pi.egi.eu/rest/".$version."/";
    }
    private function getData($url){
        return web_get_contents($url);
    }
    private function builQuery($q){
        $res="";
        $plen = GadgetRequest::getPageLength();
        if($plen!==null){
            $q["pagelength"] = $plen;
        }
        $poff = GadgetRequest::getPageOffset();
        if($poff!==null){
            $q["pageoffset"] = $poff;
        }
        foreach($q as $k=>$v){
            $res .= $k."=".$v."&";
        } 
        $res = substr($res, 0,  strlen($res)-1);
        return $res;
    }
    public function applications(){
        if(GadgetRequest::hasPaging()){
            return $this->applicationsByQuery(array());
        }
        return $this->getData($this->base."applications/");
    }
    public function applicationsById($id){
        return $this->getData($this->base."applications/".$id."/");
    }
    public function applicationsByQuery($query){
        $q = $this->builQuery($query);
        return $this->getData($this->base."applications/?".$q);
    }
}
?>
