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

class AaimpxController extends AbstractActionController
{
    private $_idp;
    private $_baseURL;
    private $_redirectURI;
    private $_uid="";
    private $_clientID;
    private $_clientSecret;
    private $_storagePath;
    private $_vomsesBasePath;
    private $_expirationThreshold;

	public function __construct() {
		$this->view = new ViewModel();
		$this->session = new \Zend\Session\Container('base');
		$this->_clientID= \ApplicationConfiguration::aaimp("client_id");
		$this->_clientSecret = \ApplicationConfiguration::aaimp("client_secret");
		$this->_idp = \ApplicationConfiguration::aaimp("idp");
		$this->_baseURL = \ApplicationConfiguration::aaimp("base_url");
		$this->_redirectURI = \ApplicationConfiguration::aaimp("redirect_uri");    
		$this->_storagePath = \ApplicationConfiguration::aaimp("storage_path");
		if ($this->_storagePath == "") {
			$this->_storagePath = "/tmp/certs";
		}
		$this->_vomsesBasePath = $_SERVER['APPLICATION_PATH'] . "/../public/assets/ui/vomses/";
		$this->_expirationThreshold = \ApplicationConfiguration::aaimp("expiration_threshold");
		if ($this->_expirationThreshold == "") {
			$this->_expirationThreshold = 1; // one hour
		}
	}

    public function indexAction() {
		$auth = \SamlAuth::isAuthenticated();
		if( ! $auth ) {
			$this->view->auth = false;
		} else {
			$this->view->auth = true;
			$this->view->done = false;

			if (isset($_GET['appdbvo']) && trim($_GET['appdbvo']) !='') {
					   $_SESSION['appdbvo'] = $_GET['appdbvo'];
			}/* else {
				$_SESSION['appdbvo']='none';
			}*/
			if ($this->isValidVO($_SESSION['appdbvo']) == false) {
				$this->view->error = 1001;
				$this->view->error_description = "invalid VO provided";
				return DISABLE_LAYOUT($this);
			}

			$this->_redirectURI = urlencode($this->_redirectURI);
			$samlattrs = $auth->getAttributes();
			if (! isset($samlattrs["idp:uid"][0]) || trim($samlattrs["idp:uid"][0]) == "" ) {
				$this->view->error = 1000;
				$this->view->error_description = "invalid UID provided";
				return DISABLE_LAYOUT($this);
			}
			$this->_uid = $samlattrs["idp:uid"][0];

			if (isset($samlattrs['idp:traceidp'])) {
				$numofelems = count($samlattrs['idp:traceidp']);
				if ($numofelems >= 2) {
					$this->_idp = $samlattrs['idp:traceidp'][($numofelems - 2)];
				} elseif ($numofelems == 1) {
					$this->_idp = $samlattrs['idp:traceidp'][0];
				}
			}

			if (isset($_GET['error'])) {
				$this->view->error = $_GET['error'];
			}
			if (isset($_GET['error_description'])) {
				$this->view->error_description = $_GET['error_description'];
			}

			if (isset($_GET['error'])) {
				return DISABLE_LAYOUT($this);
			}

			if(isset($_GET['init'])) {
				#echo "here is where we start......";
				return DISABLE_LAYOUT($this);
			} elseif (! isset($_GET['code'])) {
				/*if (isset($_GET['appdbvo']) && trim($_GET['appdbvo']) != '') {
					$_SESSION['appdbvo'] = $_GET['appdbvo'];
				} else {
					$_SESSION['appdbvo'] = false;
				} */
				// authorize request
				$url = $this->_baseURL . "/authorize";
				$fields = array(
					//'scope' => 'openid edu.uiuc.ncsa.myproxy.getcert',
					//'scope' => 'openid org.cilogon.userinfo edu.uiuc.ncsa.myproxy.getcert',
					'scope' => 'openid email profile org.cilogon.userinfo edu.uiuc.ncsa.myproxy.getcert',
					'response_type' => 'code',
					'client_id' => $this->_clientID,
					'redirect_uri' => $this->_redirectURI,
					'state' => hash('sha256', session_id())
				);
				// Add specific IdP hint: this will bypass the WAYF
				$fields['idphint'] = urlencode($this->_idp);
		
				//url-ify the data for the POST
				$fields_string="";
				foreach ($fields as $key => $value) {
					$fields_string .= $key .'='. $value .'&';
				}
				rtrim($fields_string, '&');
		
				// Redirect
				header("Location: " . $url ."?". $fields_string);
			} else {
				$url = $this->_baseURL . "/token";
				$fields = array(
					'grant_type' => 'authorization_code',
					'code' => urlencode($_GET['code']),
					'redirect_uri' => $this->_redirectURI,
					'client_id' => "$this->_clientID",
					'client_secret' => "$this->_clientSecret"
				);
				$status_code = $this->do_curl($url, $fields, $response, $error);
				
				if($this->parse_curl_status("token", $status_code, $response, $error) === false) {
					return DISABLE_LAYOUT($this);
				}
				// Decoded response
				$values = json_decode($response, true);
					
				// Get access token (and ID Token)
				$access_token = $values['access_token'];
				
				if (! isset($access_token)) {
					$this->view->error = 1000;				
					$this->view->error_description .= "Cannot find token in response<br>";
					$this->view->error_description .= "response=".$this->sanitize($response)."<br>";
					$this->view->error_description .= "url=".$url."<br>";
					$this->view->error_description .= "status_code=".$status_code."<br>";
					$this->view->error_description .= "fields=";
					$this->view->error_description .= $this->sanitize(print_r($fields, true));
					$this->view->error_description .= "<br>";
					return DISABLE_LAYOUT($this);
				}

				//$id_token=$values['id_token'];
				//$this->print_token_response($values);

				// getproxy request: either with or without VOMS extensions
				$url = $this->_baseURL . "/getproxy";
				$fields = array(
					'client_id' => "$this->_clientID",
					'client_secret' => "$this->_clientSecret",
					'access_token' => urlencode($access_token),
					//'proxylifetime' => 86399,
				);
			
				// Add voms request parameters when needed
				if ($_SESSION['appdbvo'] !== false)    {
					$fields['voname'] = trim($_SESSION['appdbvo']);
					// make a query and get the correponding vomses
					$fields['vomses'] = $this->getVomses(trim($fields['voname']));
					//$fields['vomses'] = '"fedcloud.egi.eu" "voms1.grid.cesnet.cz" "15002" "/DC=org/DC=terena/DC=tcs/OU=Domain Control Validated/CN=voms1.grid.cesnet.cz" "fedcloud.egi.eu" "24"';
				}

				$status_code = $this->do_curl($url, $fields, $response, $error);

				if ($this->parse_curl_status("proxy", $status_code, $response, $error) === false) {
					return DISABLE_LAYOUT($this);
				}
				$this->store_proxy("fs", $response, $this->_uid, $_SESSION['appdbvo']);
				$this->view->done = true;
			}
		}
		return DISABLE_LAYOUT($this);			
	}
    
