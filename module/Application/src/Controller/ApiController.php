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

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Mvc\MvcEvent;

class ApiController extends AbstractActionController
{
    private $entry;
    private $apiver;
    private $handle;
    private $latest;

	public function __construct() {
		$this->view = new ViewModel();
		$this->session = new \Zend\Session\Container('base');
		$this->verb = strtolower($this->getRequest()->getMethod());
		if ($this->verb == "get") {
			if ($this->session->getManager()->getStorage()->isLocked()) {
				$this->session->getManager()->getStorage()->unLock();
			}
			session_write_close();
		}
//		if ( isset($_GET["format"]) ) {	
//			$this->_setParam("format", $_GET["format"]);
//		}
		switch(strtolower($this->getRequest()->getMethod())) {
			case "post":
				$this->pars = $_POST;
				break;
			default:
				$this->pars = $_GET;
		}	
		$this->latest = $_SERVER['APILatestVersion']; //ApplicationConfiguration::api('latestVersion'); 
		$this->apiver = $this->latest;
//		if ( ! isset($this->pars["version"]) ) $this->pars["version"] = $this->latest;
//        $this->apiver = $this->pars["version"];
		header('Cache-control: no-cache');
//		if ( isset($this->pars["format"]) && ($this->pars["format"] === "json") ) {
//	        header('Content-type: application/json');			# NOTE: Content-type is already set to 'application/xml' by Zend
//		} else {
	        header('Content-type: text/xml');			# NOTE: Content-type is already set to 'application/xml' by Zend
			        									# due to implied 'format=xml' in the query string, and this may have no effect
//		}
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Request-Method: GET POST PUT DELETE OPTIONS');
		$this->handle = true;
		error_log($this->apiver);
        switch ($this->apiver) {
//        case "0.2":
//            $this->handle = false;
//            require_once($_SERVER['APPLICATION_PATH'] . '/controllers/Api02Controller.php');
//            $c = new Api02Controller($this->getRequest(), $this->getResponse(), $this->getInvokeArgs(), $this->pars);
//            break;
        case "1.0":
            break;
        default:
			header('HTTP/1.0 400 Bad Request');
            exit;
        }
	}

	public function onDispatch(MvcEvent $e) {
		$this->pars = array_merge($this->pars, $this->params()->fromRoute());
		$this->pars = array_merge($this->pars, $this->params()->fromQuery());
		return parent::onDispatch($e);
	}

//	public function setEventManager(\Zend\EventManager\EventManagerInterface $events)
//    {
//        parent::setEventManager($events);
//		$this->pars = $this->params()->fromRoute('resource', 'aaa');
//		error_log(var_export($this->pars, true));
//	}
		

    private function handleResource($res) {
		$ok = true;
		if ( class_exists($res) ) {
            $r = new $res($this->pars);
            $r->startLogging($_SERVER['APPLICATION_PATH'] .'/appdbapilog.xml');
		} else $ok = false;
		if ( $ok ) {
			$method = strtolower($_SERVER['REQUEST_METHOD']);
			error_log("METHOD: $method");
            if ( method_exists($r, $method) ) {
				$this->entry = $r->$method();
            } else {
                $this->entry = $r->unknown();
            }
            if ( $this->entry === false ) $this->entry = new XMLFragmentRestResponse("", $r);
			$this->Error = \RestErrorEnum::toString($r->error);
			if ( $r->error != "" && $r->extError != "" ) $this->Error = $this->Error.". ".$r->extError;
			$this->total = $r->total;
			$this->dataType = $r->dataType;
			$this->length = $r->pageLength;
			$this->offset = $r->pageOffset;
			$this->authenticated = $r->authenticate();
		} else {
			$this->Error = \RestErrorEnum::toString(\RestErrorEnum::RE_INVALID_RESOURCE);
			$this->total = -1;
            $this->entry = \RestAPIHelper::wrapResponse("", null, null, $this->total, null, null, \RestErrorEnum::RE_INVALID_RESOURCE, null);
			$this->authenticated = false;
			header('HTTP/1.0 400 Bad Request');
		}
    }

