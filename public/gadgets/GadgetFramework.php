<?php

define("GADGET_BASE_URI",  "/gadgets/");
define("GADGET_LIB_URI",GADGET_BASE_URI . "lib/");
define("GADGET_RESOURCES_URI",GADGET_BASE_URI."resources/");
define("GADGET_IMAGES_URI",GADGET_BASE_URI."images/");
define("GADGET_SCRIPTS_URI",GADGET_BASE_URI."scripts/");
define("GADGET_SCRIPTS_CSS",GADGET_BASE_URI."css/");
define("GADGET_SCRIPTS_SKINS",GADGET_BASE_URI."skins/");
define("GADGET_MODULES_URI",GADGET_BASE_URI."modules/");
define("DS",DIRECTORY_SEPARATOR);
define("GADGET_PATH_BASE",dirname(__FILE__));
define("GADGET_PATH_LIB",GADGET_PATH_BASE.DS."lib");
define("GADGET_PATH_MODULES",GADGET_PATH_BASE.DS."modules");
define("GADGET_PATH_RESOURCES",GADGET_PATH_BASE.DS."resources");
define("GADGET_PATH_IMAGES",GADGET_PATH_RESOURCES.DS."images");
define("GADGET_PATH_SCRIPTS", GADGET_PATH_RESOURCES.DS."scripts");
define("GADGET_POSTFIX_MODULE", "Module");
define("GADGET_POSTFIX_GADGET", "Gadget");
define("GADGET_ACTIONS_FOLDER","actions");
define("GADGET_DEFAULT_RESPONSE_TYPE","html");
define("GADGET_CACHE_DIR",GADGET_PATH_BASE.DS."cache");
class ETypeError{
    const ModuleNotFound =0;
    const GadgetNotFound = 1;
    const NoDefaultAction = 2;
    const GadgetPartNotFound = 3;
    const UnsupportedResponseType = 4;
    const ViewNotFound = 5;
    const PartialViewNotFound = 6;
}
class ErrorDefaults {
    public static function getMessage($errortype,$msg=null){
        if($msg===null){
            $msg = '';
        }
        $res = "";
        switch($errortype){
            case ETypeError::ModuleNotFound:
                $res = "404 Module Not Found";
                break;
            case ETypeError::GadgetNotFound:
                $res = "404 Gadget Not Found";
            case ETypeError::NoDefaultAction:
                $res = "No Default Action Found";
                break;
            case ETypeError::GadgetPartNotFound:
                $res = "Specified part not found";
                break;
            case ETypeError::UnsupportedResponseType:
                $res = "No response for specified type";
                break;
            case ETypeError::PartialViewNotFound:
                $res = "Partial View Not Found";
                break;
            case ETypeError::ViewNotFound:
                $res = "View Not Found";
                break;
            default:
                $res ="Not Found";
                break;
        }
        return "<html><head><title>404 Not Found</title></head><body style='color:#999999'>".
               "<center><p><h2>".$res."</h2></p><p>".$msg."</p></center></body></html>";
    }
}
class GadgetError{
    private $type;
    private $message;
    public function __construct($type,$message=null){
        $this->type = $type;
        $this->message = $message;
    }
    public function setMessage($msg){
        $this->message = $msg;
    }
    public function getMessage(){
        return $this->message;
    }
    public function Raise(){
        header("HTTP/1.0 404 Not Found");
        header("Status: 404 Not Found");
        if($this->message===null){
            echo ErrorDefaults::getMessage($this->type);
        }else{
            echo $this->message;
        }
        exit;
    }
    public static function RaiseError($type,$message=null){
        $e = new GadgetError($type, $message);
        $e->Raise();
    }
}
class GadgetHTTP{
    private $headers;

    function  __construct() {
        $this->headers = getallheaders();
    }

    function  __get($name) {
        if($name=="all"){
            return $this->headers;
        }
        $h = $this->headers[$name];
        return (isset($h)?$h:null);
    }
}
class GadgetRequest{
    private $viewparameters;
    private $viewname;
    private $viewpart;
    private $actionparameters;
    private $modulename;
    private $gadgetname;
    private $actionname;
    private $responsetype;
    private $httpheaders;
    private $operation;
    private $skin;

