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

abstract class Argo {
	protected $_serviceType;
	protected $_reportType;
	protected $_apikey;

	public function __construct($serviceType = "eu.egi.cloud.vm-management.occi", $reportType = "Critical") {
		$app = Zend_Registry::get('app');
		$this->_apikey = $app["argo_api_key"];
		$this->_timezone = $app["timezone"];
		$this->_reportType = $reportType;
		$this->_serviceType = $serviceType;
	}

	abstract public function syncStatus($site = null);
}

class ArgoOCCI extends Argo {
	public function __construct($reportType = "Critical") {
		parent::__construct("eu.egi.cloud.vm-management.occi", $reportType);
	}

	public function syncStatus($site = null) {
		error_log("OCCI ARGO status sync started");
		db()->beginTransaction();
		db()->query("ALTER TABLE gocdb.va_providers DISABLE TRIGGER tr_gocdb_va_providers_99_refresh_permissions;");
		db()->setFetchMode(Zend_Db::FETCH_NUM);
		if (isset($site)) {
			$vas = db()->query("SELECT pkey, sitename, hostname, argo_status, argo_status_date FROM gocdb.va_providers WHERE sitename = ? ORDER BY sitename, hostname", array($site))->fetchAll();
		} else {
			$vas = db()->query("SELECT pkey, sitename, hostname, argo_status, argo_status_date FROM gocdb.va_providers ORDER BY sitename, hostname")->fetchAll();
		}
		if (is_array($vas) && count($vas) > 0) {
			foreach($vas as $va) {
				$id = $va[0];
				$sitename = $va[1];
				$endpoint = $va[2];
				$status = $va[3];
				$lastChecked = $va[4];
				if (is_null($lastChecked)) {
					$wfrom = "2016-01-01T00:00:00Z";
				} else {
					// create datetime object from SQL server in local timezone
					$tmptime1 = strtotime($lastChecked);
					// format datetime object to string in UTC
					date_default_timezone_set("UTC");
					$wfrom = date("Y-m-d\TH:i:s\Z", $tmptime1);
					// restore local timezone
					date_default_timezone_set($this->_timezone);
				}	
				$wto = gmdate("Y-m-d\TH:i:s\Z");
				$ch = curl_init();
				$uri = "https://web-api-devel.argo.grnet.gr/api/v2/status/" . $this->_reportType . "/SITES/$sitename/services/" . $this->_serviceType . "/endpoints/${endpoint}?start_time=$wfrom&end_time=$wto";
				error_log("Syncing OCCI ARGO status for site $sitename");
				curl_setopt($ch, CURLOPT_URL, $uri);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, 181, 1 | 2);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        
				curl_setopt($ch, CURLOPT_SSLCERT, APPLICATION_PATH . '/../bin/sec/usercert.pem');
				curl_setopt($ch, CURLOPT_SSLKEY, APPLICATION_PATH . '/../bin/sec/userkey.pem');
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					"Accept: application/xml",
					"x-api-key: " . $this->_apikey
				));
				$xml = curl_exec($ch);

				if ( $xml === false ) {
					error_log("connection error in Argo::syncStatus for site $sitename and endpoint $endpoint: " . var_export(curl_error($ch), true));
					db()->query("ALTER TABLE gocdb.va_providers ENABLE TRIGGER tr_gocdb_va_providers_99_refresh_permissions;");
					error_log("OCCI ARGO status sync failed");
					db()->rollBack();
					return;
				}
				@curl_close($ch);
				try {
					$xml = new SimpleXMLElement($xml);
				} catch (Exception $e) {
					error_log("error reading OCCI status XML output in Argo::syncStatus for site $sitename and endpoint $endpoint: $e");
					continue;
				}
				$statuses = $xml->xpath("//status");
				if (is_array($statuses) && count($statuses) > 0) {
					$T = strtotime($wfrom);
					$V = "";
					foreach($statuses as $status) {
						$timestamp = strtotime($status->attributes()->timestamp);
						if ($timestamp >= $T) {
							$value = strval($status->attributes()->value);
							if ((strtolower(trim($value)) != "") && (strtolower(trim($value)) != "missing")) {
								$T = $timestamp;
								$V = $value;
							}
						}
					}
					if ($V != "") {
						try {
							db()->query("UPDATE gocdb.va_providers SET argo_status = ?, argo_status_date = ? WHERE pkey = ?", array($V, date("Y-m-d H:i:s",$T), $id))->fetchAll();
						} catch (Exception $e) {
							error_log("error updating OCCI ARGO status in DB for site $sitename and endpoint $endpoint: $e");
						}
					}
				}
			}
		}
		db()->query("ALTER TABLE gocdb.va_providers ENABLE TRIGGER tr_gocdb_va_providers_99_refresh_permissions;");
		db()->commit();
		error_log("OCCI ARGO status sync ended");
	}
}

?>
