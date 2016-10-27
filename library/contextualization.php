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

class ContextXMLParser {
	public static function parse($xml){
		$data = array(
			"id" => 0,
			"version" => null,
			"description" => null
		);
		$x = null;
		if( count($xml->xpath("//contextualization:context")) > 0 ){
			$x = $xml->xpath("//contextualization:context");
			$x  = $xml[0];
			$data["id"] = intval(trim(strval($x->attributes()->id)));
		}
		
		if( count($x->xpath("//contextualization:version")) > 0 ){
			$xx = $x->xpath("//contextualization:version");
			$data["version"] = trim(strval($xx[0]));
		} 
		
		if( count($x->xpath("//contextualization:description")) > 0 ){
			$xx = $x->xpath("//contextualization:description");
			$data["description"] = trim(strval($xx[0]));
		} 
		return $data;
	}
}
class ContextScriptXMLParser {
	public static function parse($xml){
		$data = array(
			"parentid" => 0,
			"id" => 0,
			"url"=> "",
			"title"=>"",
			"description"=>"",
			"format" => 0,
			"applications" => array()
		);
		$x = null;
		if( count($xml->xpath("//contextualization:context")) > 0 ){
			$x = $xml->xpath("//contextualization:context");
			$x  = $xml[0];
			$data["parentid"] = intval(trim(strval($x->attributes()->id)));
		}
		
		if( count($x->xpath("//contextualization:contextscript")) > 0 ){
			$x = $x->xpath("//contextualization:contextscript");
			$x = $x[0];	
		} 
		if( $x === null ){
			return "No context script data given";
		}
		$data["id"] = intval(trim(strval($x->attributes()->id)));
		if( count($x->xpath("./contextualization:url")) > 0 ){
			$xx = $x->xpath("./contextualization:url");
			$data["url"] = trim(strval($xx[0]));
		} 
		if( count($x->xpath("./contextualization:title")) > 0 ){
			$xx = $x->xpath("./contextualization:title");
			$data["title"] = trim(strval($xx[0]));
		}
		if( count($x->xpath("./contextualization:description")) > 0 ){
			$xx = $x->xpath("./contextualization:description");
			$data["description"] = trim(strval($xx[0]));
		}
		if( count($x->xpath("./contextualization:format")) > 0 ){
			$xx = $x->xpath("./contextualization:format");
			$xx = $xx[0];
			$data["format"] = intval(trim(strval($xx->attributes()->id)));
		}
		if( count($x->xpath("./contextualization:images")) > 0 ){
			$vmiinstances = array();
			foreach($x->xpath("./contextualization:images") as $el){
				if( intval(trim(strval($el->attributes()->id))) > 0 ){
					$apps[] = intval(trim(strval($el->attributes()->id)));
				}
			}
			$data["vmiinstances"] = $vmiinstances; 
		}else if( count($x->xpath("./contextualization:application")) > 0 ){
			$apps = array();
			foreach($x->xpath("./contextualization:application") as $el){
				if( intval(trim(strval($el->attributes()->id))) > 0 ){
					$apps[] = intval(trim(strval($el->attributes()->id)));
				}
			}
			$data["applications"] = $apps;
		}
		return $data;
	}
}
/**
 * Description of contextualization
 *
 * @author nakos
 */
class ContextScript {
	CONST CS_DESCR_MIN_SIZE = 0;
	CONST CS_DESCR_MAX_SIZE = 5000;
	CONST CS_TITLE_MIN_SIZE = 0;
	CONST CS_TITLE_MAX_SIZE = 150;
	
	
	public static function checkUrl($url){
		return Storage::fetchExternalUrl($url);
		//return ContextualizationScripts::fetchUrl($url);
	}
	
	public static function getContextScript($contextscript){
		if( !$contextscript ) {
			return null;
		}
		
		if( $contextscript instanceof Default_Model_ContextScript){
			return $contextscript;
		}
		
		$m = new Default_Model_ContextScripts();
		if( intval($contextscript) > 0 ){
			$m->filter->id->numequals($contextscript);
		}else{
			$m->filter->guid->numequals($contextscript);
		}
		
		if( count($m->items) > 0 ){
			$cs = $m->items[0];
			if( $cs->hasContext() ){
				return $m->items[0];
			}
		}
		
		return null;
	}
	
