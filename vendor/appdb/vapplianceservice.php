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


/*
 * Stores va version state transition.
 * Created by RestAppVAXMLParser::parseVAppVersion 
 * which can set previous and current state of version.
 */
class VApplianceVersionState {
	private $oldData = array();
	private $newData = array();
	private $instances = null;
	private $needcheck = null;
	
	function __construct(Default_Model_VAversion $olddata=null, Default_Model_VAversion $newdata=null){
		$this->oldData = $olddata;
		if( $newdata !== null ){
			$this->newData = $newdata;
		}
	}
	public function isNewVersion(){
		if( !is_numeric($this->oldData->id) || intval($this->oldData->id) <= 0 ){
			return true;
		}
		return false;
	}
	public function setVersionNewState($newdata){
		$this->newData = $newdata;
		if( $this->newData->enabled === null ){
			$this->newData->enabled = true;
		}
		if( $this->newData->published === null ){
			$this->newData->published = false;
		}
		if( $this->newData->archived === null ){
			$this->newData->archived = false;
		}
		if( $this->newData->status === null ){
			$this->newData->status = 'init';
		}
		return $this->validate();
	}
	public function getInstances(){
		if( !$this->instances ){
			$this->instances = array();
			$lists = new Default_Model_VALists();
			$lists->filter->vappversionid->equals($this->getId());
			if( count( $lists->items ) > 0 ){
				for($i=0; $i<count($lists->items); $i+=1){
					$list = $lists->items[$i];
					$this->instances[] = $list->getVMIinstance();
				}
				
			}
		}
		return $this->instances;
	}
	public function validate(){
		$newversion = $this->isNewVersion();
		
		//Validation only takes place for state transitions
		//In case of a new version there is no need for it.
		if( $newversion  === true ){
			return true;
		}
		if( $this->oldData->published === true && $this->oldData->archived === false && $this->oldData->enabled === $this->newData->enabled){
			return "Cannot make changes to published version";
		}
		if( $this->oldData->archived === true ){
			return "Cannot make changes to archived version";
		}
		if( $this->oldData->enabled === false && $this->oldData->enabled === $this->newData->enabled){
			return "Cannot make changes in disabled version";
		}
		//check if publishing is valid
		if( $this->toBePublished() === true ){
			
			if( $this->toBeDisabled() ){
				return  "Cannot publish and disable a version at the same time.";
			}
			
			if( $this->isWorkingVersion() === false ){
				return "Only working versions can be published.";
			}
			
			if( in_array($this->getCurrentStatus(), array("init", "verified", "ready", "verify", "verifypublish") ) === false ){
				return "Cannot publish a version in " . $this->getCurrentStatus() . " state.";
			}
			
			$instances = $this->getInstances();
			if( count( $instances ) === 0 ){
				return "Cannot publish an empty version.";
			}
			
		}
		return true;
	}
	public function isWorkingVersion(){
		if( $this->oldData->published == false && $this->oldData->archived == false ) {
			return true;
		}
		return false;
	}
	public function isLatestVersion(){
		if( $this->oldData->published == true && $this->oldData->archived == false ) {
			return true;
		}
		return false;
	}
	public function isEnabled(){
		if( $this->oldData->enabled == true ){
			return true;
		}
		return false;
	}
	public function isPublished(){
		if( $this->oldData->published == true ){
			return true;
		}
		return false;
	}
	public function toBeDisabled(){
		if( $this->isEnabled() && $this->newData->enabled == false ){
			return true;
		}
		return false;
	}
	public function toBeEnabled(){
		if( !$this->isEnabled() && $this->newData->enabled == true ){
			return true;
		}
		return false;
	}
	public function toCancelIntegrityCheck(){
		if( ($this->oldData->status === "verifing" ||  $this->oldData->status === "verifingpublish" ) && $this->newData->status === "init"){
			return true;
		}
		return false;
	}
	public function toBePublished(){
		if( $this->oldData->published == false && $this->newData->published == true ){
			return true;
		}
		return false;
	}
	public function toBeIntegrityChecked(){
		if( ($this->oldData->published === false && $this->newData->published === true) || ($this->oldData->published === false && ($this->newData->status === "verify" || $this->newData->status === "verifypublish") ) ){
			if( $this->needcheck === null ){
				if( $this->newData->status === "verify" ){
					$this->needcheck = true;
					return $this->needcheck;
				}
				$instances = $this->getInstances();
				$this->needcheck = false;
				for( $i=0; $i<count($instances); $i+=1 ){
					$instance = $instances[$i];
					if( $instance->autointegrity == true ){
						$this->needcheck = true;
						break;
					}
				}
			}
			return $this->needcheck;
		}
		return false;
	}
	public function getCurrentStatus(){
		$status = $this->newData->status;
		return strtolower( trim( $status ) );
	}
	public function getId(){
		return $this->newData->id;
	}
	public static function getVapplianceUsedVersions( $appid )
	{
		$res = array();
		db()->setFetchMode(Zend_Db::FETCH_BOTH);
		$q = "SELECT DISTINCT va_version FROM vaviews WHERE va_version_published = true AND appid = ?;";
		$versions = db()->query( $q, array($appid) )->fetchAll();
		
		if( count($versions) > 0  )
		{
			foreach($versions as $version) {
				$res[] = $version['va_version'];
			}
		}
		
		return $res;
	}
}
 
