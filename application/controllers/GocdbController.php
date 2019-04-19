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
 * temporary fix for syncing org.openstack.nova endpoints until AppDB-IS w/ GLUE 2.1 support gets released
 */
require_once(APPLICATION_PATH . "/../library/NovaSyncer.php");
// OBSOLETE
//require_once(APPLICATION_PATH . "/../library/argo.php");

class GocdbController extends Zend_Controller_Action
{
//	private $vaSyncScopes;

	public function init()
	{
	    /* Initialize action controller here */
	    $this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$this->session = new Zend_Session_Namespace('default');
// OBSOLETE
//		$this->vaSyncScopes = Zend_Registry::get("app");
//		$this->vaSyncScopes = $this->vaSyncScopes['va_sync_scopes'];
//
//		if (trim($this->vaSyncScopes) == "") {
//			$this->vaSyncScopes = 'FedCloud';
//		} else {
//			if (strpos($this->vaSyncScopes, ",") !== false) {
//				$this->vaSyncScopes = explode(",", $this->vaSyncScopes);
//			}
//		}

	}

//OBSOLETE
//	/**
//	* checks to see if a VA provider a certain scope or set of scopes
//	* @hay where to search; may be a VA provider XML element or alternatively it's "SCOPES" or "SCOPE" XML element, or even an array of strings or a simple string
//	* @needle what to search for; may be a string or an array of strings
//	*
//	* @return bool
//	*/
//	function vaProviderHasScope($hay, $needle) {
//		if (is_null($hay) || is_null($needle)) {
////			echo "NULL input" . "\n";
//			return false;
//		}
//		
//		if (is_object($hay)) {
////			echo "hay is object" . "\n";
//			if (isset($hay->SCOPES)) {
//				$tmp = $hay->SCOPES;
//				if (is_object($tmp)) {
//					if (isset($tmp->SCOPE)) {
////						echo "found hay->scopes->scope" . "\n";
//						$hay = array();
//						foreach ($tmp->SCOPE as $t) {
//							$hay[] = strval($t);
//						}
//					} else {
////						echo "hay->scopes is object and has no scope" . "\n";
//						return false;
//					}
//				} elseif (is_array($tmp)) {
////					echo "hay->scopes is an array" . "\n";
//					$hay = $tmp;
//				} elseif (is_string($tmp) || is_numeric($tmp)) {
////					echo "hay->scopes is simple string. constructing array" . "\n";
//					$hay = array();
//					$hay[] = $tmp;
//				} else {
//					return false;
//				}
//			} elseif (isset($hay->SCOPE)) {
////				echo "found hay->scope" . "\n";
//				$tmp = $hay->SCOPE;
//				$hay = array();
//				foreach ($tmp as $t) {
//					$hay[] = strval($t);
//				}
//			} else {
////				echo "Unknown hay object (no scope or scopes)" . "\n";
//				if (count($hay) > 0) {
//					$tmp = $hay;
//					$hay = array();
//					for ($i = 0; $i < count($tmp); $i = $i + 1) {
//						$hay[] = strval($tmp[$i]);
//					}
//				} else {
//					return false;
//				}
//			}
//		} 
//
//		if (! is_array($hay)) {
////			echo "constructing array for hay" . "\n";
//			$tmp = $hay;
//			$hay = array();
//			$hay[] = $tmp;
//		}
//
//		if (! is_array($needle)) {
////			echo "constructing array for needle" . "\n";
//			$tmp = $needle;
//			$needle = array();
//			$needle[] = $tmp;
//		}
//
//		foreach ($hay as $h) {
//			foreach ($needle as $n) {
//				if (trim(strtolower($h)) == trim(strtolower($n))) {
//					return true;
//				}
//			}
//		}
//		return false;
//	}

	public function testAction() {
		//$this->syncSiteContacts();
	}

