<?php
class ObjectXml {
    private $data;
    function  __construct($docelem) {
        $this->data = $docelem;
    }
    private function getAll($name){
        $res = array();
        $domtags = $this->data->getElementsByTagName($name);
        $len = $domtags->length;
        if($len===1){
            $res= new ObjectXml($domtags->item(0));
        } else {
            $res = array();
            foreach($domtags as $d){
                if($d->parentNode->isSameNode($this->data)){
                    $res[] = new ObjectXml($d);
                }
            }
        }

        return $res;
    }
    public function  __get($name) {
        $x=$this->getAll($name);
        if(gettype($x)==="array"){
            if(count($x)>0){
                return $x[0];
            }else{
                return null;
            }
        }
        return $x;
    }
    public function attr($name=null){
        $res = array();
        if($this->data->hasAttributes()===false){
            return null;
        }
        if($name===null){
            $a = $this->data->attributes;
            foreach($a as $k=>$v){
                $res[$k] = $v->nodeValue;
            }
            return $res;
        }
        return $this->data->getAttribute($name);
    }
    public function  __call($name, $arguments) {
        $res = $this->getAll($name);
        if(gettype($res)==="array"){
            return $res;
        }
        $x = array();
        $x[] = $res;
        return $x;
    }
    public function  __toString() {
        return $this->data->nodeValue;
    }
}
class AppDBrestAPIHelper {
    private $base;
    function  __construct($version="1.0") {
		$this->base = "http://".$_SERVER['APPLICATION_API_HOSTNAME']."/rest/".$version."/";
    }
    private function getData($url){
        return web_get_contents($this->base.$url);
    }
    private function builQuery($q){
        $res="";

        foreach($q as $k=>$v){
            $res .= $k."=".$v."&";
        }
        if(strlen($res)>0){
            $res = substr($res, 0,  strlen($res)-1);
        }
        return $res;
    }
    public function applications($data=null){
        $q = array();
        $t = gettype($data);
        if($t=="array"){
            $q = $this->builQuery($data);
             if($q!==""){
                return $this->getData("applications/?".$q);
             }
        }else if($t=="string"){
            if($data!==""){
                 return $this->getData("applications/".$data."/");
            }
        }
        return $this->getData("applications/");
    }
    public function  Regional() {
        $reg= $this->getData("regional/");
         $x = new DOMDocument();
        @$x->loadXML($reg);
        return new ObjectXml($x->documentElement);
    }
    public function  Desciplines() {
        $desc = $this->getData("disciplines/");
        $x = new DOMDocument();
        @$x->loadXML($desc);
        return new ObjectXml($x->documentElement);
    }
    public function VOs(){
        $vos =  $this->getData("vos/");
        $x = new DOMDocument();
        @$x->loadXML($vos);
        return new ObjectXml($x->documentElement);
    }
    public function Middlewares(){
        $vos =  $this->getData("middlewares/");
        $x = new DOMDocument();
        @$x->loadXML($vos);
        return new ObjectXml($x->documentElement);
    }
	public function Tags(){
        $tags =  $this->getData("applications/tags/");
        $x = new DOMDocument();
        @$x->loadXML($tags);
        return new ObjectXml($x->documentElement);
    }
	public function Categories(){
		$desc = $this->getData("applications/categories/");
        $x = new DOMDocument();
        @$x->loadXML($desc);
        return new ObjectXml($x->documentElement);
	}

}
?>
