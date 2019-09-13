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
   
/** Needed for Site / VA-provider sync'ing */ 
require_once "PHPCouch/PHPOnCouch/Couch.php";
require_once "PHPCouch/PHPOnCouch/CouchClient.php";
require_once "PHPCouch/PHPOnCouch/CouchDocument.php";
require_once "PHPCouch/PHPOnCouch/Config.php";
use PHPOnCouch\CouchClient;
use PHPOnCouch\Exceptions; 
/***************************/

class SitesController extends Zend_Controller_Action{
	private $vaSyncScopes;
	
	public function init(){
		$this->vaSyncScopes = Zend_Registry::get("app");
		$this->vaSyncScopes = (isset($this->vaSyncScopes['va_sync_scopes']) ? $this->vaSyncScopes['va_sync_scopes'] : "");

		if (trim($this->vaSyncScopes) == "") {
			$this->vaSyncScopes = 'FedCloud';
//		} else {
//			if (strpos($this->vaSyncScopes, ",") !== false) {
//				$this->vaSyncScopes = explode(",", $this->vaSyncScopes);
//			}
		}	
	}
	
	public function indexAction(){
		 $this->_helper->layout->disableLayout();
	}
	
	public function detailsAction(){
		 $this->_helper->layout->disableLayout();
		 if ( $this->_getParam("id") != null ) {
			 $this->view->id = trim($this->_getParam("id"));
		 }
		 $this->view->dialogCount = $this->_getParam('dc');
		 
		 $sites = new Default_Model_Sites();
		 $sites->filter->id->equals($this->view->id);
		 if( count($sites->items) > 0 ){
			 $this->view->entry = $sites->items[0];
		 }else{
			 $this->view->entry = null;
		 }
	}
	
	private function makeVAprovidersCache($subres = "") {
		$subname = "";
		$subcname = "";
		if ($subres != "") {
			$subname = "-$subres";
			switch($subres) {
			case "nova":
				$subcname = "Nova";
				break;
			case "all":
				$subcname = "All";
				break;
			default:
				$subcname = ucfirst($subres);
			}
		}
		$copyfile = RestAPIHelper::getFolder(RestFolderEnum::FE_CACHE_FOLDER) . '../public/assets/rp/va_providers' . $subname . '.xml';
		$hashfile = RestAPIHelper::getFolder(RestFolderEnum::FE_CACHE_FOLDER) . '../public/assets/rp/datahash' . $subname;

		# truncate data hash file (i.e. sync operation in progress)
		$f_hashfile = @fopen($hashfile, "w");
		if ($f_hashfile !== false) { 
			fwrite($f_hashfile, "");
			fclose($f_hashfile);
		} else {
			$errors = error_get_last();
			error_log("[makeVAprovidersCache$subname] Could not open+truncate VA providers cache data hash file. Reason: " . $errors['message']);
		}
		$uri = 'https://' . $_SERVER['APPLICATION_API_HOSTNAME'] . '/rest/latest/va_providers/' . $subres . '?listmode=details';
		error_log($uri);
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
		// remove existing cachefiles before making API call, or else this will not work
		foreach ( glob(RestAPIHelper::getFolder(RestFolderEnum::FE_CACHE_FOLDER) .'/query_RestVAProviders' . $subcname . 'List_*.xml') as $cachefile ) {
			@unlink($cachefile);
		}
		error_log('VA providers' . $subname . ' RESTful API XML cache STARTED');
		// 1st call creates the cache
		$result = curl_exec($ch);
		error_log('VA providers' . $subname . ' RESTful API XML cache DONE');		
		// 2nd call returns the cached response
		$result = curl_exec($ch);
		$result_tmp = @gzdecode($result);
		if ($result_tmp !== false) {
			$result = $result_tmp;
		}
		$ck = "";
		try {
			$xmlresult = simplexml_load_string($result);
			if ($xmlresult === false) {
				throw new Exception("Cannot parse VA Providers$subname data as XML");
			}
			$appdb = $xmlresult->xpath("//appdb:appdb");
			$vadata = $xmlresult->xpath("//virtualization:provider");
			$vadatastring = "";
			foreach ($vadata as $vadatum) {
				$vadatastring .= $vadatum->asXML();
			}
			$hash = md5($vadatastring);
			if (count($appdb) > 0) {
				$appdb = $appdb[0];
				$ck = trim(strval($appdb->attributes()->cachekey));
				if ($ck != "") {
					debug_log("[makeVAprovidersCache$subname] cache key is " . $ck);
				} else {
					error_log("[makeVAprovidersCache$subname] Did not find cache key in XML response");
				}
			} else {
				error_log("[makeVAprovidersCache$subname] Could not find appdb:appdb root element in XML response");
			}
		} catch (Exception $e) {
			error_log("[makeVAprovidersCache$subname] Could not parse respone as XML. Reason: " . $e->getMessage());
		}
		curl_close($ch);
		if ($ck != "") {
			if (!@copy(RestAPIHelper::getFolder(RestFolderEnum::FE_CACHE_FOLDER) .'/query_RestVAProviders' . $subcname . 'List_' . $ck . '.xml', $copyfile)) {
				$errors = error_get_last();
				error_log("[makeVAprovidersCache$subname] Could not copy VA providers cache file into assets. Reason: " . $errors['message']);
			} else {
				debug_log("Copied VA providers$subname cache file into assets");
				// XML cache file has been copied to assets. Create a JSON copy as well.
				$copyfile2 = str_replace(".xml", ".json", $copyfile);
				$jsondata = RestAPIHelper::transformXMLtoJSON(file_get_contents($copyfile));
				$f_jsonop = true;
				$f_jsonfile = @fopen($copyfile2, "w");
				if ($f_jsonfile !== false) {
					if (@fwrite($f_jsonfile, "" . $jsondata) === false) {
						$errors = error_get_last();
						error_log("[makeVAprovidersCache$subname] Could not write to VA providers cache file JSON copy in assets. Reason: " . $errors['message']);
						$f_jsonop = false;
					}
					@fclose($f_jsonfile);
				} else {
						$errors = error_get_last();
						error_log("[makeVAprovidersCache$subname] Could not open VA providers cache file JSON copy for writing in assets. Reason: " . $errors['message']);
						$f_jsonop = false;
				}
				if ($f_jsonop) {
					debug_log("Created VA providers$subname cache file JSON copy in assets");
				}
				// Keep a hashfile of the cache
				$f_hashop = true;
				$f_hashfile = @fopen($hashfile, "w");
				if ($f_hashfile !== false) {
					if (@fwrite($f_hashfile, $hash) === false) {
						$errors = error_get_last();
						error_log("[makeVAprovidersCache$subname] Could not write to VA providers cache data hash file. Reason: " . $errors['message']);
						$f_hashop = false;
					}
					@fclose($f_hashfile);
				} else {
					$errors = error_get_last();
					error_log("[makeVAprovidersCache$subname] Could not open VA providers cache data hash file for writing. Reason: " . $errors['message']);
					$f_hashop = false;
				}
				debug_log("Data md5 is $hash");
				if ($f_hashop) {
					debug_log("Copied VA providers$subname cache hash file into assets");
				}
			}
		} else {
			error_log("[makeVAprovidersCache$subname] No VA providers$subname cache file to copy into assets");
			$f_hashfile = @fopen($hashfile, "w");
			@fwrite("ERROR", $hash);
			fclose($f_hashfile);
		}
	}
	
