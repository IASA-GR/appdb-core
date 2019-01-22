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

require_once('vmcaster.php');

class VoAdmin{
	private static function getAdminVoMemberships(){
		return array("vo deputy","vo manager","vo expert");
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
	 * Returns VO entry for given id or name
	 */
	public static function getVo($vo){
		if( $vo === null || ( is_string($vo) && trim($vo) === "" ) ) {
			return null;
		} else if( $vo instanceof Default_Model_VO ){
			return $vo;
		} else if( is_numeric($vo) ){
			$void = intval($vo);
			$vos = new Default_Model_VOs();
			$vos->filter->id->equals($void);
			if( count($vos->items) === 0 ){
				return null;
			}
			$vo = $vos->items[0];
		} else if( is_string($vo) && trim($vo)!=="" ){
			$vos = new Default_Model_VOs();
			$vos->filter->name->equals(trim($vo));
			if( count($vos->items) === 0 ){
				return null;
			}
			$vo = $vos->items[0];
		}
		return $vo;
	}
	/*
	 * Returns the vappliance entry for given id or cname
	 */
	public static function getVAppliance($vappliance){
		if( $vappliance instanceof \Application\Model\Application ){
			return $vappliance;
		} else if( $vappliance === null || ( is_string($vappliance) && trim($vappliance) === "" ) ) {
			return null;
		} else if( is_numeric($vappliance) ){
			$vapplianceid = intval($vappliance);
			$vappliances = new \Application\Model\Applications();
			$vappliances->filter->id->equals($vapplianceid);
			if( count($vappliances->items) === 0 ){
				return null;
			}
			$vappliance = $vappliances->items[0];
		} else if( is_string($vappliance) && trim($vappliance) !== ""  ){
			$vappliancecname = trim($vappliance);
			$vappliances = new \Application\Model\Applications();
			$vappliances->filter->cname->equals($vappliancecname);
			if( count($vappliances->items) === 0 ){
				return null;
			}
			$vappliance = $vappliances->items[0];
		}
		return $vappliance;
	}
	/*
	 * Returns the published version of the given vappliance
	 */
	public static function getVAppVersion($vappliance){
		if( $vappliance instanceof Default_Model_VAversion){
			return $vappliance;
		}
		$appliance = self::getVAppliance($vappliance);
		if( $appliance === null ) {
			return null;
		}
		
		
		$vapplications = new Default_Model_VAs();
		$vapplications->filter->appid->equals($appliance->id);
		if( count($vapplications->items) === 0 ){
			return null;
		}
		$vapplication = $vapplications->items[0];
		
		$vappvers = new Default_Model_VAversions();
		$f1 = new Default_Model_VAversionsFilter();
		$f2 = new Default_Model_VAversionsFilter();
		$f3 = new Default_Model_VAversionsFilter();
		$f1->vappid->equals($vapplication->id);
		$f2->published->equals(true);
		$f3->archived->equals(false);
		$vappvers->filter->chain($f1, "AND");
		$vappvers->filter->chain($f2, "AND");
		$vappvers->filter->chain($f3, "AND");
		
		if( count($vappvers->items) === 0 ){
			return null;
		}
		$vappver = $vappvers->items[0];
		
		return $vappver;
	}
	
	private static function getVAImages($vappliance){
		if( $vappliance instanceof Default_Model_VA ){
			$vappver = self::getVAppVersion($vappliance);
		}else if($vappliance instanceof Default_Model_VAversion) {
			$vappver = $vappliance;
		}else{
			$vappver = null;
		}
		
		if( $vappver === null ){
			return array();
		}
		$result = array();
		
		$vapplists = $vappver->getVappLists();
		if( count($vapplists->items) > 0 ){
			foreach($vapplists->items as $vapplist){
				$vmiinstance = $vapplist->getVMIinstance();
				if( $vmiinstance !== null ){
					$result[] = $vmiinstance;
				}
			}
		}
		
		return $result;
	}
	
	private static function toJSON($arr, $toJSON = true){
		if( $toJSON === false ) return $arr;
		return json_encode($arr);
	}
	
	public static function getUserMembership($researcher, $toJSON = false){
		$res = array();
		$user = self::getUser($researcher);
		if( $user === null ) {
			return self::toJSON($res, $toJSON);
		}
		
		$vomems = $user->getVOMemberships();
		if( count($vomems) === 0 ){
			return self::toJSON($res, $toJSON);
		}
		
		foreach($vomems as $mem){
			if( !$mem->vo ) {
				continue;
			}
			
			$vom = array();
			$vom["id"] = $mem->void;
			$vom["discipline"] = $mem->vo->domain->name;
			$vom["member_since"] = $mem->membersince;
			$vom["name"] = $mem->vo->name;
			array_push($res, $vom);
		}
		return self::toJSON( array_merge($res, self::getVOContacts($user)), $toJSON );
	}
	
	private static function getVOContacts($researcher){
		$res = array();
		$user = self::getUser($researcher);
		if( $user === null ) {
			return $res;
		}
		
		$vomems = $researcher->getVOContacts();
		if( count($vomems) === 0 ){
			return $res;
		}
		
		foreach($vomems as $mem){
			$vom = array();
			$vom["id"] = $mem->void;
			$vom["discipline"] = $mem->vo->domain->name;
			$vom["name"] = $mem->vo->name;
			$vom["role"] = $mem->role;
			array_push($res, $vom);
		}
		return $res;
	}
	
	public static function canEditVOImageList($researcher, $vo){
		$user = self::getUser($researcher);
		if( $user === null ){
			return false;
		}
		$voitem = self::getVo($vo);
		if( $voitem === null ){
			return false;
		}
		
		if( $user->privs->canManageVOWideImageList($voitem->guid) === true ){
			return true;
		}
		
		return false;
	}
	
	public static function getDraftVoImageList($researcher, $vo, $create = false){
		if ( $create === true ) {
			return self::createDraftVoImageList($researcher, $vo);
		} else {
			$voimglists = new Default_Model_VOWideImageLists();
			$f1 = new Default_Model_VOWideImageListsFilter();
			$f2 = new Default_Model_VOWideImageListsFilter();
			
			$f1->void->numequals($vo->id);
			$f2->state->equals("draft");
			$voimglists->filter->chain($f1, "AND");
			$voimglists->filter->chain($f2, "AND");
			
			if( count($voimglists->items) === 0 ){
				return null;
			} else {
				return $voimglists->items[0];
			}
		}
	}
	
	public static function createDraftVoImageList($researcher, $vo){
		global $application;
		$db = $application->getBootstrap()->getResource('db');
		$db->setFetchMode(Zend_Db::FETCH_BOTH);
		$q = "SELECT edit_vowide_image_list(?,?);";
		$res = $db->query( $q, array($vo->id, $researcher->id) )->fetchAll();
		if( count($res) === 0  ) {
			return null;
		}
		$res = $res[0];
		$voimglistid = $res[0];
		$voimglists = new Default_Model_VOWideImageLists();
		$voimglists->filter->id->numequals($voimglistid);
		if( count($voimglists->items) === 0 ){
			return null;
		}
		return $voimglists->items[0];
	}
	
	public static function getPublishedVoImageList( $vo){
		$voimglists = new Default_Model_VOWideImageLists();
		$f1 = new Default_Model_VOWideImageListsFilter();
		$f2 = new Default_Model_VOWideImageListsFilter();
		
		$f1->void->numequals($vo->id);
		$f2->state->equals("published");
		$voimglists->filter->chain($f1, "AND");
		$voimglists->filter->chain($f2, "AND");
		
		if( count($voimglists->items) === 0 ){
			return null;
		}
		return $voimglists->items[0];
	}
	
	private static function clearDraftImages($researcher, $vo, $vappliance = null){
		$user = self::getUser($researcher);
		if( $user === null ){
			return "Not authorized to clear draft images from vo";
		}
		
		$vorg = self::getVo($vo);
		if( $vorg === null ){
			return true; //nothing to do
		}
		
		$vodraft = self::getDraftVoImageList($user, $vorg, false);
		if( $vodraft === null ){
			return true; //nothing to do
		}
		$vodraftimages = new Default_Model_VOWideImageListImages();
		$f1 = new Default_Model_VOWideImageListImagesFilter();
		$f1->vowide_image_list_id->numequals($vodraft->id);
		$vodraftimages->filter->chain($f1, "AND");
		
		if( $vappliance !== null ){
			$vappversion = self::getVAppVersion($vappliance);
			if( $vappversion === null ){
				return true; //nothing to do
			}
			$vapplists = $vappversion->getVappLists();
			$vapplistids = array();
			foreach($vapplists as $vapplist){
				$vapplistids[] = $vapplist->id;
			}
			$f2 = new Default_Model_VOWideImageListImagesFilter();
			$f2->vapplistid->in($vapplistids);
			$vodraftimages->filter->chain($f2, "AND");
		}
		
		if( count($vodraftimages->items) === 0 ){
			return true; //nothing to do
		}
		
		foreach($vodraftimages->items as $img){
			$vodraftimages->remove($img);
		}
		
		$vodraft->alteredbyid = $user->id;
		$vodraft->save();
		
		return true;
	}
	
	public static function imageAction($action, $researcher, $vorg, $vappliance = null){
		$user = self::getUser($researcher);
		if( $user === null ){
			return "User not found";
		}
		$vo = self::getVo($vorg);
		if( $vo === null ){
			return "Virtual organization not found";
		}
		$canEdit = self::canEditVOImageList($user, $vo);
		if( $canEdit === false ){
			return "Cannot edit virtual organization image list";
		}
		$vappversion = null;
		if( $vappliance !== null ){
			$vapp = self::getVAppliance($vappliance);
			if( $vapp === null ){
				return "Virtual appliance not found";
			}
			$vappversion = self::getVAppVersion($vapp);
			if( $vappversion === null ){
				return "Virtual appliance does not have any published version";
			}
		}
		if( is_string($action) === false ){
			return "No action provided";
		}
		$action = strtolower(trim($action));
		switch( $action ){
			case "add":
				if( $vappversion === null ){
					return "No virtual appliance provided for inclusion";
				}
				return self::addVAppliance($user, $vo, $vappversion);
			case "remove":
				if( $vappversion === null ){
					return "No virtual appliance provided for removal";
				}
				return self::removeVappliance($user, $vo, $vapp);
			case "update":
				if( $vappversion === null ){
					return "No virtual appliance provided for update";
				}
				return self::updateVappliance($user, $vo, $vappversion);
			case "publish":
				return self::publishVoImageList($user, $vo);
			case "revertchanges":
				return self::revertDraftChanges($user, $vo);
			default:
				return "No action provided";
		}
	}
	
	private static function addVAppliance($researcher, $vo, $vappversion){
		if( $vappversion->isExpired() ){
			return "Virtual appliance version is expired";
		}
		
		$imagelists = $vappversion->getVappLists();
		if( count($imagelists) === 0 ){
			return "No vappliance image instances to include in vo image list";
		}
		
		$voimglist = self::getDraftVoImageList($researcher, $vo, true);
		if( $voimglist === null ){
			return "Could not retrieve draft VO wide image list";
		}
		
		//Clearing draft from current vappliance images lists
		$result = self::clearDraftImages($researcher, $vo, $vappversion);
		if( $result !== true ){
			if( $result === false ){
				return "Could not clear draft vo image list for given virtual appliance";
			}else{
				return $result;
			}
		}
		
		foreach($imagelists as $imglst){
			$voimglstimg = new Default_Model_VOWideImageListImage();
			$voimglstimg->vowideImageListID = $voimglist->id;
			$voimglstimg->vapplistid = $imglst->id;
			$voimglstimg->state = "draft";
			$voimglstimg->save();
		}
		return true;
	}
	
	private static function removeVappliance($researcher, $vo, $vappliance){
		db()->query("SELECT remove_va_from_vowide_image_list(?, ?, ?)", array($vo->id, $vappliance->id, $researcher->id))->fetchAll();
		return true;
	}
	
	private static function updateVappliance($researcher, $vo, $vappversion){
		if( $vappversion->isExpired() ){
			return "Virtual appliance version is expired";
		}
		debug_log("AETOST: " . var_export(array($vo->id, $vappversion->id, $researcher->id), true));
		db()->query("SELECT update_vowide_image_list(?, ?, ?)", array($vo->id, $vappversion->id, $researcher->id))->fetchAll();
		return true;
	}
	
	private static function publishVoImageList($researcher, $vo){
		$results = array();
		global $application;
		$db = $application->getBootstrap()->getResource('db');
		$db->setFetchMode(Zend_Db::FETCH_BOTH);
		$q = "SELECT publish_vowide_image_list(?,?);";
		$res = $db->query( $q, array($vo->id, $researcher->id) )->fetchAll();
		if( count($res) === 0  ) {
			return "Could not publish vo image list";
		}
		$res = $res[0];
		$voimglistid = $res[0];
		$voimglists = new Default_Model_VOWideImageLists();
		$voimglists->filter->id->numequals($voimglistid);
		if( count($voimglists->items) === 0 ){
			return "Could not publish vo image list";
		}else{
			$vmcast = self::publishToVMCaster($researcher, $vo, $voimglists->items[0]);
			if( $vmcast !== true ){
				if( $vmcast === false ){
					$results[] = $vmcast;
				}else{
					$results[] = "Could not publish image list with vmcaster";
				}
			}
		}
		
		$draftres = self::getDraftVoImageList($researcher, $vo, true);
		if( !$draftres ){
			$results[] = "Could not create new draft image list";
		}
		if( count($results) === 0 ){
			return true;
		}
		return implode(",",$results);
	}
	private static function publishToVMCaster($researcher, $vo, $voimagelist){
		$url = VMCaster::getVMCasterUrl();
		$url .= "/vmlistcontroller/create/" . $voimagelist->id . "/published/vos";
		$result = web_get_contents($url);		
		if( $result === false ){
			error_log("[VOAdmin:publishToVMCaster]:Could not retrieve response data from " . $url);
			return false;
		}
		return true;
	}
	private static function revertDraftChanges($researcher, $vo){
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$res = db()->query("SELECT discard_vowide_image_list(?)", array($vo->id))->fetchAll();
		if (count($res) > 0) {
			$res = $res[0];
			if ($res) {
				//recreate draft vi wide image list
				$draft = self::getDraftVoImageList($researcher, $vo, true);
				
				if( !$draft ){
					return "Could not recreate draft image list version";
				}
				return true;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}
	public static function getImageInfoById($voimageid,$identifier = null,$strict=false){
		if( $voimageid !== null && !is_numeric($voimageid)) { return null; }
		if( $identifier!==null && trim($identifier) === "") { return null; }
		
		$voimages = new Default_Model_VOWideImageListImages();
		
		$f1 = new Default_Model_VOWideImageListImagesFilter();
		$f1->id->numequals($voimageid);
		$voimages->filter->chain($f1, "AND");
		
		if( $identifier !== null ){
			$f2 = new Default_Model_VOWideImageListImagesFilter();
			$f2->guid->equals($identifier);
			$voimages->filter->chain($f2, "AND");
		}
		
		if( count($voimages->items) === 0 ){ return null; }
		
		$voimage = $voimages->items[0];
		
		//Get vo wide image list for future use
		$voimagelist = $voimage->getVowideImageList();
		if( $voimagelist === null ) { return null; }
		
		//Get VO entry for future use
		$vo = $voimagelist->getVo();
		if( $vo === null ) { return null; }
		
		//Retrieve vapp list to collect image from there
		$vapplist = $voimage->getVappList();
		if( $vapplist === null ) { return null; }
		
		//Get vapp image entry
		$vmiimage = $vapplist->getVMIinstance();
		if( $vmiimage === null ) { return null; }
		
		$image = $vmiimage;
		$originalimageid = $vmiimage->id;
		//Get good vmi instance id (same with up to date metadata)
		if( $strict === false ){
			db()->setFetchMode(Zend_Db::FETCH_BOTH);
			$res = db()->query("SELECT get_good_vmiinstanceid(?)", array($vmiimage->id))->fetchAll();
			if (count($res) > 0) {
				$res = $res[0];
			}
			if (count($res) > 0) {
				$res = $res[0];
			}
			//if good instance id differs use that one
			if ($res && is_numeric($res) && intval($res) !== intval($vmiimage->id)) {
				$originalimageid = $image->id;
				$images = new Default_Model_VMIinstances();
				$images->filter->id->numequals(intval($res));
				if( count($images->items) > 0 ){
					$image = $images->items[0];
				}
			}
		}
		//Retrieve data for image
		$result = VmCaster::getImageInfoById($image->id,$image->guid,$strict);
		if( $result === null ) { return null; }
		
		//Enrich returned data with vo image specific information
		$result["vo"] = $vo;
		$result["voimage"] = $voimage;
		$result["voimagelist"] = $voimagelist;
		$result["mpuri"] = 'https://'.$_SERVER['HTTP_HOST'].'/store/vo/image/' .$voimage->guid . ':' . $voimage->id . '';
		$result["id"] = $voimage->id;
		$result["baseid"] = $image->id;
		if( $originalimageid !== $image->id ){
			$result["requested_baseid"] = $originalimageid;
		}
		$result["identifier"] = $voimage->guid;
		$result["baseidentifier"] = $image->guid;
		$result["basempuri"] = 'https://'.$_SERVER['HTTP_HOST'].'/store/vm/image/' .$image->guid . ':' . $image->id . '';
		return $result; 
	}
	public static function getImageInfoByIdentifier($identifier){
		if( $identifier!==null && trim($identifier) === "") { return null; }
		$voimagelist = null;
		//first search published image lists
		$publists = new Default_Model_VOWideImageLists();
		$f1 = new Default_Model_VOWideImageListImagesFilter();
		$f2 = new Default_Model_VOWideImageListsFilter();
		$f1->guid->equals($identifier);
		$f2->state->equals("published");
		$publists->filter->chain($f1, "AND");
		$publists->filter->chain($f2, "AND");
		if( count($publists->items) > 0 ){ 
			$voimagelist = $publists->items[0];
		}
		
		//Then check draft
		if( $voimagelist == null ){
			$prevlists = new Default_Model_VOWideImageLists();
			$f3 = new Default_Model_VOWideImageListImagesFilter();
			$f4 = new Default_Model_VOWideImageListsFilter();
			$f3->guid->equals($identifier);
			$f4->state->equals("obsolete");
			$prevlists->filter->chain($f3, "AND");
			$prevlists->filter->chain($f4, "AND");
			$prevlists->filter->orderby("published_on DESC");
			if( count($prevlists->items) > 0 ){ 
				$voimagelist = $prevlists->items[0];
			}
		}
		
		if( $voimagelist === null ){
			return null;
		}
		
		//Retrieve vo wide image entry
		$images = new Default_Model_VOWideImageListImages();
		$f5 = new Default_Model_VOWideImageListImagesFilter();
		$f6 = new Default_Model_VOWideImageListImagesFilter();
		
		$f5->vowide_image_list_id->numequals($voimagelist->id);
		$f6->guid->equals($identifier);
		$images->filter->chain($f5, "AND");
		$images->filter->chain($f6, "AND");
		
		if( count($images->items) === 0 ){
			return null;
		}
		$voimage = $images->items[0];
		
		//Get VO entry for future use
		$vo = $voimagelist->getVo();
		if( $vo === null ) { return null; }
		
		//Retrieve vapp list to collect image from there
		$vapplist = $voimage->getVappList();
		if( $vapplist === null ) { return null; }
		
		//Get vapp image entry
		$image = $vapplist->getVMIinstance();
		if( $image === null ) { return null; }
		
		//Retrieve data for image
		$result = VmCaster::getImageInfoById($image->id);
		if( $result === null ) { return null; }
		
		//Enrich returned data with vo image specific information
		$result["vo"] = $vo;
		$result["voimage"] = $voimage;
		$result["voimagelist"] = $voimagelist;
		$result["mpuri"] = 'https://'.$_SERVER['HTTP_HOST'].'/store/vo/image/' .$voimage->guid . ':' . $voimage->id . '';
		$result["id"] = $voimage->id;
		$result["baseid"] = $image->id;
		$result["identifier"] = $voimage->guid;
		$result["baseidentifier"] = $image->guid;
		$result["basempuri"] = 'https://'.$_SERVER['HTTP_HOST'].'/store/vm/image/' .$image->guid . ':' . $image->id . '';
		
		return $result;
	}
	public static function convertImage($result, $format = 'xml'){
		return VMCaster::convertImage($result, $format);
	}
	
	public static function getDefaultVORoles(){
		$default = array("VO MANAGER", "VO DEPUTY", "VO SHIFTER", "VO EXPERT");
		require_once('Zend/Config/Ini.php');
		$conf = new Zend_Config_Ini('../application/configs/application.ini', $_SERVER['APPLICATION_ENV']);
		$appconf = $conf->app;
		$voroles = $appconf->voroles;
		if( trim($voroles) === ""){
			return $default;
		}else {
			$voroles = explode(",",$voroles);
			$saneroles = array();
			foreach($voroles as $role){
				$r = trim(strtoupper($role));
				if( substr($r, 0, 3) !== "VO "){
					$r = "VO " . $r;
				}
				$saneroles[] = $r; 
			}
			if( count($saneroles) > 0 ){
				return $saneroles;
			}
		}
		return $default;
	}
	/* 
	 * Return given roles in a normalized form. 
	 * Only manager, expert, deputy and shifter as accepted.
	 */
	private static function normalizeVORoles($role){
		$roles = array();
		if( is_string($role) ) {
			$roles[] = $role;
		} else if ( is_array($role) ) {
			$roles = $role;
		} else {
			return array();
		}
		$result = array();
		$validroles = self::getDefaultVORoles();
		foreach($roles as $r){
			foreach($validroles as $v){
				if( stripos($v, $r) !== false ){
					$result[] = $v;
					break;
				}
			}
		}
		return array_unique($result);
	}
	/*
	 * Retrieve recipients for given list of vo ids 
	 * grouped by vo name
	 */
	public static function getRecipientsPerVO($vos){
		if( $vos && !is_array($vos) ){
			if( is_numeric($vos) ){
				$vos = array($vos);
			}else{
				$vos = array();
			}
		}
		$roles = VoAdmin::getDefaultVORoles();
		$res = array();
		foreach($vos as $void){
			$contactinfos = VoAdmin::getVOContactInfo($void, $roles);
			if( !$contactinfos || count($contactinfos) === 0){
				continue;
			}
			$voname = "";
			$contacts = array();
			foreach($contactinfos as $ci){
				$voname = $ci["vo"];
				//email, name, role
				$contacts[] = array(
					"name"=> $ci["name"],
					"email"=> $ci["email"],
					"role"=> $ci["role"]
				);
			}
			$res[] = array("vo"=>$voname, "void"=>$void, "contacts"=>$contacts);
		}
		return $res;
	}
	/*
	 * Get the emails of a vo based on the given roles.
	 */
	public static function getVOContactInfo($vo, $roles = array()){
		$voitem = self::getVo($vo);
		if( !$voitem ){
			return array();
		}
		$voroles = self::normalizeVORoles($roles);
		
		$query = "SELECT DISTINCT egiops.vo_contacts.email, egiops.vo_contacts.name , egiops.vo_contacts.role, egiops.vo_contacts.vo FROM egiops.vo_contacts WHERE egiops.vo_contacts.vo = ?";
		if( count($voroles) > 0 ){
			$query .= " AND role in ('" . implode("','", $voroles) . "')";
		}
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$res = db()->query($query, array($voitem->name))->fetchAll();
		
		return $res;
	}
	/*
	 * Returns VOs which published image list contains obsolete/deleted images 
	 * or references a deleted or expired virtual appliances.
	 * 
	 * The results also contain the obsolete vappliances.
	 */
	public static function getObsoleteVOImagelists($vo=null){
		$query = "SELECT * FROM vo_obsolete_images";
		$params = array();
		if( $vo !== null ){
			$voitem = self::getVo($vo);
			if( $voitem === null ){
				error_log("[VOAdmin::getObsoleteVOImagelists] Could not find vo " . $vo);
				return false;
			}
			$query .= " WHERE void = ?";
			$params[] = $voitem->id;
		}
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$res = db()->query($query, $params)->fetchAll();
		return $res;
	}
	/*
	 * Returns VOs which published image list contains images 
	 * of expired virtual appliance versions.
	 * 
	 * The results also contain the obsolete vappliances.
	 */
	public static function getExpiredVOImagelists($vo=null){
		$query = "SELECT * FROM vo_obsolete_images WHERE hasexpired = true";
		$params = array();
		if( $vo !== null ){
			$voitem = self::getVo($vo);
			if( $voitem === null ){
				error_log("[VOAdmin::getObsoleteVOImagelists] Could not find vo " . $vo);
				return false;
			}
			$query .= " AND void = ?";
			$params[] = $voitem->id;
		}
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$res = db()->query($query, $params)->fetchAll();
		return $res;		
	}
	/*
	 * Returns VOs which published image list contains images
	 * of deleted virtual appliances.
	 * 
	 * The results also contain the obsolete vappliances.
	 */
	public static function getDeletedVOImagelists($vo=null){
		$query = "SELECT * FROM vo_obsolete_images WHERE hasdeleted = true";
		$params = array();
		if( $vo !== null ){
			$voitem = self::getVo($vo);
			if( $voitem === null ){
				error_log("[VOAdmin::getObsoleteVOImagelists] Could not find vo " . $vo);
				return false;
			}
			$query .= " AND void = ?";
			$params[] = $voitem->id;
		}
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$res = db()->query($query, $params)->fetchAll();
		return $res;
	}
	private static function getAllVOs(){
		$result = array();
			$query = "SELECT distinct vos.id, vos.name,	domains.name AS discipline, false as endorsed, false as uptodate FROM vos 
			inner join domains on domains.id = vos.domainid 
			WHERE deleted = FALSE GROUP BY vos.id, domains.name ORDER BY vos.name ASC";
		$result = db()->query($query)->fetchAll();
		return $result;
	}
	/*
	 * Return all of the VOs that endorsed the given virtual appliance
	 * if parameter $all is set to true, it will alse return all other VOs 
	 * with endorsed and updated columns set to FALSE
	 */
	public static function getEndorsedVos($vappliance, $all = false){
		$vapp = self::getVAppliance($vappliance);
		if( $vapp === null && $all !== true ){
			return array();
		}
		if( $vapp !== null ){
			$query = "SELECT 
					distinct vos.id, 
					vos.name,
					domains.name AS discipline, 
					bool_and(vowide_image_list_images.state = 'up-to-date'::e_vowide_image_state) AS uptodate
				FROM vos 
					LEFT OUTER JOIN vowide_image_lists ON vowide_image_lists.void = vos.id
					LEFT OUTER JOIN vowide_image_list_images ON vowide_image_list_images.vowide_image_list_id = vowide_image_lists.id
					LEFT OUTER JOIN vaviews ON vaviews.vapplistid = vowide_image_list_images.vapplistid
					LEFT OUTER JOIN domains ON domains.id = vos.domainid
				WHERE
					vowide_image_lists.state = 'published'::e_vowide_image_state AND
					deleted = FALSE  AND 
					vaviews.appid = ? 
				GROUP BY vos.id, domains.name ORDER BY vos.name ASC ";
			db()->setFetchMode(Zend_Db::FETCH_BOTH);
			$res = db()->query($query, array($vappliance->id))->fetchAll();
		} else {
			$res = array();
		}
		if( $all === true ){
			$result = array();
			$query = "SELECT distinct vos.id, vos.name,	domains.name AS discipline, false as endorsed, false as uptodate FROM vos 
				inner join domains on domains.id = vos.domainid 
				WHERE deleted = FALSE GROUP BY vos.id, domains.name ORDER BY vos.name ASC";
			$result = db()->query($query)->fetchAll();
			for($i=0; $i<count($result); $i+=1){
				foreach($res as $e){
					if( $result[$i]["id"] === $e["id"] ){
						$result[$i]["endorsed"] = true;
						$result[$i]["uptodate"] = $e["uptodate"];
						break;
					}
				}
			}
			return $result;
		}
		return $res;
	}
}

class VoAdminNotifications {
	private static function debugSendMultipart($subject, $to, $txtbody='', $htmlbody='', $username, $password, $replyto = false, $attachment = null, $cc = false, $ext = null){
		error_log("SENDING VO OBSOLETE IMAGE LIST NOTIFICATION: ");
		error_log("TO: " . implode(",", $to));
		error_log("REPLY_TO: " . $replyto);
		error_log("SUBJECT: " . $subject);
		error_log("MESSAGE: " . $txtbody);
	}
	private static function getVOObsoleteNotificationMessage($notification){
		$subject = "[EGI AppDB] VO " . $notification["voname"] . " image list notification";
		$message = "-- This is an automated message, please do not reply -- \n\n";
		$message .= "Dear VO management team,\n\n";
		$message .= "  the published image list of the VO " . $notification["voname"] . " contains some obsolete images as follows:\n\n";
		if( $notification["outdated"] > 0 ){
			$message .= "    " . $notification["outdated"] . " image" . ( ($notification["outdated"]>1)?"s":"" ) . " from an outdated virtual appliance version\n";
		}
		if( $notification["deleted"] > 0 ){
			$message .= "    " . $notification["deleted"] . " image" . ( ($notification["deleted"]>1)?"s":"" ) . " from a user deleted virtual appliance\n";
		}
		if( $notification["expired"] > 0 ){
			$message .= "    " . $notification["expired"] . " image" . ( ($notification["expired"]>1)?"s":"" ) . " from an expired virtual appliance version\n";
		}
		$message .= "\n  It is recommended to update and republish the vo image list by visiting the vo wide image list editor [1].";
		$message .= "\n  A guide to managing VO image lists is available at [2].\n\n";
		$message .= "Best regards,\n";
		$message .= "AppDB team\n";
		$message .= "\n_____________________________________________________________________________________________________________________\n";
		$message .= "[1].https://" . $_SERVER['APPLICATION_UI_HOSTNAME'] . "/store/vo/" . $notification["voname"] . "/imagelist (login required)\n";
		$message .= "[2].https://wiki.appdb.egi.eu/main:guides:manage_vo-wide_image_lists\n";
		$notification["subject"] = $subject;
		$notification["message"] = $message;
		
		return $notification;
	}
	/*
	 * Create and return a VO obsolete notification. Parameter $data is a row from the 
	 * list returned by VoAdmin::getVOObsoleteNotification function
	 */
	private static function getVOObsoleteNotification($data){
		$notification = array(
			"subject"=>"",
			"message"=>"",
			"recipients"=>array(), 
			"voname"=> $data["voname"], 
			"void"=>$data["void"],
			"hasdeleted"=>( ($data["hasdeleted"])?true:false ),
			"hasexpired"=>( ($data["hasexpired"])?true:false ),
			"hasoutdated"=>( ($data["hasoutdated"])?true:false ),
			"deleted"=>0,
			"expired"=>0,
			"outdated"=>0,
			"vappliances"=> array()
		);
		//Must have at least one type of obsolete data
		if( $notification["hasdeleted"] === false && $notification["hasexpired"] === false && $notification["hasoutdated"] === false ){
			return null;
		}
		//Parse json with obsolete virtual appliances
		try{
			$apps =  trim($data["apps"]);
			if( $apps === "" ){
				return null;
			}
			$apps = json_decode($apps, true);
			if( $apps === null || count($apps) === 0){
				return null;
			}
			$notification["vappliances"] = $apps;
			$deleted = 0;
			$expired = 0;
			$outdated = 0;
			foreach($apps as $a){
				if( $a["expired"] == "true"){
					$expired += 1;
				}
				if( $a["deleted"] == "true"){
					$deleted += 1;
				}
				if( $a["outdated"] == "true"){
					$outdated += 1;
				}
			}
			if( ($deleted+$expired+$outdated) === 0 ){
				return null;
			}
			$notification["deleted"] = $deleted;
			$notification["expired"] = $expired;
			$notification["outdated"] = $outdated;
		}catch(Exception $ex){
			return null;
		}
		//Retrieve vo emails
		$to = VoAdmin::getRecipientsPerVO(intval($data["void"]));
		$recs = array();
		foreach($to as $t){
			if( trim($t["void"]) === trim($data["void"]) ){
				$cnts = $t["contacts"];
				foreach($cnts as $cnt){
					if( trim($cnt["email"]) !== "" ){
						$recs[$cnt["email"]] = $cnt["email"];
					}
				}
				break;
			}
		}
		$recipients = array();
		foreach($recs as $r){
			$recipients[] = $r;
		}
		if( count($recipients) === 0 ){
			return null;
		}
		$notification["recipients"] = $recipients;
		return self::getVOObsoleteNotificationMessage($notification);
	}
	/*
	 * Create and return a list of VO obsolete notifiactions.
	 * Vo obsolete images are retrieved by 
	 * VoAdmin::getObsoleteVOImagelists function
	 */
	public static function createVOObsoleteNotifications(){
		$notifications = array();
		$obsolete = VoAdmin::getObsoleteVOImagelists();
		foreach($obsolete as $obs){
			$nt = self::getVOObsoleteNotification($obs);
			if( $nt !== null ){
				$notifications[] = $nt;
			}
		}
		return $notifications;
	}
	public static function sendVOObsoleteNotifications(){
		$obsolete = VoAdmin::getObsoleteVOImagelists();
		foreach($obsolete as $obs){
			$nt = self::getVOObsoleteNotification($obs);
			if( $nt !== null ){
				if ( ApplicationConfiguration::isProductionInstance() === FALSE ) {
					self::debugSendMultipart($nt["subject"], $nt["recipients"], $nt["message"], null, "appdb reports username", "appdb reports password", false, null, false, null);
				} else {
					//sendMultipartMail($nt["subject"], $nt["recipients"], $nt["message"], null, 'appdb-reports@iasa.gr', 'enadyskolopassword', false, null, false, array("Precedence"=>"bulk"));
					EmailService::sendBulkReport($nt["subject"], $nt["recipients"], $nt["message"], null);
				}
			}
		}
	}
}

class VoContact{
	CONST VO_NOTIFY_SUBJECT_MIN_SIZE = 1;
	CONST VO_NOTIFY_SUBJECT_MAX_SIZE = 200;
	CONST VO_NOTIFY_MESSAGE_MIN_SIZE = 1;
	CONST VO_NOTIFY_MESSAGE_MAX_SIZE = 1000;
	/*
	 * Normalize VO ids. Return an array of integers.
	 */
	private static function normalizeVOs($voids){
		$result = array();
		if( !$voids ) {
			return $result;
		}
		if( is_array($voids) === false ){
			if( is_numeric($voids) === false ) {
				return $result;
			}else{
				$voids = array($voids);
			}
		}
		
		foreach( $voids as $void ){
			if( is_numeric($void) && intval($void) > 0){
				$result[] = intval($void);
			}
		}
		return $result;
	}
	/*
	 * Check if user can send notifications to VOs for the given 
	 * virtual appliance.
	 */
	private static function canSendVONotification($user, $vappliance){
		$user = VoAdmin::getUser($user);
		$vappliance = VoAdmin::getVAppliance($vappliance);
		if( $vappliance === null ){
			return "Virtual appliance not found";
		}
		if( $user === null ){
			return "User not found";
		}
		if( trim($user->getPrimaryContact()) === "" ){
			return "Cannot find user's primary email contact";
		}
		$privs = $user->getPrivs();
		if( $privs == null ){
			return "Could not retrieve user's permissions";
		}
		
		return $privs->canManageVAs($vappliance->guid);		
	}
	/*
	 * Returns only the ids of the given vos that have endorsed 
	 * the given virtual appliance (with any vappliance version)
	 */
	private static function getExclusionVOs($vappliance, $vos){
		$endorsed = VoAdmin::getEndorsedVos($vappliance);
		$result = array();
		foreach($endorsed as $e){
			foreach($vos as $vo){
				if( trim($vo) === trim($e["id"]) ){
					$result[] = $vo;
				}
			}
		}
		return $result;
	}
	/*
	 * Validate user's defined subject and message.
	 */
	private static function validateGenericNotificationData($subject, $message){
		if( count($subject) > self::VO_NOTIFY_SUBJECT_MAX_SIZE || count($subject) < self::VO_NOTIFY_SUBJECT_MIN_SIZE){
			return "Subject is mandatory for generic VO notifications and must not exceed " . VO_NOTIFY_SUBJECT_MAX_SIZE . " characters.";
		}
		if( count($message) > self::VO_NOTIFY_MESSAGE_MAX_SIZE || count($message) < self::VO_NOTIFY_MESSAGE_MIN_SIZE){
			return "Message is mandatory for generic VO notifications and must not exceed " . VO_NOTIFY_MESSAGE_MAX_SIZE . " characters.";
		}
		return true;
	}
	/*
	 * Initial validation of given data for the request
	 */
	private static function validateRequest($user, $vappliance, $notificationtype, $vos, $subject, $message){
		if( count($vos) === 0 ){
			return "No virtual organizations given.";
		}
		
		if( trim($subject) !== "" ){
			if( preg_match("/(\r|\n)*(to:|from:|cc:|bcc:)/i",$subject) ) {
				return "The subject contains invalid headers";
			}
		}
		
		if( trim($message) !== "" ){
			if( preg_match("/(\r|\n)(to:|from:|cc:|bcc:)/i",$message) ) {
				return "The message contains invalid headers";
			}
		}
		
		$vappliance = VoAdmin::getVAppliance($vappliance);
		$user = VoAdmin::getUser($user);
		
		//Check for user permissions
		$cansend = self::canSendVONotification($user, $vappliance);
		if( $cansend === false ){
			return "Only users with permission to manage virtual appliance versions can send notifications to VOs";
		} else if( is_string($cansend) ){
			return $cansend;
		}
		
		//Validate data for notifiaction types
		switch($notificationtype){
			case "suggest":
			case "newversion":
				if( count($message) > self::VO_NOTIFY_MESSAGE_MAX_SIZE ){
					return "Message should not exceed " . self::VO_NOTIFY_MESSAGE_MAX_SIZE . "characters.";
				}
				break;
			case "exclude":
				$vos = self::getExclusionVOs($vappliance, $vos);
				if( count($vos) === 0 ){
					return "No endorsed VOs to send notification";
				}
				break;
			case "generic":
				$valid = self::validateGenericNotificationData($subject, $message);
				if( $valid !== true ){
					return $valid;
				}
				break;
			default:
				return "Unknown notification type given";
		}
		return true;
	}
	private static function getSuggestNotificationMessage($user, $vappliance, $subject, $message){
		$res = array("subject"=>"","body"=>"");
		$res["subject"] = "[EGI APPDB VO Notification] User requests virtual appliance endorsement";
		$body = "Dear VO management team,\n\n";
		$body .= "  user " . $user->firstname . " " . $user->lastname . " [1] requests that the virtual appliance '" . $vappliance->name . "' [2] should be endorsed by the {{vo.name}} VO [3] and therefore be included into the VO wide image list.\n";
		$body .= "A guide to managing VO image lists is available at [4].\n ";
		if(strlen(trim($message)) > 0 ){
			$body .= "\nUser's message follows::\n";
			$body .= "\n--------------------------------------------------------\n\n";
			$body .= $message;
			$body .= "\n\n-------------------------------------------------------\n";
		}
		$body .= "\nBest regards,\n";
		$body .= "AppDB team\n\n";
		$body .= "________________________________________________________________________________________________________\n";
		$body .= "[1].https://" . $_SERVER['APPLICATION_UI_HOSTNAME'] . "/store/person/". $user->cname ."\n";
		$body .= "[2].https://" . $_SERVER['APPLICATION_UI_HOSTNAME'] . "/store/vappliance/". $vappliance->cname ."\n";
		$body .= "[3].https://" . $_SERVER['APPLICATION_UI_HOSTNAME'] . "/store/vo/{{vo.name}}\n";
		$body .= "[4].https://wiki.appdb.egi.eu/main:guides:manage_vo-wide_image_lists\n";
		
		$res["message"] = $body;
		return $res;
	}
	private static function getNewVersionNotificationMessage($user, $vappliance, $subject, $message){
		$res = array("subject"=>"","body"=>"");
		$res["subject"] = "[EGI APPDB VO Notification] New virtual appliance version available";
		$body = "Dear VO management team,\n\n";
		$body .= "  user " . $user->firstname . " " . $user->lastname . " [1] informs you that virtual appliance " . $vappliance->name . " [2] has published a new version for {{vo.name}} [3] VO. You should consider to update or not your VO wide image list.\n";
		$body .= "A guide to managing VO image lists is available at [4].\n ";
		if( strlen(trim($message)) > 0 ){
			$body .= "\nUser's message follows:\n";
			$body .= "--------------------------------------------------------\n\n";
			$body .= $message;
			$body .= "\n\n-------------------------------------------------------\n\n";
		}
		$body .= "\nBest regards,\n";
		$body .= "AppDB team\n\n";
		$body .= "________________________________________________________________________________________________________\n";
		$body .= "[1].https://" . $_SERVER['APPLICATION_UI_HOSTNAME'] . "/store/person/". $user->cname ."\n";
		$body .= "[2].https://" . $_SERVER['APPLICATION_UI_HOSTNAME'] . "/store/vappliance/". $vappliance->cname ."\n";
		$body .= "[3].https://" . $_SERVER['APPLICATION_UI_HOSTNAME'] . "/store/vo/{{vo.name}}\n";
		$body .= "[4].https://wiki.appdb.egi.eu/main:guides:manage_vo-wide_image_lists\n";
		
		$res["message"] = $body;
		return $res;
	}
	private static function getExclusionNotificationMessage($user, $vappliance, $subject, $message){
		$res = array("subject"=>"","body"=>"");
		$res["subject"] = "[EGI APPDB VO Notification] Request for virtual appliance exclusion from VO image list";
		$body = "Dear VO management team,\n\n";
		$body .= "  user " . $user->firstname . " " . $user->lastname . " [1] requests that the virtual appliance " . $vappliance->name . " [2] should be excluded from the {{vo.name}} [3] VO and therefore be excluded from the VO wide image list.\n";
		$body .= "A guide to managing VO image lists is available at [4].\n ";
		if( strlen(trim($message)) > 0 ){
			$body .= "\nUser's message follows:\n";
			$body .= "--------------------------------------------------------\n\n";
			$body .= $message;
			$body .= "\n\n-------------------------------------------------------\n\n";
		}
		$body .= "\nBest regards,\n";
		$body .= "AppDB team\n\n";
		$body .= "________________________________________________________________________________________________________\n";
		$body .= "[1].https://" . $_SERVER['APPLICATION_UI_HOSTNAME'] . "/store/person/". $user->cname ."\n";
		$body .= "[2].https://" . $_SERVER['APPLICATION_UI_HOSTNAME'] . "/store/vappliance/". $vappliance->cname ."\n";
		$body .= "[3].https://" . $_SERVER['APPLICATION_UI_HOSTNAME'] . "/store/vo/{{vo.name}}\n";
		$body .= "[4].https://wiki.appdb.egi.eu/main:guides:manage_vo-wide_image_lists\n";
		
		$res["message"] = $body;
		return $res;
	}
	private static function getGenericNotificationMessage($user, $vappliance, $subject, $message){
		$res = array("subject"=>"","body"=>"");
		$res["subject"] = "[EGI APPDB VO Notification] " ;
		if( trim($subject) !== "" ){
			$res["subject"] .= trim($subject);
		}else{
			$res["subject"] .= "Virtual Appliance general notification";
		}
		
		$body = "Dear VO management team,\n\n";
		$body .= "  user " . $user->firstname . " " . $user->lastname . " [1] sent you a notification regarding virtual appliance " . $vappliance->name . " [2] for the {{vo.name}} [3] VO.\n";
		if( strlen(trim($message)) > 0 ){
			$body .= "\nUser's message follows:\n";
			$body .= "--------------------------------------------------------\n\n";
			$body .= $message;
			$body .= "\n\n-------------------------------------------------------\n\n";
		}
		$body .= "Best regards,\n";
		$body .= "AppDB team\n\n";
		$body .= "________________________________________________________________________________________________________\n";
		$body .= "[1].https://" . $_SERVER['APPLICATION_UI_HOSTNAME'] . "/store/person/". $user->cname ."\n";
		$body .= "[2].https://" . $_SERVER['APPLICATION_UI_HOSTNAME'] . "/store/vappliance/". $vappliance->cname ."\n";
		$body .= "[3].https://" . $_SERVER['APPLICATION_UI_HOSTNAME'] . "/store/vo/{{vo.name}}\n";
		
		$res["message"] = $body;
		return $res;
	}
	/**
	 * Creates a report for the sender.
	 */
	private static function sendVONotificationReportMessage($notification, $user, $vappliance, $notificationtype, $usersubject, $usermessage){
		if( !$vappliance || !$user || !$notification){
			return;
		}
		$useremail = trim($notification["useremail"]);
		if( $useremail === "" ){
			return;
		}
		$recipients = $notification["recipients"];
		if( count($recipients) === 0 ){
			return null;
		}
		$noticationtypedisplay = "";
		switch($notificationtype){
			case "suggest":
				$noticationtypedisplay = "your endorsement request for virtual appliance " . $vappliance->name;
				break;
			case "exclude":
				$noticationtypedisplay = "your VO exclusion request for virtual appliance " . $vappliance->name;
				break;
			case "newversion":
				$noticationtypedisplay = "your notification for a new " . $vappliance->name . " version";
				break;
			case "generic":
			case "default":
				$noticationtypedisplay = "your notification for " . $vappliance->name . " virtual appliance";
				break;
		}
		$subject = "[EGI APPDB] VO contact notification";
		
		$message = "-- This is an automated message, please do not reply -- \n\n";
		$message .= "Dear " . $user->firstname . " " . $user->lastname . ",\n\n";
		$message .= "  we report that " . $noticationtypedisplay . " has been sent to the VO management teams of the following VOs:\n\n";
		$recs = array();
		foreach($recipients as $rec){
			$voname = $rec["vo"];
			$recs[] = $voname;
		}
		$message .= "    " . implode(", ", $recs);
		if( trim($usermessage) !== "" ) {
			$message .= "\n\nwith the following message:\n";
			$message .= "-------------------------------------------------------\n";
			if( trim($usersubject) !== "" ){
				$message .= "[subject]: " . $usersubject . "\n\n";
			}
			$message .= "\n" . $usermessage;
			$message .= "\n-------------------------------------------------------\n";
		}
		$message .= "\n\nBest regards,\n";
		$message .= "AppDB team\n";
		
		$to = array($useremail);
		if( ApplicationConfiguration::isProductionInstance() === FALSE ) {
			self::debugSendMultipart($subject, $to, $message, null, "appdb reports username", "appdb reports password", false, null, false, null);
		} else {
			//sendMultipartMail($subject, $to, $message, null, 'appdb-reports@iasa.gr', 'enadyskolopassword', false, null, false, array("Precedence"=>"bulk"));
			EmailService::sendBulkReport($subject, $to, $message);
		}
	}
	private static function getNotificationMessage($user, $vappliance, $notificationtype, $vos, $subject, $message){
		$msg = "";
		switch($notificationtype){
			case "suggest":
				$msg = self::getSuggestNotificationMessage($user, $vappliance, $subject, $message);
				break;
			case "newversion":
				$msg = self::getNewVersionNotificationMessage($user, $vappliance, $subject, $message);
				break;
			case "exclude":
				$msg = self::getExclusionNotificationMessage($user, $vappliance, $subject, $message);
				break;
			case "generic":
				$msg = self::getGenericNotificationMessage($user, $vappliance, $subject, $message);
				break;
			default:
				break;
		}
		return $msg;
	}
	
	private static function debugSendMultipart($subject, $to, $txtbody='', $htmlbody='', $username, $password, $replyto = false, $attachment = null, $cc = false, $ext = null){
		error_log("SENDING VO NOTIFICATION: ");
		error_log("TO: " . implode(",", $to));
		error_log("REPLY_TO: " . $replyto);
		error_log("SUBJECT: " . $subject);
		error_log("MESSAGE: " . $txtbody);
	}
	/*
	 * Creates and sends a VO notification. In case of success it also sends a 
	 * report email back to the sender and returns TRUE. In case of error it returns FALSE or a 
	 * description of the error.
	 */
	public static function sendVONotification($user, $vappliance, $notificationtype, $vos, $subject, $message){
		$notification = null;
		$usermessage = "" . $message;
		$usersubject = "" . $subject;
		$result = self::createVONotification($user, $vappliance, $notificationtype, $vos, $subject, $message, $notification);
		if( $result !== true){
			return $result;
		}
		if( !$notification ){
			return "Could not send notification";
		}
		$recipients = $notification["recipients"];
		$subject = $notification["subject"];
		$message = $notification["message"];
		$replyto = $notification["useremail"];
		try{
			foreach($recipients as $rec){
				$voname = $rec["vo"];
				$to = array();
				foreach($rec["contacts"] as $cnt){
					$to[] = trim( $cnt["email"] );
				}
				$txtbody = preg_replace('/\{\{vo\.name\}\}/i', $voname, $message);
				$subj = preg_replace('/\{\{vo\.name\}\}/i', $voname, $subject);
				if( ApplicationConfiguration::isProductionInstance() === FALSE ) {
					self::debugSendMultipart($subj, $to, $txtbody, null, "appdb reports username", "appdb reports password", $replyto, null, false, null);
				} else {
					//sendMultipartMail($subj, $to, $txtbody, null, 'appdb-reports@iasa.gr', 'enadyskolopassword', $replyto, null, false, array("Precedence"=>"bulk"));
					EmailService::sendBulkReport($subj, $to, $txtbody, null, $replyto);
				}
			}
			self::sendVONotificationReportMessage($notification, $user, $vappliance, $notificationtype, $usersubject, $usermessage);
		}catch(Exception $ex){
			return $ex->getMessage();
		}
		return true;
	}
	/*
	 * Creates a VO notification object and pass it to the $output parameter.
	 * If the given data are invalid or the creation failed it returns the description
	 * of the error or FALSE if no description available. If the creation succeeds
	 * it returns TRUE.
	 */
	public static function createVONotification($user, $vappliance, $notificationtype, $vos, $subject, $message, &$output=""){
		$vappliance = VoAdmin::getVAppliance($vappliance);
		$user = VoAdmin::getUser($user);
		$vos = self::normalizeVOs($vos);
		$isvalid = self::validateRequest($user, $vappliance, $notificationtype, $vos, $subject, $message);
		if( $isvalid !== true ){
			return $isvalid;
		}
		
		$notification = self::getNotificationMessage($user, $vappliance, $notificationtype, $vos, $subject, $message);
		$notification["recipients"] = VoAdmin::getRecipientsPerVO($vos);
		$notification["useremail"] = $user->getPrimaryContact();
		$notification["username"] = $user->firstname . " " . $user->lastname;
		$output = $notification;
		return true;
	}
	
}
?>
