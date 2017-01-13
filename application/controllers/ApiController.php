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

class ApiController extends Zend_Controller_Action
{
    private $entry;
    private $apiver;
    private $handle;
    private $latest;
	
	public function init() {
		$contextSwitch = $this->_helper->getHelper('contextSwitch');
    	$this->session = new Zend_Session_Namespace('default');
		if(trim($_SERVER['REQUEST_METHOD']) === "GET"){
			if ($this->session->isLocked()) {
				$this->session->unLock();
			}
			session_write_close();
		}
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
		$this->verb = strtolower($this->getRequest()->getMethod());
		if ( isset($_GET["format"]) ) {	
			$this->_setParam("format", $_GET["format"]);
		}
        $this->pars = $this->_getAllParams();
        $this->latest = Zend_Registry::get("api");
        $this->latest = $this->latest['latestVersion'];
		if ( ! isset($this->pars["version"]) ) $this->pars["version"] = $this->latest;
        $this->apiver = $this->pars["version"];
		header('Cache-control: no-cache');
		if ( isset($this->pars["format"]) && ($this->pars["format"] === "json") ) {
	        header('Content-type: application/json');			# NOTE: Content-type is already set to 'application/xml' by Zend
		} else {
	        header('Content-type: text/xml');			# NOTE: Content-type is already set to 'application/xml' by Zend
			        									# due to implied 'format=xml' in the query string, and this may have no effect
		}
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Request-Method: GET POST PUT DELETE OPTIONS');
        $this->handle = true;
        switch ($this->apiver) {
//        case "0.2":
//            $this->handle = false;
//            require_once(APPLICATION_PATH . '/controllers/Api02Controller.php');
//            $c = new Api02Controller($this->getRequest(), $this->getResponse(), $this->getInvokeArgs(), $this->pars);
//            break;
        case "1.0":
            break;
        default:
			header('HTTP/1.0 400 Bad Request');
            exit;
        }
    }

    private function handleResource($res) {
		$ok = true;
		if ( class_exists($res) ) {
            $r = new $res($this->pars);
            $r->startLogging(APPLICATION_PATH .'/appdbapilog.xml');
		} else $ok = false;
		if ( $ok ) {
            $method = strtolower($_SERVER['REQUEST_METHOD']);
            if ( method_exists($r, $method) ) {
                $this->entry = $r->$method();
            } else {
                $this->entry = $r->unknown();
            }
            if ( $this->entry === false ) $this->entry = new XMLFragmentRestResponse("", $r);
			$this->Error = RestErrorEnum::toString($r->error);
			if ( $r->error != "" && $r->extError != "" ) $this->Error = $this->Error.". ".$r->extError;
			$this->total = $r->total;
			$this->dataType = $r->dataType;
			$this->length = $r->pageLength;
			$this->offset = $r->pageOffset;
			$this->authenticated = $r->authenticate();
		} else {
			$this->Error = RestErrorEnum::toString(RestErrorEnum::RE_INVALID_RESOURCE);
			$this->total = -1;
            $this->entry = RestAPIHelper::wrapResponse("", null, null, $this->total, null, null, RestErrorEnum::RE_INVALID_RESOURCE, null);
			$this->authenticated = false;
			header('HTTP/1.0 400 Bad Request');
		}
    }

    public function restAction() {
        if ( ! $this->handle ) return;
        $this->handleResource($this->pars["resource"]);
        if ( is_object($this->entry) && $this->entry->getFormat() === "xml" && $this->entry->isFragment() === true) $this->entry = $this->entry->finalize();
        if ( is_object($this->entry) && $this->entry->getFormat() === "xml" ) {
            $routeXslt = strval($this->_getParam("routeXslt"));
	    	if ( isset($routeXslt) ) $this->entry = $this->entry->transform(RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER).$routeXslt);
        }
		$ret = strval($this->entry);

		if( isset($_GET['format']) && trim($_GET['format']) === 'js' ) {
			echo $this->transformXmlToJson($ret);
			return;
		}