	public function restAction() {
		error_log("REST ACTION");
        if ( ! $this->handle ) {
			return SET_NO_RENDER($this);
		}
        $this->handleResource($this->pars["resource"]);
        if ( is_object($this->entry) && $this->entry->getFormat() === "xml" && $this->entry->isFragment() === true) $this->entry = $this->entry->finalize();
        if ( is_object($this->entry) && $this->entry->getFormat() === "xml" ) {
			$routeXslt = null;
			$routeOpts = $this->params()->fromRoute();
			if (array_key_exists("formats", $routeOpts)) {
				$routeFmts = $routeOpts["formats"];
				foreach ($routeFmts as $f) {
					if ($f["format"] == $this->entry->getFormat()) {
						if (array_key_exists("xslt", $f)) {
							$routeXslt = strval($f["xslt"]);
						}
						break;
					}
				}
			}
			if ( isset($routeXslt) ) {
				$routeXslt = explode("/", $routeXslt);
				foreach($routeXslt as $xslt) {
					if (trim($xslt) != "") {
						$this->entry = $this->entry->transform(\RestAPIHelper::getFolder(\RestFolderEnum::FE_XSL_FOLDER) . $xslt);
					}
				}
			}
        }
		$ret = strval($this->entry);

		if (isset($_GET['format'])) {
			if ((trim($_GET['format']) === 'js') || (trim($_GET['format']) === 'json')) {
				echo \RestAPIHelper::transformXMLtoJSON($ret);
				return SET_NO_RENDER($this);;
			}
		}
		echo $ret;
		return DISABLE_LAYOUT($this);
    }

	public function redirectAction() {
		header('Location: ' . "http://".$this->params()->fromQuery("url"));
		return DISABLE_LAYOUT($this, true);
    }

	public function latestAction() {
		$uri = preg_replace('/\/rest\/*(latest){0,1}\//', '/rest/' . $this->latest . '/', $_SERVER['REQUEST_URI']);
		if ($uri === "") {
			header('Location: https://' . $_SERVER['APPLICATION_API_HOSTNAME'] . '/rest/latest/resources');
		} else {
			header('Location: ' . $uri);
		}
//		OLD CODE: use proxy instead of re-writting header -- this did not honor cached XML responses via wget for some reason
//		NOTE: new code might break clients that do not honor redirections
//		$proxy = new AppDBRESTProxy($this->latest);
//		$data = array();
//		$act = $this->getRequest()->getMethod();
//		if ($act === "POST") $data['data'] = $_POST['data'];
//		$uri = preg_replace('/.*\/rest\/*(latest){0,1}\/*/', '', $_SERVER['REQUEST_URI']);
//		//if ($uri === "") $uri .= 'schema';
//		if ($uri === "") {
//			header('Location: https://' . $_SERVER['$_SERVER['APPLICATION_API_HOSTNAME']'] . '/rest/latest/resources');
//		} else {
//			$proxy->request($uri, $act, $data);
//		}
	}

    public function schemaAction() {
        $url = $_SERVER['REQUEST_URI'];
        if ( substr($url,-1,1) !== "/" ) $url = $url."/";
        $proto = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === "on" ? "https://" : "http://";
        header('Location: ' . $proto . $_SERVER['HTTP_HOST'] . $url . "schema");
    }

    public function resourcesAction() {
        $url = $_SERVER['REQUEST_URI'];
        if ( substr($url,-1,1) !== "/" ) $url = $url."/";
        $proto = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === "on" ? "https://" : "http://";
        header('Location: ' . $proto . $_SERVER['HTTP_HOST'] . $url . "resources");
    }

	public function proxyAction() {
		$this->newproxy();
		return DISABLE_LAYOUT($this, true);
	}