	/*
	 * Input:$script:  Default_Model_ContextScript
	 */
	public static function validateContextScriptMetaData($script){
		//Check if title(description) is within valid length of characters
		if( strlen($script->title) < ContextScript::CS_TITLE_MIN_SIZE || strlen($script->title) > ContextScript::CS_TITLE_MAX_SIZE){
			if(ContextScript::CS_TITLE_MIN_SIZE === 0){
				return "Description must not exceed " .ContextScript::CS_TITLE_MAX_SIZE . " characters";
			}else{
				return "Description must be " .ContextScript::CS_TITLE_MIX_SIZE . " to " .ContextScript::CS_TITLE_MAX_SIZE . " characters long";
			}
		}
		//Check if description(notes) is within valid length of characters
		if( strlen($script->description) < ContextScript::CS_DESCR_MIN_SIZE || strlen($script->description) > ContextScript::CS_DESCR_MAX_SIZE){
			if(ContextScript::CS_DESCR_MIN_SIZE === 0){
				return "Notes must not exceed " .ContextScript::CS_DESCR_MAX_SIZE . " characters";
			}else{
				return "Notes must be " .ContextScript::CS_DESCR_MIX_SIZE . " to " .ContextScript::CS_DESCR_MAX_SIZE . " characters long";
			}
		}
		
		//Check url value
		if( strlen(trim($script->url)) === 0 ){
			return "A valid URL location is required";
		}
		
		//Check for valid format id
		$fs = new Default_Model_ContextFormats();
		$fs->filter->id->numequals($script->formatid);
		if( count($fs->items) === 0 ){
			return "Invalid context script format given.";
		}
		
		return true;
	}
	public static function initContextScript($data){
		$m = null;
		if( intval($data["id"]) > 0 ){
			$m = ContextScript::getContextScript($data["id"]);
			if( $m === null ){
				throw new Exception("Invalid context script id given");
			}
		}else{
			$m = new Default_Model_ContextScript();
			$m->id = null;
		}
		if( isset($data["url"]) ){
			$m->url = $data["url"];
		}
		if( isset($data["title"]) ){
			$m->title = $data["title"];
		}
		if( isset($data["description"]) ){
			$m->description = $data["description"];
		}
		if( isset($data["format"]) ){
			$m->formatid = $data["format"];
		}
		
		return $m;
	}
}

/*
 * Replaced by ContextualizationStorage class in library/Storage.php
 */
class ContextualizationStorage_OLD{
	CONST STORE_PATH = "/storage/cs/";
	private static function getExtension($format){
		$f = strtolower(trim(preg_replace("/[\-\ ]*/", "", $format)));
		switch($f){
			case "cloudinit":
				return "cloudinit";
			case "unixshell":
				return "sh";
			case "python":
				return "py";
			case "chef":
				return "chef";
			case "puppet":
				return "puppet";
		}
		return $f;
		
	}
	private static function getContextScript($scriptid){
		$scripts = new Default_Model_ContextScripts();
		$scripts->filter->id->numequals($scriptid);
		if( count($scripts->items) > 0 ){
			return $scripts->items[0];
		}
		return null;
	}
	private static function getStorePath($script, $swappliance, $context){
		$path = dirname(dirname(__FILE__)) . ContextualizationStorage::STORE_PATH . $swappliance->id . "/" . $context->id . "/" . $script->guid . "." . self::getExtension($script->getContextFormat()->name);
		return $path;
	}
	private static function createBasePath($swappliance, $context){
		$path = dirname(dirname(__FILE__)) . ContextualizationStorage::STORE_PATH . $swappliance->id . "/" . $context->id;
		if(!file_exists($path)){
			return mkdir($path, 0777, true);
		}
		return true;
	}
	private static function archiveFile($script, $path){
		$arcpath = dirname($path) . "/" . $script->guid . "_" . date('Y') . date('m') . date('d') . "." . self::getExtension($script->getContextFormat()->name);
		rename($path,$arcpath);
	}
	public static function store($script, $swappliance, $context, $data){
		try{
			//reload to ensure guid
			$sc = self::getContextScript($script->id);
			if( $sc === null ){
				throw new Exception("Contextualization script entry not found");
			}
			$path = self::getStorePath($sc, $swappliance, $context);
			if( !file_exists($path) ){
				if( !self::createBasePath($swappliance,$context) ){
					throw new Exception("Could not store contextualization script");
				}
			}
			if(file_exists($path)){
				self::archiveFile($sc, $path);
			}
			file_put_contents($path, $data);
		}catch(Exception $ex){
			error_log("[CONTEXTUALIZATION::STORAGE] " . $ex->getMessage());
			return true;
			//return $ex->getMessage();
		}
		return true;
	}
}