    private function  __construct() {
        $this->httpheaders = new GadgetHTTP();
        $this->parseRequest();
    }
    private function createArrayObject($q){
        $res = array();
        if(!isset($q)){
            return $res;
        }
        $q = urldecode($q);
        $q = explode(";", $q);
        foreach($q as $i){
            $tmp = explode(":",$i);
            if(count($tmp)>1){
                $res[$tmp[0]] = $tmp[1];
            }
        }
        return $res;
    }
    private function parseOperation(){
        $op = @$_GET["op"];
         $this->operation = $op;
        $op = explode(".", $op);
        $opcount = count($op);
        if($opcount>0){
            $this->modulename = $op[0];
        }
        if($opcount>1){
            $this->gadgetname = $op[1];
        }
        if($opcount>2){
            $this->actionname = $op[2];
        }
    }
    private function parseRequest(){
        $this->parseOperation();
        $rt = @$_GET["op_response"];       
        $this->responsetype = (isset($rt)?$rt:GADGET_DEFAULT_RESPONSE_TYPE);
        $this->actionparameters = $this->createArrayObject(@$_GET["oppars"]);
        $this->viewparameters = $this->createArrayObject(@$_GET["vpars"]);
        $this->viewname = (isset($_GET["vname"])?$_GET["vname"]:null);
        $this->viewpart = (isset($_GET["vpart"])?$_GET["vpart"]:null);
        $this->skin = (isset($_GET["skin"])?$_GET["skin"]:null);
    }
    public function  __set($name, $value) {
        switch($name){
            case "ViewPart":
                $this->viewpart = $value;
                break;
            case "ViewName":
                $this->viewname = $value;
        }
    }
    public function  __get($name) {
        switch($name){
            case "ViewParameters":
                return $this->viewparameters;
            case "ViewName":
                return $this->viewname;
            case "ViewPart":
                return $this->viewpart;
            case "ActionParameters":
                return $this->actionparameters;
            case "ActionName":
                return $this->actionname;
            case "ModuleName":
                return $this->modulename;
            case "GadgetName":
                return $this->gadgetname;
            case "ResponseType":
                return $this->responsetype;
            case "Skin":
                return $this->skin;
            case "Headers":
                return $this->httpheaders;
            case "Operation":
                return $this->operation;
        }
    }
    public function toArray(){
        $p = array();
        $p["op_response"] = $this->ResponseType;
        $p["op"] = $this->Operation;
        $p["oppars"] = $this->ActionParameters;
        $p["vname"] = $this->ViewName;
        $p["vpart"] = $this->ViewPart;
        $p["skin"] = $this->Skin;
        $p["vpars"] = $this->ViewParameters;
        return $p;
    }
    private static $request;
    public static function GetRequest(){
        if(!isset(self::$request)){
            self::$request = new GadgetRequest();
        }
        return self::$request;
    }
}
class GadgetHelper{
    private $modulename;
    private $gadgetname;
    private $helpers;
    function __construct($modulename,$gadgetname){
        $this->gadgetname = $gadgetname;
        $this->modulename = $modulename;
        $this->helpers = array();
        $this->requireHelpers();
    }
    private function requireHelpers(){
        $files = array();
        $p = GADGET_PATH_MODULES.DS.$this->modulename.DS."gadgets".DS.$this->gadgetname.DS."helpers";
        $handle = opendir($p);
        if ($handle ) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && is_dir($file)===false) {
                    $files[] = $file;
                }
            }
            closedir($handle);
        }
        foreach ($files as $f){
            require_once $p.DS.$f;
        }
    }
    private function loadHelper($name){
        $c = $name."Helper";
        if(class_exists($c)){
            $this->helpers[$name] = new $c();
        }else{
            return null;
        }
        return $this->helpers[$name];
    }
    function  __get($name) {
        $h = @$this->helpers[$name];
        if(isset($h)===false){
            $h = $this->loadHelper($name);
        }
        return $h;
    }
}

