<?php
/**
 * Copyright (C) 2015 IASA - Institute of Accelerating Systems and Applications (http://www.iasa.gr)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and 
 * limitations under the License.
 */

/**
 * Description of AppdbAPIHelper
 *
 * @author nakos
 */
class AppdbAPIHelper {

    //************************   STATIC REGION  ***************************************************
    private static function GetPageLengthKey() {
        return "pagelength";
    }
    private static function GetPageLengthDetailsMaxValue(){
        return 50;
    }
    private static function GetPageLengthMaxValue(){
        return 300;
    }
    /*Parameter name for paging*/
    private static function GetPageOffsetKey() {
        return "pageoffset";
    }
    /* Default path to the controllers folder.Used for requiring and instanciating controllers.*/
    private static function getControllersPath() {
        return "../application/controllers/";
    }
    /* Default path for the xslt files used to transform the output xml. (...duh!)*/
    private static function getXSLTPath($version) {
        return "../application/configs/api/".$version."/xslt/";
    }
    /* Default path of xsd files needed by the schemaAction*/
    /*private static function getXSDUri($version) {
        return "http://" . $_SERVER["HTTP_HOST"] . "/api/schema/".$version."/";
    }*/
    /*Schema files' path*/
    private static function GetXSDPath($version) {
        return "../application/configs/api/".$version."/schema/";
    }
    /* A variable name for the query data to be passed. */
    private static function GetQueryKey() {
        return "filter";
    }
    /* There are some parameters that are passed from the router which are required
     * only from the controllers. These parameters are returned by this function in order
     * to ignore while processing query and paging parameters. */
    private static function GetIgnoreParameterList() {
        return array("format", "dc", "XDEBUG_SESSION_START");
    }
    /* Returns the default tag name of the root element of the response xml.*/
    public static function getRootTagName() {
        return "appdb";
    }
    /*Returns a data scheme with the given image in base64 format.
     * Note     :   not all browsers supports this. It is mostly done for development purposes.
     * $data    :   String image data in base64 formating*/
    public static function createBase64Scheme($data) {
        return "data:image/png;base64," . $data;
    }
    /* Called from view object of restAction in order to normalize output.*/
    public static function TransformResult($result, $view) {//return $result;
        $xname = (isset($view->routeXslt)) ? $view->routeXslt : $view->routeController;
        $xf = AppdbAPIHelper::getXSLTPath($view->apiVersion) . $xname . '.xsl';
		if (file_exists($xf)) {
			$xml = xml_transform($xf, $result);
			if ($xml === false) {
				return null;
			} else {
	            return str_replace('<?xml version="1.0"?>', '', $xml);
			}
        } else {
            return $result;
        }
    }
    //************************   INSTANCE REGION  ************************************************
    private $routeController;
    private $routeDataType;
    private $routeAction;
    private $routeEntryName;
    private $routeParams;
    private $routeInitEntries;
    private $routeXslt;
    private $routeRecursive;
    private $routeModel;
    private $useDetails;
    private $forcePaging;
    private $version;
    private $api;

