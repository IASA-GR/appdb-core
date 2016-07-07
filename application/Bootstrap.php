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

class PageRouter extends Zend_Controller_Plugin_Abstract {
	public function preDispatch(Zend_Controller_Request_Abstract $req) {
		$dispatcher = Zend_Controller_Front::getInstance()->getDispatcher();
		if (!$dispatcher->isDispatchable($req, $req)) {
			if ( ($req->getRequestUri() === "/rest") || (substr($req->getRequestUri(), 0, 6) === "/rest/") ) {
				$req->setModuleName('default');
				$req->setControllerName('error');
				$req->setActionName('invalidrestresource');
				Zend_Controller_Front::getInstance()->getRouter()->removeRoute("latest");
			}
		}

	}
}

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initRestRoute()
    {

		$this->bootstrap('Request');
		if ( strpos($_SERVER['REQUEST_URI'],'/rest/') !== false) { 
			// only load REST API routes when really needed
			$this->LoadRouters();
		}
		// don't load obsolete v0.2 REST API routes
		//include('APIBootstrap02.php');
		//LoadRouters02($this);
		$front = $this->getResource('FrontController');
		$front->getRouter()->addRoute("hashnavigation", new Zend_Controller_Router_Route(':head/*',array(
            "controller" => "index",
            "action" => "index"
        ),array("head"=>"store|browse|pages|mp")));
		$front->getRouter()->addRoute("vmimage_contextscript", new Zend_Controller_Router_Route('store/vmi/:guid/script',array(
            "controller" => "Storage",
            "action" => "vmi"
        )));
		$front->getRouter()->addRoute("swappliance_contextscript", new Zend_Controller_Router_Route('store/swapp/:associationid/script',array(
            "controller" => "Storage",
            "action" => "swapp"
        ),array('associationid' => '\d+')));
		$front->getRouter()->addRoute("vmimage", new Zend_Controller_Router_Route('store/vm/image/:guid/:format',array(
            "controller" => "apps",
            "action" => "vappimage"
        ),array("format" => "xml|json")));
		$front->getRouter()->addRoute("voimage", new Zend_Controller_Router_Route('store/vo/image/:guid/:format',array(
            "controller" => "vo",
            "action" => "voimage"
        ),array("format" => "xml|json")));
		$front->getRouter()->addRoute("entitydescriptor_edugain", new Zend_Controller_Router_Route('edugain-sp',array(
            "controller" => "saml",
            "action" => "entitydescriptor",
			"source" => "edugain-sp"
        )));
		$front->getRouter()->addRoute("entitydescriptor_edugain_connect", new Zend_Controller_Router_Route('edugain-connect',array(
            "controller" => "saml",
            "action" => "entitydescriptor",
			"source" => "edugain-connect"
        )));
		$front->getRouter()->addRoute("storage_store2", new Zend_Controller_Router_Route('storage/:group/:folder/:folder2/:filename',array(
            "controller" => "Storage",
            "action" => "group"
        )));
		$front->getRouter()->addRoute("storage_store", new Zend_Controller_Router_Route('storage/:group/:folder/:filename',array(
            "controller" => "Storage",
            "action" => "group"
        )));
		$front->getRouter()->addRoute("storage_drafts", new Zend_Controller_Router_Route('storage/drafts/:folder/:filename',array(
            "controller" => "Storage",
            "action" => "drafts"
        )));
    }

    protected function _initRequest()
	{
        $this->bootstrap('FrontController');
        $front = $this->getResource('FrontController');
		$front->addModuleDirectory(APPLICATION_PATH . '/modules');
        $request = $front->getRequest();
        if (null === $front->getRequest()) {
            $request = new Zend_Controller_Request_Http();
            $front->setRequest($request);
		}
		
		date_default_timezone_set('Europe/Athens');
		$https = false;
		if (array_key_exists('HTTPS',$_SERVER)) {
			if ($_SERVER['HTTPS'] != '') {
				$https = true;
			}
		}
//		DO NOT RELOCATE FROM API/REST CALLS, LEST THE SESSION BE CLEARED.
		if($_SERVER['HTTP_HOST']=='appdb-pi.egi.eu' && $_SERVER['REQUEST_URI']== '/'){
			header('Location: https://appdb.egi.eu');
			exit();
		}
        if($this->isBot()===false && (strpos($_SERVER['REQUEST_URI'],'/news/atom') !== false || strpos($_SERVER['REQUEST_URI'],'/news/rss') !== false || strpos($_SERVER['REQUEST_URI'],'/news/mail') !== false )){
			if(!$https){
                header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
                exit();
            }
        } else if ( strpos($_SERVER['REQUEST_URI'],'/rest/') === false) {
			$session = new Zend_Session_Namespace('default');
			if ($session->userid !== null) {
				if (!$https) {
					header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
                    exit();
				}
			}
		}
		
		require_once('Zend/Config/Ini.php');
		$conf = new Zend_Config_Ini(__DIR__ . '/configs/application.ini', $_SERVER['APPLICATION_ENV']);
		$appconf = $conf->app;
		if( $this->setupFragment($https,$appconf->useHash) ){
			exit;
		}
		if( $this->setupHash($https,$appconf->useHash) ){
			exit;
		}
		header('Access-Control-Allow-Origin: *');
        return $request;        
    }   

	protected function _initSession()
	{
//		DO NOT CALL "Zend_Session::start()" FROM API/REST CALLS, LEST THE SESSION BE CLEARED
//		IS THIS REALLY NEEDED? EVERYTHING SEEMS OK WITHOUT IT

		if ( strpos($_SERVER['REQUEST_URI'],'/rest/') === false ) {
			Zend_Session::start();
		}
	}
	
  	protected function _initDoctype()
    {
		$this->bootstrap('view');
        $view = $this->getResource('view');
        $view->doctype('XHTML1_STRICT');
        $view->addHelperPath('Zend/Dojo/View/Helper/', 'Zend_Dojo_View_Helper');
        $view->addHelperPath('ZendX/JQuery/View/Helper', 'ZendX_JQuery_View_Helper');
        Zend_Dojo::enableView($view);
        $view->dojo()->setDjConfigOption('usePlainJson',true)
            	     ->setDjConfigOption('parseOnLoad', true)
            	     ->setDjConfigOption('parseWidgets', true)
        			 ->addStylesheetModule('dijit.themes.tundra')
        			 ->setLocalPath('/js/dojo/dojo.js')
        			 ->disable();
        include APPLICATION_PATH."/../library/globals.php";
        ini_set('display_errors','off'); 
        ini_set('log_error','on'); 
    }
    
  	private function properCase($s) {
        $s = strtolower($s);
        return substr_replace($s, strtoupper(substr($s, 0, 1)), 0, 1);
    }
	
	protected function _initAutoload()
    {
        $front = $this->bootstrap("frontController")->frontController;
        $modules = $front->getControllerDirectory();
        $default = $front->getDefaultModule();
        $mal=array();
        foreach (array_keys($modules) as $module) {
            $t =  $front->getModuleDirectory($module);
            $moduleloader = new Zend_Application_Module_Autoloader(array(
                'namespace' => $this->properCase($module)."_",
                'basePath'  => $front->getModuleDirectory($module)));
            $mal[]=$moduleloader;
        }
        return $mal;
    }
	
	
	public function run(){
		$resource = $this->getPluginResource('multidb');
		$resource->init();
		Zend_Registry::set('repository', $resource->getDb('repository'));
		
		$db = $this->getPluginResource('db');
		$db->init();
		$db = $db->getDbAdapter();
		Zend_Registry::set("db", $db);
		
		parent::run();
	}

	private function LoadRouters() {
        $front = $this->getResource('FrontController');
        $apiRoutes = array();

        $fname = APPLICATION_PATH . "/apiroutes.xml";
        $f = fopen($fname,"r");
        $xml = fread($f, filesize($fname));
        fclose($f);
        $xml = new SimpleXMLElement($xml);
        $routes = $xml->xpath('//route[@type="rest"]');
        foreach ($routes as $route) {
            $attrs = $route->attributes();
            $disabled = false;
            if (isset($attrs["disabled"]) && $attrs["disabled"] == "true") $disabled = true;
            if ( ! $disabled ) {
                $routeOpts = array();
                $routeOpts["resource"] = "".strval($route->resource);
                $routeOpts["controller"] = "api";
                $routeOpts["action"] = "rest";
                if ( isset($route->format) ) {
					if ( is_array($route->format) ) {
						$format = $route->format;
					} else {
						$format = array();
						$format[] = $route->format;
					}
					foreach($format as $f) {
						if ( strval($f) == "xml" ) {
							$routeOpts["format"] = strval($f);
							if ( isset($f->attributes()->xslt) ) $routeOpts["routeXslt"] = strval($f->attributes()->xslt);
						}
					}
                }
                $routePars = array();            
                if ( isset($route->param) ) {
					if ( is_array($route->param) ) {
						$param = $route->param;
					} else {
						$param = array();
						$param[] = $route->param;
					}
                    foreach($param as $p) {
                        $routePars["".strval($p->attributes()->name)] = strval($p->attributes()->fmt);
                    }
                }
                $apiRoutes["".$attrs["name"]] = new Zend_Controller_Router_Route("/".$attrs["type"].$attrs["url"], $routeOpts, $routePars);
            }
        }
        $apiRoutes["defaulttolatest"] = new Zend_Controller_Router_Route('/rest/',array(
            "controller" => "api",
            "action" => "latest"
        ));
        $apiRoutes["defaulttoresources"] = new Zend_Controller_Router_Route('/rest/:version/',array(
            "controller" => "api",
            "action" => "resources"
        ));
        $apiRoutes["latest"] = new Zend_Controller_Router_Route('/rest/latest/*',array(
            "controller" => "api",
            "action" => "latest"
        ));
        foreach($apiRoutes as $rk=>$rv){
           $front->getRouter()->addRoute($rk,$rv);
		}

    }
	
	public function setupHash($https,$useHash){
		if($this->isBot()){
			return false;
		}
		$b = @get_browser(null,true);
		$ruri = (isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:"");
		if( $useHash === 'true' || ($b["browser"] === 'IE' && $b["majorver"] <= 9 ) ){
			$proto = (($https==true)?"https://":"http://");
			if( $ruri!=="" && in_array(substr($ruri,1, 5), array("store","brows","pages", "mp"))){
				header('Location: ' . $proto . $_SERVER['HTTP_HOST'] . "/#" . $ruri);
				exit();
				//return true;
			}
		}
		return false;
	}
	public function isBot(){
	 if(isset($_SERVER['HTTP_USER_AGENT'])){
	  $bot = $_SERVER['HTTP_USER_AGENT'];
	  $bot = strtolower($bot);
	  if ((strpos($bot,"googlebot")>-1) || (strpos($bot,"msnbot")>-1)) return true; else return false;
	 }
	 return false;
	}
	
	public function setupFragment($isSecure=false,$useHash='true'){
	 if($useHash==='false'){
		 $b = @get_browser(null,true);
		 if( !( $b["browser"] === 'IE' && $b["majorver"] <= 9 ) ){
			 return false;
		 }
	 }
	 if( isset($_GET["p"]) == false || trim($_GET["p"])=="" || $this->isBot()){
	  return false;
	 }
	 //Check if request is MVC. In this case it should have a controller and an action, 
	 //e.g. http://appdb.egi.eu/people/authorizerole
	 $ruri = (isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:"");
	 $ruri = explode($ruri,"?");
	 $ruri = ($ruri[0]=="?")?"":$ruri[0];
	 $expl = explode("/",$ruri);
	 if(count($expl) > 2 ){
		 return false;
	 }
	 $p = "";
	 $prot = ($isSecure)?"https://":"http://";
	 if(isset($_GET["p"]) && trim($_GET["p"])!=="" && true){
	  $u = parse_url($prot.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	  $q = $u["query"];
	  
	  $qf = explode("&",$q);
	  $qf2 = array();
	  foreach($qf as $qfi) {
		$qff = explode("=",$qfi);
		if (count($qff)>1) {
			if ($qff[0] != "p") {
				$qf2[] = $qff[0]."=".$qff[1];
			} else {
				$p = $qff[1];
			}
		}
	  }
	  $q = implode("&", $qf2);
	  if( strlen($q) === 0 || $q === "?"){
		  $q = "";
	  }else{
		  $q = "?" . $q;
	  }
	  if ($p !== "") {
		  $q = $q."#p=$p";
		  
	  }
	  header('Location: ' . $prot . $_SERVER['HTTP_HOST'] . "/" . $q);
	  exit();
	  return true;
	 }
	 return false;
	}


	protected function _initFrontControllerPlugins() {
		$this->bootstrap('FrontController');
		$fc = $this->getResource('FrontController');
		$pluginPageRouter = new PageRouter();
		$fc->registerPlugin($pluginPageRouter);    
	}
}