class GadgetsViewResult{
    public $data;
    protected $gadget;
    protected $action;
    protected $response;
    public $resource;
    protected $partialname;
    public function  __construct($gadget,$action,$data=array()) {
        $this->data = $data;
        $this->action = $action;
        $this->gadget = $gadget;
        $this->response = GadgetRequest::GetRequest()->ResponseType;
        $this->resource = GadgetResourceManager::getManager($this);
        $this->partialname = "";
    }
    public function  __get($name) {
        if($name==="_LIST"){
            return $this->getData();
        }
        return $this->data[$name];
    }
    public function getData(){
        return $this->data;
    }
    public function internalCall($op,$oppars=array(),$p=null){
        $res =$this->gadget->callAction($op,$oppars);
        if(is_null($p)){
            return $res;
        }
        $res->partial($p);
        return null;
    }
    public function getGadgetName(){
        return $this->gadget->getName();
    }
    public function getModuleName(){
        return $this->gadget->module->getName();
    }
    public function getActionName(){
        return $this->action;
    }
    public function getViewName(){
        $res = GadgetRequest::GetRequest()->ViewName;
        if(!isset($res)){
            $res= $this->gadget->getDefaultView();
        }
        return $res;
    }
    public function render(){
        $view = $this->resource->getViewResponsePath($this->response);

        if(file_exists($view)){
            $this->startView();
            include $view;
            $this->endView();
        }else{
            GadgetError::RaiseError(ETypeError::ViewNotFound);
        }
    }
    public function getPartialName(){
        return $this->partialname;
    }
    public function partial($controlname,$isexternal=false){
        $include = $this->resource->getPartialResponsePath($controlname,$this->response);
      
        if(file_exists($include)){
            $this->setPartialName($controlname);
            $this->startPartial($controlname,$isexternal);
            include $include;
            $this->endPartial($isexternal);
            $this->setPartialName("");
        }else{
            GadgetError::RaiseError(ETypeError::PartialViewNotFound);
        }
    }
    protected function startView(){
        echo "<div  id='".$this->resource->getClient()->getID()."' class='viewpart'>";
    }
    protected function endView(){
        echo "</div>";
    }
    protected function startPartial($controlname,$isexternal=false){
        if($isexternal){
            echo "<div id='".$this->resource->getClient()->getID()."' >";
        }else{
            echo "<div id='".$this->resource->getClient()->getID()."' class='viewpart'>";
        }
    }
    protected function endPartial($isexternal=false){
        echo "</div>";
    }
    private function setPartialName($pname){
        $this->partialname = $pname;
    }
}

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
class GadgetsViewResultXML extends GadgetsViewResult {
    private $xdata;
    function  __construct($gadget, $action, $data = array()) {
        parent::__construct($gadget,$action,$data);
        $this->response = GadgetRequest::GetRequest()->ResponseType;
        $x = new DOMDocument();
        @$x->loadXML($data);
        $this->xdata = new ObjectXml($x->documentElement);
    }
    
    public function  __get($name) {
        return $this->xdata->$name;
    }
    
    public function attr($name=null){
        return $this->xdata->attr($name);
    }
    public function  __call($name, $arguments) {
        return $this->xdata->$name($arguments);
    }
}

class GadgetAbstractGadget{
    private $name;
    private $_module;
    private $_help;
    private $_data;
    private $actionName;
    private $requestAction;
    private $requestActionParameters;
    private $cachedActions;
    private $defaultViewName;
    public  $resource;
    