class Contextualization {
	CONST CX_DESCR_MIN_SIZE = 0;
	CONST CX_DESCR_MAX_SIZE = 5000;
	CONST CX_VERSION_MIN_SIZE = 1;
	CONST CX_VERSION_MAX_SIZE = 150;
	
	private $entry;
	private $swappliance;
	private $user;
	private $canedit;
	
	/*
	 * Only static initContextualization can create instances of this
	 * class, in order to auto create necessary entries for context script 
	 * manipulation.
	 * The given user will be used as the actor for the actions to follow.
	 */
	function __construct($entry,$user){
		$this->entry = Contextualization::getContextualization($entry);
		if( $this->entry === null ){
			$this->entry = new Default_Model_Context();
		}
		$this->swappliance = $this->entry->getApplication();
		$this->user = $user;
		$this->canEdit = false;
		if( $this->user instanceof Default_Model_Researcher ){
			$privs = $this->user->getPrivs();
			if( $privs ){
				$this->canEdit = $privs->canManageContextScripts($this->swappliance);
			}
		}
	}
	/*
	 * Returns true if given user (in constructor) has permissions to 
	 * edit contextualization scripts (canManageContextScripts)
	 */
	public function canEdit(){
		return $this->canedit;
	}
	private function getRelatedSWApplianceGuids(){
		$q = "SELECT DISTINCT apps.guid, apps.name FROM contexts
			INNER JOIN context_script_assocs ON context_script_assocs.contextid = contexts.id
			INNER JOIN contextscripts AS cs ON cs.id = context_script_assocs.scriptid
			INNER JOIN vmiinstance_contextscripts AS vcs ON vcs.contextscriptid = cs.id
			INNER JOIN vaviews ON vaviews.vmiinstanceid = vcs.vmiinstanceid
			INNER JOIN applications AS apps ON apps.id = vaviews.appid
			WHERE apps.metatype = 1 AND contexts.appid = " . $this->swappliance->id;
		$rows = db()->query($q)->fetchAll();
		$res = array();
		foreach($rows as $r){
			$res[] = $r->guid;
		}
		return $res;
	}
	private function removeAllEntityRelations(){
		
	}
	
