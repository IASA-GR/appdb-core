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

class GocdbController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
        $this->session = new Zend_Session_Namespace('default');
	}

	private function syncVAProviders() {
		$inTransaction = false;
		try {
			$ch = curl_init();
			$uri = "https://goc.egi.eu/gocdbpi/public/?method=get_service_endpoint&service_type=eu.egi.cloud.vm-management.occi";
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
				error_log("error in syncVAProviders: " . var_export(curl_error($ch), true));
				return;
			}
			@curl_close($ch);
			@exec("rm ". APPLICATION_PATH . "/../cache/va_providers.xml.old");
			@exec("cp " . APPLICATION_PATH . "/../cache/va_providers.xml ". APPLICATION_PATH . "/../cache/va_providers.xml.old");
			$f = fopen(APPLICATION_PATH . "/../cache/va_providers.xml","w");
			fwrite($f, $xml);
			fclose($f);
			if (@md5_file(APPLICATION_PATH . "/../cache/va_providers.xml") !== @md5_file(APPLICATION_PATH . "/../cache/va_providers.xml.old")) {	
				$xml = new SimpleXMLElement(file_get_contents(APPLICATION_PATH . "/../cache/va_providers.xml"));
				$rows = $xml->xpath("//results/SERVICE_ENDPOINT");
				if (count($rows) > 0) {
					error_log("Sync'ing VA providers...");
					db()->beginTransaction();
					$inTransaction = true;
					db()->query("ALTER TABLE gocdb.va_providers DISABLE TRIGGER tr_gocdb_va_providers_99_refresh_permissions;");
					db()->query("DELETE FROM gocdb.va_providers;");
					foreach($rows as $row) {
						db()->query("INSERT INTO oses (name) SELECT '" . pg_escape_string(trim($row->HOST_OS)) . "' WHERE '" . pg_escape_string(trim($row->HOST_OS)) . "' <> '' AND '" . pg_escape_string(trim($row->HOST_OS)) . "' NOT IN (SELECT name FROM oses)", array(trim($row->HOST_OS)));
						db()->query("INSERT INTO gocdb.va_providers(pkey,hostname,gocdb_url,host_dn,host_os,host_arch,beta,service_type,host_ip,in_production,node_monitored,sitename,country_name,country_code,roc_name,url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);", array(trim($row->PRIMARY_KEY), trim($row->HOSTNAME), trim($row->GOCDB_PORTAL_URL), trim($row->HOSTDN), trim($row->HOST_OS), trim($row->HOST_ARCH), trim($row->BETA), trim($row->SERVICE_TYPE), trim($row->HOST_IP), trim($row->IN_PRODUCTION), trim($row->NODE_MONITORED), trim($row->SITENAME), trim($row->COUNTRY_NAME), trim($row->COUNTRY_CODE), trim($row->ROC_NAME), trim($row->URL)));
					}
					db()->commit();
					db()->query("ALTER TABLE gocdb.va_providers ENABLE TRIGGER tr_gocdb_va_providers_99_refresh_permissions;");
					db()->query("SELECT request_permissions_refresh();");
					error_log("VA providers sync'ed");
				}

			} 
		} catch (Exception $e) {
			if ($inTransaction) {
				$db = db();
				@$db->rollBack();
			}
			db()->query("ALTER TABLE gocdb.va_providers ENABLE TRIGGER tr_gocdb_va_providers_99_refresh_permissions;");
			db()->query("REFRESH MATERIALIZED VIEW CONCURRENTLY va_providers;");
			db()->query("SELECT request_permissions_refresh();");
			debug_log("error in syncVAProviders: $e");
		}
	}

	public function syncvaprovidersAction() {
		if ( localRequest() ) {
			$this->syncVAProviders();
		} else {
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
			$this->getResponse()->setHeader("Status","403 Forbidden");
		}
	}
}