    public function  __construct($name,$module) {
        $this->name = $name;
        $this->_module = $module;
        $this->_help = new GadgetHelper($module->getName(),$name);
        $this->data = array();
        $this->actionName = GadgetRequest::GetRequest()->ActionName;
        $this->requestAction = $this->actionName."Action";
        $this->requestActionParameters = GadgetRequest::GetRequest()->ActionParameters;
        $this->requestActionParameters =(isset($this->requestActionParameters)?$this->requestActionParameters:null);
        $this->resource = GadgetResourceManager::getManager($this);
        $this->cachedActions = array();
    }
    function __get($name){
        switch($name){
            case "module":
                return $this->_module;
            case "data":
                return $this->_data;
            default:
                return $this->_help->$name;
        }
    }
    public function init(){
        if(!method_exists($this,$this->requestAction)){
            GadgetError::RaiseError(ETypeError::NoDefaultAction);
        }
        $this->onInit();
    }
    public function onInit(){
        
    }
    public function registerCachedAction($act,$tout=3600,$viewtype="xml"){
        if(isset($act)){
            $this->cachedActions[$act] = 
                    array("file"=>$this->_module->getName()."_".$this->name."_".$act,
                            "timeout"=>$tout,"viewtype"=>$viewtype);
        }
    }
    protected function setDefaultView($viewname){
        $this->defaultViewName = $viewname;
    }
    public function getDefaultView(){
        return $this->defaultViewName;
    }
    private function getCachedActionView($actname){
        if(isset($this->cachedActions[$actname])){
            $actcache = $this->cachedActions[$actname];
        }else{
            return null;
        }
        $fname = $actcache["file"];
        $pname = GADGET_CACHE_DIR.DS.$fname;
        if(@file_exists($pname)){
            $ftime = intval(@filemtime($pname));
            $ctime =intval($actcache["timeout"]);
            if((time()-$ftime) < $ctime){
                $cached = file_get_contents($pname);
                if($actcache["viewtype"]==="xml"){
                    return new GadgetsViewResultXML($this, $this->actionName, $cached);
                }else{
                    return new GadgetsViewResult($this,$this->actionName, $cached);
                }
            }
        }
        return null;
    }
    private function cacheView($v,$cact){
        $f = @fopen(GADGET_CACHE_DIR.DS.$cact['file'],'w');
        if($f){
            fwrite($f,$v->data);
            fclose($f);
            return true;
        }
        return false;

    }
    public function getRequestedActionName(){
        return $this->requestAction;
    }
    public function getRequestedActionParameters(){
        return $this->requestActionParameters;
    }
    public function callAction($name=null,$parameters=null){
        $cached = $this->getCachedActionView($name);
        if(is_null($cached)===false){
            return $cached;
        }
        $n = $name;
        $p = $parameters;
        if($name==null){
            $n = $this->requestAction;
            if(gettype($name)==="array"){
                $p = $name;
            }
        }else{
            $n = $name."Action";
            if($parameters==null){
                $p = $this->requestActionParameters;
            }else{
                $p = $parameters;
            }
        }
        if(isset($this->cachedActions[$name])){
            $v = $this->$n($p);
            $this->cacheView($v, $this->cachedActions[$name]);
            return $v;
        }else{
            return $this->$n($p);
        }
        
    }
    public function getName(){
        return $this->name;
    }
    protected function view($data=null){
        if($data==null){
            $v = new GadgetsViewResult($this,$this->actionName,$this->_data);
        }else{
            $v = new GadgetsViewResult($this,$this->actionName,$data);
        }
        return $v;
    }
    protected function xmlView($data=null){
        if($data==null){
            $v = new GadgetsViewResultXML($this,$this->actionName,$this->_data);
        }else{
            $v = new GadgetsViewResultXML($this,$this->actionName,$data);
        }
        return $v;
    }
    protected function addScript($filename){
        $p = GADGET_BASE_URI."resources/".$this->module->getName()."/".$this->name."/";
        GadgetScript::addScript($filename,$p);
        return $this;
    }
    protected function addExternalScript($url){
        GadgetScript::addExternalScript($url);
        return $this;
    }
    protected function addInitScript($script){
        GadgetScript::addInitScript($script);
        return $this;
    }
}
class GadgetBaseModule{
    private $name;
    private $gadgets;
    public $resource;
    private function __construct($name){
        $this->name = $name;
        $this->gadgets = array();
        $this->resource = GadgetResourceManager::getManager($this);
    }
    public function init(){
        $gadname = GadgetRequest::GetRequest()->GadgetName;
        if($gadname==null){
            GadgetError::RaiseError(ETypeError::GadgetNotFound);
        }
        $gadget = $this->getGadget($gadname);
        if($gadget==null){
            GadgetError::RaiseError(ETypeError::GadgetNotFound);
        }
        $this->onInit();
        return $gadget;
    }
    public function onInit(){
       
    }
    public function getName(){
        return $this->name;
    }
    public function getGadget($name){
        foreach($this->gadgets as $g){
            if($g->getName()===$name){
                return $this->gadgets[$name];
            }
        }
        return $this->loadGadget($name);
    }
    protected function addScript($filename){
        $p = GADGET_BASE_URI."resources/".$this->name."/";
        GadgetScript::addScript($filename,$p);
        return $this;
    }
    protected function addExternalScript($url){
        GadgetScript::addExternalScript($url);
        return $this;
    }
    protected function addInitScript($script){
        GadgetScript::addInitScript($script);
        return $this;
    }
    private function loadGadget($name){
        $c = $this->resource->getGadgetClassName($name);
        $p = $this->resource->getGadgetPath($name);
        if(!class_exists($c)){
            if(file_exists($p)){
                require_once $p;
            }else{
                return null;
            }
        }
        $this->gadgets[$name] = new $c($name,$this);
        return $this->gadgets[$name];
    }
    //////////////////////STATIC////////////////////
    private static $modules = array();
    private static function loadModule($name){
        $c = GadgetResourceManager::getModuleClassName($name);
        $p = GadgetResourceManager::getModulePath($name);
        if(!class_exists($c)){            
            if(file_exists($p)){
                require_once $p;
            }else{
                return null;
            }
        }
        self::$modules[$name] = new $c($name);
        return self::$modules[$name];
    }
    public static function getModule($name){
        foreach(self::$modules as $m){
            if($m.getName()===$name){
                return $m;
            }
        }
        return self::loadModule($name);
    }    
}