	private function handleEntityRelations(){
		$subjectguid = $this->swappliance->guid;
		$reltype = EntityRelations::getRelationType("swappliance","usage","vappliance");
		if( $reltype === null){
			throw new Exception("No relation type for sofwtare appliance and virtual appliance");
		}
		$vappguids = $this->getRelatedSWApplianceGuids();
		$result = EntityRelations::unrelateAll($subjectguid, array(), false, array($reltype->id));
		if( $result !== true ){
			throw new Exception($result);
		}
		
		if( count($vappguids) > 0 ){
			$userid = null;
			if( $this->user && is_numeric($this->user->id) && $this->user->id > 0 ){
				$userid = $this->user->id;
			}
			foreach($vappguids as $vg){
				$result = EntityRelations::relate($reltype->id, $subjectguid, $vg, $userid);
				if( is_string($result) ){
					throw new Exception($result);
				}
			}
		}
		return true;
	}
	/*
	 * Just save/update the entry
	 */
	public function save(){
		if( $this->entry ){
			if( $this->user && is_numeric($this->user->id) && intval($this->user->id) > 0 ){
				$this->entry->lastupdatedbyID = $this->user->id;
			}
			$this->entry->lastupdatedon = "NOW()";
			$this->entry->save();
		}
	}
	/*
	 * In the extreme case the entry's deletion is needed
	 */
	public function delete(){
		if( is_numeric($this->entry->id) && intval($this->entry->id) > 0 ){
			$this->entry->remove();
			$this->handleEntityRelations();
		}
	}
	public function updateContextScriptVMIInstances($script, $vmiinstances){
		$changes = array("deleted" => array(), "added" => array() );
		//remove associated vmi instances which are no longer wanted
		$m = new Default_Model_VMIinstanceContextScripts();
		$f1 = new Default_Model_VMIinstanceContextScriptsFilter();
		$f2 = new Default_Model_VMIinstanceContextScriptsFilter();
		$f1->contextscriptid->numequals($script->id);
		$f2->vmiinstanceid->notin($vmiinstances);
		$m->filter->chain($f1->chain($f2,"AND"),"AND");
		if( count($m->items) > 0 ){
			foreach($m->items as $item){
				$changes["deleted"][] = $item->vmiinstanceid;
				$item->getMapper()->delete($item);
			}
		}
		
		//collect already existing vmi instances
		$excludeids = array();
		$m = new Default_Model_VMIinstanceContextScripts();
		$f1 = new Default_Model_VMIinstanceContextScriptsFilter();
		$f2 = new Default_Model_VMIinstanceContextScriptsFilter();
		$f1->contextscriptid->numequals($script->id);
		$f2->vmiinstanceid->in($vmiinstances);
		$m->filter->chain($f1->chain($f2,"AND"),"AND");
		if( count($m->items) > 0 ){
			foreach($m->items as $item){
				if( $item->hasContext() ){
					$excludeids[] = "" . $item->vmiinstanceid;
				}
			}
		}
		
		//get final vmi instances to be included
		for($i=0; $i<count($vmiinstances); $i+=1){
			if( in_array($vmiinstances[$i], $excludeids) === false ){
				$item = new Default_Model_VMIinstanceContextScript();
				$item->vmiinstanceid = $vmiinstances[$i];
				$item->contextscriptid = $script->id;
				if( $this->user !== null ){
					$item->addedbyID = $this->user->id;
				}
				$item->save();
				$changes["added"][] = $vmiinstances[$i];
			}
		}
		if( count($changes["added"]) > 0 || count($changes["deleted"]) > 0 ){
			return true;
		}
		return false;
	}
	/*
	 * Retrieve latest version's vmi instance ids for the 
	 * given virtual appliance id (application->id)
	 */
	public function getVapplianceVMIInstances($vapplianceid){
		$res = array();
		$m = new Default_Model_VAviews();
		$f1 = new Default_Model_VAviewsFilter();
		$f2 = new Default_Model_VAviewsFilter();
		$f3 = new Default_Model_VAviewsFilter();
		
		$f1->appid->numequals($vapplianceid);
		$f2->va_version_published->equals(true);
		$f3->va_version_archived->equals(false);
		
		$m->filter->chain($f1->chain($f2->chain($f3, "AND"), "AND"),"AND");
		if( count($m->items) > 0 ){
			foreach($m->items as $item){
				$res[] = $item->getVmiinstanceID();
			}
		}
		return $res;
	}
	public function getScriptAssociation($scriptid){
		$m = new Default_Model_ContextScriptAssocs();
		$f1 = new Default_Model_ContextScriptAssocsFilter();
		$f2 = new Default_Model_ContextScriptAssocsFilter();
		$f1->contextid->equals($this->entry->id);
		$f2->scriptid->equals($scriptid);
		$m->filter->chain($f1->chain($f2, "AND"), "AND");
		if( count($m->items) > 0 ){
			return $m->items[0];
		}
		return null;
	}
	public function createAssociation($script){
		//Create association with this object
		$m = new Default_Model_ContextScriptAssoc();
		$m->contextID = $this->entry->id;
		$m->contextscriptID = $script->id;
		if( $this->user ){
			$m->addedbyID = $this->user->id;
		}
		$m->save();
		return $m;
	}
	/*
	 * Retrieves a virtual appliance application vmi instances
	 * Used when vappliance(application) id is given instead
	 * of vmi instance ids.
	 * Function assumes it must return all the vmi instances 
	 * of the latest vappliance version
	 */
	public function getVMIInstances($vappids){
		$res = array();
		foreach($vappids as $vappid){
			$res = array_merge($res, $this->getVapplianceVMIInstances($vappid));
		}
		return $res;
	}
	//Check if given context script belongs to this contextualization
	public function ownsScript($contextscript){
		$m = new Default_Model_ContextScriptAssocs();
		$f1 = new Default_Model_ContextScriptAssocsFilter();
		$f2 = new Default_Model_ContextScriptAssocsFilter();
		$f1->scriptid->numequals($contextscript->id);
		$f2->contextid->numequals($this->entry->id);
		$m->filter->chain($f1->chain($f2,"AND"),"AND");
		if( count($m->items) > 0 ){
			return true;
		}
		return false;
	}
	
