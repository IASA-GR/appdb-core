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
?>
<?php
// PUT YOUR CUSTOM CODE HERE
class VOMSConfig {
	private $voms;
	private $vomses;
	private $vomsdir;
	
	public function __construct(Default_Model_VOMS $voms) {
		$this->voms = $voms;
	}

	public function refresh() {
		$ch = curl_init();
		$url = "https://" . $this->voms->hostname . ":" . $this->voms->https_port . "/voms/" . $this->voms->VO->name . "/configuration/configuration.action";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, 181, 1 | 2);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSLCERT, APPLICATION_PATH . '/../bin/sec/usercert.pem');
		curl_setopt($ch, CURLOPT_SSLKEY, APPLICATION_PATH . '/../bin/sec/userkey.pem');
		curl_setopt($ch, CURLOPT_HTTPHEADER, apache_request_headers());
		$data = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch); 
		curl_close($ch);

		$ok = true;
		if (!$error && trim($code) !== "200") {
			# http error : self::getHttpErrorCodes($code);
			$ok = false;
		}
		
		$filesize = strlen($data);
		if ($filesize === 0) {
			# data error : "No data retrieved";
			$ok = false;
		}

		if ($ok) {
			$doc = new DOMDocument();
			libxml_use_internal_errors(true);
			#$data = mb_convert_encoding($data, 'HTML-ENTITIES', "UTF-8")
			@$doc->loadHTML($data);
			$confs = $doc->getElementsByClass('configurationInfo');
			if (count($confs) === 4) {
				$this->vomses = $confs[1]->nodeValue;
				$this->vomsdir = $confs[2]->nodeValue;
			}
		}
	}

	public function getVOMSES() {
		return $this->vomses;
	}

	public function getVOMSDIR() {
		return $this->vomsdir;
	}

}

class Default_Model_VOMS extends Default_Model_VOMSBase
{
	private $config;

	public function getConfig() {
		if (is_null($this->config)) {
			$this->config = new VOMSConfig($this);
		}
		return $this->config;
	}
}
