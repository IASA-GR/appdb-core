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

require_once('email_service.php');

class VMCaster{
	private static $vmcasterurl = null;
	public static function getVMCasterUrl(){
		if( VMCaster::$vmcasterurl === null ){
			require_once('Zend/Config/Ini.php');
			$conf = new Zend_Config_Ini(__DIR__ . '/../application/configs/application.ini', $_SERVER['APPLICATION_ENV']);
			$appconf = $conf->app;
			VMCaster::$vmcasterurl = $appconf->vmcasterUrl;
		}
		return VMCaster::$vmcasterurl;
	}
	
	public static function transformXml($vmcxml = null, $apiversion = '1.0') {
		$envelop_start = '<appdb:appdb xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:appdb="http://appdb.egi.eu/api/1.0/appdb" xmlns:application="http://appdb.egi.eu/api/1.0/application" xmlns:discipline="http://appdb.egi.eu/api/1.0/discipline" xmlns:category="http://appdb.egi.eu/api/1.0/category" xmlns:dissemination="http://appdb.egi.eu/api/1.0/dissemination" '.
				'xmlns:person="http://appdb.egi.eu/api/' . $apiversion . '/person" '.
				'xmlns:permission="http://appdb.egi.eu/api/' . $apiversion . '/permission" '.
				'xmlns:privilege="http://appdb.egi.eu/api/' . $apiversion . '/privilege" '.
				'xmlns:user="http://appdb.egi.eu/api/' . $apiversion . '/user" '.
				'xmlns:virtualization="http://appdb.egi.eu/api/' . $apiversion . '/virtualization" '.
				'datatype="virtualization" version="' . $apiversion . '">';
		$envelop_end = '</appdb:appdb>';
		$result = '';
		
		if( $vmcxml !== null && trim($vmcxml) !== "" && !is_numeric($vmcxml) ) {
			if( strpos($vmcxml, "<" . "?xml") !== 0 ) {
				$vmcxml = '<' . '?xml version="1.0" encoding="utf-8"?' . '>' . $vmcxml;
			}
			
			try {
				$grouped = xml_transform(APPLICATION_PATH . "/configs/api/1.0/xslt/vmc2appdb_group.xsl", $vmcxml);
				if ($grouped === false) {
					throw new Exception("[VMCaster::transformXml] Error while applying XSL transformation #1");
				} else {
					$result = xml_transform(APPLICATION_PATH . "/configs/api/1.0/xslt/vmc2appdb.xsl", $grouped);
					if ($result === false) {
						throw new Exception("[VMCaster::transformXml] Error while applying XSL transformation #2");
					}
				}
			} catch(Exception $e) {
				error_log($e->getMessage());
				$result = '';
			}
		}
		
		return $envelop_start . $result . $envelop_end;
	}
	public static function createImageList($vaversionid, $target="published"){
		//Call vmcaster service to produce image list
		$url = VMCaster::getVMCasterUrl() . "/" . "vmlistcontroller/create/" . $vaversionid . "/".$target;
		$result = web_get_contents($url);
		if( $result === false ){
			error_log("[VAPP:VMCaster:createImageList]:Could not retrieve response data from " . $url);
			return "Could not retrieve response data from " . $url;
		}
		return true;
	}
	private static function parseIntegrityCheckResponse($response){
		if( is_string($response) && substr($response, 0, strlen('[ERROR]')) === '[ERROR]') {
			return array(
				"status"=> "error",
				"message" => substr($response, strlen('[ERROR]'))
			);
		}
		
		$result = array("id"=>null,"status"=>null,"message"=>null);
		$xml = simplexml_load_string($response);
		if ($xml === false) {
			return array(
				"status"=> "error",
				"message" => "[VMCaster::parseIntegrityCheckResponse] Cannot parse response data as XML"
			);
		}
		if( count($xml->xpath("./id")) > 0 ){
			$id = $xml->xpath("./id");
			$result["id"] = intval($id[0]);
		}
		if( count($xml->xpath("./status")) > 0 ){
			$status = $xml->xpath("./status");
			$result["status"] = strval($status[0]);
		}
		if( count($xml->xpath("./message")) > 0 ){
			$message = $xml->xpath("./message");
			$result["message"] = strval($message[0]);
		}
		$images = array();
		if( count($xml->xpath("//details/image")) > 0 ){
			$ximgs = $xml->xpath("//details/image");
			$immgcount = count($xml->xpath("//details/image"));
			
			for($i=0; $i<$immgcount; $i+=1){
				$ximage = $ximgs[$i];
				if( count($ximage->xpath("./id")) > 0 ){
					$image["id"] = $ximage->xpath("./id");
					$image["id"] = intval($image["id"][0]);
				}
				if( count($ximage->xpath("./status")) > 0 ){
					$image["status"] = $ximage->xpath("./status");
					$image["status"] = strval($image["status"][0]);
				}
				if( $image["status"] === "ignore" ){
					continue;
				}
				$image["http"] = array();
				if( count($ximage->xpath("./details/Server")) > 0 ){
					$image["http"]["server"] = $ximage->xpath("./details/Server");
					$image["http"]["server"] = strval($image["http"]["server"][0]);
				}
				if( count($ximage->xpath("./details/Date")) > 0 ){
					$image["http"]["date"] = $ximage->xpath("./details/Date");
					$image["http"]["date"] = strval($image["http"]["date"][0]);
				}
				if( count($ximage->xpath("./details/Content-Type")) > 0 ){
					$image["http"]["contenttype"] = $ximage->xpath("./details/Content-Type");
					$image["http"]["contenttype"] = strval($image["http"]["contenttype"][0]);
				}
				if( count($ximage->xpath("./details/Content-Length")) > 0 ){
					$image["http"]["contentlength"] = $ximage->xpath("./details/Content-Length");
					$image["http"]["contentlength"] = intval($image["http"]["contentlength"][0]);
				}
				if( count($ximage->xpath("./details/Last-Modified")) > 0 ){
					$image["http"]["lastmodified"] = $ximage->xpath("./details/Last-Modified");
					$image["http"]["lastmodified"] = strval($image["http"]["lastmodified"][0]);
				}
				if( count($ximage->xpath("./details/Connection")) > 0 ){
					$image["http"]["connection"] = $ximage->xpath("./details/Connection");
					$image["http"]["connection"] = strval($image["http"]["connection"][0]);
				}
				if( count($ximage->xpath("./details/Accept-Ranges")) > 0 ){
					$image["http"]["acceptranges"] = $ximage->xpath("./details/Accept-Ranges");
					$image["http"]["acceptranges"] = strval($image["http"]["acceptranges"][0]);
				}
				if( count($ximage->xpath("./details/Code")) > 0 ){
					$image["http"]["code"] = $ximage->xpath("./details/Code");
					$image["http"]["code"] = intval($image["http"]["code"][0]);
				}
				if( count($ximage->xpath("./details/message")) > 0 ){
					$image["http"]["message"] = $ximage->xpath("./details/message");
					$image["http"]["message"] = strval($image["http"]["message"][0]);
				}
				$image["process"] = array();
				if( count($ximage->xpath("./size")) > 0 ){
					$image["process"]["size"] = $ximage->xpath("./size");
					$image["process"]["size"] = strval($image["process"]["size"][0]);
				}
				if( count($ximage->xpath("./downloaded")) > 0 ){
					$image["process"]["downloaded"] = $ximage->xpath("./downloaded");
					$image["process"]["downloaded"] = strval($image["process"]["downloaded"][0]);
				}
				if( count($ximage->xpath("./downloaded_percentage")) > 0 ){
					$image["process"]["percentage"] = $ximage->xpath("./downloaded_percentage");
					$image["process"]["percentage"] = strval($image["process"]["percentage"][0]);
				}
				$images[] = $image;
			}
		}
		$result["images"] = $images;
		
		return $result;
	}
	public static function clearIntegrityCheck($vaversionid){
		$vapplists = new Default_Model_VALists();
		$vapplists->filter->vappversionid->equals($vaversionid);
		if( count($vapplists->items) > 0 ){
			for($i=0; $i<count($vapplists->items); $i+=1){
				$vapplist = $vapplists->items[$i];
				$instance = $vapplist->getVMIinstance();
				if( $instance->autointegrity === true ){
					$instance->integrityStatus = "";
				}
				$instance->integrityMessage = "";
				$instance->save();
			}
		}
		$vaversions = new Default_Model_VAversions();
		$vaversions->filter->id->equals($vaversionid);
		if( count($vaversions->items) > 0 ){
			$vaversion = $vaversions->items[0];
			$vaversion->status = "canceled";
			$vaversion->save();
		}
	}
	public static function needsIntegrityCheck($vaversionid){
		$valists = new Default_Model_VALists();
		$valists->filter->vappversionid->equals($vaversionid);
		if( count($valists->items) > 0 ){
			for( $i=0; $i<count($valists->items); $i+=1 ){
				$valist = $valists->items[$i];
				$inst = $valist->getVMIinstance();
				if( $inst !== null && $inst->autointegrity === true ){
					return true;
				}
			}
		}
		return false;
	}
	public static function startIntegrityCheck($vaversionid){
		$versions = new Default_Model_VAVersions();
		$versions->filter->id->equals($vaversionid);
		if( count($versions->items) === 0 ){
			return false;
		}
		$version = $versions->items[0];
		$prevstatus = $version->status;
		//first cancel any running integrity check for this version
		VMCaster::cancelIntegrityCheck($vaversionid);
		$version->status = $prevstatus;
		$version->save();
		
		if( !($version->published=== false && $version->archived === false && ($version->status=="verifing" || $version->status == "verifingpublish") && $version->enabled===true) ){
			return false;
		}
		
		$url = VMCaster::getVMCasterUrl() . "/integrity/checkimagelist/".$vaversionid."/xml";
		
		try{
			$xml = web_get_contents($url);
			if( trim($xml) === "" ){
				throw new Exception('Could not connect to integrity check service. Please, try again later.');
			}
			$result = VMCaster::parseIntegrityCheckResponse($xml);
		} catch( Exception $ex) {
			$result = VMCaster::parseIntegrityCheckResponse('[ERROR]' . $ex->getMessage());
			
			if( intval($vaversionid) > 0 ) {
				VMCaster::cancelIntegrityCheck($vaversionid);
			}
			
			return $result;
		}
		
		$allimagesfailed = true;
		if( $result["status"] !== "success" ){
			for($i=0; $i<count($result["images"]); $i+=1){
				$image = $result["images"][$i];
				if( $image["status"] === "error" ){
					$instances = new Default_Model_VMIinstances();
					$instances->filter->id->equals($image["id"]);
					if( count($instances->items) > 0 ) {
						$instance = $instances->items[0];
						if($instance->autointegrity == true ){
							$instance->integrityStatus = $image["status"];
						}else{
							$instance->integrityStatus = "warning";
						}
						$instance->integrityMessage = $image["http"]["message"];//"Server responded with code: " . $image["http"]["code"];
						$instance->save();
					}
				}else{
					$allimagesfailed = false;
				}
			}
		}else{
			$allimagesfailed = false;
		}
		
		if( $allimagesfailed === true ){
			$version->status = 'failed';
		}else{
			//'verifing';
		}
		$needscheck = self::needsIntegrityCheck($version->id);
		if( $needscheck === false ){
			$version->status = 'verified';
		}else{
			if( $version->status === 'verify' ){
				$version->status = 'verifing';
			}
			if( $version->status === 'verifypublish' ){
				$version->status = 'verifingpublish';
			}
		}
		
		$version->save();
		return $result;
	}
	public static function cancelIntegrityCheck($vaversionid){
		//clear statuses
		VMCaster::clearIntegrityCheck($vaversionid);
		
		$url = VMCaster::getVMCasterUrl() . "/integrity/cancelimageList/".$vaversionid."/xml";
		$xml = web_get_contents($url);		
		if( trim($xml) === "" ) return false;
		$result = VMCaster::parseIntegrityCheckResponse($xml);
		return $result;
	}
	