	private function syncSiteContacts() {
		$inTransaction = false;
		try {
			$ch = curl_init();
			$uri = "https://goc.egi.eu/gocdbpi/public/?method=get_site_contacts";
			curl_setopt($ch, CURLOPT_URL, $uri);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, 181, 1 | 2);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        
			curl_setopt($ch, CURLOPT_SSLCERT, APPLICATION_PATH . '/../bin/sec/usercert.pem');
			curl_setopt($ch, CURLOPT_SSLKEY, APPLICATION_PATH . '/../bin/sec/userkey.pem');
			$headers = apache_request_headers();
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$xml = curl_exec($ch);
			
			if ( $xml === false ) {
				error_log("error in syncSiteContacts: " . var_export(curl_error($ch), true));
				return;
			}
			@curl_close($ch);
			@exec("rm ". APPLICATION_PATH . "/../cache/site_contacts.xml.old");
			@exec("cp " . APPLICATION_PATH . "/../cache/site_contacts.xml ". APPLICATION_PATH . "/../cache/site_contacts.xml.old");
			$f = fopen(APPLICATION_PATH . "/../cache/site_contacts.xml","w");
			fwrite($f, $xml);
			fclose($f);
			if (@md5_file(APPLICATION_PATH . "/../cache/site_contacts.xml") !== @md5_file(APPLICATION_PATH . "/../cache/site_contacts.xml.old")) {
				$xml = new SimpleXMLElement(file_get_contents(APPLICATION_PATH . "/../cache/site_contacts.xml"));
				$rows = $xml->xpath("//results/SITE");
				if (count($rows) > 0) {
					error_log("Sync'ing site contacts...");
					db()->beginTransaction();
					db()->query("TRUNCATE TABLE gocdb.site_contacts");
					$inTransaction = true;
					$si = 0;
					foreach($rows as $row) {
						$si = $si + 1;
						error_log("Site $si / " . count($rows));
						$siteid = trim($row->PRIMARY_KEY);
						$sitename = trim($row->NAME);
						$contacts = $row->xpath(".//CONTACT");
						foreach ($contacts as $contact) {
							$cid = trim($contact->PRIMARY_KEY);
							$cfname = trim($contact->FORENAME);
							$clname = trim($contact->SURNAME);
							$cname = $cfname . ' ' . $clname;
							$cdn = trim($contact->CERTDN);
							$crole = trim($contact->ROLE_NAME);
							try {
								db()->query("INSERT INTO gocdb.site_contacts (site_pkey, name, dn, role) VALUES (?, ?, ?, ?);", array($siteid, $cname, $cdn, $crole));
							} catch (Exception $e) {}
						}
					}
					db()->commit();
					db()->query("SELECT request_permissions_refresh();");
					error_log("Site contacts sync'ed");
				}
			} 
		} catch (Exception $e) {
			if ($inTransaction) {
				$db = db();
				@$db->rollBack();
			}
			db()->query("SELECT request_permissions_refresh();");
			debug_log("error in syncSiteContacts: $e");
		}
	}

	public function syncsitecontactsAction() {
		if ( localRequest() ) {
			$this->syncSiteContacts();
		} else {
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
			$this->getResponse()->setHeader("Status","403 Forbidden");
		}
	}