class GadgetResourceManager{
    protected $base;
    protected $path_helpers;
    protected $path_libs;
    protected $path_resources;
    protected $path_res_images;
    protected $path_res_scripts;
    protected $clientscript;
    public function __construct($base){
       $b = $base;
       $this->base = $base;
       $this->path_helpers = $b.DS."helpers";
       $this->path_libs = $b.DS."libs";
       $this->path_resources = $b.DS."resources";
       $this->path_res_images = $this->path_resources.DS."images";
       $this->path_res_scripts = $this->path_resources.DS."scripts";
    }
    protected  function getDirectoryContents($dir,$filename=null){
        $files = array();
        $handle = opendir($dir);
        if ($handle ) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && is_dir($file)===false) {
                    if($filename!=null){
                        $f =pathinfo($dir.DS.$file);
                        if($f["filename"]===$filename){
                            $files = $file;
                            break;
                        }
                    }else{
                        $files[] = $file;
                    }
                }
            }
            closedir($handle);
        }
        return $files;
    }
    protected function getDirectories($path){
        $files = array();
        $handle = opendir($dir);
        if ($handle ) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && is_dir($file)) {
                    $files[] = $file;
                }
            }
            closedir($handle);
        }
        return $files;
    }
    public function getHelpersPath(){
        return $this->path_helpers;
    }
    public function getAvailableHelpersPaths(){
        return $this->getDirectoryContents($this->getHelpersPath());
    }
    public function getLibsPath(){
        return $this->path_libs;
    }
    public function getAvailableLibs(){
        return $this->getDirectoryContents($this->getLibsPath());
    }
    public function getResourcesPath(){
        return $this->path_resources;
    }
    public function getResourceImagesPath(){
        return $this->path_res_images;
    }
    public function getAvailableImages(){
        return $this->getDirectoryContents($this->getResourceImagesPath());
    }
    public function getResourceScriptsPath(){
        return $this->path_res_scripts;
    }
    public function getAvailableScripts(){
        return $this->getDirectoryContents($this->getResourceScriptsPath());
    }
    public function getBasePath(){
        return $this->base;
    }
    public function getClient(){
        return $this->clientscript;
    }
    public static function getManager($object){
        $t = gettype($object);
        $rm = null;
        switch(get_parent_class($object)){
            case "GadgetBaseModule":
                $rm = new GadgetModuleResourceManager(GADGET_PATH_MODULES .DS.$object->getName());
                break;
            case "GadgetAbstractGadget":
                $rm = new GadgetGadgetsResourceManager( GADGET_PATH_MODULES.DS.$object->module->getName().DS."gadgets".DS.$object->getName());
                break;
            case "GadgetsViewResult":
                $rm = new GadgetViewResultResourceManager(GADGET_PATH_MODULES.DS.$object->getModuleName().DS."gadgets".DS.$object->getGadgetName(),$object->getActionName());
                break;
            default:
                $rm = new GadgetResourceManager(GADGET_PATH_BASE);
                break;
        }
        $rm->clientscript = new GadgetClientScript($object);
        return $rm;
    }
    public static function getModuleClassName($name){
        return $name.GADGET_POSTFIX_MODULE;
    }
    public static function getModulePath($name){
        return  GADGET_PATH_MODULES.DS.$name.DS.$name.GADGET_POSTFIX_MODULE.".php";
    }
    public static function getLibPath(){
        return GADGET_PATH_LIB;
    }
    public static function getScriptPath($module=null,$gadget=null){
        $res = GADGET_PATH_SCRIPTS;
        if($module!=null){
            $res .= DS.$module;
            if($gadget!=null){
                $res .= DS.$gadget;
            }
        }
        return $res;
    }
    public static function getScriptsUri($module=null,$gadget=null){
        $res = GADGET_SCRIPTS_URI;
        if($module!=null){
            $res .= "/".$module;
            if($gadget!=null){
                $res .= "/" . $gadget;
            }
        }
        return $res;
    }
    
}
class GadgetViewResultResourceManager extends GadgetResourceManager{
    private $path_partials;
    private $path_views;
    function __construct($base,$action){
        parent::__construct($base);
        $this->path_views = $base.DS."views".DS.$action;
        $this->path_partials = $base.DS."partial".DS.$action;
    }
    public function getViewResponsePath($response = null){
        if(is_null($response)){
            return $this->getDirectoryContents($this->path_views);
        }else{
            return $this->path_views.DS.$this->getDirectoryContents($this->path_views,$response);
        }
    }
    public function getAvailableViewResponses(){
        return  $this->getDirectoryContents($this->path_views);
    }
    public function getPartialResponsePath($name,$response = null){
        if(is_null($response)){
            return $this->getDirectoryContents($this->path_partials,$name);
        }else{
            return  $this->path_partials.DS.$this->getDirectoryContents($this->path_partials,$name.".".$response);
        }
    }
    public function getAvailablePartialResponses(){
        return  $this->getDirectoryContents($this->path_partials);
    }
   
}
class GadgetGadgetsResourceManager extends GadgetResourceManager{
    private $path_views;
    private $path_partial;
    public function __construct($base){
        parent::__construct($base);
        $this->path_views = $this->base.DS."views";
        $this->path_partial = $this->base.DS."partial";
    }
    public function getViewPath(){
        return $this->path_vies;
    }
    public function getAvailableViews(){
        return $this->getDirectories($this->getViewPath());
    }
    public function getPartialPath(){
        return $this->path_partial;
    }
    public function getAvailablePartials(){
        return $this->getDirectories($this->getPartialPath());
    }
}
class GadgetModuleResourceManager extends GadgetResourceManager{
    private $path_gadgets;
    public function __construct($base){
        parent::__construct($base);
         $this->path_gadgets = $base.DS. "gadgets";
    }
    public function getGadgetsPath(){
        return $this->gadgetspath;
    }
    public function getGadgetPath($name){
        return $this->path_gadgets.DS.$name.DS.$name.GADGET_POSTFIX_GADGET.".php";
    }
    public function getGadgetClassName($name){
        return $name.GADGET_POSTFIX_GADGET;
    }
     public function getAvailableGadgets(){
        return $this->getDirectories($this->getGadgetsPath());
    }
}