	/*
	 * Create a new context script entry and associate given vmi instances
	 */
	public function insertContextScript($contextscript, $vmiinstances = array()){
		$script = ContextScript::getContextScript($contextscript);
		//If contextualization script already exists don;t do anything
		if( $script !== null && $script->id > 0 ){
			return true;
		}
		
		//Check that vappliances/vmi instances are given
		if( count($vmiinstances) === 0 ){
			throw new Exception("Cannot insert contextualization script without asscociating a virtual appliance");
		}
		
		//Validate metadata
		$valid = ContextScript::validateContextScriptMetaData($contextscript);
		if( $valid !== true ){
			throw new Exception($valid);
		}
		
		//URL checking
		$data = ContextScript::checkUrl($script->url);
		if( is_string($data) ){
			throw new Exception($data);
		}else{
			$script->name = $data["name"];
			$script->size = $data["size"];
			$script->checksum = $data["md5"];
			$script->checksumfunc = "md5";
		}
		
		//Save context script entry
		if( $this->user ){
			$script->addedbyID = $this->user->id;
		}
		$script->id = null;
		$script->save();
		
		//Store context script file
		$stored = ContextualizationStorage::store($script,$this->swappliance,$this->entry, $data["data"]);
		if( is_string($stored) ){
			throw new Exception($stored);
		}
		
		//Create association with this object
		$assoc = $this->createAssociation($script);
		if( $assoc === null ){
			throw new Exception("Could not create association for context script and software appliance");
		}
		
		//Finally associate the vmi instances
		$haschanges = $this->updateContextScriptVMIInstances($script, $vmiinstances);
		if( $haschanges === true ){
			$this->handleEntityRelations();
		}
		$this->save();
		return true;
	}
	
	/*
	 * Update metadata and associations(vmi instances) of 
	 * a context script entry
	 */
	public function updateContextScript($contextscript, $vmiinstances = array()){
		$haschanges = false;
		$script = ContextScript::getContextScript($contextscript);
		if( $script === null ){
			return;
		}
		
		//Check ownership
		if( $this->ownsScript($script) === false ){
			throw new Exception("Cannot update contextualization script of a different software appliance");
		}
		
		//Validate metadata
		$valid = ContextScript::validateContextScriptMetaData($script);
		if( $valid !== true ){
			throw new Exception($valid);
		}
		
		//URL checking
		$data = ContextScript::checkUrl($script->url);
		if( is_string($data) ){
			throw new Exception($data);
		}else{
			if( trim($script->checksum) !== trim($data["md5"]) ){
				$haschanges = true;
			}else{
				//error_log("no changes");
			}
			$script->name = $data["name"];
			$script->size = $data["size"];
			$script->checksum = $data["md5"];
			$script->checksumfunc = "md5";
		}
		
		//Save context script entry
		if( $this->user ){
			$script->lastupdatedByID = $this->user->id;
		}
		$script->lastupdatedon = "NOW()";
		$script->save();
		
		if( $haschanges === true ){
			//Remove old script from strorage
			ContextualizationStorage::remove($script, $this->entry, $this->user->id);
			//Store context script file
			$script->guid = generate_uuid_v4();
			$script->save();
		}
		$stored = ContextualizationStorage::store($script,$this->swappliance,$this->entry, $data["data"]);
		if( is_string($stored) ){
			throw new Exception($stored);
		}
		
		$assoc = $this->getScriptAssociation($script->id);
		if( $assoc === null ){
			//Create association with this object
			$assoc = $this->createAssociation($script);
			if( $assoc === null ){
				throw new Exception("Could not create association for context script and software appliance");
			}	
		}
		
		$haschanges = $this->updateContextScriptVMIInstances($script, $vmiinstances);
		if( $haschanges === true ){
			$this->handleEntityRelations();
		}
		$this->save();
		return true;
	}
	