		echo $ret;
    }

	private function transformXmlToJson( $x ) {
		$json = $x;
		$xslt_path1 = RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER) . 'xml2js_preprocess.xsl';
		$xslt_path2 = RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER) . 'xml2js.xsl';
		if( file_exists($xslt_path1) && file_exists($xslt_path2) ) {
			try{
				//convert all attributes to elements
				$xsl = new DOMDocument();
				$xsl->load($xslt_path1);
				$xml = new DOMDocument();
				$xml->loadXML($x, LIBXML_NSCLEAN | LIBXML_COMPACT);
				$proc = new XSLTProcessor();
				$proc->registerPHPFunctions();
				$proc->importStylesheet($xsl);
				$json = $proc->transformToXml( $xml );
				
				//convert all attributes to json
				$xsl = new DOMDocument();
				$xsl->load($xslt_path2);
				$xml = new DOMDocument();
				$xml->loadXML($json, LIBXML_NSCLEAN | LIBXML_COMPACT);
				$proc = new XSLTProcessor();
				$proc->registerPHPFunctions();
				$proc->importStylesheet($xsl);
				$json = $proc->transformToXml( $xml );
				header('Content-type: application/json');
			}catch( Exception $e) {
				error_log('[Api::transformXmlToJson]: ' . $e->getMessage());
				return $x;
			}
		}
		
		return $json;
	}
    public function redirectAction(){
        $this->_redirect("http://".$this->getRequest()->getParam("url"));
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
//			header('Location: https://' . $_SERVER['APPLICATION_API_HOSTNAME'] . '/rest/latest/resources');
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
	}

	public function newproxy() {
		$apiroutes = new SimpleXMLElement(APPLICATION_PATH . "/apiroutes.xml", 0, true);
		$pars = array();
		$postdata = null;
		$method = strtolower($this->getRequest()->getMethod());
		$error = null;
		$extError = null;
		if ($method === "post") {
			$postdata = $_POST['data'];
			if( isset($_POST['resource']) && trim($_POST['resource']) === "broker") {
				if ($this->session->isLocked()) {
					$this->session->unLock();
				}
				session_write_close();
				$res = $_POST['resource'];
			} else {
				$res = $this->_getParam("resource");
			}
		} else {
			$res = $this->_getParam("resource");
		}
		$url = preg_replace('/\?.*/', '', $res);
		$qs = explode("&", preg_replace('/.*\?/', '', $res));
		$rx = RestBroker::matchResource($url, $apiroutes, $pars);
		if (is_null($rx)) {
			// FIXME: workaround for erroneous proxy resource notation (double URL-encoded)
			// FIXME: should be fixed at the source
			$res = urldecode($res);
			$url = preg_replace('/\?.*/', '', $res);
			$qs = explode("&", preg_replace('/.*\?/', '', $res));
			$rx = RestBroker::matchResource($url, $apiroutes, $pars);
			if (! is_null($rx)) {
				// FIXME: workaround for erroneous people canonical URLs with query strings
				if (($rx->resource == "RestPplItem") && ($method = "get")) {
					$qs = null;
				}
			}
		} else {
			// FIXME: workaround for erroneous people canonical URLs with query strings
			if (($rx->resource == "RestPplItem") && ($method = "get")) {
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
				$method = RestMethodEnum::RM_GET;
				break;
			case "put":
				$method = RestMethodEnum::RM_PUT;
				break;
			case "post":
				$method = RestMethodEnum::RM_POST;
				break;
			case "delete":
				$method = RestMethodEnum::RM_DELETE;
				break;
			case "options":
				$method = RestMethodEnum::RM_OPTIONS;
				break;
			default:
				$method = RestMethodEnum::RM_GET;
				break;
		}

		$ret = "";
		if ( ! is_null($rx) ) {
			try {
				$resclass = strval($rx->resource);
				$this->session = new Zend_Session_Namespace('default');
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
					$apiconf = Zend_Registry::get("api");
					$apikey = $apiconf["key"];
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
				$error = RestErrorEnum::toString(RestErrorEnum::RE_INVALID_REPRESENTATION);
				$extError = "Could not instantiate REST resource for request `" . $res . "'";
				$this->getResponse()->clearAllHeaders();
				$this->getResponse()->setRawHeader("HTTP/1.0 400 Bad Request");
				$this->getResponse()->setHeader("Status","400 Bad Request");
				if ($extError != "") {
					error_log($error . '\n' . $extError);
					echo $error . '\n' . $extError;
				} else {
					error_log($error);
					echo $error;
				}
				return;
			}
		} else {
			$error = RestErrorEnum::toString(RestErrorEnum::RE_INVALID_REPRESENTATION);
			$extError = "Could not resolve REST resource for request `" . $res . "'";
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 400 Bad Request");
			$this->getResponse()->setHeader("Status","400 Bad Request");			
			if ($extError != "") {
				error_log($error . '\n' . $extError);
				echo $error . '\n' . $extError;
			} else {
				error_log($error);
				echo $error;
			}
			return;
		}	
		$s_method = strtolower(RestMethodEnum::toString($method));
		$_res = $res->$s_method();
		if ( $_res !== false ) {
			if ( $_res->isFragment() ) {
				$res = $_res->finalize();
			} else {
				$res = $_res;
			}
			if ( ! is_null($routeXslt) ) $res = $res->transform(RestAPIHelper::getFolder(RestFolderEnum::FE_XSL_FOLDER).$routeXslt);
			echo $res;
		} else {
			$error = RestErrorEnum::toString($res->getError());
			$extError = $res->getExtError();
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 400 Bad Request");
			$this->getResponse()->setHeader("Status","400 Bad Request");
			if ($extError != "") {
				error_log($error . '\n' . $extError);
				echo $error . '\n' . $extError;
			} else {
				error_log($error);
				echo $error;
			}
		}
	}

	public function oldproxy() {
		$ver = $this->_getParam("version");
		if ((!isset($ver)) || (trim($ver) == "")) $ver = 'latest';
		$proxy = new AppDBRESTProxy($ver);
		$data = array();
		$act = $this->getRequest()->getMethod();
		if ($act === "POST") {
			$data['data'] = $_POST['data'];
			if( isset($_POST['resource']) && trim($_POST['resource']) === "broker"){
				if ($this->session->isLocked()) {
					$this->session->unLock();
				}
				session_write_close();
			}
		}
		$proxy->request($this->_getParam("resource"), $act, $data);
	}
}