/*
 * Handles Virtual appliance version state transitions.
 */
class VApplianceService{
	private $state = null;
	private $version = null;
	private $latestversion = null;
	/*
	 * Needs VApplianceVersionState object to check which action to take
	 */
	function __construct(VApplianceVersionState $state) {
		$this->state = $state;
	}
	
	public function publish(){
		$version = $this->getVAVersion();
		$version->status = "verified";
		$version->createdon = "now()";
		$version->save();
		$result = $this->archiveLatestVersion();
		$va = $version->getVa();
		if( $va ) {
			$app = $va->getApplication();
			if( $app ) {
				$app->lastupdated = "now()";
				$app->save();
			}
		}

		return $result;
	}
	
	public function checkintegrity(){
		$version = $this->getVAVersion();
		$version->published = false;
		if($version->status === "verifypublish" ){ //if request was made for publishing
			$version->status = "verifingpublish";
		}else{
			$version->status = "verifing";
		}
		$version->save();
		return true;
	}	
	public function cancelIntegrityCheck(){
		$version = $this->getVAVersion();
		$version->published = false;
		$version->status = "init";
		$version->save();
		VMCaster::cancelIntegrityCheck($version->id);
		return true;
	}
	//Archive current published version if exists
	public function archiveLatestVersion(){
		$latestversion = $this->getLatestVersion();
		if( $latestversion ){
			$latestversion->archived = true;
			$latestversion->save();
		}
		return true;
	}
	public function disable(){
		$version = $this->getVAVersion();
		$version->enabled = false;
		$version->save();
		return true;
	}
	public function enable(){
		$version = $this->getVAVersion();
		$version->enabled = true;
		$version->save();
		return true;
	}
	public function validate(){
		return $this->state->validate();
	}
	//Calls actions according to va versions state
	public function dispatch(){
		$valid = $this->validate();
		if( $valid !== true ){
			return $valid;
		}
		$tobeintegritychecked = $this->state->toBeIntegrityChecked();
		$tocancelintegritycheck = $this->state->toCancelIntegrityCheck();
		$tobepublished = $this->state->toBePublished();
		$tobedisabled = $this->state->toBeDisabled();
		$tobeenabled = $this->state->toBeEnabled();
		
		if( $tobeintegritychecked === true ){
			return $this->checkintegrity();
		}else if( $tocancelintegritycheck === true ){
			return $this->cancelIntegrityCheck();
		} else if( $tobepublished === true ) {
			return $this->publish();
		}else if( $tobedisabled === true ){
			return $this->disable();
		}else if( $tobeenabled === true ){
			return $this->enable();
		}
		
		return true;
	}
	
	public function postDispatch(){
		$vaversion = $this->getVAVersion(true);
		
		//Create image list for unpubished working version
		if( $vaversion->published === false && 
			$vaversion->archived === false && 
			$vaversion->enabled === true && 
			$vaversion->status === "init" ){
			VMCaster::createImageList($vaversion->id, "unpublished");
		}else if ($vaversion->published === true &&
			$vaversion->archived === false &&
			$vaversion->enabled === true &&
			$vaversion->status === "verified" ){
			VMCaster::createImageList($vaversion->id, "published");
		}else if( $this->state->toBeIntegrityChecked() ){
			VMCaster::startIntegrityCheck($vaversion->id);
		}
		return true;
	}
	
	//Get vaversion current data
	//If force = true it will refetch it from db
	public function getVAVersion($force = false){
		if( !$this->version || $force === true){
			$id = $this->state->getId();
			if( $id ){
				$vers = new Default_Model_VAversions();
				$vers->filter->id->equals($id);
				if( count($vers->items) > 0 ){
					$this->version = $vers->items[0];
				}
			}
		}
		return $this->version;
	}
	//Get published va version if exists
	public function getLatestVersion(){
		if( $this->latestversion === null ){
			$version = $this->getVAVersion();
			$vaversions = new Default_Model_VAversions();
			$f = $vaversions->filter;
			$f->vappid->equals($version->vappid)->and($f->published->equals(true)->and($f->archived->equals(false)->and($f->id->notequals($version->id))));
			if( count( $vaversions->items ) > 0 ) {
				$this->latestversion = $vaversions->items[0];
			}
		}
		return $this->latestversion;
	}
}
?>