	public static function statusIntegrityCheck($vaversionid){
		$url = VMCaster::getVMCasterUrl() . "/integrity/statusimageList/".$vaversionid."/xml";
		try{
			$xml = web_get_contents($url);		
			if( trim($xml) === "" ){
				throw new Exception('Could not connect with integrity check service. Please, try again later.');
			}
			$result = VMCaster::parseIntegrityCheckResponse($xml);
		} catch( Exception $ex) {
			$result = VMCaster::parseIntegrityCheckResponse('[ERROR]' . $ex->getMessage());
			return $result;
		}
		
		$newres = VMCaster::syncStatusIntegrityCheck($result);
		return $newres;
	}
	private static function getVerifiedResponse($version){
		$newimagelist = array();
		$images = array();
		$newimagelist["id"] = $version->id;
		if( $version->status === "failed" ){
			$newimagelist["status"] = "error";
		}else if( $version->status === "verified" ){
			$newimagelist["status"] = "success";
		}else if( $version->status === "verifing" ){
			$newimagelist["status"] = "running";
		}else {
			$newimagelist["status"] = $version->status;
		}
		$newimagelist["message"] = $newimagelist["status"];
		
		$vapplists = new Default_Model_VALists();
		$vapplists->filter->vappversionid->equals($version->id);
		if( count($vapplists->items) > 0 ){
			$isrunning = false;
			for($i=0; $i<count($vapplists->items); $i+=1){
				$vapplist = $vapplists->items[$i];
				$img = $vapplist->getVMIinstance();
				if( !$img || $img->integrityStatus == "" ) continue;
				if( $img->integrityStatus === "success" && $img->integrityMessage !== "current" ) continue;
				$image = array(
					"id" => $img->id, 
					"status" => $img->integrityStatus, 
					"message"=> $img->integrityMessage,
					"http"=>array(),
					"process"=>array("size" => $img->size,"downloaded"=>$img->size,"percentage"=>100));
				if( in_array($image["status"], array("downloading","checksuming"))){
					$image["status"] = "running";
				}
				if ($image["status"] === "success" ){
					$image["message"] = "success";
				}
				if( $image["status"] === "running"){
					$isrunning = true;
				}
				$images[] = $image;
			}
		}
		$newimagelist["images"] = $images;
		if( $isrunning === true ){
			$newimagelist["status"] = "running";
			$newimagelist["message"] = "running";
		}
		return $newimagelist;
	}
	private static function syncStatusIntegrityCheck($res){
		$tobepublished = false;
		if( !$res || (is_array($res) && ( !isset($res["images"]) || !isset($res["status"]) || !isset($res["id"]) ) ) ) return;
		$versions = new Default_Model_VAversions();
		$versions->filter->id->equals($res["id"]);
		if( count($versions->items) === 0 ){
			return $res;
		}
		$version = $versions->items[0];
		if( $version->status === "init" || $version->status === "verified" || $version->status === "ready"){
			return $res;
		}
		$images = $res["images"];
		$hasimageerrors = false;
		$successfulimages = array();
		$isrunning = false;
		for($i=0; $i<count($images); $i+=1){
			$image = $images[$i];
			$process = $image["process"];
			$instances = new Default_Model_VMIinstances();
			$instances->filter->id->equals($image["id"]);
			if( count($instances->items) === 0 ) continue;
			$instance = $instances->items[0];
			if( $instance->integrityStatus === "error" && $instance->autointegrity == true){
			}
			if( $instance->autointegrity == false || $image["status"] === "n/a" ) {
				continue;
			}
			
			switch($image["status"]){
				case "running":
					if( $process["percentage"] == 100 ){
						$instance->integrityStatus = "checksuming";
					}else{
						$instance->integrityStatus = "downloading";
					}
					$instance->integrityMessage = "current";
					$isrunning = true;
					break;
				case "success":
					$instance->integrityStatus = "success";
					$instance->integrityMessage = "current";
					$successfulimages[] = $instance->id;
					break;
				case "cancelled":
					$instance->integrityStatus = "canceled";
					$instance->integrityMessage = "";
					$hasimageerrors = true;
					break;
				case "error":
					if($instance->integrityStatus == "checksuming"){
						$instance->integrityMessage = "Error while calculating checksum";
					}else if($instance->integrityStatus == "downloading"){
						$instance->integrityMessage = "Error while downloading image";
					}else{
						$instance->integrityMessage = "Unknown error";
					}
					$instance->integrityStatus = "error";
					$hasimageerrors = true;
				default:
					continue;
			}
			$instance->save();
		}
		if( $isrunning === true ){
			$res["status"] = "running";
			$res["message"] = "running";
		}
		switch($res["status"]){
			case "running":
				if( trim($version->status) !== "verifingpublish"){
					$version->status = "verifing";
				}
				break;
			case "success":
				if( $version->status === "verifing" || $version->status === "verifingpublish" ){
					if( $hasimageerrors === true ){
						$version->status = "failed";
					}else{
						if( $version->status === "verifingpublish" ){
							$tobepublished = true;
						}
						$version->status = "verified";
					}
				}
				break;
			case "canceled":
				if( $version->status === "verifing" || $version->status === "verifingpublish" ){
					$version->status = "canceled";
				}
				break;
			case "error":
				if( ($version->status === "verifing" || $version->status === "verifingpublish") && $isrunning === false){
					$version->status = "failed";
				}else if($isrunning === true){
					//"verifing";
				}
				break;
			default:
				return $res;
		}
		$version->save();
		if( in_array($version->status, array("canceled","failed","verified") ) === true ){
			for($i=0; $i<count($successfulimages); $i+=1){
				$instances = new Default_Model_VMIInstances();
				$instances->filter->id->equals($successfulimages[$i]);
				if( count($instances->items) > 0 ) {
					$img = $instances->items[0];
					if( $img->integrityStatus === "success"){
						$img->autointegrity = false;
						$img->integrityMessage = "current";
						$img->save();
					}
				}
			}
		}
		if( $tobepublished === true ){
			self::publishVersion($version);
			$res["published"] = "true";
		}else if($version->published === true){
			$res["published"] = "true";
		}
		return $res;
	}
	private static function publishVersion($version){
		$vaversions = new Default_Model_VAversions();
		$f = $vaversions->filter;
		$f->vappid->equals($version->vappid)->and($f->published->equals(true)->and($f->archived->equals(false)->and($f->id->notequals($version->id))));
		if( count( $vaversions->items ) > 0 ) {
			$latestversion = $vaversions->items[0];
			$latestversion->archived = true;
			$latestversion->save();
		}
		$version->published = true;
		$version->status = "verified";
		$version->createdon = "now()";
		$version->save();
		
		VMCaster::createImageList($version->id, "published");
	}
	public static function deleteVersion($version){
		try{
			$vapplists = new Default_Model_VALists();
			$vapplists->filter->vappversionid->equals($version->id);
			if( count($vapplists->items) > 0 ){
				for($i=0; $i<count($vapplists->items); $i+=1){
					$vapplist = $vapplists->items[$i];
					self::deleteVALists($vapplist);
				}
			}
			$version->delete();
		}catch(Exception $e){
			return $e->getMessage();
		}
		return true;
	}
	private static function deleteVALists($item){
		$inst = $item->getVMIInstance();
		self::deleteVMIInstance($inst);
		$item->delete();
	}
	private static function deleteVMIInstance($item){
		$instances = new Default_Model_VMIInstances();
		$instances->filter->vmiflavourid->equals($item->vmiflavourid)->and($instances->filter->id->notequals($item->id));
		if( count($instances->items) === 0 ){
			self::deleteFlavour($item->getFlavour(),$item);
		}
		self::deleteContextScripts($item->id);
		$item->delete();
	}
	private static function deleteContextScripts($vmiinstanceid){
		$scriptids = array();
		$vmiscripts = new Default_Model_VMIinstanceContextScripts();
		$vmiscripts->filter->vmiinstanceid->numequals($vmiinstanceid);
		if( count($vmiscripts->items) > 0 ){
			foreach($vmiscripts->items as $item){
				$scriptids[] = $item->contextscriptid;
				$vmiscripts->remove($item);
			}
		}
		$scriptids = array_unique($scriptids);
		//check if the referenced scripts have relations
		//if no relation found remove them from db.
		foreach($scriptids as $id){
			$vmiscripts = new Default_Model_VMIinstanceContextScripts();
			$vmiscripts->filter->contextscriptid->numequals($id);
			if( count($vmiscripts->items) === 0 ){
				$scripts = new Default_Model_ContextScripts();
				$scripts->filter->id->numequals($id);
				if( count($scripts->items) > 0 ){
					$scripts->remove($scripts->items[0]);
				}
			}
		}
	}
	private static function deleteFlavour($item,$parent){
		$instances = new Default_Model_VMIflavours();
		$instances->filter->vmiid->equals($item->vmiid)->and($instances->filter->id->notequals($parent->id));
		if( count($instances->items) === 0 ){
			self::deleteVMI($item->getVMI());
			$item->delete();
		}
	}
	private static function deleteVMI($item, $parent){
		$item->delete();
	}
	