	public function syncsitesAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		if ( ! localRequest() ) {
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
			$this->getResponse()->setHeader("Status","403 Forbidden");
			return;
		}

		$config = Zend_Registry::get("infosys");
		$couchHost = $config["host"];
		$couchDB = $config["db"];
		$glueVer = $config["glueVersion"];
		$client = new CouchClient($couchHost, $couchDB);
		$inTransaction = false;
		$release_sp_vap = false;
		$startTime = microtime(true);
		$success = true;
		$res = null;
		try{
			db()->beginTransaction();
			$inTransaction = true;			

			// keep a snaphot of current data, in order to compare post-syncing
			db()->exec("DROP TABLE IF EXISTS egiis.vapj2; CREATE TABLE egiis.vapj2 AS SELECT * FROM egiis.vapj;");
			db()->exec("DROP TABLE IF EXISTS egiis.tvapj2; CREATE TABLE egiis.tvapj2 AS SELECT * FROM egiis.tvapj;");
			db()->exec("DROP TABLE IF EXISTS egiis.sitej2; CREATE TABLE egiis.sitej2 AS SELECT * FROM egiis.sitej;");
			db()->exec("DROP TABLE IF EXISTS egiis.downtimes2; CREATE TABLE egiis.downtimes2 AS SELECT * FROM egiis.downtimes;");
			db()->exec("DROP TABLE IF EXISTS egiis.argo2; CREATE TABLE egiis.argo2 AS SELECT * FROM egiis.argo;");

			$docs = $client->limit(1000)->find(['meta.collection'=>['$eq'=>'egi.goc.vaproviders']]);
			$topids = array();
			foreach ($docs as $doc) {
				$id = $doc->_id;
				db()->query('INSERT INTO egiis.vapj (pkey,j,h) VALUES (?, ?, ?)', array(
					$doc->info->SiteEndpointPKey,
					json_encode($doc),
					$doc->meta->hash
				));
				$tid = str_replace('egi.goc.vaproviders.', 'egi.top.vaproviders.', $id) . '.glue' . $glueVer;
				$topids[] = $tid;
				try {
					$sp_vap = "sync_egi_vap_tvapj" . (microtime(true) * 10000);
					db()->query("SAVEPOINT $sp_vap");
					$release_sp_vap = true;
					$tdoc = $client->getDoc($tid);
					db()->query('INSERT INTO egiis.tvapj (pkey,j,h) VALUES (?, ?, ?)', array(
						$tdoc->info->SiteEndpointPKey,
						json_encode($tdoc),
						$tdoc->meta->hash
					)); 
				} catch(Exceptions\CouchNotFoundException $e){
					if($e->getCode() == 404) {
						error_log("[syncvaprovidersAction] Sync operation exception while querying $tid due to CouchDB error. Reason: TopBDII document not found. Operation will continue." );
					} else {
						error_log("[syncvaprovidersAction] Sync operation exception while querying $tid due to CouchDB error. Reason: " . $e->getMessage() . ". Operation will continue.");
					}
					$release_sp_vap = false;
					db()->query("ROLLBACK TO SAVEPOINT $sp_vap");
				} catch(Exception $e){
					error_log("[syncvaprovidersAction] Sync operation exception while querying $tid. Reason: " . $e->getMessage . ". Operation will continue." );
					$release_sp_vap = false;
					db()->query("ROLLBACK TO SAVEPOINT $sp_vap");
				}
				if ($release_sp_vap) {
					db()->query("RELEASE SAVEPOINT $sp_vap");
				}
			}
			$sp_vap = "sync_egi_vap_done" . (microtime(true) * 10000);
			db()->query("SAVEPOINT $sp_vap");			

			/*** SITES ***/
			$docs = $client->limit(1000)->find(['meta.collection'=>['$eq'=>'egi.goc.sites']]);
			foreach ($docs as $doc) {				
				db()->query('INSERT INTO egiis.sitej (pkey,j,h) VALUES (?, ?, ?)', array(
					$doc->info->SitePKey,
					json_encode($doc),
					$doc->meta->hash
				));
			}
			db()->query("RELEASE SAVEPOINT $sp_vap");

			/* Downtimes START */
			$sp_vap = "sync_egi_site_done" . (microtime(true) * 10000);
			db()->query("SAVEPOINT $sp_vap");
			$release_sp_vap = true;
			try {
				$docs = $client->limit(1000)->find(['meta.collection'=>['$eq'=>'egi.goc.vadowntimes']]);
			} catch (Exception $e) {
				error_log("[syncvaprovidersAction] Cannot fetch site downtimes info. Reason: " . $e->getMessage());
				$docs = array();
			}
			foreach ($docs as $doc) {				
				try {
					if ($doc->info->SiteEndpointPKey != "") {
						$sp_vap = "sync_egi_site_done" . (microtime(true) * 10000);
						db()->query("SAVEPOINT $sp_vap");
						db()->query("INSERT INTO egiis.downtimes(pkey, j, h) VALUES (?, ?, ?)", array(
							$doc->info->DowntimePKey,
							json_encode($doc),
							$doc->meta->hash
						)); 
						db()->query("RELEASE SAVEPOINT $sp_vap");
					}
				} catch (Exception $e) {
					db()->query("ROLLBACK TO SAVEPOINT $sp_vap");
					error_log("[syncvaprovidersAction] DB error while processing site downtime info for endpoint \`" . $doc->info->SiteEndpointPKey . "'. Operation will continue. Message: " . $e->getMessage());
				}
			}
			/* Downtimes END */

			/* ARGO status START */
			try {
				$docs = $client->limit(1000)->find(['meta.collection'=>['$eq'=>'egi.argo.vaproviders']]);
			} catch (Exception $e) {
				error_log("[syncvaprovidersAction] Cannot fetch site ARGO status info. Reason: " . $e->getMessage());
			}
			foreach ($docs as $doc) {				
				try {
	//				db()->query("SELECT process_site_argo_status(?::jsonb[])", array(php_to_pg_array($statinfo))); 
					$sp_vap = "sync_egi_downtime_done" . (microtime(true) * 10000);
					db()->query("SAVEPOINT $sp_vap");
					db()->query("INSERT INTO egiis.argo(pkey, egroup, j, h) VALUES (?, ?, ?, ?)", array(
						$doc->info->SiteEndpointPKey,
						$doc->info->StatusEndpointGroup,
						json_encode($doc),
						$doc->meta->hash
					)); 
					db()->query("RELEASE SAVEPOINT $sp_vap");
				} catch (Exception $e) {
					db()->query("ROLLBACK TO SAVEPOINT $sp_vap");
					error_log("[syncvaprovidersAction] DB error while processing site ARGO status. Operation will continue. Message: " . $e->getMessage());
				}
			}
			/* ARGO status END */

			db()->setFetchMode(Zend_Db::FETCH_NUM);
			$nres = db()->query("SELECT egiis.sitej_changes()")->fetchAll();
			if (count($nres) > 0) {
				$nres = $nres[0];
				if (count($nres) > 0) {
					$nres = $nres[0];
					$nres = pg_to_php_array($nres);
				} else {
					$nres = null;
				} 
			} else {
				$nres = 0;
			}
			$res = db()->query("SELECT refresh_sites(?)", array($this->vaSyncScopes))->fetchAll(); 
			if (count($res) > 0) {
				$res = $res[0];
				if (count($res) > 0) {
					$res = $res[0];
				} else {
					$res = null;
				}
			} else {
				$res = null;
			};
			if ($res == 0) {
				error_log("[syncvaprovidersAction] Sync operation complete, no data changed");
			}

			db()->commit();
			$inTransaction = false;
		} catch(Exceptions\CouchNotFoundException $e) {
			$success = false;
			$error_message = $e->getMessage();
			if($e->getCode() == 404) {
				error_log("[syncvaprovidersAction] Sync operation failed due to CouchDB error. Reason: GOCDB document not found. Operation aborted." );
			} else {
				error_log("[syncvaprovidersAction] Sync operation failed due to CouchDB error. Reason: " . $e->getMessage() . ". Operation aborted.");
			}
			if ($inTransaction) {
				db()->rollBack();
				$inTransaction = false;
			}
		} catch(Exception $e){
			$success = false;
			$error_message = $e->getMessage();
			error_log("[syncvaprovidersAction] Sync operation failed. Reason: " . $e->getMessage() . ". Operation aborted.");
			if ($inTransaction) {
				db()->rollBack();
				$inTransaction = false;
			}
		}

