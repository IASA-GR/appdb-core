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
 
class ContextualizationScripts {
	CONST MAX_BYTE_SIZE = 5242880; //5MB 
	private static function getHttpErrorCodes($code){
		$codes = array(
			"400"=>"Bad request",
			"401"=>"Unauthorized",
			"402"=>"Payment Required",
			"403"=>"Forbidden",
			"404"=>"Not found",
			"500"=>"Internal Error",
			"501"=>"Not implemented",
			"502"=>"Service temporarily overloaded",
			"503"=>"Gateway timeout",
			"204"=>"No Response"
		);
		
		if( isset( $codes[$code] ) ){
			return $codes[$code] . " (" . $code . ")";
		}
		return "code " . $code;
		
	}
	public static function fetchUrl($url){
		$error = false;
		$errorno  = -1;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_SSLVERSION,3);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		if( self::MAX_BYTE_SIZE > 1024 ){
			//curl_setopt($ch, CURLOPT_BUFFERSIZE, 10240); //10kb
			curl_setopt($ch, CURLOPT_NOPROGRESS, false);
			curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function(
				$DownloadSize, $Downloaded, $UploadSize, $Uploaded
			){
				return ($Downloaded > (1 * ContextualizationScripts::MAX_BYTE_SIZE)) ? 1 : 0;
			});
		}
		try{
			$data = curl_exec ($ch);
			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$error = curl_error($ch);
			$errorno = curl_errno($ch);
		} catch (Exception $ex) {
			$error = $ex->getMessage();
		}
		curl_close ($ch);
			
		if( self::MAX_BYTE_SIZE > 1024 && $errorno === CURLE_ABORTED_BY_CALLBACK ){
			$error = "Context script size exceeds " . (ContextualizationScripts::MAX_BYTE_SIZE /1024 /1024) . "MB";
		}else if( !$error && trim($code) !== "200" ){
			$error = "Resource responded with: " . self::getHttpErrorCodes($code);
		}
		
		if( $error ) {
			return $error;
		}
		
		$filesize = strlen( $data );
		if( $filesize === 0 ){
			return "The given url/location returned an empty file.";
		}
		
		$md5 = md5($data);
		$parts = parse_url($url);  
		if( isset($parts['path']) )
		{
			$file_name = trim( basename($parts['path']) );
		} else {
			$file_name = '';
		}
		return array (
			"url" => $url,
			"name" => $file_name,
			"md5" => $md5,
			"data" => $data,
			"size"=> $filesize
		);
		
	}
	
	private static function clearVmiInstances($vmiinstanceid, $userid, $usetransaction=true){
		$scripts = array();
		//clear existing vmiinstancecontextscripts since only 
		//one contextscript per vmiinstance is allowed
		$vmis = new Default_Model_VMIinstanceContextScripts();
		$vmis->filter->vmiinstanceid->numequals($vmiinstanceid);
		if( count($vmis->items) >0 ){
			foreach($vmis->items as $vmi){
				$vmiscript = $vmi->getContextScript();
				if( $vmiscript->hasContext() === false ){
					$scripts[$vmi->id] = $vmi->getContextScript();
				}
			}
		}
		
		foreach( $scripts as $k=>$v){
			self::removeRelatedScript($k, $v, $userid, $usetransaction);
		}
		
	}
	
	private static function relateScriptToVmiInstance( $vmiinstanceid, $script, $user, $usetransaction=true ){
		self::clearVmiInstances($vmiinstanceid,$user->id, $usetransaction);
		
		//Associate context script entry with vmi instance
		$vmiscript = new Default_Model_VMIinstanceContextScript();
		$vmiscript->vmiinstanceid = $vmiinstanceid;
		$vmiscript->contextscriptid = $script->id;
		$vmiscript->addedbyid = $user->id;
		$vmiscript->save();
		
		return $script;
	}
	
	private static function updateScriptData($script, $data, $user, $usetransaction=true){
		try{
			if( $usetransaction ) db()->beginTransaction();
			$script->checksum = $data["md5"];
			$script->checksumfunc = "md5";
			$script->size = $data["size"];
			$script->formatid = intval($data['formatid']);
			
			if( trim($script->name) === "" ){
				$script->name = $data["name"];
			}
			$script->lastupdatedbyid = $user->getId();
			$script->save();
			$script = VapplianceStorage::store($script, $data['vmiinstanceid'], $user->id);
			if( $usetransaction ) db()->commit();
		}catch(Exception $ex){
			if( $usetransaction ) {
				db()->rollback();
				return $ex->getMessage();
			} else {
				throw $ex;
			}
		}
		return $script;
	}
	
	private static function createPseudoScript( $data ){
		$script = new Default_Model_ContextScript();
		$script->name = trim( $data["name"] );
		$script->url = trim( $data["url"] );
		$script->formatid = $data['formatid'];
		$script->checksum = $data["md5"];
		$script->checksumfunc = "md5";
		$script->size = $data["size"];
		
		return $script;
	}
	
	private static function addScriptData( $data, $vmiinstanceid, $user, $usetransaction = true){
		try{
			if( $usetransaction ) db()->beginTransaction();
			//create context script entry
			$script = new Default_Model_ContextScript();
			//$script->id = -1;
			$script->name = trim( $data["name"] );
			$script->url = trim( $data["url"] );
			$script->formatid = intval($data["formatid"]);
			$script->checksum = $data["md5"];
			$script->checksumfunc = "md5";
			$script->size = $data["size"];
			$script->addedbyid = $user->id;
			$script->save();
			$script = VapplianceStorage::store($script, $vmiinstanceid, $user->id);
			self::relateScriptToVmiInstance($vmiinstanceid, $script, $user, false);
			
			if( $usetransaction ) db()->commit();
		}catch(Exception $ex){
			if( $usetransaction ) {
				db()->rollback();
				return $ex->getMessage();
			}else{
				throw $ex;
			}
		}
		return $script;
	}
	
	private static function removeRelatedScript( $id, $script, $userid, $usetransaction=true ){
		try{
			$vmiinstanceid = 0;
			if( $usetransaction ) db()->beginTransaction();
			$vmis = new Default_Model_VMIinstanceContextScripts();
			$vmis->filter->id->numequals($id);
			if( count($vmis->items) > 0 ){
				$vmi = $vmis->items[0];
				if( $vmi->contextscriptid === $script->id ){
					$vmiinstanceid = $vmi->vmiinstanceid;
					$vmis->remove($vmi);
				}
			}
			
			$vmis = new Default_Model_VMIinstanceContextScripts();
			$vmis->filter->contextscriptid->numequals($script->id);
			if( count($vmis->items) === 0 ){
				//The script is not longer used. Remove it.
				$scripts = new Default_Model_ContextScripts();
				$scripts->filter->id->numequals($script->id);
				if( count($scripts->items) > 0 ){
					VapplianceStorage::remove($scripts->items[0],$vmiinstanceid, $userid);
					$scripts->remove($scripts->items[0]);
				}
			}
			if( $usetransaction ) db()->commit();
		}catch(Exception $ex){
			if( $usetransaction ) {
				db()->rollback();
				return $ex->getMessage();
			}else{
				throw $ex;
			}
		}
		return true;
	}
	
	public static function contextualizationScriptAction($userid, $action, $url, $vmiinstanceid, $appid = null, $formatid=1){
		$user = null;
		$app = null;
		$vaview = null;
		$issamescript = false;
		$similarscript = false;
		$vmiscript = null;
		$relationid = -1;
		$formatid = intval($formatid);
		if( $formatid <= 0 ) {
			$formatid = 1;
		}
		$users = new Default_Model_Researchers();
		$users->filter->id->numequals($userid);
		if( count( $users->items ) > 0 ){
			$user = $users->items[0];
		} else {
			return false;
		}
		
		if( !in_array($action, array("set", "remove"))  ){
			return "Invalid action type";
		}
		
		if( $vmiinstanceid !== null ){
			$vaviews = new Default_Model_VAviews();
			$vaviews->filter->vmiinstanceid->numequals($vmiinstanceid);
			if( count($vaviews->items) > 0 ){
				$vaview = $vaviews->items[0];
				$appid = $vaview->appid;
			}
		}
		
		$apps = new Default_Model_Applications();
		$apps->filter->id->numequals($appid);
		if( count( $apps->items) > 0 ){
			$app = $apps->items[0];
		} else {
			return "Virtual appliance not found";
		}
		
		$privs = $user->getPrivs();
		if( !$privs || !$privs->canManageVAs($app->guid) ){
			return "No permission for this action";
		}
		
		$url = trim( filter_var(urldecode($url), FILTER_VALIDATE_URL) );
		if( !$url ){
			return "Invalid url";
		}
		
		if( $action !== 'remove') 
		{
			$scriptdata = self::fetchUrl($url);
			if( $scriptdata === false || is_string($scriptdata) ){
				return $scriptdata;
			}
		}
		
		$scriptdata['formatid'] = $formatid;
		$scriptdata['vmiinstanceid'] = $vmiinstanceid;
		
		if( $vmiinstanceid === null ){
			//just do the hashing
			//and return pseudo script object
			//will be used in case of a new working version
			return self::createPseudoScript( $scriptdata );
		}
		
		
		
		//Find if script is already related to the current vmi instance
		$vmis = new Default_Model_VMIinstanceContextScripts();
		$vmis->filter->vmiinstanceid->numequals($vmiinstanceid);
		if( count($vmis->items) > 0 ){
			//find first association with vmiinstance that 
			//does not belong to a sw appliance
			foreach($vmis->items as $vmi){
				if( $vmi->hasContext() === false ){
					$vmiscript = $vmi->getContextScript();
					$relationid = $vmi->id;	
					break;
				}
			}
		
		}
		
		if( !$vmiscript || trim($vmiscript->url) !== trim($url) ){ 
			//if script does not belong to the current vmi instance 
			//Perform checks to determine if a new context script should be created
			
			//Find context script with same url
			$scripts = new Default_Model_ContextScripts();
			$scripts->filter->url->equals( $url );
			if( count( $scripts->items )  > 0 ){
				foreach($scripts->items as $script){
					if( $script->hasContext() === false ){
						$similarscript = $script;
						$vmis = new Default_Model_VMIinstanceContextScripts();
						$vmis->filter->vmiinstanceid->numequals($vmiinstanceid)->and($vmis->filter->contextscriptid->numequals($similarscript->id));
						if( count($vmis->items) > 0 ){
							foreach($vmis->items as $vmi){
								if( $vmi->hasContext() === false ){
									$issamescript = true;
									$relationid = $vmi->id;
									break;
								}
							}
						}
						break;
					}
				}
			}
		} else if( trim($vmiscript->url) === trim($url) ) {
			$issamescript = true;
			$similarscript = false;
		}
		//if script is referenced by a sw appliance then
		//assume it is not a similar script, to avoid conflict 
		//with swappliances contexualization scripts
		if( $similarscript && $similarscript->hasContext() === true ){
			$similarscript = false;
		}
		
		if( $action === "set" ){
			if( $issamescript ){
				//this is the same script for the same vmiinstance
				//just update hashes.
				return self::updateScriptData($vmiscript, $scriptdata, $user);
			} else if( $similarscript ){
				//the script already exists but is not related to the same vmiinstance
				//update hashes and relate to vmi instance
				$res = self::updateScriptData($similarscript, $scriptdata, $user);
					if( $res && !is_string($res) && $relationid && $vmiscript ){
						$res = self::removeRelatedScript($relationid, $vmiscript, $user->id);
					}
					if( $res && !is_string($res) ){
						$res = self::relateScriptToVmiInstance($vmiinstanceid, $similarscript, $user);
					}
					return $res;
			}else {
				//Remove any existing relation with a script
				if( $relationid && $vmiscript ){
					$res = self::removeRelatedScript($relationid, $vmiscript, $user->id);
					if( !$res || is_string($res) ){
						return $res;
					}
				}
				//add script and related it to vmiinstance (will remove any other script relation)
				return self::addScriptData($scriptdata, $vmiinstanceid, $user);
			}
		} else if ( $action === "remove" && $issamescript ){
			if( $vmiscript && $vmiscript->hasContext() === false ){
				return self::removeRelatedScript($relationid, $vmiscript, $user->id);
			}
		}
		
		return "No actions performed";
	}
}
?>