	public function getproxyAction(){
		$auth = \SamlAuth::isAuthenticated();
		if( $auth === false ) {
			$this->view->auth = false;
			$this->view->type = 'error';
			$this->view->msg = 'not authenticated';
			return DISABLE_LAYOUT($this);
		} else {	
			$this->view->auth = true;
			$samlattrs = $auth->getAttributes();
			$this->_uid = $samlattrs["idp:uid"][0];
			$path = $this->_storagePath;
			$vo = false;
			if (isset($_GET['appdbvo']) && trim($_GET['appdbvo']) !='') {
				$vo=$_GET['appdbvo'];
			} else {
				$this->view->type = 'error';
				$this->view->msg = 'no vo provided';
				return DISABLE_LAYOUT($this);
			}
			$path .= "/".$vo;
			$proxy = $path . '/x509up_u' . $this->_uid;
			if (!file_exists($proxy) || !file_exists($proxy."_meta")) {
				// return notice not valid proxy
				$this->view->type = 'notice';
				$this->view->msg = 'not valid proxy - proxy file does not exist';
				return DISABLE_LAYOUT($this);
			}

			$proxy_meta = parse_ini_file($proxy."_meta");
			if ($proxy_meta['uid'] !== $this->_uid){
				$this->view->type = 'error';
				$this->view->msg = 'UID does not match';
				return DISABLE_LAYOUT($this);
			}

			if($proxy_meta['valid_to'] - time() < ($this->_expirationThreshold * 60 * 60)) {
				$this->view->type = 'notice';
				$this->view->msg = 'not valid proxy - proxy has been expired';
				return DISABLE_LAYOUT($this);
			}
			if(time() < $proxy_meta['valid_from']) {
				$this->view->type = 'notice';
				$this->view->msg = 'not valid proxy - proxy is not valid yet!!';
				return DISABLE_LAYOUT($this);
			}

			$this->view->type = 'ok';
			$this->view->msg = 'success';
			$this->view->cert = file_get_contents($proxy);
		}
		return DISABLE_LAYOUT($this);
	}


