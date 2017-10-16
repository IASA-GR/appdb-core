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
		$this->vaSyncScopes = $this->vaSyncScopes['va_sync_scopes'];

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
	
// OBSOLETE	
// 	public function syncsitesAction(){
// 		$this->_helper->layout->disableLayout();
// 		$this->_helper->viewRenderer->setNoRender();
// 		$islocal = localRequest();
// 		
// 		$update =  ( $this->_getParam("update") != null )?$this->_getParam("update"):"true";
// 		$update = ( strtolower(trim($update)) === "false" )?false:true;
// 		
// 		$force =  ( $this->_getParam("force") != null )?$this->_getParam("force"):"true";
// 		$force = ( strtolower(trim($force)) === "true" )?true:false;
// 		
// 		if( !$islocal ){
// 			header('HTTP/1.0 404 Not Found');
// 			header("Status: 404 Not Found");
// 			return;
// 		}
// 		header('Content-type: text/xml');
// 		echo '<' . '?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
// 		$result = Gocdb::syncSites( $update, $force );
// 		db()->query("REFRESH MATERIALIZED VIEW CONCURRENTLY sites;");
// 		db()->query("SELECT request_permissions_refresh();");
// 		db()->query("REFRESH MATERIALIZED VIEW site_services_xml;");
// 		db()->query("REFRESH MATERIALIZED VIEW site_service_images_xml;");
// 		db()->query("DELETE FROM cache.filtercache WHERE m_from LIKE '%FROM sites%'");
// 		if( is_array($result) ){
// 			echo "<result success='true'";
// 			if( isset($result["inserted"]) ){
// 				echo " inserted='" . $result["inserted"] . "'";
// 			}
// 			if( isset($result["updated"]) ){
// 				echo " updated='" . $result["updated"] . "'";
// 			}
// 			if( isset($result["deleted"]) ){
// 				echo " deleted='" . $result["deleted"] . "'";
// 			}
// 			echo " />";
// 			return;
// 		}
// 		
// 		$error_message = trim($result);
// 		if( is_string($result) === false ) {
// 			$error_message = 'Unknown error';
// 		}
// 		ExternalDataNotification::sendNotification('Sites::syncSites', $error_message, ExternalDataNotification::MESSAGE_TYPE_ERROR);
// 		echo "<result success='false' error='" . htmlspecialchars($error_message, ENT_QUOTES). "' />";
// 	}

	private function makeVAprovidersCache() {
		$copyfile = RestAPIHelper::getFolder(RestFolderEnum::FE_CACHE_FOLDER) . '../public/assets/rp/va_providers.xml';
		$hashfile = RestAPIHelper::getFolder(RestFolderEnum::FE_CACHE_FOLDER) . '../public/assets/rp/datahash';

		# truncate data hash file (i.e. sync operation in progress)
		$f_hashfile = @fopen($hashfile, "w");
		if ($f_hashfile !== false) { 
			fwrite($f_hashfile, "");
			fclose($f_hashfile);
		} else {
			$errors = error_get_last();
			error_log("[makeVAprovidersCache] Could not open+truncate VA providers cache data hash file. Reason: " . $errors['message']);
		}
		$uri = 'https://' . $_SERVER['APPLICATION_API_HOSTNAME'] . '/rest/latest/va_providers?listmode=details';
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
		foreach ( glob(RestAPIHelper::getFolder(RestFolderEnum::FE_CACHE_FOLDER) .'/query_RestVAProvidersList_*.xml') as $cachefile ) {
			@unlink($cachefile);
		}
		error_log('VA providers RESTful API XML cache STARTED');
		// 1st call creates the cache
		$result = curl_exec($ch);
		error_log('VA providers RESTful API XML cache DONE');		
		// 2nd call returns the cached response
		$result = curl_exec($ch);
		$result_tmp = @gzdecode($result);
		if ($result_tmp !== false) {
			$result = $result_tmp;
		}
		$ck = "";
		try {
			$xmlresult = new SimpleXMLElement($result);
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
					debug_log("[makeVAprovidersCache] cache key is " . $ck);
				} else {
					error_log("[makeVAprovidersCache] Did not find cache key in XML response");
				}
			} else {
				error_log("[makeVAprovidersCache] Could not find appdb:appdb root element in XML response");
			}
		} catch (Exception $e) {
			error_log("[makeVAprovidersCache] Could not parse respone as XML. Reason: " . $e->getMessage());
		}
		curl_close($ch);
		if ($ck != "") {
			if (!@copy(RestAPIHelper::getFolder(RestFolderEnum::FE_CACHE_FOLDER) .'/query_RestVAProvidersList_' . $ck . '.xml', $copyfile)) {
				$errors = error_get_last();
				error_log("[makeVAprovidersCache] Could not copy VA providers cache file into assets. Reason: " . $errors['message']);
			} else {
				$f_hashfile = @fopen($hashfile, "w");
				if ($f_hashfile !== false) {
					fwrite($f_hashfile, $hash);
					fclose($f_hashfile);
				} else {
					$errors = error_get_last();
					error_log("[makeVAprovidersCache] Could not open+write VA providers cache data hash file. Reason: " . $errors['message']);
				}
				debug_log("Copied VA providers cache file into assets");
				debug_log("Data md5 is $hash");
			}
		} else {
			error_log("[makeVAprovidersCache] No VA providers cache file to copy into assets");
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
		try{
			db()->beginTransaction();
			$inTransaction = true;			
			$docs = $client->limit(1000)->find(['meta.collection'=>['$eq'=>'egi.goc.vaproviders']]);
			foreach ($docs as $doc) {
				$id = $doc->_id;
				db()->query('INSERT INTO vapj (pkey,j,h) VALUES (?, ?, ?)', array(
					$doc->info->SiteEndpointPKey,
					json_encode($doc),
					$doc->meta->hash
				));
				$tid = str_replace('egi.goc.vaproviders.', 'egi.top.vaproviders.', $id) . '.glue' . $glueVer;
				try {
					$sp_vap = "sync_egi_vap_tvapj" . (microtime(true) * 10000);
					db()->query("SAVEPOINT $sp_vap");
					$release_sp_vap = true;
					$tdoc = $client->getDoc($tid);
					db()->query('INSERT INTO tvapj (pkey,j,h) VALUES (?, ?, ?)', array(
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
				db()->query('INSERT INTO sitej (pkey,j,h) VALUES (?, ?, ?)', array(
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
			}
			$dtinfo = array();
			foreach ($docs as $doc) {				
				$dtinfo[] = json_encode($doc);
			}
			try {
				db()->query("SELECT process_site_downtimes(?::jsonb[])", array(php_to_pg_array($dtinfo))); 
			} catch (Exception $e) {
					db()->query("ROLLBACK TO SAVEPOINT $sp_vap");
					$release_sp_vap = false;
					error_log("[syncvaprovidersAction] DB error while processing site downtime info. Operation will continue. Message: " . $e->getMessage());
			}
			if ($release_sp_vap) {
				db()->query("RELEASE SAVEPOINT $sp_vap");
			}
			/* Downtimes END */

			/* ARGO status START */
			$sp_vap = "sync_egi_downtime_done" . (microtime(true) * 10000);
			db()->query("SAVEPOINT $sp_vap");
			$release_sp_vap = true;
			try {
				$docs = $client->limit(1000)->find(['meta.collection'=>['$eq'=>'egi.argo.vaproviders']]);
			} catch (Exception $e) {
				error_log("[syncvaprovidersAction] Cannot fetch site ARGO status info. Reason: " . $e->getMessage());
			}
			$statinfo = array();
			foreach ($docs as $doc) {				
				$statinfo[] = json_encode($doc);
			}
			try {
//				echo "SELECT process_site_argo_status('" . php_to_pg_array($statinfo) . "'::jsonb[])\n"; 
				db()->query("SELECT process_site_argo_status(?::jsonb[])", array(php_to_pg_array($statinfo))); 
			} catch (Exception $e) {
					db()->query("ROLLBACK TO SAVEPOINT $sp_vap");
					$release_sp_vap = false;
					error_log("[syncvaprovidersAction] DB error while processing site ARGO status. Operation will continue. Message: " . $e->getMessage());
			}
			if ($release_sp_vap) {
				db()->query("RELEASE SAVEPOINT $sp_vap");
			}
			/* ARGO status END */

			db()->query("SELECT refresh_sites(?)", array($this->vaSyncScopes)); 
			db()->commit();
			
			$this->makeVAprovidersCache();
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
			}
		} catch(Exception $e){
			$success = false;
			$error_message = $e->getMessage();
			error_log("[syncvaprovidersAction] Sync operation failed. Reason: " . $e->getMessage() . ". Operation aborted.");
			if ($inTransaction) {
				db()->rollBack();
			}
		}
		header('Content-type: text/xml');
		echo '<' . '?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
		if ($success) {
			echo "<result success='true'";
			$endTime = microtime(true);
			$dt = ($endTime - $startTime);
			echo " time='" . $dt . "'";
			echo " />";
		} else {
			ExternalDataNotification::sendNotification('Sites::syncSites', $error_message, ExternalDataNotification::MESSAGE_TYPE_ERROR);
			echo "<result success='false' error='" . htmlspecialchars($error_message, ENT_QUOTES). "' />";
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 500 Internal Server Error");
			$this->getResponse()->setHeader("Status","500 Internal Server Error");
		}

	}	
}