    /* Maps the parameters of the user request to the local variables
    * and removes them form the request object. The remaining parameters
    * are passed to the request call to be used by the calling action.
    * $api    :   the api controller instance
    * $pars  :   array with all of the request parmeters. This is usually aquired
    *                 by the Zend_Controller_Action->_getAllParams() function.*/
    public function InitHelper(&$api, $pars) {
        $this->api = $api;
        if (isset($pars["routeController"])) {
            $this->routeController = $pars["routeController"];
        }
        if (isset($pars["routeAction"])) {
            $this->routeAction = $pars["routeAction"];
        }
        if (isset($pars["routeEntryName"])) {
            $this->routeEntryName = $pars["routeEntryName"];
        }
        if (isset($pars["routeDataType"])) {
            $this->routeDataType = $pars["routeDataType"];
        } else {
            $this->routeDataType = "object";
        }
        if (isset($pars["routeInitEntries"])) {
            $this->routeInitEntries = $pars["routeInitEntries"];
        }
        if (isset($pars["routeXslt"])) {
            $this->routeXslt = (isset($pars["routeXslt"]) ? $pars["routeXslt"] : $this->routeController);
        }
        if(isset($pars["routeRecursive"])){
            $this->routeRecursive = ($pars["routeRecursive"]=="0")?false:true;
        }else{
            $this->routeRecursive = true;
        }
        if(isset($pars["details"])){
            $this->useDetails = ($pars["details"]=="true")?true:false;
            $this->routeRecursive =$this->useDetails;
        }else{
            $this->useDetails = false;
        }
        if(isset($pars["routeForcePaging"])){
            $this->forcePaging = $pars["routeForcePaging"];
        }else{
            $this->forcePaging = false;
        }
        if(isset($pars["dataType"])){
            $this->routeDataType = $pars["dataType"];
        }
        if(isset($pars["routeModel"])){
          $this->routeModel = $pars["routeModel"];
        }
        $this->version = $pars["version"];
        $this->routeParams = array();
        //Clear unessecary parameters
        unset($pars["routeController"]);
        unset($pars["routeAction"]);
        unset($pars["routeEntryName"]);
        unset($pars["controller"]);
        unset($pars["action"]);
        unset($pars["routeInitEntries"]);
        unset($pars["routeXslt"]);
        unset($pars["routeRecursive"]);
        unset($pars["routeModel"]);
        unset($pars["details"]);
        unset($pars["routeForcePaging"]);
        unset($pars["routeDataType"]);
        unset($pars["version"]);
       

        //Move the unused parameters to the superglobal $_GET in order to be processed
        //by the proceeding controller. Usually these parameters come by the user or
        //by the router mechanism which is declared in the Bootstrap.php file.
        foreach ($pars as $k => $v) {
            $this->routeParams[$k] = $v;
            $_GET[$k] = $v;
        }
    }
    /*Fills and transforms the view object fields for use from the view phtml file */
    private function BuildView(){
         //Resort to a single variable name (entry) for the results
        if (isset($this->api->view->entries)) {
            $this->api->view->entry = $this->api->view->entries;
        } else if (isset($this->api->view->entry)) {
            $this->api->view->entry = $this->api->view->entry;
        }
        
        if (isset($this->routeEntryName)) {
            if (isset($this->api->view->entry)) {
                $this->api->view->entry = $this->api->view->entry->{$this->routeEntryName};
            } else {
                $this->api->view->entry = $this->api->view->{$this->routeEntryName};
            }
        }
              
        $this->api->view->total = (isset($this->api->view->total)) ? $this->api->view->total : ((isset($this->api->view->entry)) ? ((gettype($this->api->view->entry) == "array") ? count($this->api->view->entry) : 1) : 0);
        $this->api->view->offset = (isset($this->api->view->offset)) ?$this->api->view->offset : 0;
        $this->api->view->length = (isset($this->api->view->length)) ? intval($this->api->view->length) + 1 : 0;
        $this->api->view->pageCount = (isset($this->view->pageCount)) ? $this->api->view->pageCount : 0;
        $this->api->view->currentPage = (isset($this->view->currentPage)) ?$this->api->view->currentPage : 0;
        
        $this->api->view->routeController = $this->routeController;
        $this->api->view->routeAction = $this->routeAction;
        $this->api->view->routeInitEntries = $this->routeInitEntries;
        $this->api->view->routeXslt = $this->routeXslt; 
        $this->api->view->apiVersion = $this->version;
        $this->api->view->useDetails = $this->useDetails;
        $this->api->view->dataType = $this->routeDataType;
        $this->api->view->routeRecursive = $this->routeRecursive;
       
    }
    /* Builds and sets the final request which will be used by the controller.*/
    private function BuildRequest() {
        $this->BuildPaging();
        $this->BuildQuery();
        foreach ($this->routeParams as $k => $v) {
            $_GET[$k] = $v;
        }
    }
    /* Sets the right paging parameters in the request.*/
    private function BuildPaging() {
         if (isset($this->routeParams[AppdbAPIHelper::GetPageLengthKey()])) {
            $_GET["len"]  = intval($this->routeParams[AppdbAPIHelper::GetPageLengthKey()]) - 1;
            unset($this->routeParams[AppdbAPIHelper::GetPageLengthKey()]);
        }
        if (isset($this->routeParams[AppdbAPIHelper::GetPageOffsetKey()])) {
            $_GET["ofs"]  = $this->routeParams[AppdbAPIHelper::GetPageOffsetKey()];
            unset($this->routeParams[AppdbAPIHelper::GetPageOffsetKey()]);
        }

        if($this->forcePaging==true){
            if(!isset($_GET["len"])){
                $_GET["len"] = 0;
            }
            if(!isset($_GET["ofs"])){
                $_GET["ofs"] = 0;
            }
            //Force paging for simple lists of applications or people
            if($_GET["len"]==0 | $_GET["len"]>AppdbAPIHelper::GetPageLengthMaxValue()){
                $_GET["len"]=AppdbAPIHelper::GetPageLengthMaxValue()-1;
            }
            //Force paging if "details" parameter is used. The default value for page length is
            //given by the statuc function GetPageLenghDefaultMax
            if($this->useDetails==true){
                    if($_GET["len"]>AppdbAPIHelper::GetPageLengthDetailsMaxValue() | $_GET["len"]<=0){
                        $_GET["len"] = AppdbAPIHelper::GetPageLengthDetailsMaxValue()-1;
                    }
            }
        }
        unset($_GET[AppdbAPIHelper::GetPageLengthKey()]); 
        unset($_GET[AppdbAPIHelper::GetPageOffsetKey()]);
    }
    /* Builds the json filter object in case the user inserts extra parameters to the request.*/
    private function BuildQuery() {
        $q = array();
        $json = "{}";
        $ignore = AppdbAPIHelper::GetIgnoreParameterList();
        //Collect query related parameters
        foreach ($this->routeParams as $k => $v) {
            if (in_array($k, $ignore)) {
                continue;
            }
            $q[$k] = $v;
            //Clear retreived parameters from request parameters
            unset($this->routeParams[$k]);
            unset($_GET[$k]);
        }
        $q = AppdbAPIRequestProcessor::Transform($this->routeXslt,$this->version,$q);
        if($q===null){
            $this->api->view->Error = "Invalid query parameter";
            return;
        }
        
       
        //Create json query object
        if (count($q) > 0) {
            if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                $json = json_encode($q, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
            } else {
                $json = json_encode($q);
            }
        }
        if($json!="{}"){
            $_GET[AppdbAPIHelper::GetQueryKey()] = $json;
        }
    }
    /* Finds the controller, creates an instance and then call the
     * required action.Finally, it builds the resulting view.*/
    private function CallGetController() {
       //Construct the correct file name of the controller
        $cname = $this->routeController;
        $obj = strtoupper(substr($cname, 0, 1)) . substr($cname, 1, strlen($cname) - 1) . "Controller";
        //Aquire the controller class file and create a new instance for use.
        require_once(AppdbAPIHelper::getControllersPath() . $obj . '.php');
        $c = new $obj($this->api->getRequest(), $this->api->getResponse(), $this->api->getInvokeArgs());
        //Construct the correct action name to be called by the instance of the controller and try to execute it.
        $actionName = $this->routeAction . "Action";
        try {
            if (method_exists($c, $this->routeAction . "Action")) {
                $c->{$actionName}();
            }
        } catch (Exception $e) {
            $this->api->view->Error = "Invalid Query property";//htmlspecialchars($e->getMessage(),ENT_QUOTES);
        }
        //synchronize the controller's view object with the api's view object
        foreach($c->view as $k=>$v){
            $this->api->view->{$k} = $v;
        }        
    }
    /* Calls the appropriate model objects and concatanates their results.
     * The object names to be called are given by the route object. */
    private function CallGetModel(){
        $c = array();
        try{
            foreach(explode(";",$this->routeModel) as $v){
                $cname = "Default_Model_" . $v;
                $m = new $cname();
                $c = array_merge($c ,$m->refresh()->items);
            }
        }catch(Exception $e){
            $this->api->view->Error = $e->getMessage();
        }
        if(count($c)==1){
            $this->api->view->entry = $c[1];
        }else{
            $this->api->view->entry =$c;
        }
    }
    /*Makes the calls to the backend to retreive the appropriate data.
     * It serves only the read-only part of the application and should be
     * called only on HTTP GET requests. */
    public function CallGet(){
         //Transforms the current request to be valid for the callee
         $this->BuildRequest();
         if(!isset($this->api->view->Error)){
             if(isset($this->routeModel)){
                $this->CallGetModel();
            }else{
                $this->CallGetController();
            }
        }
        $this->BuildView();
         
    }
    /*Returns a list of xsd files and their locations.These xsd files describe the structure
     * of the data to be returned after an api request is made by the user. */
    public function GetSchemas($xsdname=null) {
        if($xsdname==null){
            $results = array();
            $handle = @opendir(AppdbAPIHelper::GetXSDPath($this->version));
            if ($handle) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && (substr($file, strlen($file) - strlen("xsd"))) == "xsd") {
                        $results[] = "<schema name='" . str_replace(".xsd", "", $file) . "' uri='http://" . $_SERVER["APPLICATION_API_HOSTNAME"]  . "/rest/"  . $this->version . "/schema/" . str_replace(".xsd", "", $file) . "' />";
                    }
                }
                closedir($handle);
            }
            return $results;
        }else{
            $file = AppdbAPIHelper::GetXSDPath($this->version) .  $xsdname . ".xsd";
            if(file_exists($file)){
                return file_get_contents($file);
            }else{
                return null;
            }
        }
    }
}