	/*
	 * Remove a context script along with its vmi instance associations
	 */
	public function deleteContextScript($contextscript){
		$script = ContextScript::getContextScript($contextscript);
		if( $script === null || intval($script->id) <=0){
			return true;
		}
		
		//Check ownership
		if( $this->ownsScript($script) === false ){
			throw new Exception("Cannot remove contextualization script of a different software appliance");
		}

		//Remove contextscript association with context
		$assoc = $this->getScriptAssociation($script->id);
		if( $assoc !== null ){
			$assoc->getMapper()->delete($assoc);
		}
		
		//Remove vmi intances association with context script
		$images = new Default_Model_VMIinstanceContextScripts();
		$images->filter->contextscriptid->numequals($script->id);
		if( count($images->items) > 0 ){
			foreach($images->items as $item){
				$images->remove($item);
			}
		}

		//Remove contextscript
		$scripts = new Default_Model_ContextScripts();
		$scripts->filter->id->equals($script->id);
		if( count($scripts->items) > 0 ){
			ContextualizationStorage::remove($script, $this->entry, $this->user->id);
			$scripts->remove($scripts->items[0]);
		}
		$this->handleEntityRelations();
		$this->save();
		return true;
	}
	public function updateVMIInstances($contextscript, $vmiinstances){
		$script = ContextScript::getContextScript($contextscript);
		if( $script === null ){
			return;
		}
		
		//Check ownership
		if( $this->ownsScript($script) === false ){
			throw new Exception("Cannot update contextualization script of a different software appliance");
		}
		
		//Perform update
		$haschanges = $this->updateContextScriptVMIInstances($script, $vmiinstances);
		if( $haschanges === true ){
			$this->handleEntityRelations();
		}
		$script->lastupdatedon = "NOW()";
		$script->save();
		return true;
	}
	public function updateUrlInfo($contextscript){
		$haschanges = false;
		$script = ContextScript::getContextScript($contextscript);
		if( $script === null || intval($script->id) <=0){
			return true;
		}
		
		//Check ownership
		if( $this->ownsScript($script) === false ){
			throw new Exception("Cannot remove contextualization script of a different software appliance");
		}
		//URL checking
		$data = ContextScript::checkUrl($script->url);
		if( is_string($data) ){
			throw new Exception($data);
		}else{
			if( trim($script->checksum) !== trim($data["md5"]) ){
				$haschanges = true;
			}
			$script->name = $data["name"];
			$script->size = $data["size"];
			$script->checksum = $data["md5"];
			$script->checksumfunc = "md5";
		}
		
		//Save context script entry
		if( $this->user ){
			$script->lastupdatedByID = $this->user->id;
		}
		$script->lastupdatedon = "NOW()";
		$script->save();
		
		if( $haschanges === true ) {
			//remove old script from storage
			ContextualizationStorage::remove($script, $this->entry, $this->user->id);
			//Store context script file
			$script->guid = generate_uuid_v4();
			$script->save();
		}
		$stored = ContextualizationStorage::store($script,$this->swappliance,$this->entry, $data["data"]);
		if( is_string($stored) ){
			throw new Exception($stored);
		}
		$this->save();
	}
	public function updateMetadata($data){
		$version = null;
		$description = null;
		if( $data["version"] !== null ){
			$version = trim($data["version"]);
			if( strlen($version) === 0 ){
				throw new Exception("Empty version value given");
			}
			//Check if version is within valid length of characters
			if( strlen($version) < Contextualization::CX_VERSION_MIN_SIZE || strlen($version) > Contextualization::CX_VERSION_MAX_SIZE){
				if( Contextualization::CX_VERSION_MIN_SIZE === 0 ){
					throw new Exception("Version must not exceed " . Contextualization::CX_VERSION_MAX_SIZE . " characters");
				}else{
					throw new Exception("Version must be " . Contextualization::CX_VERSION_MIN_SIZE . " to " . Contextualization::CX_VERSION_MAX_SIZE . " characters long");
				}
			}
			$this->entry->version = $version;
		}
		
		if( $data["description"] !== null ){
			$description = trim($data["description"]);
			//Check if description is within valid length of characters
			if( strlen($description) < Contextualization::CX_DESCR_MIN_SIZE || strlen($description) > Contextualization::CX_DESCR_MAX_SIZE){
				if( Contextualization::CX_DESCR_MIN_SIZE === 0 ){
					throw new Exception("Description must not exceed " . Contextualization::CX_DESCR_MAX_SIZE . " characters");
				}else{
					throw new Exception("Description must be " . Contextualization::CX_DESCR_MIN_SIZE . " to " . Contextualization::CX_DESCR_MAX_SIZE . " characters long");
				}
			}
			$this->entry->description = $description;
		}
		if( $version !== null || $description !== null ){
			$this->entry->save();
		}
		return true;
		
	}
	/*
	 * Dispatch actions for contextualization concerning 
	 * its context script entries
	 */
	public function action($action, $data){
		if( $this->canEdit() === false ){
			throw new Exception("No permission to " . $action . " context scripts", RestErrorEnum::RE_ACCESS_DENIED);
		}
		
		
		//Preload context script data and associated vmi instance ids
		if( $action !== "updatemetadata" ) {
			$model = ContextScript::initContextScript($data);
			if(isset($data["vmiinstances"])){
				$vmiinstances = $data["vmiinstances"];
			}else if( isset($data["applications"]) ){
				$vmiinstances = $this->getVMIInstances($data["applications"]);
			}
		}
		
		switch($action){
			case "update":
				return $this->updateContextScript($model, $vmiinstances);
			case "insert":
				return $this->insertContextScript($model, $vmiinstances);
			case "remove":
				return $this->deleteContextScript($model);
			case "retrieve":
				return true;
			case "updateurl":
				return $this->updateUrlInfo($model);
			case "updateimages":
				return $this->updateVMIInstances($model,$vmiinstances);
			case "updatemetadata":
				return $this->updateMetadata($data);
		}
	}
	