		if (($res != 0) && $success) {
			error_log("[syncvaprovidersAction] Sync operation compete, data change code: $res");
			try {
				// clean potantially related filter cache
				db()->query("DELETE FROM cache.filtercache WHERE m_from LIKE '%FROM sites%'");
				db()->query("DELETE FROM cache.filtercache WHERE m_from LIKE '%FROM va_provider%'");
			} catch(Exception $e) {
				$error_message = $e->getMessage();
				error_log("[syncvaprovidersAction] Error while cleaning filter cache. Reason: " . $e->getMessage());
			}

			// give the commit some time to settle before creating VA provider cache and notifying the dashboard
			sleep(2);
			// create VA providers cache
			$this->makeVAprovidersCache();
			// create VA providers cache for all service types, including native OpenStack endpoints (default resource only lists OCCI endpoints)
			$this->makeVAprovidersCache("all");
		}

		if ($success) {
			// notify dashboard
			if ( strtolower($_SERVER["SERVER_NAME"]) == "appdb.egi.eu" ) {
				web_get_contents("https://dashboard.appdb.egi.eu/services/appdb/sync/cloud");
			}
		}
	
		$endTime = microtime(true);
		error_log("[syncvaprovidersAction] Sync operation took " . number_format($endTime - $startTime, 2) . "s");
		// echo response
		header('Content-type: text/xml');
		echo '<' . '?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
		if ($success) {
			echo "<result success='true'";
			$dt = ($endTime - $startTime);
			echo " time='" . number_format($dt, 2) . "'";
			echo " changed='" . ($res == 0 ? "false" : "true") . "'";
			echo " changecode='" . $res . "'";
			// print number of ins, upd, del for sites, only if the sites_changed bit is set
			if ($res & 1) {
				if (is_array($nres)) {
					if (count($nres) == 3) {
						echo " inserted='" . $nres[0] . "' updated='" . $nres[1] . "' deleted='" . $nres[2] . "'";	
					}
				}
			} else {
				echo " inserted='0' updated='0' deleted='0'";
			}
			echo " />";
		} else {
//			ExternalDataNotification::sendNotification('Sites::syncSites', $error_message, ExternalDataNotification::MESSAGE_TYPE_ERROR);
			echo "<result success='false'";
			$dt = ($endTime - $startTime);
			echo " time='" . number_format($dt, 2) . "'";
			echo " error='" . xml_escape($error_message). "' />";
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 500 Internal Server Error");
			$this->getResponse()->setHeader("Status","500 Internal Server Error");
		}

	}	
}