/*This class is used by the AppdbAPIHelper class in order to bridge the differences
 * between the represented naming of the responded data with the request parameters
 * given by the user. */
class AppdbAPIRequestProcessor {

    //************************   STATIC REGION  ************************************************
    /* The name of the root element to envelop the produced xml string */
    private static function GetRequestTagName() {
        return "query";
    }
    /* The path where the request xslt file reside. */
    private static function GetRequestXSLTPath($version) {
        return "../application/configs/api/".$version."/xslt/request/";
    }
    /* Takes an array of key value pairs which represent the request parameters
     * and produces an equivelant xml string for the xslt files to process. */
    private static function RequestToXml($query) {
        $attrs = "";
        foreach ($query as $k => $v) {
            $attrs .= "<" . $k . ">" . $v . "</" . $k . ">";
        }
        return '<' . AppdbAPIRequestProcessor::GetRequestTagName() . ">" .
        $attrs .
        "</" . AppdbAPIRequestProcessor::GetRequestTagName() . ">";
    }
    /*Takes a xml string , given by the xslt process and produces the equivelant
     * parameter array to be used in place of the initial request object*/
    private static function XmlToRequest($xml) {
        $res = array();
        $x = simplexml_load_string($xml);
        foreach ($x->children() as $k => $v) {
            $res[$k] = (string)$x->$k;
        }
        return $res;
    }
    private static function IsQueryValid($q){
        foreach($q as $k=>$v){
            if(is_numeric($k) | !isset($v)){
                return false;
            }
        }
        return true;
    }
    /* Transforms the given query parameters to the correct naming
     * to be passed to the controller for filtering and querying the data*/
    public static function Transform($xsltname,$version,$query) {
        if (count($query) > 0) {
            if(!AppdbAPIRequestProcessor::IsQueryValid($query)){
                return null;
            }
            $qxml = AppdbAPIRequestProcessor::RequestToXml($query);
            $qres = ""; //xml representation of the request
            $xf = AppdbAPIRequestProcessor::GetRequestXSLTPath($version) . $xsltname . '.xsl';
			if (file_exists($xf)) {
				$xml = xml_transform($xf, $qxml);
				if ($xml === false) {
					return null;
				} else {
	                $qres = str_replace('<?xml version="1.0"?>', '', $xml);
		            return AppdbAPIRequestProcessor::XmlToRequest($qres);
				}
            }
        }
        return $query;
    }
}

?>