	/*
	 * Create a Conetxtualization model based on id or guid
	 */
	public static function getContextualization($context){
		if( $context === null ){ 
			return null;
		}
		if( $context instanceof Default_Model_Context ){
			return $context;
		}
		$contexts = new Default_Model_Contexts();
		if( is_numeric($context) ){
			$contexts->filter->id->numequals($context);
		}else if(is_string($context) ){
			$contexts->filter->guid->equals($context);
		}
		
		if( count($contexts->items) > 0 ){
			return $contexts->items[0];
		}
		
		return null;
	}
	
	/*
	 * Retrieves a sofwtare appliance Contextualization entry.
	 * Used by initContextualization to decide if a new entry
	 * must be created.
	 */
	public static function getContextualizationForSWAppliance($swapp){
		$ms = new Default_Model_Contexts();
		$ms->filter->appid->numequals($swapp->id);
		if( count($ms->items) > 0 ){
			return $ms->items[0];
		}
		return null;
	}
	
	/*
	 * Creates new or loads existing contextualization for given sw appliance($app)
	 */
	public static function initContextualization($app, $user=null){
		$swapp = null;
		$person = null;
		
		//Get Software appliance
		if( $app instanceof Default_Model_Application ){
			$swapp = $app;
		}else{
			$swapp = EntityTypes::getEntity("swappliance", $app);
		}
		
		if( $swapp === null ){
			return null;
		}		
		
		//Get user actor
		if( $user instanceof Default_Model_Researcher ){
			$person = $user;
		}else {
			$ps = new Default_Model_Researchers();
			if( is_numeric($user) ){
				$ps->filter->id->numequals($user);
			}else if( is_string($user) ){
				$ps->filter->guid->equals($user);
			}
			if( count($ps->items) > 0 ){
				$person = $ps->items[0];
			}
		}
		
		//Check if software appliance has associated contextualization
		//If NOT create a new entry and return its Contextualization 
		//class instance
		$context = self::getContextualizationForSWAppliance($swapp);
		if( $context === null ){
			$context = new Default_Model_Context();
			$context->applicationID = $swapp->id;
			if( $person !== null ){
				$context->addedbyID = $person->id;
			}
			
			$context->save();
		}
		return new Contextualization($context, $person);
	}
}


class ContextualizationNotifications{
	/*
	 * Returns the user ids which are owners of software appliances
	 * with expired or outdated referenced virtual appliances
	 */
	private static function getSWAppUsers(){
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$q = "SELECT DISTINCT swappownerid AS id, swappownername AS name, swappowneremail AS email FROM swappliance_report";
		$res = array();
		$rows = db()->query($q)->fetchAll();
		if( count($rows) > 0 ){
			for($i=0; $i< count($rows); $i+=1){
				$r = $rows[$i];
				$res[] = $r;
			}
		}
		return $res;
	}
	
	private static function sendNotification($notification){
		$subject = $notification["subject"];
		$to = $notification["recipient"];
		$txtbody = $notification["message"];
		if( ApplicationConfiguration::isProductionInstance() === FALSE ) {
			self::debugSendMultipart($subject, $to, $txtbody, null, "appdb reports username", "appdb reports password", false, null, false, null);
		} else {
			//sendMultipartMail($subject, $to, $txtbody, null, 'appdb-reports@iasa.gr', 'enadyskolopassword', false, null, false, array("Precedence"=>"bulk"));
			EmailService::sendBulkReport($subject, $to, $txtbody);
		}
	}
	private static function debugSendMultipart($subject, $to, $txtbody='', $htmlbody='', $username, $password, $replyto = false, $attachment = null, $cc = false, $ext = null){
		error_log("SENDING NOTIFICATION LIST: ");
		error_log("TO: " . implode(",", $to));
		error_log("REPLY_TO: " . $replyto);
		error_log("SUBJECT: " . $subject);
		error_log("MESSAGE: " . $txtbody);
	}
	
