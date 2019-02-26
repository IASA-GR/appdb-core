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
	}

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
				$xml = simplexml_load_string(file_get_contents(APPLICATION_PATH . "/../cache/site_contacts.xml"));
				if ($xml === false) {
					throw new Exception("Cannot parse site contacts as XML");
				}
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
}
