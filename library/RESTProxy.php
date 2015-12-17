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

class RESTProxy {
	protected $baseURI;
	protected $_requestTime;

	public function __construct($baseURI) {
		$this->_requestTime = microtime(true);
		$this->baseURI = $baseURI;
	}

	public function get($resource, $data = array(), $immediate = true, $compression = "gzip") {
		$this->request($resource, "GET", $data, $immediate, $compression);
	}

	public function put($resource, $data = array(), $immediate = true, $compression = "gzip") {
		$this->request($resource, "PUT", $data, $immediate, $compression);
	}

	public function post($resource, $data = array(), $immediate = true, $compression = "gzip") {
		$this->request($resource, "POST", $data, $immediate, $compression);
	}

	public function del($resource, $data = array(), $immediate = true, $compression = "gzip") {
		$this->request($resource, "DELETE", $data, $immediate, $compression);
	}

	public function options($resource, $data = array(), $immediate = true, $compression = "gzip") {
		$this->request($resource, "OPTIONS", $data, $immediate, $compression);
	}

	public function onError($errorDesc) {
		echo $errorDesc;
	}

	public function request($resource, $method = "GET", $data = array(), $immediate = true, $compression = "gzip") {
		$uri = $resource;
        $uri2 = explode('?',$uri);
        if ( count($uri2) > 1 ) {
            $uri = $uri2[0];
            $uri2 = explode('&',$uri2[1]);
            for($i=0; $i<count($uri2); $i++) {
                $uu = explode('=', $uri2[$i], 2);
                if (count($uu) > 1) {
                    $uri2[$i] = $uu[0] . '=' . (urlencode(urldecode($uu[1])));
                } else {
                    $uri2[$i] = urlencode($uri2[$i]);
                }
            }
            if ( count($uri2) > 0 ) $uri = $uri . '?' . implode("&",$uri2);
        }
        $act = strtoupper($method);
		if ( $act != "POST" ) {
			if ( strpos($uri, '?') !== false ) {
				$uri = $uri . '&';
			} else {
				$uri = $uri . '?';
			}
			$data2 = array();
			foreach ($data as $datak => $datav) {
				$data2[] = "${datak}=${datav}";
			}
			$uri .= implode("&", $data2);
		}
        $uri = $this->baseURI . '/' . $uri;
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		if ( defined('CURLOPT_PROTOCOLS') ) curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        
		$headers = apache_request_headers();
		$h = array();
		$h["Expect"] = '';
		if ( isset($headers['Accept-Encoding']) ) $h['Accept-Encoding'] = $headers['Accept-Encoding'];
		foreach($h as $k => $v) {
			$h[] = "$k: $v";
		}
		$h['Connection']='Keep-Alive';
		$h['Keep-Alive']='300';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $h);
		switch($act) {
        case "GET":
            break;
        case "PUT":
            curl_setopt($ch, CURLOPT_PUT, true);
			$putstream = fopen('php://input','r');
            curl_setopt($ch, CURLOPT_INFILE, $putstream);
            break;
        case "POST":
			curl_setopt($ch, CURLOPT_POSTREDIR, 2); // PHP cURL bug: https://bugs.php.net/bug.php?id=49571
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            break;
        case "DELETE":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
        case "OPTIONS":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
            break;
		}
		if ( ($compression != null) && ($compression != "") ) {
			curl_setopt($ch, CURLOPT_ENCODING, $compression); 
		}
        $result = curl_exec($ch);
		if ( $result === false ) {
			$result = curl_error($ch);
			if ($immediate) $this->onError($result);
		}
		if ( isset($putstream) ) fclose($putstream);
		curl_close($ch);
		$endTime = microtime(true);
		$procTime = $endTime - $this->_requestTime;
		$result = str_replace('processingTime=', 'proxyStart="' . sprintf("%.3f", $this->_requestTime) . '" proxyEnd="' . sprintf("%.3f", $endTime) . '" proxyTime="' . sprintf("%.3f", $procTime) . '" processingTime=', $result);
		if ($immediate) {
			echo $result;
		} else {
			return $result;
		}
	}
}


class AppDBRESTProxy extends RESTProxy {
	private $_reqData = array();
	private $session;

	public function __construct($version = 'latest') {
		parent::__construct('https://' . $_SERVER['APPLICATION_API_HOSTNAME'] . '/rest/' . $version);
		$this->session = new Zend_Session_Namespace('default');
		if ( isset($_SERVER['REMOTE_ADDR']) && ($_SERVER['REMOTE_ADDR'] != '') ) {
			$src = base64_encode($_SERVER['REMOTE_ADDR']);
		} else {
			$src = '';
		}
		$this->_reqData['src'] = $src;
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
		$this->_reqData['userid'] = $userid;
		$this->_reqData['passwd'] = $passwd;
		$this->_reqData['apikey'] = $apikey;
		$this->_reqData['sessionid'] = session_id();
		$this->_reqData['cid'] = 0;
	}

	public function request($resource, $method = "GET", $data = array(), $immediate = true, $compression = "gzip") {
		if (!is_array($data)) $data = array();
		$data = array_merge($this->_reqData, $data);
		return parent::request($resource, $method, $data, $immediate, $compression);
	}

	public function onError($errorDesc) {
		echo RestAPIHelper::responseHead("unknown", $error = "Internal Server Error", $exterror = $errorDesc) . RestAPIHelper::responseTail();
	}
}
?>