// OBSOLETE
//	private function syncOcciDowntimeInfo() {
//		error_log("Syncing OCCI downtimes");
//		try {
//			db()->beginTransaction();
//		} catch (Exception $e) {
//			error_log("[syncOcciDowntimeInfo] Cannot initiate transaction. Aborting...");
//		}
//		try {
//			db()->query("ALTER TABLE gocdb.va_providers DISABLE TRIGGER tr_gocdb_va_providers_99_refresh_permissions;");
//			db()->query("UPDATE gocdb.va_providers SET service_downtime = 0::bit(2);");
//			$ch = curl_init();
//			$uri = "https://goc.egi.eu/gocdbpi/public/?method=get_downtime_nested_services&ongoing_only=yes";
//			curl_setopt($ch, CURLOPT_URL, $uri);
//			curl_setopt($ch, CURLOPT_HEADER, false);
//			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//			curl_setopt($ch, 181, 1 | 2);
//			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        
//			curl_setopt($ch, CURLOPT_SSLCERT, APPLICATION_PATH . '/../bin/sec/usercert.pem');
//			curl_setopt($ch, CURLOPT_SSLKEY, APPLICATION_PATH . '/../bin/sec/userkey.pem');
//			$headers = apache_request_headers();
//			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//			$xml = curl_exec($ch);
//			
//			if ( $xml === false ) {
//				error_log("error in syncOcciDowntimeInfo: " . var_export(curl_error($ch), true));
//				$db = db();
//				@$db->rollBack();
//				return;
//			}
//			@curl_close($ch);
//			$xml = new SimpleXMLElement($xml);
//			$xps = $xml->xpath("//SERVICE_TYPE[text()='eu.egi.cloud.vm-management.occi']/../../../.");
//			$activeDTimes = array();
//			foreach ($xps as $xp) {
//				$dstart = strval(($xp->xpath("./START_DATE")[0]));
//				$dend = strval(($xp->xpath("./END_DATE")[0]));
//				$pkey = strval(($xp->xpath("./SERVICES/SERVICE/SERVICE_TYPE[text()='eu.egi.cloud.vm-management.occi']/../PRIMARY_KEY")[0]));
//				$activeDTimes[] = array($dstart, $dend, $pkey);
//				error_log("Currently down: " . strval($pkey));
//				db()->query("UPDATE gocdb.va_providers SET service_downtime = service_downtime | 2::bit(2) WHERE pkey = '$pkey';");
//			}
//
//			$wstart = new DateTime('yesterday');
//			$wstart = $wstart->format('Y-m-d');
//			$wend = new DateTime('tomorrow');
//			$wend = $wend->format('Y-m-d');
//			$ch = curl_init();
//			$uri = "https://goc.egi.eu/gocdbpi/public/?method=get_downtime_nested_services&windowstart=$wstart&windowend=$wend";
//			curl_setopt($ch, CURLOPT_URL, $uri);
//			curl_setopt($ch, CURLOPT_HEADER, false);
//			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//			curl_setopt($ch, 181, 1 | 2);
//			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        
//			curl_setopt($ch, CURLOPT_SSLCERT, APPLICATION_PATH . '/../bin/sec/usercert.pem');
//			curl_setopt($ch, CURLOPT_SSLKEY, APPLICATION_PATH . '/../bin/sec/userkey.pem');
//			$headers = apache_request_headers();
//			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//			$xml = curl_exec($ch);
//			
//			if ( $xml === false ) {
//				error_log("error in syncOcciDowntimeInfo: " . var_export(curl_error($ch), true));
//				$db = db();
//				@$db->rollBack();
//				return;
//			}
//			@curl_close($ch);
//
//			$fff = fopen(APPLICATION_PATH . "/../cache/downtimes.xml","w");
//			fwrite($fff, $xml);
//			fclose($fff);
//
//			$xml = new SimpleXMLElement($xml);
//			$xps = $xml->xpath("//SERVICE_TYPE[text()='eu.egi.cloud.vm-management.occi']/../../../.");
//			foreach ($xps as $xp) {
//				$dstart = strval(($xp->xpath("./START_DATE")[0]));
//				$dend = strval(($xp->xpath("./END_DATE")[0]));
//				$nowStart = time();
//				$nowEnd = strtotime('+1 day', time());
//				if (
//					(($dend >= $nowStart) && ($dend <= $nowEnd)) ||
//					(($dstart >= $nowStart) && ($dstart <= $nowEnd)) ||
//					(($dstart <= $nowStart) && ($dend >= $nowEnd))
//				) {
//					$pkey = strval(($xp->xpath("./SERVICES/SERVICE/SERVICE_TYPE[text()='eu.egi.cloud.vm-management.occi']/../PRIMARY_KEY")[0]));
//					// exclude current active downtime, if any
//					$exclude = false;
//					foreach($activeDTimes as $a) {
//						if ($a[2] == $pkey) {
//							if (($nowStart >= $a[0]) && ($nowStart <= $a[1])) {
//								$exclude = true;
//							}
//						}
//					}
//					if ($exclude) continue;
//					error_log('Down sometime between now and 24h from now: ' . strval($pkey));
//					db()->query("UPDATE gocdb.va_providers SET service_downtime = service_downtime | 1::bit(2) WHERE pkey = '$pkey';");
//				}
//			}
//			db()->commit();
//			db()->query("ALTER TABLE gocdb.va_providers ENABLE TRIGGER tr_gocdb_va_providers_99_refresh_permissions;");
//			db()->query("REFRESH MATERIALIZED VIEW va_providers;");
//		} catch (Exception $e) {
//			error_log("[syncOcciDowntimeInfo] error $e");
//			$db = db();
//			@$db->rollBack();
//			return;
//		}
//	}