	private static function instanceInLatestVersion($instance){
		$version = $instance->getVAVersion();
		if( $version && $version->published === true && $version->archived === false && $version->enabled === true ){
			return $version;
		}
		return null;
	}
	private static function getInstanceContextScript($instance){
		$vmiscripts = new Default_Model_VMIinstanceContextScripts();
		$vmiscripts->filter->vmiinstanceid->numequals($instance->id);
		if( count($vmiscripts->items) > 0 ){
			$vmiscript = $vmiscripts->items[0];
			return $vmiscript->getContextScript();
		}
		return null;
	}
	public static function getImageInfoById($imageid,$identifier=null,$strict=false){
		if( $imageid !== null && !is_numeric($imageid)) return null;
		if( $identifier!==null && trim($identifier) === "") return null;
		
		//check if image with identifier exists
		$vmiinstances = new Default_Model_VMIinstances();
		if( $identifier !== null ){
			$vmiinstances->filter->id->equals($imageid)->and($vmiinstances->filter->guid->equals(trim($identifier)));
		}else{
			$vmiinstances->filter->id->equals($imageid);
		}
		if( count($vmiinstances->items) === 0 ) return null;
		
		$instance = $vmiinstances->items[0];
		$originalimageid = $instance->id;
		//Get good vmi instance id (same with up to date metadata)
		if( $strict === false ){
			db()->setFetchMode(Zend_Db::FETCH_BOTH);
			$res = db()->query("SELECT get_good_vmiinstanceid(?)", array($instance->id))->fetchAll();
			if (count($res) > 0) {
				$res = $res[0];
			}else{
				$res = null;
			}
			if (count($res) > 0) {
				$res = $res[0];
			}else{
				$res = null;
			}
			//if good instance id differs use that one
			if ($res && is_numeric($res) && intval($res) !== intval($instance->id)) {
				$originalimageid = $instance->id;
				$images = new Default_Model_VMIinstances();
				$images->filter->id->numequals(intval($res));
				if( count($images->items) > 0 ){
					$instance = $images->items[0];
				}
			}
		}
		
		$version = $instance->getVAVersion();
		if( $version === null )return null;
		$vapp = $version->getVa();
		if( $vapp === null ) return null;
		$result = array("va"=>$vapp,"version"=>$version,"image"=>$instance);
		if( $originalimageid !== $instance->id ){
			$result["requested_id"] = $originalimageid;
		}
		
		//Retrieve conetxt script associated with image (if any)
		$contextscript = self::getInstanceContextScript($instance);
		if( $contextscript !== null){
			$result["contextscript"] = $contextscript;
		}
		return $result;
	}
	public static function getImageInfoByIdentifier($identifier){
		
		//Check if parameters are valid
		if( trim($identifier) === "" ) return null;
		
		//check if image with identifier exists
		$instances = new Default_Model_VMIinstances();
		$instances->filter->guid->equals($identifier);
		if( count($instances->items) === 0 ) return null;
		
		//Retrieve virtual appliance of image
		$instance = $instances->items[0];
		$version = $instance->getVAVersion();
		if( $version === null )return null;
		$vapp = $version->getVa();
		if( $vapp === null ) return null;
		
		//Check latest version 
		$latest = $vapp->getLatestVersion();
		if( $latest !== null ){
			$image = $latest->getImageByIdentifier($identifier);
			if( $image !== null ){
				$result = array("va"=>$vapp,"version"=>$latest,"image"=>$image);
				//Retrieve conetxt script associated with image (if any)
				$contextscript = self::getInstanceContextScript($instance);
				if( $contextscript !== null){
					$result["contextscript"] = $contextscript;
				}
				return $result;
			}
		}
		
		//check previous versions
		$previous = $vapp->getArchivedVersions();
		if( $previous === null || count($previous) === 0 ) return null;
		
		for($i=0; $i<count($previous); $i+=1){
			$prev = $previous[$i];
			$image = $prev->getImageByIdentifier($identifier);
			if( $image === null )continue;
			$result = array("va"=>$vapp,"version"=>$prev,"image"=>$image);
			//Retrieve conetxt script associated with image (if any)
			$contextscript = self::getInstanceContextScript($instance);
			if( $contextscript !== null){
				$result["contextscript"] = $contextscript;
			}
			return $result;
		}
		
		return null;
	}
	private static function convertArrayToXML($data, &$xml){
		foreach($data as $key => $value) {
			if(is_array($value)) {
				if(!is_numeric($key)){
					$subnode = $xml->addChild("$key");
					self::convertArrayToXML($value, $subnode);
				}
				else{
					$subnode = $xml->addChild("item$key");
					self::convertArrayToXML($value, $subnode);
				}
			}
			else {
				$xml->addChild("$key", htmlspecialchars("$value",ENT_XML1 | ENT_COMPAT,'UTF-8'));
			}
		}
	}
	public static function getSitesByVMI($guid,$id){
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$sql = "SELECT distinct sites.id AS site_id, 
				sites.name AS site_name, 
				sites.officialname AS site_officialname, 
				sites.portalurl AS site_portalurl, 
				sites.homeurl AS site_homeurl, 
				va_provider_images.mp_uri AS mp_uri, 
				vos.name AS vo_name, 
				vowide_image_lists.state AS voimagelist_state, 
				vowide_image_list_images.state AS voimage_state,
				va_providers.id AS service_id,
				va_providers.url AS service_url,
				va_providers.gocdb_url AS service_gocdb_url,
				va_providers.hostname AS service_hostname,
				va_providers.ngi AS service_ngi,
				va_provider_images.va_provider_image_id as occi_id,
				va_provider_endpoints.endpoint_url as occi_endpoint
			FROM sites
			INNER JOIN va_providers ON va_providers.sitename = sites.name
			INNER JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id
			INNER JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
			LEFT OUTER JOIN vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid
			LEFT OUTER JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id
			LEFT OUTER JOIN vos ON  vos.id = vowide_image_lists.void
			LEFT OUTER JOIN va_provider_endpoints ON va_provider_endpoints.va_provider_id = va_providers.id
			WHERE  (vaviews.vmiinstance_guid = ? 
			AND vaviews.vmiinstanceid = ?)
			AND (vowide_image_lists.state <> 'draft' OR vowide_image_lists.state is NULL)
			AND (va_providers.id = va_provider_images.va_provider_id)";
		$items = db()->query($sql,array($guid,$id))->fetchAll();
		$sites = array();
		foreach($items as $item){
			if( count($sites) === 0 || isset($sites[$item['site_name']]) === false ){
				$sites[$item['site_name']] = array(
					'id' => $item['site_id'],
					'name' => $item['site_name'],
					'officialname' => $item['site_officialname'],
					'url' => array(
						'portal' => $item['site_portalurl'],
						'home' => $item['site_homeurl']
					),
					'services' => array()
				);
			}
			$site = $sites[$item['site_name']];
			$services = $site['services'];
			$srvindex = -1;
			for($i=0; $i< count($services); $i+=1){
				$s = $services[$i];
				if( $s['id'] === $item['service_id'] ){
					$srvindex = $i;
					break;
				}
			}
			if( $srvindex  === -1 ){
				$service = array('id' => $item['service_id'],
					'hostname' => $item['service_hostname'],
					'url' => array(
						'default' => $item['service_url'],
						'gocdb' => $item['service_gocdb_url']
					),
					'ngi' => $item['service_ngi'],
					'vos' => array()
				);
			}else{
				$service = $services[$srvindex];
			}
			
			$vos = $service['vos'];
			$voindex = -1;
			for($i=0; $i< count($vos); $i+=1){
				$s = $vos[$i];
				if (array_key_exists("id", $s)) {
					if( $s['id'] === $item['service_id'] ){
						$voindex = $i;
						break;
					}
				}
			}
			
			if( $voindex === -1 ){
				$vo = array(
					'name' => (trim($item['vo_name'])===''?'none':$item['vo_name']),
					'imageliststate' => $item['voimagelist_state'],
					'imagestate' => $item['voimage_state'],
					'url' => array(
						'operations_portal' => 'http://operations-portal.egi.eu/vo/view/voname/' . strtolower(trim($item['vo_name']))
					),
					'occi' => array(
						'id' => $item['occi_id'],
						'endpoint' => $item['occi_endpoint']
					)
				);
				if( trim($item['vo_name']) === '' ) {
					unset($vo['imageliststate']);
					unset($vo['imagestate']);
					unset($vo['url']);
				}
			}else{
				$vo = $vos[$voindex];
			}
			
			if( $voindex > -1 ){
				$vos[$voindex] = $vo;
			}else{
				$vos[] = $vo;
			}
			
			$service['vos'] = $vos;
			
			if( $srvindex > -1 ){
				$services[$srvindex] = $service;
			}else{
				$services[] = $service;
			}
			$site['services'] = $services;
			$sites[$item['site_name']] = $site;
		}
		
		return $sites;
	}
	public static function convertImage($data, $format='xml'){
		$result = "";
		$img = $data["image"];
		$flavour = $img->getFlavour();
		$arch = $flavour->getArch();
		$os = $flavour->getOs();
		$ver = $data["version"];
		$vapp = $data["va"];
		$vo = ( isset($data["vo"])?$data["vo"]:null );
		$voimage = ( isset($data["voimage"])?$data["voimage"]:null );
		$voimagelist = ( isset($data["voimagelist"])?$data["voimagelist"]:null );
		$app = $vapp->getApplication();
		$addedby = $img->getAddedBy();
		$updatedby = $img->getLastUpdatedBy();
		$d = array(
			"id" => $img->id,
			"identifier" => $img->guid,
			"version" => $img->version,
			"url" => $img->uri,
			"size" => $img->size,
			"checksum" => array( "hash"=>$img->checksumFunc, "value"=>$img->checksum ),
			"arch" => array( "id"=>$arch->id, "name"=>$arch->name ),
			"os" => array( "id"=>$os->id,"family"=>$os->name, "version"=> $flavour->osversion ),
			"format" => $flavour->format,
			"hypervisor" => $flavour->getHypervisors(),
			"title" => $img->title,
			"notes" => $img->notes,
			"description" => $img->description,
			"cores" => array( "minimum" => $img->coreMinimum, "recommended" => $img->coreRecommend ),
			"ram" => array( "minimum" => $img->RAMminimum, "recommended" => $img->RAMrecommend ),
			"addedon" => str_replace("+00:00","Z",gmdate("c", strtotime($img->addedon))),
			"addedby" => array( "id" => $addedby->id, "cname" => $addedby->cname, "firstname" => $addedby->firstname, "lastname" => $addedby->lastname, "permalink" =>  'https://'.$_SERVER['HTTP_HOST'].'/store/person/'.$addedby->cname),
			"published" => $ver->published,
			"archived" => $ver->archived,
			"vappliance" => array( "version" => $ver->version,),
			"application" => array( "id" => $app->id, "name" => $app->name, "cname" => $app->cname )
		);
		if( isset($d["hypervisor"]) ){
			if( is_array($d["hypervisor"]) ){
				$d["hypervisor"] = implode(",",$d["hypervisor"]);
			}
		}
		if( isset($data["id"]) ){
			$d["id"] = trim($data["id"]);
		}
		if( isset($data["requested_id"]) ){
			$d["requested_id"] = trim($data["requested_id"]);
		}
		if( isset($data["baseid"]) ){
			$d["baseid"] = trim($data["baseid"]);
		}
		if( isset($data["requested_baseid"]) ){
			$d["requested_baseid"] = trim($data["requested_baseid"]);
		}
		if( isset($data["identifier"]) ){
			$d["identifier"] = trim($data["identifier"]);
		}
		if( isset($data["baseidentifier"]) ){
			$d["baseidentifier"] = trim($data["baseidentifier"]);
		}
		if( isset($data["mpuri"]) ){
			$d["mpuri"] = trim($data["mpuri"]);
		}else{
			$d["mpuri"] = 'https://'.$_SERVER['HTTP_HOST'].'/store/vm/image/' .$img->guid . ':' . $img->id . '';
		}
		if( isset($data["basempuri"]) ){
			$d["basempuri"] = trim($data["basempuri"]);
		}
		if( isset($data["contextscript"]) &&  $data["contextscript"] !== null ){
			$cscript = $data["contextscript"];
			$d["contextscript"] = array("id"=> $cscript->id, "url" => $cscript->url, "hashtype" =>$cscript->checksumfunc, "checksum" => $cscript->checksum, "size"=>$cscript->size );
		}
		if( $vo !== null && $voimage !== null && $voimagelist !== null){
			$d["vo"] = array(
				"id"=>$vo->id, 
				"name"=> $vo->name, 
				"domain"=>$vo->domain->name, 
				"voimagelist"=> array("id"=>$voimagelist->id, "state"=>$voimagelist->state, "voimage"=> array("id"=>$voimage->id, "state"=>$voimage->state))
			);
		}
		if( trim($img->lastUpdatedOn)!== "" ){
			$d["lastupdatedon"] =  str_replace("+00:00","Z",gmdate("c", strtotime($img->lastUpdatedOn)));
			$d["lastupdatedby"] = array( "id" => $updatedby->id, "cname" => $updatedby->cname, "firstname" => $updatedby->firstname, "lastname" => $updatedby->lastname, "permalink" =>  'http://'.$_SERVER['HTTP_HOST'].'/store/person/'.$updatedby->cname );
		}
		if( trim($ver->createdon) !== "" ){
			$d["vappliance"]["createdon"] = str_replace("+00:00","Z",gmdate("c", strtotime($ver->createdon)));
		}
		if( trim($ver->archivedon) !== "" ){
			$d["vappliance"]["archivedon"] =  str_replace("+00:00","Z",gmdate("c", strtotime($ver->archivedon)));
		}
		if( trim($ver->expireson) !== "" ){
			$d["vappliance"]["expireson"] = str_replace("+00:00","Z",gmdate("c", strtotime($ver->expireson)));
		}
		$d["hypervisor"] = preg_replace("/[\\{\\}]/", "", $d["hypervisor"]);
		
		//Hide private data if needed
		if( isset($data["isprivateimage"]) && $data["isprivateimage"] === true && isset($data["canaccessprivate"]) && $data["canaccessprivate"] === false){
			$d["url"] = "";
			$d["checksum"] = "";
			$d["size"] = "";
		}
		
		if( isset($data["sites"]) ){
			$d["sites"] = array();
			foreach($data["sites"] as $site){
				$d["sites"][] = $site;
			}
		}
		if( $format === "xml" ){
			$d["published"] = ($d["published"] === true)?"true":"false";
			$d["archived"] = ($d["archived"] === true)?"true":"false";
			$xml = simplexml_load_string('<vmiinstance></vmiinstance>');
			self::convertArrayToXML($d, $xml);
			$result = $xml->asXML();
			$apiversion = "1.0";
			$result = substr($result, strpos($result, '?>') + 2);
			$result = '<?xml version="1.0" encoding="utf-8"?><appdb:appdb xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:appdb="http://appdb.egi.eu/api/1.0/appdb" xmlns:application="http://appdb.egi.eu/api/1.0/application" xmlns:discipline="http://appdb.egi.eu/api/1.0/discipline" xmlns:category="http://appdb.egi.eu/api/1.0/category" xmlns:dissemination="http://appdb.egi.eu/api/1.0/dissemination" '.
				'xmlns:person="http://appdb.egi.eu/api/' . $apiversion . '/person" '.
				'xmlns:virtualization="http://appdb.egi.eu/api/' . $apiversion . '/virtualization" '.
				'xmlns:site="http://appdb.egi.eu/api/' . $apiversion . '/site" '.
				'xmlns:siteservice="http://appdb.egi.eu/api/' . $apiversion . '/site" '.
				'xmlns:vo="http://appdb.egi.eu/api/' . $apiversion . '/vo" '.
				'datatype="virtualization" version="' . $apiversion . '">' . $result . '</appdb:appdb>';
			try {
				$result = xml_transform(APPLICATION_PATH . "/configs/api/1.0/xslt/virtualization.image.xsl", $result);
				if ($result === false) {
					throw new Exception("[VMCaster::convertImage] Error while applying XSL trasformation");
				}
			}catch(Exception $e){
				error_log($e->getMessage());
				return null;
			}
		}else if( $format === "json" ){ 
			$result = json_encode($d,JSON_HEX_TAG | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES );
		}
		return $result;
	}
	