	public static function groupSWappliances($data){
		$res = array();
		
		foreach($data as $d){
			$swappid = $d["swappid"];
			$tmp = null;
			$states = array();
			if( isset($res[$swappid]) === false ){
				$tmp = array("id"=>$d["swappid"], "name"=>$d["swappname"],"cname"=>$d["swappcname"],"deleted"=>array(), "moderated"=>array(), "archived"=>array(),"expired"=>array());
			}else{
				$tmp = $res[$swappid];
			}
			if( $d["isarchived"] == true ){
				$tmp["archived"][] = $d;
			}
			if( is_numeric($d["days_expired"]) && intval($d["days_expired"]) > 0 ){
				$tmp["expired"][] = $d;
			}
			if( $d["deleted"] == true ){
				$tmp["deleted"][] = $d;
			}
			if( $d["moderated"] == true ){
				$tmp["moderated"][] = $d;
			}
			$tmp["state"] = array();			
			if( count($tmp["archived"]) > 0 ) {
				$states[] = "outdated";
			}
			
			if( count($tmp["expired"]) > 0 ){
				$states[] = "expired";
			}
			
			if( count($tmp["deleted"]) > 0 || count($tmp["moderated"]) > 0 ){
				$states[] = "deleted/moderated";
			}
			$tmp["states"] = $states;
			$res[$swappid] = $tmp;
		}
		
		return $res;
	}
	
	public static function getSWAppliances($user){
		$id = $user["id"];
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$q = "SELECT * FROM swappliance_report WHERE swappownerid = " . $id;
		$res = null;
		$rows = db()->query($q)->fetchAll();
		if( count($rows) > 0 ){
			$res = self::groupSWappliances($rows);
		}
		return $res;
	}
	
	public static function getNotificationMessage($data){
		$user = $data["user"];
		$swapps = $data["swappliances"];
		$index = 0;
		$footer = "\n\nBest regards,\n";
		$footer .= "AppDB team\n\n";
		$footer .= "____________________________________________________________________________\n";
		
		$msg = "-- This is an automated message, please do not reply -- \n\n";
		
		$msg .= "Dear " . $user["name"] . ",\n";
		//$msg .= "according to our records, you are the owner of ". ((count($swapps)>1)?count($swapps):"a") ." software appliance" . ((count($swapps)>1)?"s":"") . " which need your attention for the reasons listed bellow:\n\n";
		$msg .= "according to our records, you are the owner of one or more software appliances which need your attention for the reasons listed bellow:\n\n";
		foreach($swapps as $swapp){
			$states = $swapp["states"];
			if( count($states) > 1 ){
				$states[count($states)-1] = "and " . $states[count($states)-1];
			}
			$msg .= $swapp["name"] ."[" . ($index+1) . "] includes " .implode(",",$states) . " virtual appliances.\n";
			$footer .= "[" . ($index+1) . "] https://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/swappliance/" . $swapp["cname"] ."/contextualization\n";
		}
		
		$msg .= $footer;
		return $msg;
	}
	public static function getNotificationData($user){
		if( $user === null ) return null;
		$data = array();
		$data["user"] = $user;
		$data["recipient"] = array($data["user"]["email"]);
		$data["swappliances"] = self::getSWAppliances($user);
		$data["subject"] = "[EGI APPDB] Software appliances notification";
		$data["message"] = self::getNotificationMessage($data);
		return $data;
	}

	public static function getNotificationList(){
		$users = self::getSWAppUsers();
		$res = array();
		foreach($users as $u){
			$notification = self::getNotificationData($u);
			if( $notification !== null && count($notification["swappliances"]) > 0 ){
				$res[] = $notification;
			}
		}
		return $res;
	}
	
	public static function sendNotificationList(){
		$notifications = self::getNotificationList();
		foreach($notifications as $notification){
			self::sendNotification($notification);
		}
	}
}