class GadgetRawScript{
    private $script ;
    function __construct(){
        $this->script = array();
    }
    public function append($script){
        $this->script[] = $script;
    }
    public function render(){
        if(count($this->script)>0){
            $res = "<script type='text/javascript' >";
            foreach($this->script as $s){
                $res .= $s;
            }
            $res .= "</script>";
            echo $res;
        }
    }
}

class GadgetScript{
    private static $js = array();
    private static $extjs = array();
    private static $initjs = array();
    public static function addInitScript($script){
        if(isset($script)){
            self::$initjs[] = $script;
        }
    }
    public static function addScript($name,$path){
        self::$js[$name] = $path.$name.".js";
    }
    public static function addExternalScript($url){
        self::$extjs[] = $url;
    }
    public static function removeScript($name){
        unset(self::$js[$name]);
    }
    public static function hasScript($name){
        if(isset(self::$js[$name])){
            return true;
        }
        return false;
    }
    public static function render(){
        $res = "";
        foreach(self::$extjs as $e){
            $res.= "<script type='text/javascript' src='" . $e . "'></script>";
        }        
        foreach(self::$js as $v){
            $res .= "<script type='text/javascript' src='". $v ."'></script>";
        }
        if(count(self::$initjs)>0){
            $res .= "<script type='text/javascript'>";
            foreach(self::$initjs as $i){
                $res.=$i;
            }
            $res .= "</script>";
        }
        echo $res;
    }
}