	public static function getExpiredVappliances(){
		$query = "SELECT DISTINCT applications.id, applications.cname, applications.name, applications.owner, applications.addedby FROM vaviews " +
			"INNER JOIN applications ON applications.id = vaviews.appid" + 
			"WHERE va_version_published = TRUE AND va_version_archived = FALSE AND va_version_expireson::date < now()::date";
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$res = db()->query($query)->fetchAll();
		return $res;
	}
	public static function vappliancesToBeExpired($days = 5){
		$query = "SELECT DISTINCT applications.id, applications.cname, applications.name, applications.owner, applications.addedby FROM vaviews " +
			"INNER JOIN applications ON applications.id = vaviews.appid" + 
			"WHERE va_version_published = TRUE AND va_version_archived = FALSE";
		$countdays = null;
		if( is_numeric($days) ){
			$countdays = intval($days);
		}
		
		if( $countdays !== null && $countdays > 0 ){
			$query .= " AND (va_version_expireson::date) = (now()::date + " . $countdays . ") and va_version_expireson > now()";
		}else {
			$query .= " AND (va_version_expireson::date) < (now()::date)";
		}
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$res = db()->query($query)->fetchAll();
		return $res;	
	}
	
	public static function cleararchivedvappversions($appid, $fromindex){
		if( !is_numeric($appid) || $appid <= 0 ) {
			return "Invalid vapplication id";
		}
		
		if( !is_numeric($fromindex) || $fromindex <= 0){
			return "Invalid index value";
		}
		
		if( $fromindex < 20 ){
			//Make sure it will never delete all of the vapp archived versions
			$fromindex = 20;
		}
		
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$res = db()->query("select vapp_old_archived_versions(?,?);", array($appid, $fromindex))->fetchAll();
		$result = array();
		if( count($res) > 0 ){
			foreach($res as $r){
				$vappversions = new Default_Model_VAversions();
				$vappversions->filter->id->equals($r[0]);
				if( count($vappversions->items) > 0 ){
					$vappversion = $vappversions->items[0];
					$deleted = VMCaster::deleteVersion($vappversion);
					if( $deleted !== true ){
						error_log("[VMCaster::cleararchivedvappversions]: " . $deleted);
					}else{
						$result[] = $vappversion->id;
					}
				}
			}
		}		
		return implode(",",$result);
	}
}