	public function newproxy() {
		// optionally run custom server initialization code
		if (file_exists($_SERVER['APPLICATION_PATH'] . "/config/api_proxy_init.php")) {
			require_once($_SERVER['APPLICATION_PATH'] . "/config/api_proxy_init.php");
		}
		if (function_exists("appdb_api_proxy_init")) {
			$func = "appdb_api_proxy_init";
			$ret = $func($this);
			if ($ret === false) {
				return;
			}
		}

		$apiroutes = new \SimpleXMLElement($_SERVER['APPLICATION_PATH'] . "/config/apiroutes.xml", 0, true);
		$pars = array();
		$postdata = null;
		$method = strtolower($this->getRequest()->getMethod());
		$error = null;
		$extError = null;
		if ($method === "post") {
			$postdata = $_POST['data'];
			if( isset($_POST['resource']) && trim($_POST['resource']) === "broker") {
				if ($this->session->getManager()->getStorage()->isLocked()) {
					$this->session->getManager()->getStorage()->unLock();
				}
				session_write_close();
				$res = $_POST['resource'];
			} else {
				$res = $this->params()->fromQuery("resource");
			}
		} else {
			$res = $this->params()->fromQuery("resource");
		}
		$url = preg_replace('/\?.*/', '', $res);
		$qs = explode("&", preg_replace('/.*\?/', '', $res));
		$rx = \RestBroker::matchResource($url, $apiroutes, $pars);
		// validate resource type forproxy use
		if (! is_null($rx)) {
			if (! in_array($rx->attributes()->type, array("rest", "proxy"))) {
				$rx = null;
			}
		}
		if (is_null($rx)) {
			// FIXME: workaround for erroneous proxy resource notation (double URL-encoded)
			// FIXME: should be fixed at the source
			$res = urldecode($res);
			$url = preg_replace('/\?.*/', '', $res);
			$qs = explode("&", preg_replace('/.*\?/', '', $res));
			$rx = \RestBroker::matchResource($url, $apiroutes, $pars);
			// validate resource type forproxy use
			if (! in_array($rx->attributes()->type, array("rest", "proxy"))) {
				$rx = null;
			}
			if (! is_null($rx)) {
				// FIXME: workaround for erroneous people canonical URLs with query strings
				if (($rx->resource == "RestPplItem") && ($method == "get")) {
					$qs = null;
				}
			}
		} else {
			// FIXME: workaround for erroneous people canonical URLs with query strings
			if (($rx->resource == "RestPplItem") && ($method == "get")) {
				$qs = null;
			}
		}
		if (is_array($qs)) {
			foreach ($qs as $q) {
				$i = explode("=", $q);
				if (count($i) > 1) {
					$pars[$i[0]] = urldecode($i[1]);
				}
			}
		}
		if (! is_null($postdata)) {
			$pars['data'] = $postdata;
		}
		$routeXslt = null;
		switch(strtolower($method)) {
			case "get":
				$method = \RestMethodEnum::RM_GET;
				break;
			case "put":
				$method = \RestMethodEnum::RM_PUT;
				break;
			case "post":
				$method = \RestMethodEnum::RM_POST;
				break;
			case "delete":
				$method = \RestMethodEnum::RM_DELETE;
				break;
			case "options":
				$method = \RestMethodEnum::RM_OPTIONS;
				break;
			default:
				$method = \RestMethodEnum::RM_GET;
				break;
		}

		$ret = "";
		if ( ! is_null($rx) ) {
			try {
				$resclass = strval($rx->resource);
				$this->session = new \Zend\Session\Container('base');
				if ( isset($_SERVER['REMOTE_ADDR']) && ($_SERVER['REMOTE_ADDR'] != '') ) {
					$src = base64_encode($_SERVER['REMOTE_ADDR']);
				} else {
					$src = '';
				}
				$pars['src'] = $src;
				if ( isset($_SERVER['SERVER_ADDR']) && ($_SERVER['SERVER_ADDR'] != '') ) {
					$srv = base64_encode($_SERVER['SERVER_ADDR']);
				} else {
					$srv = '';
				}
				$pars['remoteaddr'] = $srv;
				$apikey = $userid = $passwd = '';
				if ( $this->session->userid !== null ) {
					$userid = $this->session->userid;
					if (isset($_COOKIE['SimpleSAMLAuthToken'])) {
						$passwd = $_COOKIE['SimpleSAMLAuthToken'];
					} else {
						error_log("Warning: auth token cookie ('SimpleSAMLAuthToken') is undefined!");
					}
					$apikey = \ApplicationConfiguration::api("key");
				}
				$pars['userid'] = $userid;
				$pars['passwd'] = $passwd;
				$pars['apikey'] = $apikey;
				$pars['sessionid'] = session_id();
				$pars['cid'] = 0;
				if ($userid != '') {
					$_GET['userid'] = $userid;
				}
				$res = new $resclass($pars);
				$fmt = $rx->xpath("format");
				if ( count($fmt) > 0 ) {
					foreach ( $fmt as $f ) {
						if ( strval($f) === "xml" ) {
							if ( strval($f->attributes()->xslt) != '' ) $routeXslt = strval($f->attributes()->xslt);
							break;
						}
					}
				}
			} catch (Exception $e) {
				$error = \RestErrorEnum::toString(\RestErrorEnum::RE_INVALID_REPRESENTATION);
				$extError = "Could not instantiate REST resource for request `" . $res . "'";
				$this->getResponse()->clearAllHeaders();
				$this->getResponse()->setRawHeader("HTTP/1.0 400 Bad Request");
				$this->getResponse()->setHeader("Status","400 Bad Request");
				if ($extError != "") {
					error_log($error . '\n' . $extError);
					echo \RestAPIHelper::wrapResponse("", null, null, 0, null, null, \RestErrorEnum::RE_INVALID_RESOURCE, $extError);
				} else {
					error_log($error);
					echo \RestAPIHelper::wrapResponse("", null, null, 0, null, null, \RestErrorEnum::RE_INVALID_RESOURCE, null);
				}
				return;
			}
		} else {
			$error = \RestErrorEnum::toString(\RestErrorEnum::RE_INVALID_REPRESENTATION);
			$extError = "Could not resolve REST resource for request `" . $res . "'";
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 400 Bad Request");
			$this->getResponse()->setHeader("Status","400 Bad Request");			
			if ($extError != "") {
				error_log($error . '\n' . $extError);
				echo \RestAPIHelper::wrapResponse("", null, null, 0, null, null, \RestErrorEnum::RE_INVALID_RESOURCE, $extError);

			} else {
				error_log($error);
				echo \RestAPIHelper::wrapResponse("", null, null, 0, null, null, \RestErrorEnum::RE_INVALID_RESOURCE, null);
			}
			return;
		}	
		$s_method = strtolower(\RestMethodEnum::toString($method));
		$res->startLogging($_SERVER['APPLICATION_PATH'] .'/appdbapilog.xml');
		$_res = $res->$s_method();
		if ( $_res !== false ) {
			if ( $_res->isFragment() ) {
				$res = $_res->finalize();
			} else {
				$res = $_res;
			}
			if ( ! is_null($routeXslt) ) $res = $res->transform(\RestAPIHelper::getFolder(\RestFolderEnum::FE_XSL_FOLDER).$routeXslt);
			echo $res;
		} else {
			$error = \RestErrorEnum::toString($res->getError());
			$extError = $res->getExtError();
			if ($extError != "") {
				error_log($error . '\n' . $extError);
				echo \RestAPIHelper::wrapResponse("", null, null, 0, null, null, $res->getError(), $extError);
			} else {
				error_log($error);
				echo \RestAPIHelper::wrapResponse("", null, null, 0, null, null, $res->getError(), null);
			}
		}
	}

	public function oldproxy() {
		$ver = $this->params()->fromQuery("version");
		if ((!isset($ver)) || (trim($ver) == "")) $ver = 'latest';
		$proxy = new AppDBRESTProxy($ver);
		$data = array();
		$act = $this->getRequest()->getMethod();
		if ($act === "POST") {
			$data['data'] = $_POST['data'];
			if( isset($_POST['resource']) && trim($_POST['resource']) === "broker"){
				if ($this->session->getManager()->getStorage()->isLocked()) {
					$this->session->getManager()->getStorage()->unLock();
				}
				session_write_close();
			}
		}
		$proxy->request($this->params()->fromQuery("resource"), $act, $data);
	}
}