class GadgetClientScript{
    private $type;
    private $object;
    public function  __construct($object) {
        $this->object = $object;
        $P = get_parent_class($object);
         switch($P){
            case "GadgetBaseModule":
                $this->type = "module";
                break;
            case "GadgetAbstractGadget":
               $this->type = "gadget";
                break;
            case "GadgetsViewResult":
                $this->type = "view";
                break;
            default:
                return null;
        }
    }
    public function getID(){
        switch($this->type){
            case "module":
                return "m_".$this->object->getName();
            case "gadget":
                return "g_".$this->object->module->getName()."_".$this->object->getName();
            case "view":
                if($this->object->getPartialName()==""){
                    return "v_".$this->object->getModuleName()."_".$this->object->getGadgetName()."_".$this->object->getActionName();
                }else{
                    return "p_".$this->object->getModuleName()."_".$this->object->getGadgetName()."_".$this->object->getPartialName();
                }
            default:
                return "";
        }
    }
    public static function buildJSConfigObject(){
        $r = GadgetRequest::GetRequest();
        $p = $r->toArray();
        $p["base"] =  $_SERVER["PHP_SELF"];
        return "gadgets.config = " . json_encode($p) . ";\n";
    }
    public static function buildJSObject($module){
        $res = "";
       if(isset($module)){
           $modjs =  self::buildJSModuleObject($module);
           if(isset($modjs)){
               $res = "gadgets.state." . $module->getName()."=".json_encode($modjs) . ";\n";
           }
           return $res;
        }
    }
    private static function buildJSModuleObject($module){
        $obj = array();
        $obj["_id"] = $module->resource->getClient()->getID();
        $obj["_name"] =$module->getName();
        $gname = GadgetRequest::GetRequest()->GadgetName;
        $obj[$gname] = self::buildJSGadgetObject($module->getGadget($gname));
        return $obj;
    }
    private static function buildJSGadgetObject($gadget){
        $obj = array();
        $obj["_id"] = $gadget->resource->getClient()->getID();
        $obj["_name"] = $gadget->getName();
        $act = self::builJSActionObject($gadget);
        $obj[GadgetRequest::GetRequest()->ActionName] = $act;
        return $obj;
    }
    private static function builJSActionObject($gadget){
        $obj = array();
        $obj["parameters"] =GadgetRequest::GetRequest()->ActionParameters;
        $obj["queryparameters"] = $obj["parameters"];
        $obj["baseparameters"] = $obj["parameters"];
        return $obj;
    }  
}

class GadgetExecution{
    private $module;
    private $request;
    private $gadget;
    private $ispartial;
    private $resultView;

    public function  __construct($request,$module) {
        $this->module = $module;
        $this->request = GadgetRequest::GetRequest();
        $vp = $this->request->ViewPart;
        $this->ispartial = isset($vp)?true:false;
    }

    public function run(){
        GadgetScript::addInitScript(GadgetClientScript::buildJSConfigObject());
        $this->initModule();
        $this->gadget->init();
        $this->resultView = $this->gadget->callAction($this->request->ActionName,$this->request->ActionParameters);
        if($this->ispartial){
           $this->renderPartial($this->request->ViewPart);
        }else{
            $laypath = $this->getLayoutPath();
            GadgetScript::addInitScript(GadgetClientScript::buildJSObject($this->module));
            include $laypath;
        }
    }
    public function renderScripts(){
        
        GadgetScript::render();
    }
    public function renderSkin(){
        $s = GadgetRequest::GetRequest()->Skin;
        if(is_null($s)){
            $s = "default";
        }
		$v=exec("cat ../../VERSION"); 
        $p = GADGET_RESOURCES_URI."skins/".$s."/default.css?v=" . $v;
        echo "<link rel='stylesheet' type='text/css' href='".$p."' id='skincss' />";
    }
    public function renderCss(){
        
    }
    public function renderView(){
        $this->resultView->render();
    }
    private function initModule(){
        $this->gadget = $this->module->init();
    }
    private function getLayoutPath(){
        return GADGET_PATH_BASE.DS."layouts".DS.$this->request->ResponseType.".php";
    }
    private function renderPartial($part){
        header("Content-Type:text/html");
        echo "<div>";
        $part = explode(";",$part);
        foreach($part as $p){
           $this->resultView->partial($p,true);
        }
        echo "</div>";
        
    }
}

class GadgetBootLoader{
    private $module;
    private $request;
    private $execution;
    public function init(){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Request-Method: GET');
        $this->request = GadgetRequest::GetRequest();
        $modname = $this->request->ModuleName;
        $this->module = GadgetBaseModule::getModule($modname);
        if($this->module===null){
            GadgetError::RaiseError(ETypeError::ModuleNotFound);
        }
        $this->execution = new GadgetExecution($this->request, $this->module);
        return $this->execution;
    }
    ///////////////////////STATIC///////////////////////
    private static $boot;
    public static function GetBootLoader(){
        if(!isset(self::$boot)){
            self::$boot = new GadgetBootLoader();
        }
        return self::$boot;
    }
}
?>