	private function isValidVO($vo){
		$path = $this->_vomsesBasePath . $vo . ".*";
		return glob($path);
	}

	private function getVomses($vo)  {
		$path = $this->_vomsesBasePath . $vo . ".*";	
		$files = glob($path);

		$vomses='';
		foreach ($files as $file) {
			if (file_exists($file)) {
				$vomses .= trim(file_get_contents($file)) . '\n';
			}
		}

		$vomses = rtrim($vomses, '\n');
		return ''. $vomses . ''; 
	}


	 private function do_curl($url, $fields, &$response, &$error)  {
		//url-ify the data for the POST
		$fields_string = "";
		foreach ($fields as $key=>$value) {
			$fields_string .= $key.'='.$value.'&';
		}
		rtrim($fields_string, '&');

		// open connection
		$ch = curl_init();

		// set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

		curl_setopt($ch,CURLOPT_HEADER, false);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION, false);

		// force IPv4 resolution
		// curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

		// next lines are optional, to give cURL debug output in file
		$curl_log = fopen("/tmp/curl_demo_stderr.log", "a");
		curl_setopt($ch,CURLOPT_STDERR, $curl_log);
		curl_setopt($ch,CURLOPT_VERBOSE, true);

		// execute post
		$response = curl_exec($ch);
		$status_code = "";
		$error = "";
		if (empty($response)) {
			// probably connection error
			$error = curl_error($ch);
		} else {
			$status_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
			$info = curl_getinfo($ch);
		}

		// close connection
		curl_close($ch);
		return $status_code;
	}

	// Parses curl status code and prints error when available
	private function parse_curl_status($type, $status_code, $response, $error) {
		if ($status_code >= 300 || !empty($error)) {
			$this->view->error=$status_code;
		        $this->view->error_description .= "Error obtaining $type <br>";
			if ($error) {
				$this->view->error_description .= "CURL error: ".$this->sanitize($error)."<br>";
			} else {
				$this->view->error_description .= "Status code: ".$status_code."<br>";
				// We might get a error= and error_description= back
				if (strpos($response, 'error_description=') !== false) {
					$err=parse_ini_string($response);
					if (isset($err['error']))
						$this->view->error_description .= $this->sanitize($err['error'])."<br>";
					if (isset($err['error_description']))
						$this->view->error_description .= "Description: ".$this->sanitize(urldecode($err['error_description']))."<br>";
				} else {
					$this->view->error_description .= "Response:".$this->sanitize($response)."<br>";
				}
			}
			return false;
		}
		return true;
	}

	// Prints content of the (json parsed) token response
	private function print_token_response($values) {
		print("<h1>First cURL response (/token request):</h1>\n");
		// Decoded response
		print("<h2>Parsed response:</h2>\n<pre>\n");
		print($this->sanitize(print_r($values, true)));
		print("</pre>\n");
		// Decoded ID token
		print("<h2>Parsed ID Token:</h2>\n<pre>\n");
		foreach (explode(".", $values['id_token']) as $block) {
			$subblock=json_decode(base64_decode($block));
			print($this->sanitize(print_r($subblock, true)));
		}
		print("</pre>\n");
	}


	private function store_proxy($datastore, $response, $uid, $vo){
		if ($datastore === 'fs') {
			$path = $this->_storagePath;
			if (!isset($vo) || $vo === false || trim($vo) == "") {
				$path .= "/novo";
			} else {
				$path .= "/".$vo;
			}

			if (!file_exists($path)) {
				mkdir($path, 0777, true);
			}
			$proxy = $path . '/x509up_u' . $uid;
			file_put_contents($proxy, $response);

			$certItems = openssl_x509_parse($response);
		
			$proxy_meta=$proxy."_meta";	
			file_put_contents($proxy_meta,"[proxy]\n");
			file_put_contents($proxy_meta,"file_name=\"".$proxy."\"\n", FILE_APPEND);
			file_put_contents($proxy_meta,"uid=\"".$uid."\"\n", FILE_APPEND);
			file_put_contents($proxy_meta,"dn=\"".$certItems['name']."\"\n", FILE_APPEND);
			file_put_contents($proxy_meta,"valid_from=".$certItems['validFrom_time_t']."\n",FILE_APPEND);
			file_put_contents($proxy_meta,"valid_to=".$certItems['validTo_time_t']."\n",FILE_APPEND);

		} elseif ($datastore === 'db') {
			// TODO
		}
	}

	private function sanitize($input)    {
		return htmlspecialchars($input, ENT_QUOTES, "UTF-8");
   	}
}