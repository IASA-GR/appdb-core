<?php
class AppDBrestAPIHelper {
    private $base;
	private $domainsmap = array(
		"1" => array(1082,1083,1084), //astronomy, astrophysics ...
		"2" => array(1002,1007), //Computer Science and Mathematics
		"3" => array(1185), //Life Sciences
		"4" => array(1109), //Computational Chemistry
		"5" => array(1032), //Earth sciences
		"6" => array(1091), //Fusion
		"7" => array(1092), //High-Energy Physics
		"8" => array(998), //Others
		"9" => array(1082,1083,1084,1109,1002,1007,1032,1091,1092,1378,1185), //Multidisciplinary
		"10"=> array(1378) //Infrastructure
	);
    function  __construct($version="0.2") {
        $this->base = "http://".$_SERVER['APPLICATION_API_HOSTNAME']."/rest/".$version."/";
    }
    private function getData($url){
        return file_get_contents($this->base.$url);
    }
    private function getVOIDFromValue($value){
        $vo = $this->VOs();
        $x = new DOMDocument();
        $x->loadXML($vo);
        $v = $x->getElementsByTagName("vo");
        for($i=0; $i<count($v); $i++){
            if(strtolower($v->item($i)->getAttribute("name"))===strtolower($value)){
                return $v->getAttribute("id")->nodeValue;
            }
        }
        return $value;
    }
    
    private function ValueToID($key,$value){
        if(is_int($value)){
            return $value;
        }
        $data = null;
        $x = new DOMDocument();
        $v = null;
		switch(strtolower($key)){
            case "vo":
                $data = $this->VOs();
                $x->loadXML($data);
                $v = $x->getElementsByTagName("vo");
                break;
            case "country":
                $data = $this->Regional();
                $x->loadXML($data);
                $v = $x->getElementsByTagName("country");
                break;
            default:
                return $value;
                break;
            }
        $c =$v->length;

        for($i=0; $i<$v->length; $i++){
            $vv = ((strtolower($key)==='vo')?$v->item($i)->getAttribute("name"):$v->item($i)->nodeValue);
			if(strtolower($vv)===strtolower($value)){
                return $v->item($i)->getAttribute("id");
            }
        }
        return $value;
    }
	private function MapDomainValue($value){
		$v = trim($value);
		if( isset($this->domainsmap[$v]) === true && is_array($this->domainsmap[$v]) === true ){
			$domain = $this->domainsmap[$v];
			$res = array();
			for($i = 0; $i < count($domain); $i++){
				$res[] = "%3Ddiscipline.id:".$this->ValueToID("discipline", $domain[$i]);
			}
			return implode("%20",$res);
		}else{
			return "%3Ddiscipline.id:".$this->ValueToID("discipline", $v);
		}
	}
    private function builQuery($q){
        $res="";
		$flt = "flt=";
        foreach($q as $k=>$v){
			if($k=='tag'){
				$flt .= "%3Dtag:".$this->ValueToID($k, $v);
			}else if($k == 'category' ){
				$flt .= ((trim($flt)!=='')?"%20":"");
				$flt .= "%2B%3D%26category.id:".$this->ValueToID($k, $v);
			} else if($k == 'discipline' ){
				$flt .= ((trim($flt)!=='')?"%20":"");
				$flt .= $this->MapDomainValue($v); 
			}else{
				$res .= $k."=".$this->ValueToID($k, $v)."&";
			}
        }
		if( $flt !== "flt="){
			$res = $flt . "&" . $res;
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
                if (isset($_SERVER['HTTP_X_FORWARD_FOR']) && $_SERVER['HTTP_X_FORWARD_FOR']!='') {
                    $ip = $_SERVER['HTTP_X_FORWARD_FOR'];
                } else {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }
                 return $this->getData("applications/".$data."/?cid=1".(($ip)?"&src=".base64_encode($ip):""));
            }
        }
        return $this->getData("applications/");
    }
    public function  Regional() {
        return $this->getData("regional/");
    }
    public function  Desciplines() {
		return file_get_contents("http://".$_SERVER['APPLICATION_API_HOSTNAME']."/rest/1.0/disciplines/"); //override. Does not return disciplines in version 0.2
        //return $this->getData("disciplines/");
    }
    public function VOs(){
        return $this->getData("vos/");
    }
	public function Tags(){
		return $this->getData("tags/");
	}
	public function Categories(){
		return $this->getData("categories/");
	}
}
?>