// OBSOLETE
//	private function syncVAProviders() {
//		$inTransaction = false;
//		try {
//			$ch = curl_init();
//			$uri = "https://goc.egi.eu/gocdbpi/public/?method=get_service_endpoint&service_type=eu.egi.cloud.vm-management.occi";
//			curl_setopt($ch, CURLOPT_URL, $uri);
//			curl_setopt($ch, CURLOPT_HEADER, false);
//			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//			curl_setopt($ch, 181, 1 | 2);
//			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        
//			curl_setopt($ch, CURLOPT_SSLCERT, APPLICATION_PATH . '/../bin/sec/usercert.pem');
//			curl_setopt($ch, CURLOPT_SSLKEY, APPLICATION_PATH . '/../bin/sec/userkey.pem');
//			$headers = apache_request_headers();
//			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//			$xml = curl_exec($ch);
//			if ( $xml === false ) {
//				error_log("error in syncVAProviders: " . var_export(curl_error($ch), true));
//				return;
//			}
//			@curl_close($ch);
//			@exec("rm ". APPLICATION_PATH . "/../cache/va_providers.xml.old");
//			@exec("cp " . APPLICATION_PATH . "/../cache/va_providers.xml ". APPLICATION_PATH . "/../cache/va_providers.xml.old");
//			$f = fopen(APPLICATION_PATH . "/../cache/va_providers.xml","w");
//			fwrite($f, $xml);
//			fclose($f);
//			if (@md5_file(APPLICATION_PATH . "/../cache/va_providers.xml") !== @md5_file(APPLICATION_PATH . "/../cache/va_providers.xml.old")) {	
//				$xml = new SimpleXMLElement(file_get_contents(APPLICATION_PATH . "/../cache/va_providers.xml"));
//				$rows = $xml->xpath("//results/SERVICE_ENDPOINT");
//				if (count($rows) > 0) {
//					error_log("Sync'ing VA providers...");
//					db()->beginTransaction();
//					$inTransaction = true;
//					db()->query("ALTER TABLE gocdb.va_providers DISABLE TRIGGER tr_gocdb_va_providers_99_refresh_permissions;");
//					db()->query("DELETE FROM gocdb.va_providers;");
//					foreach($rows as $row) {
//						db()->query("INSERT INTO oses (name) SELECT '" . pg_escape_string(trim($row->HOST_OS)) . "' WHERE '" . pg_escape_string(trim($row->HOST_OS)) . "' <> '' AND '" . pg_escape_string(trim($row->HOST_OS)) . "' NOT IN (SELECT name FROM oses)", array(trim($row->HOST_OS)));
//						if ($this->vaProviderHasScope($row, $this->vaSyncScopes)) {
//							db()->query("INSERT INTO gocdb.va_providers(pkey,hostname,gocdb_url,host_dn,host_os,host_arch,beta,service_type,host_ip,in_production,node_monitored,sitename,country_name,country_code,roc_name,url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);", array(trim($row->PRIMARY_KEY), trim($row->HOSTNAME), trim($row->GOCDB_PORTAL_URL), trim($row->HOSTDN), trim($row->HOST_OS), trim($row->HOST_ARCH), trim($row->BETA), trim($row->SERVICE_TYPE), trim($row->HOST_IP), trim($row->IN_PRODUCTION), trim($row->NODE_MONITORED), trim($row->SITENAME), trim($row->COUNTRY_NAME), trim($row->COUNTRY_CODE), trim($row->ROC_NAME), trim($row->URL)));
//						}
//					}
//					db()->commit();
//					db()->query("ALTER TABLE gocdb.va_providers ENABLE TRIGGER tr_gocdb_va_providers_99_refresh_permissions;");
//					db()->query("SELECT request_permissions_refresh();");
//					error_log("VA providers sync'ed");
//				}
//			} 
//			$this->syncOcciDowntimeInfo();
//			$this->syncArgoOcciStatus();
//		} catch (Exception $e) {
//			if ($inTransaction) {
//				$db = db();
//				@$db->rollBack();
//				error_log("Rollback. Cause: $e");
//			}
//			db()->query("ALTER TABLE gocdb.va_providers ENABLE TRIGGER tr_gocdb_va_providers_99_refresh_permissions;");
//			db()->query("REFRESH MATERIALIZED VIEW CONCURRENTLY va_providers;");
//			db()->query("SELECT request_permissions_refresh();");
//			debug_log("error in syncVAProviders: $e");
//		}
//	}
//
//	public function syncvaprovidersAction() {
//		if ( localRequest() ) {
//			$this->syncVAProviders();
//		} else {
//			$this->getResponse()->clearAllHeaders();
//			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
//			$this->getResponse()->setHeader("Status","403 Forbidden");
//		}
//	}
//
//	public function syncoccidowntimeinfoAction() {
//		if ( localRequest() ) {
//			$this->syncOcciDowntimeInfo();
//		} else {
//			$this->getResponse()->clearAllHeaders();
//			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
//			$this->getResponse()->setHeader("Status","403 Forbidden");
//		}
//	}
//	
//	public function syncArgoOcciStatus() {
//		$argo = new ArgoOCCI();
//		$argo->syncStatus();
//	}
//
//	public function syncargooccistatusAction() {
//		if ( localRequest() ) {
//			$this->syncArgoOcciStatus();
//		} else {
//			$this->getResponse()->clearAllHeaders();
//			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
//			$this->getResponse()->setHeader("Status","403 Forbidden");
//		}
//	}

	/**
	 * Synchronizes org.openstack.nova endpoints from GocDB. Temporary solution until AppDB-IS w/ support for GLUE 2.1 gets released
	 * Uses NovaSyncer class
	 */
	public function syncnovaprovidersAction() {
		if ( localRequest() ) {
			$ns = new NovaSyncer();
			$ns->sync();
		} else {
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
			$this->getResponse()->setHeader("Status","403 Forbidden");
		}
	}

}