class VMCasterNotifications{
	private static function getMaxPastDays(){
		return 30;
	}
	/*
	 * Returns user's entry for given id or cname
	 */
	public static function getUser($user){
		if( $user === null || ( is_string($user) && trim($user) === "" ) ) {
			return null;
		} else if( is_numeric($user) ){
			$userid = intval($user);
			$users = new Default_Model_Researchers();
			$users->filter->id->equals($userid);
			if( count($users->items) === 0 ){
				return null;
			}
			$user = $users->items[0];
		} else if( is_string($user) && trim($user) !== "" ){
			$usercname = trim($user);
			$users = new Default_Model_Researchers();
			$users->filter->cname->equals($usercname);
			if( count($users->items) === 0 ){
				return null;
			}
			$user = $users->items[0];
		}
		return $user;
	}
	/*
	 * Returns users with va management permissions for vappliances 
	 * that will be expired in given days (days > 0)
	 * or has been expired for given days (days < 0)
	 * or expires today (days = 0 )
	 */
	public static function getExpirationData($days = 5){
		$query = 'SELECT DISTINCT researchers.id as id,
			researchers.name as name,
			researchers.cname as cname ,
			contacts.data as email,
			(\'[\'::text || string_agg(DISTINCT ((((((((\'{"id":"\'::text || applications.id::text) || \'"\'::text) || \',"cname":"\'::text) || replace(applications.cname, \'"\'::text, \'\\"\'::text)) || \'"\'::text) || \',"name":"\'::text) || replace(applications.name, \'"\'::text, \'\\"\'::text)) || \'"\'::text)   || \'}\'::text, \',\'::text)) || \']\'::text AS apps
		FROM 
			applications 
			INNER JOIN vaviews ON vaviews.appid = applications.id
			INNER JOIN researchers_apps ON researchers_apps.appid = applications.id
			INNER JOIN researchers ON researchers.id = researchers_apps.researcherid
			INNER JOIN permissions ON (permissions.object = applications.guid OR permissions.object is null)
			INNER JOIN contacts ON contacts.researcherid = researchers.id
		WHERE 
		permissions.actionid = 32 AND 
		permissions.actor = researchers.guid AND
		vaviews.va_version_published = TRUE AND 
		vaviews.va_version_archived = FALSE AND 
		contacts.isprimary = true AND 
		{{expireson}}
		GROUP BY researchers.id , contacts.data';
		
		$expireson = "";
		$qdays = intval(floor(abs($days)));
		if( $days > 0 ){
			$expireson = '(vaviews.va_version_expireson::date) = (now()::date + ' . $qdays . ') and vaviews.va_version_expireson > now()';
		}else if ( $days < 0){
			if( $qdays > VMCasterNotifications::getMaxPastDays()){
				$expireson = '(vaviews.va_version_expireson::date) < (now()::date - ' . VMCasterNotifications::getMaxPastDays() . ')';
			}else{
				$expireson = '(vaviews.va_version_expireson::date) = (now()::date - ' . $qdays . ')';
			}
			
		}else {
			$expireson = '(vaviews.va_version_expireson::date) = (now()::date)';
		}
		$q = preg_replace('/\{\{expireson\}\}/i', $expireson, $query);
		
		
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$res = db()->query($q)->fetchAll();
		
		return $res;
	}
	private static function getToBeExpiredMessage($notification){
		$qdays = intval(floor(abs($notification["days"])));
		$appindex = array();
		$user = $notification["user"];
		if( $qdays === 1){
			$subject = "[EGI APPDB] Virtual appliances expire tomorrow";
		}else{
			$subject = "[EGI APPDB] Virtual appliances will expire in " . $qdays . " days";
		}
		
		$message = "Dear " . $user["name"] . ",\n";
		if( $qdays === 1){
			$message .= "  the published versions of the following virtual appliances expire tomorrow.\n\n";
		}else{
			$message .= "  the published versions of the following virtual appliances will expire in " . $qdays . " days.\n\n";
		}
		
		foreach($notification["vappliances"] as $vapp){
			$appindex[] = $vapp;
			$message .= "    [" . count($appindex) . "]. " . $vapp["name"] . " \n";
		}
		$message .= "\n\nBest regards,\n";
		$message .= "AppDB team\n\n";
		$message .= "________________________________________________________________________________________________________\n";
		for($i=0; $i<count($appindex); $i+=1){
			$ap = $appindex[$i];
			$message .= "[" . ($i+1) . "] https://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/vappliance/" . $ap["cname"] ."\n";
		}
		$notification["message"] = $message;
		$notification["subject"] = $subject;
		
		return $notification;
	}
	private static function getAlreadyExpiredMessage($notification){
		$qdays = intval(floor(abs($notification["days"])));
		$appindex = array();
		$user = $notification["user"];
		
		$message = "Dear " . $user["name"] . ",\n";
		if( $qdays > VMCasterNotifications::getMaxPastDays()){
			$subject = "[EGI APPDB] Virtual appliances expired more than " . VMCasterNotifications::getMaxPastDays() . " days ago";
			$message .= "  the published versions of the following virtual appliances expired more than " . VMCasterNotifications::getMaxPastDays() . " days.\n\n";
		}else{
			$subject = "[EGI APPDB] Virtual appliances expired " . $qdays . " days ago";
			$message .= "  the published versions of the following virtual appliances expired " . $qdays . " days ago.\n\n";
		}
		foreach($notification["vappliances"] as $vapp){
			$appindex[] = $vapp;
			$message .= "    [" . count($appindex) . "]. " . $vapp["name"] . " \n";
		}
		$message .= "\n\nBest regards,\n";
		$message .= "AppDB team\n\n";
		$message .= "________________________________________________________________________________________________________\n";
		for($i=0; $i<count($appindex); $i+=1){
			$ap = $appindex[$i];
			$message .= "[" . ($i+1) . "] https://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/vappliance/" . $ap["cname"] ."\n";
		}
		$notification["message"] = $message;
		$notification["subject"] = $subject;
		
		return $notification;
	}
	private static function getExpiresMessage($notification){
		$appindex = array();
		$user = $notification["user"];
		$subject = "[EGI APPDB] Virtual appliances expire today";
		$message = "Dear " . $user["name"] . ",\n";
		$message .= "  the published versions of the following virtual appliances expire today.\n\n";
		foreach($notification["vappliances"] as $vapp){
			$appindex[] = $vapp;
			$message .= "    [" . count($appindex) . "]. " . $vapp["name"] . " \n";
		}
		$message .= "\n\nBest regards,\n";
		$message .= "AppDB team\n\n";
		$message .= "________________________________________________________________________________________________________\n";
		for($i=0; $i<count($appindex); $i+=1){
			$ap = $appindex[$i];
			$message .= "[" . ($i+1) . "] https://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/vappliance/" . $ap["cname"] ."\n";
		}
		$notification["message"] = $message;
		$notification["subject"] = $subject;
		
		return $notification;
	}
	/*
	 * Creates a notification object for the given item.
	 * $item is an item of the list returned from 
	 * self::getExpirationData function
	 */
	public static function getExpirationNotification($item,$days = 5){
		$notification = array(
			"user"=>array("id"=>$item["id"], "name"=>$item["name"], "cname"=>$item["cname"]), 
			"subject"=>"", 
			"message"=>"",
			"days"=>$days,
			"recipient"=>$item["email"], 
			"vappliances" => array() 
		);
		if( trim($notification["recipient"]) === "" ){
			return null;
		}
		if( trim($item["id"]) === "" ){
			return null;
		}
		
		try{
			$apps =  trim($item["apps"]);
			if( $apps === "" ){
				return null;
			}
			$apps = json_decode($apps, true);
			if( $apps === null || count($apps) === 0){
				return null;
			}
			
			$notification["vappliances"] = $apps;
		}catch(Exception $ex){
			return null;
		}
		
		if( $days > 0 ){
			$notification = self::getToBeExpiredMessage($notification);
		}else if ( $days < 0 ){
			$notification = self::getAlreadyExpiredMessage($notification);
		}else {
			$notification = self::getExpiresMessage($notification);
		}
		$notification["message"] = "-- This is an automated message, please do not reply -- \n\n" . $notification["message"];
		return $notification;
	}
	private static function sendExpirationNotification($notification){
		$subject = $notification["subject"];
		$to = array($notification["recipient"]);
		$txtbody = $notification["message"];
		if ( ApplicationConfiguration::isProductionInstance() === FALSE ) {
			self::debugSendMultipart($subject, $to, $txtbody, null, "appdb reports username", "appdb reports password", false, null, false, null);
		} else {
			//sendMultipartMail($subject, $to, $txtbody, null, 'appdb-reports@iasa.gr', 'enadyskolopassword', false, null, false, array("Precedence"=>"bulk"));
			EmailService::sendBulkReport($subject, $to, $txtbody);
		}
	}
	private static function debugSendMultipart($subject, $to, $txtbody='', $htmlbody='', $username, $password, $replyto = false, $attachment = null, $cc = false, $ext = null){
		error_log("SENDING EXPIRATION NOTIFICATION: ");
		error_log("TO: " . implode(",", $to));
		error_log("REPLY_TO: " . $replyto);
		error_log("SUBJECT: " . $subject);
		error_log("MESSAGE: " . $txtbody);
	}
	public static function getExpirationNotificationList($days = 5){
		$data = self::getExpirationData($days);
		$res = array();
		foreach($data as $d){
			$notification = self::getExpirationNotification($d, $days);
			if( $notification !== null ){
				$res[] = $notification;
			}
		}
		return $res;
	}
	
	public static function sendExpirationNotificationList($days = 5){
		$data = self::getExpirationData($days);
		$res = array();
		foreach($data as $d){
			$notification = self::getExpirationNotification($d, $days);
			if( $notification !== null ){
				self::sendExpirationNotification($notification);
			}
		}
		return $res;
	}
}
class VMCasterOsSelector {
	public static function findOSOrAlias($os) {
		db()->setFetchMode(Zend_Db::FETCH_OBJ);
		$rs = db()->query( "SELECT find_os(?) AS os", array( trim($os) ) )->fetchAll();
		$oses = array();
		foreach ($rs as $r) {
			if ($r->os !== null) {
				$os = $r->os;
				$os = pg_to_php_array($os);
				if ($os[3] !== false) {
					$os[3] = pg_to_php_array($os[3]);
				} else {
					$os[3] = null;
				}
				$oses[] = $os;
			}
		}
		if (count($oses) > 0) {
			$os = $oses[0];
			$oses = new Default_Model_OSes();
			$oses->filter->id->equals($os[0]);
			if( count($oses->items) > 0 ){
				return $oses->items[0];
			}
		} else {
			return null;
		}
	}
	private static function findOsFamilyByOs($os){
		$os = self::getOs($os);
		if( $os === null ) {
			return null;
		}
		return self::getOsFamily($os->os_family_id);
	}
	private static function getOsFamily($osfamily){
		if( $osfamily instanceof Default_Model_OSFamily ){ //if OS Family model is given
			if( !is_numeric($osfamily->id) || intval($osfamily->id) <= 0 ){
				return null;
			} else {
				return $osfamily;
			}
		} else if ( $osfamily instanceof Default_Model_OS ){ //if OS model is given
			if( !is_numeric($osfamily->id) || intval($osfamily->id) <= 0 ){
				return null;
			} else {
				return $osfamily->getOSFamily();
			}
		} else if ( is_numeric($osfamily) && intval($osfamily) > 0 ){ //If OS Family id is given
			$osfamilies = new Default_Model_OSFamilies();
			$osfamilies->filter->id->equals($osfamily);
			if( count($osfamilies->items) > 0 ){
				return $osfamilies->items[0];
			}
		} else if( is_string($osfamily) && trim($osfamily) !== "" ){ //If OS Family name is given
			$osfamilies = new Default_Model_OSFamilies();
			$osfamilies->filter->name->ilike(trim($osfamily));
			if( count($osfamilies->items) > 0 ){
				return $osfamilies->items[0];
			}
		}
		
		//retrieve os family from oses
		if( is_string($osfamily) && trim($osfamily) !== "" ){
			$os = self::getOs($osfamily);
			if( $os !== null ){
				return $os->getOSFamily();
			}
		}
		return null;
	}
	private static function getOsOther($family){
		if( $family instanceof Default_Model_OS ){
			if( is_numeric($family->id) && intval($family->id)>0 ){
				return self::getOsOther($family->getOSFamily());
			}
			return null;
		} else if( $family instanceof Default_Model_OSFamily ){
			if( is_numeric($family->id) && intval($family->id)>0 ){
				$res = new Default_Model_OSes();
				$f1 = new Default_Model_OSesFilter();
				$f2 = new Default_Model_OSesFilter();
				$f1->os_family_id->equals($family->id);
				$f2->name->ilike("Other");
				$res->filter->chain($f1->chain($f2, "AND"), "AND");
				if( count($res->items) > 0 ){
					return $res->items[0];
				}
			}
			return null;
		} else if( (is_numeric($family) && intval($family)>0 ) || (is_string($family) && trim($family) !== "" )){
			$osfamily = self::getOsFamily($family);
			if( $osfamily === null ){
				return null;
			}
			return self::getOsOther($osfamily);
		} else {
			$osfamily = self::getOsFamily("others");
			if( $osfamily === null ){
				return null;
			}
			return self::getOsOther($osfamily);
		}
	}
	private static function getOs($osname){
		if( $osname instanceof Default_Model_OS ){
			if( !is_numeric($osname->id) || intval($osname->id) <= 0 ){
				return null;
			} else {
				return $osname;
			}
		} else if ( is_numeric($osname) && intval($osname) > 0 ){
			$oses = new Default_Model_OSes();
			$oses->filter->id->equals($osname);
			if( count($oses->items) > 0 ){
				return $oses->items[0];
			}
		} else if( is_string($osname) && trim($osname) !== "" ){
			$os = self::findOSOrAlias($osname);
			return $os;
		}
		return null;
	}
	public static function getOsInfo($osfamilyname, $osname, $osversion){
		$osfamily = self::getOsFamily($osfamilyname);
		$os = self::getOs($osname);
		$osversion = trim($osversion);
		$debug = "";
		if( $osversion === "") {
			$osversion = "n\a";
		}
		$osfromversion = self::findOSOrAlias($osversion);
		if( $osfamily === null && $os === null){
			$debug = "1. OSFamily=none OS=none \n";
			//retrieve from version or set family/os  as other/other
			if( $osfromversion !== null ){
				$debug .= "2. Trying to retrieve info from OSVersion...FOUND OS '" . $osfromversion->name . "' (id:" . $osfromversion->id . ")\n";
				$debug .= "3. Trying to retrieve OS Family from OS Name...";
				$os = $osfromversion;
				$osfamily = self::getOsFamily($os);
				if( $osfamily === null ){
					$debug .= "FAIL\n";
				}else{
					$debug .= "FOUND OS Family '" . $osfamily->name . "' (id:" . $osfamily->id .")\n";
				}
			}
		} else if( $osfamily === null && $os !== null ){ 
			$debug = "1. OSFamily=none OS=". $os->name ." (id:" . $os->id . ")\n";
			$debug .= "2. Trying to retrieve OS Family from OS Name...";
			//Get family from os 
			$osfamily = self::getOsFamily($os);
			if( $osfamily === null ){
				$debug .= "FAIL\n";
			}else{
				$debug .= "FOUND OS Family '" . $osfamily->name . "' (id:" . $osfamily->id .")\n";
			}
		} else if( $osfamily !== null && $os === null) {
			$debug = "1. OSFamily='". $osfamily->name ."' (id:" . $osfamily->id . ") OSname=none\n";
			//Try to guess form os family
			if( $os === null ){
				$debug .= "2. Trying to retrieve OS from given OS Family name...";
				$os = self::getOs($osfamilyname);
				$debug .= ( ( $os === null )?"FAIL\n":"FOUND OS '" . $os->name . "' (id:" . $os->id . ")\n" );
			}
			//Try to guess by OS Version
			if( $os === null ){
				$debug .= "3. Trying to retrieve OS name from OS Version...";
				$os = $osfromversion;
				$debug .= ( ( $os === null )?"FAIL\n":"FOUND OS '" . $os->name . "' (id:" . $os->id . ")\n" );
			}
			//Set OS as other
			if( $os === null ){
				$debug .= "3. Getting OS 'other' of '".$osfamily->name."' family...";
				$os = self::getOsOther($osfamily);
				$debug .= ( ( $os === null )?"FAIL\n":"FOUND\n" );
			}
		} 
		
		//Check results and set defaults.
		if( $osfamily !== null && $os !== null ){
			//Check that os is under the osfamily
			if( $os->OSFamilyID !== $osfamily->id ){
				$debug .= "[WARNING] Os name does not belong to OS family. Set OS name as " . $osfamily->name . "/Other...";
				$os = self::getOsOther($osfamily);
				$debug .= ( ($os === null )?"FAIL\n":"DONE\n");
			}
		}else if( $osfamily === null && $os === null){
			$os = self::getOsOther();
			$osfamily = $os->getOSFamily();
		}else if( $osfamily === null && $os !== null ){
			$osfamily = self::getOsFamily($os);
		}else if( $osfamily !== null && $os === null ){
			$os = self::getOsOther($osfamily);
		}
		
		return array(
			"osfamily" => $osfamily,
			"os" => $os,
			"osversion" => $osversion,
			"debug" => $debug
		);
	}
}
?>
