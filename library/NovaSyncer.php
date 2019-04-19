<?php
class NovaSyncer {

	public function __construct() {
		$this->novaSyncScopes = Zend_Registry::get("app");
		$this->novaSyncScopes = $this->novaSyncScopes['va_sync_scopes'];
		if (trim($this->novaSyncScopes) == "") {
			$this->novaSyncScopes = 'FedCloud';
		} else {
			if (strpos($this->novaSyncScopes, ",") !== false) {
				$this->novaSyncScopes = explode(",", $this->novaSyncScopes);
			}
		}
	}
	
	/**
	* checks to see if a VA provider has a certain scope or set of scopes
	* @hay where to search; may be a VA provider XML element or alternatively it's "SCOPES" or "SCOPE" XML element, or even an array of strings or a simple string
	* @needle what to search for; may be a string or an array of strings
	*
	* @return bool
	*/
	function novaProviderHasScope($hay, $needle) {
		if (is_null($hay) || is_null($needle)) {
			return false;
		}
		
		if (is_object($hay)) {
			if (isset($hay->SCOPES)) {
				$tmp = $hay->SCOPES;
				if (is_object($tmp)) {
					if (isset($tmp->SCOPE)) {
						$hay = array();
						foreach ($tmp->SCOPE as $t) {
							$hay[] = strval($t);
						}
					} else {
						return false;
					}
				} elseif (is_array($tmp)) {
					$hay = $tmp;
				} elseif (is_string($tmp) || is_numeric($tmp)) {
					$hay = array();
					$hay[] = $tmp;
				} else {
					return false;
				}
			} elseif (isset($hay->SCOPE)) {
				$tmp = $hay->SCOPE;
				$hay = array();
				foreach ($tmp as $t) {
					$hay[] = strval($t);
				}
			} else {
				if (count($hay) > 0) {
					$tmp = $hay;
					$hay = array();
					for ($i = 0; $i < count($tmp); $i = $i + 1) {
						$hay[] = strval($tmp[$i]);
					}
				} else {
					return false;
				}
			}
		} 

		if (! is_array($hay)) {
			$tmp = $hay;
			$hay = array();
			$hay[] = $tmp;
		}

		if (! is_array($needle)) {
			$tmp = $needle;
			$needle = array();
			$needle[] = $tmp;
		}

		foreach ($hay as $h) {
			foreach ($needle as $n) {
				if (trim(strtolower($h)) == trim(strtolower($n))) {
					return true;
				}
			}
		}
		return false;
	}

	public function sync() {
		$inTransaction = false;
		try {
			$ch = curl_init();
			$uri = "https://goc.egi.eu/gocdbpi/public/?method=get_service_endpoint&service_type=org.openstack.nova";
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
				error_log("error in syncNovaProviders: " . var_export(curl_error($ch), true));
				return;
			}
			@curl_close($ch);
			@exec("rm ". APPLICATION_PATH . "/../cache/nonova_providers.xml.old");
			@exec("cp " . APPLICATION_PATH . "/../cache/nonova_providers.xml ". APPLICATION_PATH . "/../cache/nova_providers.xml.old");
			$f = fopen(APPLICATION_PATH . "/../cache/nova_providers.xml","w");
			fwrite($f, $xml);
			fclose($f);
			if (@md5_file(APPLICATION_PATH . "/../cache/nova_providers.xml") !== @md5_file(APPLICATION_PATH . "/../cache/nova_providers.xml.old")) {	
				$xml = new SimpleXMLElement(file_get_contents(APPLICATION_PATH . "/../cache/nova_providers.xml"));
				$rows = $xml->xpath("//results/SERVICE_ENDPOINT");
				if (count($rows) > 0) {
					error_log("Sync'ing Nova providers...");
					db()->beginTransaction();
					$inTransaction = true;
					db()->query("DELETE FROM gocdb.nova_providers;");
					foreach($rows as $row) {
						db()->query("INSERT INTO oses (name) SELECT '" . pg_escape_string(trim($row->HOST_OS)) . "' WHERE '" . pg_escape_string(trim($row->HOST_OS)) . "' <> '' AND '" . pg_escape_string(trim($row->HOST_OS)) . "' NOT IN (SELECT name FROM oses)", array(trim($row->HOST_OS)));
						if ($this->novaProviderHasScope($row, $this->novaSyncScopes)) {
							db()->query("INSERT INTO gocdb.nova_providers(pkey,hostname,gocdb_url,host_dn,host_os,host_arch,beta,service_type,host_ip,in_production,node_monitored,sitename,country_name,country_code,roc_name,url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);", array(trim($row->PRIMARY_KEY), trim($row->HOSTNAME), trim($row->GOCDB_PORTAL_URL), trim($row->HOSTDN), trim($row->HOST_OS), trim($row->HOST_ARCH), trim($row->BETA), trim($row->SERVICE_TYPE), trim($row->HOST_IP), trim($row->IN_PRODUCTION), trim($row->NODE_MONITORED), trim($row->SITENAME), trim($row->COUNTRY_NAME), trim($row->COUNTRY_CODE), trim($row->ROC_NAME), trim($row->URL)));
						}
					}
					db()->commit();
					db()->query("SELECT request_permissions_refresh();");
					error_log("Nova providers sync'ed");
				}
			} 
		} catch (Exception $e) {
			if ($inTransaction) {
				$db = db();
				@$db->rollBack();
				error_log("Rollback. Cause: $e");
			}
			db()->query("REFRESH MATERIALIZED VIEW CONCURRENTLY nova_providers;");
			db()->query("SELECT request_permissions_refresh();");
			debug_log("error in syncNovaProviders: $e");
		}
	}

}
