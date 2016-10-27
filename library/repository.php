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

require_once('../application/modules/repository/models/DbTable/ZendDBTableBase.php');
class Repository_Db_Table_Row extends Zend_Db_Table_Row_Abstract{
	private $_externalRowData = array();
	
	public function __get($name) {
		$rm = $this->_table->getReferenceMapData($name);
		if( $rm ){
			if( ! isset($this->_externalRowData[$name]) ){
				$tbl = new $rm["refTableClass"]();
				$extId = $this->_data[$rm["columns"]];
				$select = $tbl->select()->where($rm["refColumns"] . ' = ?', $extId);
				$res = $tbl->fetchAll($select);
				if( count($res) > 0 ){
					$res = $res[0];
					$this->_externalRowData[$name] = $res;
				}
			}
			return $this->_externalRowData[$name];
		}
		return parent::__get($name);
	}
}
class Repository_Db_Table_Abstract extends Zend_Db_Table_Abstract {
	protected $_schema = "communitydb";
	protected $_primary = 'id';
	protected $_db = null;
	protected $_sequence = true;
	protected $_rowClass = 'Repository_Db_Table_Row';
	
	public function getReferenceMapData($name){
		return (isset($this->_referenceMap[$name])?$this->_referenceMap[$name]:null);
	}
	public function __construct( $config = array() ){
		parent::__construct($config);
		$registry   = Zend_Registry::getInstance();
		$this->_db = $registry['repository'];
	}
}

class Repositories extends Repository_Db_Table_Abstract {
	protected $_name = "comm_repo_repositories";
}

class RepositoryArchitecture extends Repository_Db_Table_Abstract{
	protected $_name = "comm_repo_archs";
}

class RepositoryOs extends Repository_Db_Table_Abstract {
	protected $_name = "comm_repo_oss";
	
}
class RepositoryOsArch extends Repository_Db_Table_Abstract{
	protected $_name = "comm_repo_allowed_platform_combinations";
	protected $_referenceMap = array(
		"Os"	=> array(
			"columns"		=>	"osId",
			"refTableClass"	=>	"RepositoryOs",
			"refColumns"	=>	"id"	
		),
		"Arch"	=> array(
			"columns"		=>	"archId",
			"refTableClass"	=>	"RepositoryArchitecture",
			"refColumns"	=>	"id"
		)
	);
}
class ProductState extends Repository_Db_Table_Abstract{
	protected $_name = "comm_repo_states";
}

class ProductRelease extends Repository_Db_Table_Abstract {
	protected $_name = "meta_product_release";
	protected $_referenceMap = array(
		"CurrentState" => array(
			"columns"		=>	"currentStateId",
			"refTableClass"	=>	"ProductState",
			"refColumns"	=>	"id"
		),
		"Parent" => array(
		    "columns"		=> "parent_id",
		    "refTableClass"	=> "ProductRelease",
		    "refColumns"	=> "id"
		),
		"RepoArea" => array(
		    "columns"		=> "repoAreaId",
		    "refTableClass"	=> "ProductRepositoryArea",
		    "refColumns"	=> "id"
		)
	);
}
class ProductRepositoryArea extends Repository_Db_Table_Abstract {
	protected $_name = "meta_product_repo_area";
}
class RepositoryConfig extends Repository_Db_Table_Abstract{
	protected $_name = "config";	
}
class RepositoryUtils {
	public static function toJSON($data=array()){
		return '{}';
	}
	
	public static function toXML($data=array(), $datatype="item", $schema = array()){
		$res = '<?xml version="1.0" encoding="UTF-16" standalone="yes"?><response></response>';
		return $res;
	}
}

class Repository{
	private static $db = null;
	private static function getDb(){
		return self::$db;
	}
	
	private static function getRepositoryApi(){
		return $_SERVER["Repository_Api"];
	}
	static function __initClass(){
		$registry   = Zend_Registry::getInstance();
		self::$db = $registry['repository'];
	}
	
	public static function canManageRelease($swid, $userid){
		//Get software data
		$apps = new Default_Model_Applications();
		$apps->filter->id->equals($swid);
		if( count($apps->items) === 0 ){
			return false;
		}
		$app = $apps->items[0];
		
		//Get user's information
		$users = new Default_Model_Researchers();
		$users->filter->id->equals($userid);
		if( count($users->items) === 0 ){
			return false;
		}
		$user = $users->items[0];
		
		//Check if user can manage software's releases
		$privs = $user->getPrivs();
		$hasAccess = $privs->canManageReleases($app);
		
		return $hasAccess;
	}
	public static function canManageEntity($userid, $entityid, $entitytype="release"){
		if( is_numeric($userid) ===  false ) return false;
		if( is_numeric($entityid) ===  false ) return false;
		$entitytype = strtolower(trim($entitytype));
		if( $entitytype == "" ) return false;
		$swid = "";
		switch($entitytype){
			case "release":
				$rels = new Repository_Model_MetaProductReleases();
				$rels->filter->id->equals($entityid);
				if( count($rels->items) === 0) return false;
				$rel = $rels->items[0];
				$repoarea = $rel->getRepoArea();
				if( !$repoarea ) return false;
				$swid = $repoarea->swid;
				break;
			case "repoarea":
				$repos = new Repository_Model_MetaProductRepoAreas();
				$repos->filter->id->equals($entityid);
				if( count($repos->items) === 0 ) return false;
				$repo = $repos->items[0];
				$swid = $repo->swid;
				break;
			case "poa":
				return false;
				break;
			default: 
				return false;
		}
		return self::canManageRelease($swid,$userid);
	}
	
	public static function getProductBaseReleases($swid , $state = null){
		$db = self::$db;
		$q = "SELECT rl.id id, 
			rl.displayVersion displayVersion, 
			rl.currentStateId stateId, 
			state.name stateName, 
			repo.id repoId, 
			repo.swId swId,
			repo.name repoAreaName, 
			rl.deleted isDeleted, 
			rl.timestampInserted created
			FROM meta_product_release rl 
			INNER JOIN meta_product_repo_area repo ON repo.id = rl.repoAreaId 
			INNER JOIN comm_repo_states state ON state.id = rl.currentStateId
			WHERE parent_id = 0 AND repo.swId = ? " . 
			(($state == null)?" ":" AND currentStateId = ? ") . 
			"ORDER BY displayIndex DESC";
		
		$params = array($swid);
		if( $state !== null ) $params[] = $state;
		
		return $db->query($q, $params)->fetchAll();
	}
	public static function call_delete_package($id,$userid){
		return self::call_delete_procedure("sp_delete_package", $id, $userid);
	}
	public static function call_delete_poa($id,$userid){
		return self::call_delete_procedure("sp_delete_poa", $id, $userid);
	}
	public static function call_delete_release($id,$userid){
		return self::call_delete_procedure("sp_delete_release", $id, $userid);
	}
	public static function call_delete_repo_area($id,$userid){
		return self::call_delete_procedure("sp_delete_repo_area", $id, $userid);
	}
	public static function call_delete_procedure($name, $id, $userid){
		$db = self::$db;
		try{
			$stmt = $db->query("CALL ".$name."(?,?)",array($id,$userid));
			$stmt->execute();
			$stmt->closeCursor();
		}catch(Exception $e){
			return $e->getMessage();
		}
		return true;
	}
	public static function getProductReleaseList($swid, $state = null){
		$res = array();
		$db = self::$db;
		
		$productmajors = self::getProductBaseReleases($swid,$state);
		
		if( count($productmajors) === 0){
			return $res;
		}
		
		foreach( $productmajors as $productmajor ){
			$cur = array();
			$q = "SELECT rl.id id, 
				rl.displayVersion displayVersion, 
				rl.currentStateId stateId, 
				state.name stateName, 
				repo.id repoId, 
				repo.name repoAreaName, 
				repo.swId swId,
				rl.deleted isDeleted, 
				rl.timestampInserted created
				FROM meta_product_release rl 
				INNER JOIN meta_product_repo_area repo ON repo.id = rl.repoAreaId
				INNER JOIN comm_repo_states state ON state.id = rl.currentStateId
				WHERE repo.swId = ? " .
				"AND parent_id = ? " .
				(($state == null)?" ":" AND currentStateId = ? ") . 
				"ORDER BY displayIndex DESC";
			
			$params = array($swid, $productmajor["id"]);
			if( $state !== null ) $params[] = $state;
			$releases = $db->query($q, $params)->fetchAll();
			
			foreach( $releases as $release){
				$cur[] = $release;
			}
			$res[] = array("base" => $productmajor, "updates"=> $cur);
		}
		return $res;
	}
	
	public static function hasDisplayVersion($displayversion,$swid, $reponame, $parentid){
		$db = self::$db;
		$displayversion = strtolower(trim($displayversion));
		if( $parentid != '0'){
			
		}
		$q = "SELECT rel.id, repo.swid, rel.displayVersion FROM meta_product_release rel INNER JOIN meta_product_repo_area repo ON  repo.id = rel.repoAreaId WHERE repo.swId = ? AND rel.displayVersion like ? " . ( ($reponame)?"AND repo.name like ? ":"" ) . " " . ( ($parentid!=0)?"AND rel.parent_id = ? ":"AND rel.parent_id = 0 " ) . "ORDER BY displayIndex DESC";
		
		$params = array($swid,$displayversion);
		if( $reponame ){
			$params[] = $reponame;
		}
		if( $parentid != 0 ){
			$params[] = $parentid;
		}
		$productmajors = $db->query($q, $params)->fetchAll();
		return $productmajors;
	}
	public static function getTargets(){
		$db = self::$db;
		$q = "select comb.id, 
			oss.id osId,
			oss.name osName, 
			oss.flavor osFlavor, 
			oss.label osLabel, 
			oss.artifactType osArtifactType,
			arch.id archId,
			arch.name archName,
			arch.label archLabel
		from comm_repo_allowed_platform_combinations comb
		inner join comm_repo_oss oss on oss.id = comb.osId
		inner join comm_repo_archs arch on arch.id = comb.archId;";
		return $db->query($q)->fetchAll();
	}
	
	public static function getOsArchs(){
		$osarch = new RepositoryOsArch();
		return $osarch->fetchAll();
	}
	public static function printOsArchs(){
		$res = self::getOsArchs();
		foreach($res as $oa){
			$o = $oa->Os;
			$a = $oa->Arch;
			error_log("Os: " . $o->name . "   Arch: ". $a->name);
		}
	}
	
	public static function getProductRepoAreas($swid, $repoAreaName){
		$db = self::$db;
		$repoAreaName = strtolower(trim($repoAreaName));
		$q = "SELECT repo.* FROM meta_product_repo_area repo WHERE repo.swId = ? AND repo.name like ?";
		return $db->query($q, array($swid, $repoAreaName))->fetchAll();
	}
	public static function getRelease($swid){
		$db = self::$db;
		$q = "SELECT rl.*,
			state.name stateName, 
			repo.id repoId, 
			repo.name repoAreaName, 
			repo.swId swId,
			rl.deleted isDeleted, 
			rl.timestampInserted created
			FROM meta_product_release rl 
			INNER JOIN meta_product_repo_area repo ON repo.id = rl.repoAreaId 
			INNER JOIN comm_repo_states state ON state.id = rl.currentStateId
			WHERE parent_id = 0 AND repo.swId = ? " . 
			"ORDER BY rl.displayIndex DESC";
		$params = array($swid);
		return $db->query($q, $params)->fetchAll();
	}
	public static function getReleaseFeed($stateid=2){
		$db = self::$db;
		$q = "SELECT 
			repo.id seriesId, 
			repo.name seriesName, 
			repo.swId swId,
			repo.swName swName,
			rl.id releaseId,
			rl.displayVersion releaseName,
			rl.releaseNotes releaseNotes,
			rl.deleted isDeleted, 
			rl.timestampLastUpdated lastupdated,
			rl.timestampReleaseDate lastreleasedate,
			rl.timestampLastProductionBuild lastproductiondate,
			rl.timestampInserted created
			FROM meta_product_release rl 
			INNER JOIN meta_product_repo_area repo ON repo.id = rl.repoAreaId 
			WHERE rl.currentStateId = ? ORDER BY rl.timestampReleaseDate DESC " . 
			",rl.timestampLastProductionBuild DESC,rl.timestampLastUpdated DESC, rl.timestampInserted". 
			",repo.Name ASC, repo.swName ASC, rl.displayIndex DESC";
		$params = array($stateid);
		return $db->query($q, $params)->fetchAll();
	}
	public static function getRepositoryAreaById($id){
		$rareas = new Repository_Model_MetaProductRepoAreas();
		$rareas->filter->id->equals($id);
		if( count($rareas->items) == 0 ){
			return null;
		}
		return $rareas->items[0];
	}
	public static function getRepositoryAreaByNameSw($name, $swid){
		if( trim($name) === "" ){
			return false;
		}
		
		if( !$swid || is_numeric($swid) === false){
			return false;
		}
		
		$rareas = new Repository_Model_MetaProductRepoAreas();
		$rareas->filter->name->equals($name)->and($rareas->filter->swId->equals($swid));
		if( count($rareas->items) === 0 ){
			return false;
		}
		
		return $rareas->items[0];
	}
	public static function getRepositoryAreaSWName($swid){
		if( !$swid || is_numeric($swid) === false){
			return false;
		}
		
		$rareas = new Repository_Model_MetaProductRepoAreas();
		$rareas->filter->swId->equals($swid);
		if( count($rareas->items) === 0 ){
			return false;
		}
		$area = $rareas->items[0];
		$swname = $area->swName;
		if( trim($swname) === "" ){
			return false;
		}
		return $swname;
	}
	public static function createRepositoryArea($name, $swid, $swname, $userid=0){
		if( trim($name) === "" ){
			return false;
		}
		
		if( !$swid || is_numeric($swid) === false){
			return false;
		}
		
		$rarea = self::getRepositoryAreaByNameSw($name, $swid);
		if( $rarea === false){
			//ignore new software names
			$sw_name = self::getRepositoryAreaSWName($swid);
			if( $sw_name === false ){
				$sw_name = $swname;
			}
			$rarea = new Repository_Model_MetaProductRepoArea();
			$rarea->name = $name;
			$rarea->swId = $swid;
			$rarea->swName = $sw_name;
			$rarea->insertedBy = $userid;
			$rarea->save();
		}
		$rarea = self::getRepositoryAreaByNameSw($name, $swid);
		$rareaid = $rarea->id;
		if( is_numeric($rareaid) == true && is_numeric($userid) === true && $userid!==0){
				Repository::addRepoAreaByExternalId($rareaid, $userid, "1");
				Repository::addRepoAreaByExternalId($rareaid, $userid, "2");
		}
		return $rareaid;
	}
	
	public static function createRelease($swid, $swname, $displayVersion, &$repoareaid, $parentid=0, $userid=0){
		$db = self::$db;
		
		$rel = new ProductRelease();
		$adapter = $rel->getAdapter();
		
		//check given parent (base release)
		if( $parentid != 0 ){
			$parent = new ProductRelease();
			$parent = $parent->fetchAll($parent->Select()->where("id = ?", $parentid));
			if( count($parent) == 0 ){
				return "The base release given for the update does not exist";
			} else if( $parent["deleted"] == "1") {
				return "The base release given for the update is marked as deleted";
			} else {
				$parent = $parent[0];
			}
		}
		
		//Check repository area value
		if( $parentid == 0 ){
			if( is_numeric($repoareaid) == false ){
				if( trim($repoareaid) == "" ||  trim($repoareaid) == '@'){
					return "Invalid series name value.";
				}
				$repoareaid = ltrim($repoareaid, '@');
				$repoareaid = Repository::createRepositoryArea($repoareaid,$swid,$swname, $userid);
				if( is_numeric($repoareaid) === false || $repoareaid < 0){
					return "Could not create series.";
				}
			} 
		} else {
			$repoareaid = $parent["repoAreaId"];
		}
		
		try{
			$adapter->beginTransaction();
			$data = array(
					"currentStateId" => 1,
					"displayVersion" => $displayVersion,
					"parent_id" => $parentid,
					"displayIndex" => "1",
					"repoAreaId" => $repoareaid,
					"description" => "",
					"technologyProvider" => "",
					"technologyProviderShortName" => "",
					"ISODate" => "",
					"majorVersion" => 0,
					"minorVersion"  => 0,
					"updateVersion" => 0,
					"revisionVersion" => 0,
					"releaseNotes" => "",
					"knownIssues" => "",
					"changeLog" => "",
					"installationNotes" => "",
					"repositoryURL" => "",
					"releaseXML" => "",
					"insertedBy" => $userid
			);
			$lastInsertId = $rel->insert($data);
			$adapter->commit();
		}catch(Exception $e){
			$adapter->rollBack();
			return $e->getMessage();
		}
		RepositoryServices::AppDBSyncInsertRelease($lastInsertId);
		return $lastInsertId;
	}
	public static function addReleaseContact($releaseid, $firstname, $lastname, $email, $contacttype, $externalid=0){
		try{
			$contact = new Repository_Model_MetaContact();
			$contact->assocId = $releaseid;
			$contact->assocEntity = "release";
			$contact->externalId = $externalid;
			$contact->contactTypeId = $contacttype;
			$contact->firstname = $firstname;
			$contact->lastname = $lastname;
			$contact->email = $email;
			$contact->save();
		}catch(Exception $e){
			return $e->getMessage();
		}
		return true;
	}
	public static function addReleaseByExternalId($releaseid, $externalid, $contacttype){
		try{
			$ppl = new Default_Model_Researchers();
			$ppl->filter->id->equals($externalid);
			if( count($ppl->items) === 0 ){
				return "Person's information not found.";
			}
			$p = $ppl->items[0];
			$email = $p->getPrimaryContact();
			
			$contact = new Repository_Model_MetaContact();
			$contact->assocId = $releaseid;
			$contact->assocEntity = "release";
			$contact->externalId = $externalid;
			$contact->contactTypeId = $contacttype;
			$contact->firstname = $p->firstName;
			$contact->lastname = $p->lastName;
			$contact->email = $email;
			$contact->save();
		}catch(Exception $e){
			return $e->getMessage();
		}
		return true;
	}
	public static function addRepoAreaByExternalId($repoareaid, $externalid, $contacttype){
		try{
			$ppl = new Default_Model_Researchers();
			$ppl->filter->id->equals($externalid);
			if( count($ppl->items) === 0 ){
				return "Person's information not found.";
			}
			$p = $ppl->items[0];
			$email = $p->getPrimaryContact();
			
			$contact = new Repository_Model_MetaContact();
			$contact->assocId = $repoareaid;
			$contact->assocEntity = "area";
			$contact->externalId = $externalid;
			$contact->contactTypeId = $contacttype;
			$contact->firstname = $p->firstName;
			$contact->lastname = $p->lastName;
			$contact->email = $email;
			$contact->save();
		}catch(Exception $e){
			return $e->getMessage();
		}
		return true;
	}
	public static function removeReleaseContact($id){
		try{
			$contacts = new Repository_Model_MetaContacts();
			$contacts->filter->id->equals($id);
			if( count($contacts->items) === 0 ){
				return "Person's contact information not found.";
			}
			$contact = $contacts->items[0];
			$contact->delete();
		}catch(Exception $e){
			return $e->getMessage();
		}
		return true;
	}
	public static function validateNewRelease($swid, $displayversion, $repoareaname, $parentid=0){
		//Store errors in the arrays bellow
		$result_displayversion = array();
		$result_repoarea = array();

		if( //Check parameters
			is_numeric($swid) == false ||
			trim($displayversion) == "" ||
			(trim($repoareaname) == "" && $parentid==0)
		) {
			return false;
		}

		//Display version validation
		$len = strlen( trim($displayversion) );
		if( preg_match('/^\./', $displayversion) || preg_match('/\.$/', $displayversion) ) {
			$result_displayversion[] = 'Value must not start or end with "&lt;b&gt;.&lt;/b&gt;" character.';
		}
		if( preg_match('/[\ \n\t]/', $displayversion) ) {
			$result_displayversion[] = 'No white spaces allowed.';
		}
		if( $len < 3 || $len > 20 ){
			$result_displayversion[] = 'Value must be between 3 to 20 characters long.';
		}
		if( !preg_match('/[A-Za-z0-9]+/', $displayversion) ) {
			$result_displayversion[] = 'Value must contain alphanumeric characters.';
		}
		if (!preg_match('/^[A-Za-z0-9\.\_\-]+$/',$displayversion) ) {
			$result_displayversion[] = 'Value contains invalid characters. Only . _ - symbols are allowed.';
		}

		if( count($result_displayversion) == 0 ){
			if( (is_numeric($parentid) == false && $parentid <= 0) || $parentid == 0) {
				$parentid = 0;
			}else{
				//This code is reached in case of an update release.
				//Check if display version is the same with base release.
				$res = self::getRelease($swid);
				if( count($res) > 0 ){
					$resversion = trim(strtolower($res[0]["displayVersion"]));
					$parversion = trim(strtolower($displayversion));
					if( $resversion ==  $parversion ){
						$result_displayversion[] = "Base release has the same display name";
					} 
				}else{
					$result_displayversion[] = "Base release for this software cannot be found";
				}
			}
		}
		
		if( count($result_displayversion) == 0 ){
			$res = Repository::hasDisplayVersion($displayversion, $swid, $repoareaname, $parentid);
			if( count($res) > 0 ){
				if( $parentid == 0 ){
					$result_displayversion[] = 'Another release has the same display version.';
				} else {
					$result_displayversion[] = 'Another update of this release has the same display version.';
				}
			}
		}
		
		if( $parentid == 0 ){ //In case of a major release
			// Repository Area validation
			$len = strlen( trim($repoareaname) );
			if( preg_match('/^\./', $repoareaname) || preg_match('/\.$/', $repoareaname) ) {
				$result_repoarea[] = 'Value must not start or end with "&lt;b&gt;.&lt;/b&gt;" character.';
			}
			if( preg_match('/[\ \n\t]/', $repoareaname) ) {
				$result_repoarea[] = 'No white spaces allowed.';
			}
			if( $len < 2 || $len > 20 ){
				$result_repoarea[] = 'Value must be between 2 to 20 characters long.';
			}
			if( !preg_match('/[A-Za-z0-9]+/', $repoareaname) ) {
				$result_repoarea[] = 'Value must contain alphanumeric characters.';
			}
			if (!preg_match('/^[A-Za-z0-9\.\,\_\(\)\-]+$/',$repoareaname) ) {
				$result_repoarea[] = 'Value contains invalid characters. Only . _ - ( ) symbols are allowed.';
			}


			$repos = Repository::getProductRepoAreas($swid, $repoareaname);
			if( count($repos) > 0 ){
				$result_repoarea[] = 'There is already another release using the same series name.';
			}
		}
		if( count($result_displayversion) == 0 && count($result_repoarea) == 0 ) {
			return true;
		}
		
		return array("displayversion" => $result_displayversion, "repoarea" => $result_repoarea);
	}
	
	public static function getPoaById($poaid){
		$poas = new Repository_Model_MetaPoaReleases();
		$poas->filter->id->equals($poaid);
		if( count($poas->items) === 0 ){
			return false;
		}
		return $poas->items[0];
	}
	public static function getPoaByReleaseTarget($release, $target){
		$releaseid = ( ( is_numeric($release) )?$release:$release->id);
		$targetid = ( ( is_numeric($target) )?$target:$target->id);
		
		$poas = new Repository_Model_MetaPoaReleases();
		$poas->filter->targetPlatformCombId->equals($targetid)->and($poas->filter->productReleaseId->equals($releaseid));
		if( count($poas->items) === 0 ){
			return false;
		}
		return $poas->items[0];
	}
	public static function createPoa($release, $target, $userid, &$output){
		$targetid = ( ( is_numeric($target) )?$target:$target->id);
		$releaseid = ( ( is_numeric($release) )?$release:$release->id);
		
		$poa = self::getPoaByReleaseTarget($releaseid, $targetid);
		
		if( $poa === false ){
			$dmethods = $target->getOs()->getDeployMethods();
			if(count($dmethods) === 0){
				$output = "No deploy method found for " . $target->getOs()->displayName . " (" . $target->getOs()->id . ")";
				return false;
			}
			$dmethodid = $dmethods[0]->id;
			$poa = new Repository_Model_MetaPoaRelease();
			$poa->productReleaseId = $releaseid;
			$poa->targetPlatformCombId = $targetid;
			$poa->dMethodCombId = $dmethodid;
			$poa->insertedBy = $userid;
			$poa->save();
			
			$poas = new Repository_Model_MetaPoaReleases();
			$poas->filter->targetPlatformCombId->equals($target->id)->and($poas->filter->productReleaseId->equals($release->id));
			if( count($poas->items) === 0 ){
				$output = "Could not retrieve stored POA for release: " . $release->id . " and target: " . $target->id;
				return false;
			}else{
				$poa = $poas->items[0];
			}
		}
		return $poa;
	}
	public static function createPoaPackage($poa, $info, $userid, &$output){
		$poaid = ( (is_numeric($poa) )?$poa:$poa->id);
		try{
			$pkg = new Repository_Model_MetaPoaReleasePackage();
			$pkg->poaId = $poaid;
			$pkg->pkgName = $info["name"];
			$pkg->pkgVersion = $info["version"];
			$pkg->pkgRelease = $info["release"];
			$pkg->pkgArch = $info["architecture"];
			$pkg->pkgType = $info["type"];
			$pkg->pkgFilename = $info["filename"];
			$pkg->pkgDescription = $info["description"];
			$pkg->pkgInstallationsize = ( isset($info["installationsize"])?$info["installationsize"]:"" );
			$pkg->pkgGroup = ( isset($info["group"])?$info["group"]:"" );
			$pkg->pkgRequires = ( isset($info["depends"])?$info["depends"]:"" );
			$pkg->pkgLicense = ( isset($info["license"])?$info["license"]:"" );
			$pkg->pkgUrl = ( isset($info["url"])?$info["url"]:"" );
			$pkg->pkgSize = $info["size"];
			$pkg->pkgMd5Sum = $info["md5sum"];
			$pkg->pkgSha1Sum = $info["sha1sum"];
			$pkg->pkgSha256Sum = $info["sha256sum"];
			//$pkg->pkgUrl = $info["url"];
			$pkg->insertedBy = $userid;
			$pkg->save();
		} catch(Exception $e){
			$output = $e->getMessage();
			return false;
		}
		$pkgs = new Repository_Model_MetaPoaReleasePackages();
		$pkgs->filter->id->equals($pkg->id);
		if( count($pkgs) === 0){
			$output = "Could not retrieve new package release";
			return false;
		}
		return $pkg;
	}
	public static function hasPOAPackage($poaid, $info){
		$pckgs = new Repository_Model_MetaPoaReleasePackages();
		$pckgs->filter->poaId->equals($poaid)->and($pckgs->filter->pkgFilename->equals($info["filename"]));
		if( count($pckgs->items) === 0){
			return false;
		}
		return $pckgs->items[0];
	}
	public static function getPOAPackage($id){
		$pckgs = new Repository_Model_MetaPoaReleasePackages();
		$pckgs->filter->id->equals($id);
		if( count($pckgs) === 0 ){
			return null;
		}
		return $pckgs->items[0];
	}
	public static function updatePoaPackage($id, $info, &$output){
		$pkg = $id;
		if( is_numeric($id) ){
			$pkg = self::getPOAPackage($id);
			if( is_null($pkg) ){
				return false;
			}
		}
		$id = $pkg->id;
		try{
			$pkg->pkgName = $info["name"];
			$pkg->pkgVersion = $info["version"];
			$pkg->pkgRelease = $info["release"];
			$pkg->pkgArch = $info["architecture"];
			$pkg->pkgType = $info["type"];
			$pkg->pkgFilename = $info["filename"];
			$pkg->pkgDescription = $info["description"];
			$pkg->pkgSize = $info["size"];
			$pkg->pkgMd5Sum = $info["md5sum"];
			$pkg->pkgSha1Sum = $info["sha1sum"];
			$pkg->pkgSha256Sum = $info["sha256sum"];
			$pkg->save();
		} catch(Exception $e){
			$output = $e->getMessage();
			return false;
		}
		$pkg = self::getPOAPackage($id);
		if( is_null($pkg) ){
			$output = "Could not retrieve updated package release";
			return false;
		}
		return $pkg;
	}
	public static function getReleaseById($id){
            $rels = new Repository_Model_MetaProductReleases();
            $rels->filter->id->equals($id);
            if( count($rels->items)> 0 ){
                return $rels->items[0];
            }
            return false;
        }
    public static function releaseHasPackage($release, $packageid){
            $packages = new Repository_Model_MetaPoaReleasePackages();
            $packages->filter->id->equals($packageid);
            if( count($packages->items) == 0 ){
                return "Package not found";
            }
            $package = $packages->items[0];

            $poas = new Repository_Model_MetaPoaReleases();
            $poas->filter->id->equals($package->poaId);
            if( count($poas->items) == 0 ){
                return "Package does not belong under a release";
            }
            $poa = $poas->items[0];
            if( $release->id != $poa->productReleaseId ){
                return "Package odoes not belong to the specified release";
            }
            return $package;
        }
    public static function removePoaPackages($ids, $releaseid,$userid){
            $release = self::getReleaseById($releaseid);
            
            $errors = array();
            if( !$release ){
                return "Could not retrieve release";
            }
            //this is candidate
            if( $release->currentStateId==2){
                return "Cannot remove package in a production release";
            }
            //this is candidate
            if( $release->currentStateId == 3){
                RepositoryBackend::unpublish($releaseid, "candidate");
            }
            $poastocheck = array();
            for($i = 0; $i< count($ids); $i+=1){
                $pckg = self::releaseHasPackage($release, $ids[$i]);
                if( is_string($pckg) == true ){
                    $errors[] = array("id"=>$ids[$i], "error"=>$pckg);
                    continue;
                }
                if( in_array($pckg->poaId, $poastocheck) == false ){
                    $poastocheck[] = $pckg->poaId;
                }
				$poa = Repository::getPoaById($pckg->poaId);
				$filepath = "";
				if( $poa ){
					$error = "";
					$filepath = RepositoryFS::getStoragePath($error) .  $poa->poaPath . $pckg->pkgFilename;
				}
				self::call_delete_package($pckg->id, $userid);
				
				if( trim($filepath) !== "" && file_exists($filepath)){
					@rename($filepath, $filepath.".deleted." . $ids[$i]);
				}
            }
            //Clear poas
            if( count($poastocheck) > 0 ){
                for($i=0; $i<count($poastocheck); $i+=1){
                    $poas = new Repository_Model_MetaPoaReleases();
                    $poas->filter->id->equals($poastocheck[$i]);
                    if(count($poas)>0){
                        $poa = $poas->items[0];
                        $pckgs = $poa->getPackages();
                        if(count($pckgs) == 0 ){
							self::call_delete_poa($poa->id, $userid);
                            //$poas->remove($poa);
                        }
                    }
                }
            }
            if( count($errors) == 0) return true;
            return ( (count($errors)==0)?true:$errors );
        }
    public static function markPoaPackages($ids, $releaseid){
            $release = self::getReleaseById($releaseid);
            
            $errors = array();
            if( !$release ){
                return "Could not retrieve release";
            }
            
            for($i = 0; $i< count($ids); $i+=1){
                $pckg = self::releaseHasPackage($release, $ids[$i]);
                if( is_string($pckg) == true ){
                    $errors[] = array("id"=>$ids[$i], "error"=>$pckg);
                    continue;
                }
                $pckg->pkgLevel = 'meta'; 
                $pckg->save();
            }
            if( count($errors) == 0) return true;
            return ( (count($errors)==0)?true:$errors );
        }
    public static function unmarkPoaPackages($ids, $releaseid){
            $release = self::getReleaseById($releaseid);
            
            $errors = array();
            if( !$release ){
                return "Could not retrieve release";
            }
            
            for($i = 0; $i< count($ids); $i+=1){
                $pckg = self::releaseHasPackage($release, $ids[$i]);
                if( is_string($pckg) == true ){
                    $errors[] = array("id"=>$ids[$i], "error"=>$pckg);
                    continue;
                }
                $pckg->pkgLevel = 'dep'; 
                $pckg->save();
            }
            if( count($errors) == 0) return true;
            return ( (count($errors)==0)?true:$errors );
        }
}
class RepositoryContacts{
	public static function checkContactReleaseAction($releaseid,$userid, $action){
		//Get assoicated product release
		$releases = new Repository_Model_MetaProductReleases();
		$releases->filter->id->equals($releaseid);
		if( count($releases->items) === 0){
			return "Could not retrieve associated product release for contact " . $action . ".";
		}
		$release = $releases->items[0];
		
		//Get associated repowsitory area
		$repoarea = $release->getRepoArea();
		if( $repoarea === null ){
			return "Could not retrieve associated series for contact " . $action . ".";
		}
		
		//Get associated software id
		$swid = $repoarea->swId;
		if( $swid <= 0 ){
			return "Could not retrieve related software reference for contact " . $action . ".";
		}
		
		//Get software data
		$apps = new Default_Model_Applications();
		$apps->filter->id->equals($swid);
		if( count($apps->items) === 0 ){
			return "Could not retrieve related software information for contact " . $action . ".";
		}
		$app = $apps->items[0];
		
		//Get user's information
		$users = new Default_Model_Researchers();
		$users->filter->id->equals($userid);
		if( count($users->items) === 0 ){
			return "Could not retrieve user information for contact " . $action . ".Access denied.";
		}
		$user = $users->items[0];
		
		//Check if user can manage software's releases
		$privs = $user->getPrivs();
		$hasAccess = $privs->canManageReleases($app);
		if( $hasAccess === false ){
			return "Access denied for contact removal.";
		}
		return true;
	}
	public static function checkContactAreaAction($areaid,$userid, $action){
		//Get assoicated product release
		$repos = new Repository_Model_MetaProductRepoAreas();
		$repos->filter->id->equals($areaid);
		if( count($repos->items) === 0){
			return "Could not retrieve associated series for contact " . $action . ".";
		}
		$repo = $repos->items[0];
		
		//Get associated software id
		$swid = $repo->swId;
		if( $swid <= 0 ){
			return "Could not retrieve related software reference for contact " . $action . ".";
		}
		
		//Get software data
		$apps = new Default_Model_Applications();
		$apps->filter->id->equals($swid);
		if( count($apps->items) === 0 ){
			return "Could not retrieve related software information for contact " . $action . ".";
		}
		$app = $apps->items[0];
		
		//Get user's information
		$users = new Default_Model_Researchers();
		$users->filter->id->equals($userid);
		if( count($users->items) === 0 ){
			return "Could not retrieve user information for contact " . $action . ".Access denied.";
		}
		$user = $users->items[0];
		
		//Check if user can manage software's releases
		$privs = $user->getPrivs();
		$hasAccess = $privs->canManageReleases($app);
		if( $hasAccess === false ){
			return "Access denied for contact removal.";
		}
		return true;
	}
	public static function removeContactFromRelease($contacts, $userid){
		$contact = $contacts->items[0];
		$canRemove = self::checkContactReleaseAction($contact->assocId, $userid, "removal");
		if( $canRemove !== true ){
			return $canRemove;
		}
		
		//Do the contact remove
		try{
			$contacts->remove(0);
		}catch(Exception $e){
			return $e->getMessage();
		}
		
		return true;
	}
	public static function removeContactFromRepoArea($contacts, $userid){
		$contact = $contacts->items[0];
		$canRemove = self::checkContactAreaAction($contact->assocId, $userid, "removal");
		if( $canRemove !== true ){
			return $canRemove;
		}
		
		//Do the contact remove
		try{
			$contacts->remove(0);
		}catch(Exception $e){
			return $e->getMessage();
		}
		
		return true;
	}
	public static function remove($id, $userid){
		$c = new Repository_Model_MetaContacts();
		$c->filter->id->equals($id);
		if( count($c->items) == 0 ){
			return 'Contact not found.';
		}
		$contact = $c->items[0];
		$assocEntity = $contact->assocentity;
		switch($assocEntity){
			case "poa":
				break;
			case "area":
				return self::removeContactFromRepoArea($c,$userid);
			default:
				return self::removeContactFromRelease($c, $userid);
		}
		return "Unknown entity";
	}
	
	public static function addForRelease($data, $userid){
		$cs = new Repository_Model_MetaContacts();
		$cs->filter->assocEntity->equals("release")->and($cs->filter->assocId->equals($data["assocId"])->and($cs->filter->contactTypeId->equals($data["contactTypeId"])->and($cs->filter->email->like($data["email"]))));
		if( count($cs->items) > 0 ){
			return "Contact already exists";
		}
		$canAdd = self::checkContactReleaseAction($data["assocId"], $userid, "addition");
		if( $canAdd !== true ){
			return $canAdd;
		}
		
		return true;
	}
	public static function addForRepoArea($data, $userid){
		$cs = new Repository_Model_MetaContacts();
		$cs->filter->assocEntity->equals("area")->and($cs->filter->assocId->equals($data["assocId"])->and($cs->filter->contactTypeId->equals($data["contactTypeId"])->and($cs->filter->email->like($data["email"]))));
		if( count($cs->items) > 0 ){
			return "Contact already exists";
		}
		$canAdd = self::checkContactAreaAction($data["assocId"], $userid, "addition");
		if( $canAdd !== true ){
			return $canAdd;
		}
		
		return true;
	}
	public static function updateForRelease($data, $userid){
		$cs = new Repository_Model_MetaContacts();
		$cs->filter->assocEntity->equals("release")->and($cs->filter->assocId->equals($data["assocId"])->and($cs->filter->contactTypeId->equals($data["contactTypeId"])->and($cs->filter->email->like($data["email"]))));
		if( count($cs->items) === 0 ){
			return "Contact not found.";
		}
		$canUpdate = self::checkContactReleaseAction($data["assocId"], $userid, "update");
		if( $canUpdate !== true ){
			return $canUpdate;
		}
		
		return true;
	}
	public static function updateForRepoArea($data, $userid){
		$cs = new Repository_Model_MetaContacts();
		$cs->filter->assocEntity->equals("area")->and($cs->filter->assocId->equals($data["assocId"])->and($cs->filter->contactTypeId->equals($data["contactTypeId"])->and($cs->filter->email->like($data["email"]))));
		if( count($cs->items) === 0 ){
			return "Contact not found.";
		}
		$canUpdate = self::checkContactAreaAction($data["assocId"], $userid, "update");
		if( $canUpdate !== true ){
			return $canUpdate;
		}
		
		return true;
	}
	public static function add( &$data, $userid ){
		$res = "Unknown entity";
		switch($data["assocEntity"]){
			case "poa":
				break;
			case "area":
				$data["assocEntity"] = "area";
				$res = self::addForRepoArea($data, $userid);
				break;
			default:
				$data["assocEntity"] = "release";
				$res = self::addForRelease($data, $userid);
				break;
		}
		if( $res !== true ){
			if( $res !== false ){
				return $res;
			}
			return "Unknown entity";
		}
		
		try{
			$contact = new Repository_Model_MetaContact();
			$contact->assocId = $data["assocId"];
			$contact->assocEntity = $data["assocEntity"];
			$contact->externalId = $data["externalId"];
			$contact->contactTypeId = $data["contactTypeId"];
			$contact->firstname = $data["firstname"];
			$contact->lastname = $data["lastname"];
			$contact->email = $data["email"];
			$contact->save();
			$data = $contact;
		}catch(Exception $e){
			return $e->getMessage();
		}
		return true;
	}
	public static function update( $data, $userid ){
		$res = "Unknown entity";
		switch($data["assocEntity"]){
			case "poa":
				break;
			case "area":
				break;
			case "release":
			default:
				$data["assocEntity"] = "release";
				$res = self::updateForRelease($data, $userid);
				break;
		}
		if( $res !== true ){
			return "Unknown entity";
		}
	}
}
class RepositoryConfiguration{
	const STORAGE_KEY = "storage.root.path";
	const DATABANK_KEY = "databank.root.path";
	const SCRATCHSPACE_KEY = "scratch.root.path";
	private static $_config = array();
	
	public static function getValueByName($name){
		if( trim($name) == "" ){
			return false;
		}
		if( ! isset(self::$_config[$name]) ){
			//Get storage.root.path from config
			$rl = new Repository_Model_Config();
			$rl->filter->name->equals($name);
			if( count($rl->items) == 0 ){
				return false;
			}
			self::$_config[$name] = $rl->items[0]->value;
		}
		
		return self::$_config[$name];
	}
	public static function getStorageValue(){
		return RepositoryConfiguration::getValueByName(RepositoryConfiguration::STORAGE_KEY);
	}
	public static function getDatabankValue(){
		return RepositoryConfiguration::getValueByName(RepositoryConfiguration::DATABANK_KEY);
	}
	public static function getScratchSpaceValue(){
		return RepositoryConfiguration::getValueByName(RepositoryConfiguration::SCRATCHSPACE_KEY);
	}
}
class RepositoryPackage{
	private static function parseOutput($output){
		$info = array();
		
		$tmp = preg_split("/[Dd]escription[\s]*\:[\s]*\n/", $output);
		if( count($tmp) > 1 ){
			$info["description"] = $tmp[1];
		}
		$tmp = $tmp[0];
		$tmp = preg_split("/\n/",$tmp);
		for($i=0; $i<count($tmp); $i+=1){
			$tmp2 = $tmp[$i];
			$tmp2 = preg_split("/[\s]*:[\s]*/", $tmp2);
			if( count($tmp2) > 1 ){
				if( count($tmp2) == 3 ){
					$tmp2[1] = $tmp2[1] . ":" . $tmp2[2];
				}
				$tmp2[0] = str_replace("/\s*/", "", $tmp2[0]);
				if( trim($tmp2[0]) !== ""){
					$tmp2[0] = strtolower($tmp2[0]);
					$info[$tmp2[0]] = $tmp2[1];
				}
			}
		}
		return $info;
	}
	public static function getRpmInfo($filename){
		$info = array();
		$output = "";
		$status = "";
		
		$cmd = "rpm -qp --qf 'Name : %{NAME}\nVersion: %{VERSION}\nRelease: %{RELEASE}\nArchitecture: %{ARCH}\nGroup: %{GROUP}\nSize: %{SIZE}\nSignature: %{HEADERSIGNATURES}\nPackager: %{PACKAGER}\nUrl: %{URL}\nSummary: %{SUMMARY}\nVendor: %{VENDOR}\nBuilddate: %{BUILDTIME}\nBuildhost: %{BUILDHOST}\nSourcerpm: %{SOURCERPM}\nLicense: %{LICENSE}\nDepends: [%{REQUIRES}, ]\nDescription: \n%{DESCRIPTION}' " . escapeshellarg($filename);
		exec($cmd, $output, $status);
		$output = implode("\n", $output);
		$info = self::parseOutput($output);
		
		foreach($info as $k=>$v){
			switch($k){
				case "size":
					$info["installationsize"] = $info["size"];
					break;
				default: 
					$info[$k] = $v;
			}
		};
		
		$info["filename"] = basename($filename);
		$info["md5sum"] = md5_file($filename);
		$info["sha1sum"] = sha1_file($filename);
		$info["sha256sum"] = "";
		$info["type"] = "rpm";
		$info["size"] = RepositoryFS::getFileSize($filename);
		return $info;
	}
	public static function getDebInfo($filename){
		$info = array();
		$output = "";
		$status = "";
		$cmd ="dpkg -I " .escapeshellarg($filename) . " | sed -e 's/^\\ //' -e 's/\\ *[Dd]escription\\ *\\:/Description\\:\\n/' -e 's/^\\ *size\\ [0-9]\\{1,\\}.*$/\\n/' -e 's/^\\ *[0-9]\\{1,\\}.*$/\\n/' -e 's/^new\\ debian.*$/\\n/'";
		exec($cmd,$output, $status);
		$output = implode("\n", $output);
		
		$outinfo = self::parseOutput($output);
		
		foreach($outinfo as $k=>$v){
			switch($k){
				case "package":
					$info["name"] = $v;
					break;
				case "homepage":
					$info["url"] = $v;
					break;
				case "installed-size":
				case "installedsize":	
					$info["installationsize"] = intval($v) * 1024;
					break;
				case "section":
					$info["group"] = $v;
				default:
					$info[$k] = $v;
					break;
			}
		}
		if( !isset($info["release"]) ){
			$info["release"] = "";
		}
		$info["filename"] = basename($filename);
		$info["md5sum"] = md5_file($filename);
		$info["sha1sum"] = sha1_file($filename);
		$info["sha256sum"] ="";
		$info["type"] = "deb";
		$info["size"] = RepositoryFS::getFileSize($filename);
		
		return $info;
	}
	public static function getGzipInfo($filename){
		$info = array();
		$info["name"] = basename($filename);
		$info["filename"] = basename($filename);
		$info["md5sum"] = md5_file($filename);
		$info["sha1sum"] = sha1_file($filename);
		$info["sha256sum"] = "";
		$info["type"] = "gzip";
		
		$info["version"] = "";
		$info["release"] = "";
		$info["architecture"] = "";
		$info["description"] = "";
		$info["installationsize"] = "";
		$info["group"] = "";
		$info["depends"] = "";
		$info["license"] = "";
		$info["url"] = "";
		$info["size"] = RepositoryFS::getFileSize($filename);
		return $info;
	}
	public static function getInfo($filename, $format=null){
		$ftype = RepositoryFS::getFileType($filename);
		$res = null;
		switch($ftype){
			case "rpm":
				self::getRpmInfo($filename, $format);
				break;
			case "deb":
				self::getDebInfo($filename, $format);
				break;
			case "gzip":
				self::getGzipInfo($filename, $format);
				break;
			default:
				break;
		}
		return $res;
	}
}
class RepositoryFS{
	private static function getFileMimeType($path){
		return mime_content_type($path);
	}
	private static function makePath($path) {
        //Test if path exist
        if (is_dir($path) || file_exists($path)) return true;
        //Else, create it
		try{
			mkdir($path, 0777, true);
		}catch(Exception $e){
			error_log($e->getMessage());
			return false;
		}
		return true;
    }
	
	public static function getRootPath(){
		$res = realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR);
		return $res;
	}
	public static function getScratchSpaceForRelease($release, &$error, $create=false){
		//Get storage.root.path and databank.root.path from config table
		$storage = trim(RepositoryConfiguration::getStorageValue());
		if( $storage == "" ){
			$error = "Could not retrieve storage value";
			return false;
		}
		$scratch = trim(RepositoryConfiguration::getScratchSpaceValue());
		if( $scratch === "" ){
			$error = "Could not retrieve scratch space value";
			return false;
		}
		$storagepath = self::getRootPath() . DIRECTORY_SEPARATOR . $storage . DIRECTORY_SEPARATOR . $scratch . DIRECTORY_SEPARATOR . $release->getSoftwareId() . DIRECTORY_SEPARATOR  . $release->id . DIRECTORY_SEPARATOR;
		if( $create == true && !file_exists($storagepath) ){
			if( !self::makePath($storagepath) ){
				error_log("[RepositoryFS.getScratchSpaceForRelease]: Failed to create scratch folder: " . $storagepath );
				$error = "Could not create storage scratch space";
				return false;
			}
		}
		return $storagepath;
	}
	public static function getDatabankForPoa($poa, $swid, &$error, $create=false){
		//Get storage.root.path and databank.root.path from config table
		$storage = trim(RepositoryConfiguration::getStorageValue());
		if( $storage == "" ){
			$error = "Could not retrieve storage value";
			return false;
		}
		$databank = trim(RepositoryConfiguration::getDatabankValue());
		if( $databank === "" ){
			$error = "Could not retrieve databank value";
			return false;
		}
		
		$storagepath = self::getRootPath() . DIRECTORY_SEPARATOR .$storage . DIRECTORY_SEPARATOR . $databank . DIRECTORY_SEPARATOR . $swid . DIRECTORY_SEPARATOR . $poa->productReleaseid . DIRECTORY_SEPARATOR . $poa->id . DIRECTORY_SEPARATOR;
		if( $create == true && !file_exists($storagepath) ){
			if( !self::makePath($storagepath) ){
				error_log("[RepositoryFS.getDatabankForPoa]: Failed to create databank folder: " . $storagepath );
				$error = "Could not create storage databank";
				return false;
			}
		}
		return $storagepath;
	}
	public static function getDatabankForRelease($release, &$error, $create=false){
		//Get storage.root.path and databank.root.path from config table
		$storage = trim(RepositoryConfiguration::getStorageValue());
		if( $storage == "" ){
			$error = "Could not retrieve storage value";
			return false;
		}
		$databank = trim(RepositoryConfiguration::getDatabankValue());
		if( $databank === "" ){
			$error = "Could not retrieve databank value";
			return false;
		}
		
		$storagepath = self::getRootPath() . DIRECTORY_SEPARATOR .$storage . DIRECTORY_SEPARATOR . $databank . DIRECTORY_SEPARATOR . $release->getSoftwareId() . DIRECTORY_SEPARATOR . $release->id . DIRECTORY_SEPARATOR;
		
		if( $create == true && !file_exists($storagepath) ){
			if( !self::makePath($storagepath) ){
				error_log("[RepositoryFS.getDatabankForRelease]: Failed to create databank folder: " . $storagepath );
			}
		}
		return $storagepath;
	}
	public static function getStoragePath(&$error){
		$storage = trim(RepositoryConfiguration::getStorageValue());
		if( $storage == "" ){
			$error = "Could not retrieve storage value";
			return false;
		}
		return self::getRootPath() . DIRECTORY_SEPARATOR . $storage . DIRECTORY_SEPARATOR;
	}
	public static function getFileType($file){
		$ft = self::getFileMimeType($file);
		switch($ft){
			case "application/x-rpm":
				return "rpm";
			case "application/x-debian-package":
				return "deb";
			case "application/x-gzip":
			case "application/x-tar":
				return "gzip";
			default:
				return false;
		}
	}
	public static function getFileSize($filename){
		return @filesize($filename);
	}
	public static function isSupportedArtifactType($target, $ftype){
		$res = false;
		$os = $target->getOs();
		$artifacts = $os->artifactType;
		$artifacts = split(";", $artifacts);
		if( $ftype == "gzip"){
			if( in_array("tgz", $artifacts) || 
				in_array("tar.gz", $artifacts) ||
				in_array("tar", $artifacts) ||
				in_array("gz", $artifacts)
			){
				$res = true;
			}
		} else if( in_array($ftype, $artifacts) ) {
			$res = true;
		}
		
		return $res;
	}
	
	public static function canUploadFile($filename,$release,$target){
		//Check if release is not in production
		$state = $release->getCommRepoState()->name;
		if( strtolower($state) === "production" ){
			return "Current release is in production. No uploads are permitted";
		}
		
		//Check if file type can be managed
		$ftype = self::getFileType($filename);
		if( $ftype === false ){
			return "Unsupported file type";
		}
		
		//Check if file type is valid for the specified target
		if( self::isSupportedArtifactType($target, $ftype) === false ){
			return "File type " . $ftype . " is not supported for this release";
		}
		
		//Retrieve package information to check architecture
		$info = true; //in case of generic tar packages
		if( $ftype == "rpm" ){
			$info = RepositoryPackage::getRpmInfo($filename);
		} else if( $ftype == "deb" ){
			$info = RepositoryPackage::getDebInfo($filename);
		}
		if( $info === false || count($info) === 0 ){
			return "Cannot retrieve " . $ftype . " package information";
		}
		
		return true;
	}
	public static function fileExists($filename, $release, $target, $swid, $type){
		$targetid = ( ( is_numeric($target) )?$target:$target->id);
		$releaseid = ( ( is_numeric($release) )?$release:$release->id);
		$poa = Repository::getPoaByReleaseTarget($releaseid, $targetid);
		if( $poa === false ) return false;
		$pcks = new Repository_Model_MetaPoaReleasePackages();
		$pcks->filter->poaId->equals($poa->id);
		if( count($pcks->items) == 0 ){
			return false;
		}
		
		$pck = $pcks->items[0];
		if(strtolower(trim($pck->pkgFilename)) == strtolower(trim($filename))){
			return true;
		}
		
		return false;
	}
	public static function storeUploadedFile($filename, $release, $target, $userid = 0, &$warnings = null){
		$output = "";
		if( !file_exists($filename) ){
			return "Could not find uploaded package file";
		}
		
		//Check if release is not in production
		$state = $release->getCommRepoState()->name;
		if( strtolower($state) === "production" ){
			return "Current release is in production. No uploads are permitted";
		}
		
		//Check if file type can be managed
		$ftype = self::getFileType($filename);
		if( $ftype === false ){
			return "Unsupported file type";
		}
		
		//Check if file type is valid for the specified target
		if( self::isSupportedArtifactType($target, $ftype) === false ){
			return "File type " . $ftype . " is not supported for this release";
		}
		
		//Retrieve package information to check architecture
		$info = true; //in case of generic tar packages
		if( $ftype == "rpm" ){
			$info = RepositoryPackage::getRpmInfo($filename);
		} else if( $ftype == "deb" ){
			$info = RepositoryPackage::getDebInfo($filename);
		} else if( $ftype == "gzip"){
			$info = RepositoryPackage::getGzipInfo($filename);
		}
		
		if( $info === false || count($info) === 0 ){
			return "Cannot retrieve " . $ftype . " package information";
		}
		
		$fileexists = RepositoryFS::fileExists(basename($filename), $release, $target, $release->getSoftwareId(),$info["type"]);
		if( $fileexists == true ){
			return "File with the same name already uploaded for this release";
		}
		
		//Retrieve or create POA release
		$poa = Repository::createPoa($release, $target, $userid, $output);
		if( $poa === false){
			return $output;
		}
		$poaid = $poa->id;
		
		//retriev destination path
		$storagepath = RepositoryFS::getStoragePath($error);
		$storagepath = ($storagepath === false)?"":$storagepath;
		$poapath = RepositoryFS::getDatabankForPoa($poa, $release->getSoftwareId(), $output,true);
		if( $poapath === false ){
			$info["pkgfilepath"] = "";
		} else {
			$poarelpath = str_replace($storagepath,"", $poapath);
			$info["pkgfilepath"] = $poarelpath . $info["type"] . DIRECTORY_SEPARATOR;
			$poa->poaPath = $info["pkgfilepath"];
			$poa->save();
		}
		//Check if file already exists in POA release
		$exists = Repository::hasPOAPackage($poaid, $info);
		if( $exists === false ){		
			//Save POA Release package
			$pkg = Repository::createPoaPackage($poaid, $info, $userid,$output);
			//Calculate package version indexing upon package creation success
			if( $pkg !== false ){
				RepositoryBackend::calculatePackageVersionIndex($pkg);
			}
		} else {
			//Update POA release package
			$pkg = Repository::updatePoaPackage($exists, $info, $output);
		}
		if( $pkg === false){
			return $output;
		}
		
		//move POA Release package to databank
		RepositoryFS::makePath($poapath . $info["type"] . DIRECTORY_SEPARATOR);
		if( copy($filename, $storagepath . $info["pkgfilepath"] . $info["filename"]) ) {
			unlink($filename);
		}
		
		$archname = $target->getArch()->name;
		$archname = strtolower($archname);
		
		return $pkg->id;
	}
	
	public static function getPackageDatabankUrl($id, &$mime){
		$pckg = Repository::getPOAPackage($id);
		if( $pckg == null ){
			return false;
		}
		
		$poa = Repository::getPoaById($pckg->poaId);
		if( $poa == null ){
			return false;
		}
		
		$release = Repository::getReleaseById($poa->productReleaseId);
		if( $release == null ){
			return false;
		}
		$repoarea = Repository::getRepositoryAreaById($release->repoAreaId);
		if( $repoarea == null ){
			return false;
		}
		$url = self::getDatabankForPoa($poa, $repoarea->swId, $error,false);
		if( $url === false ){
			return false;
		}
		$url = $url . strtolower($pckg->pkgType) . DIRECTORY_SEPARATOR . $pckg->pkgFilename;
		switch(strtolower($pckg->pkgType)){
			case "deb":
				$mime = "application/x-deb";
				break;
			case "rpm":
				$mime = "application/x-rpm";
				break;
			default: 
				break;
		}
		return $url;
	}
}
class RepositoryBackend{
	public static function getBackendUrl(){
		$app = Zend_Registry::get("app");
		if( !$app ) return "";
		if( isset($app["commrepoBackendUrl"]) == false ) return "";
		
		return $app["commrepoBackendUrl"];
	}
	public static function checkSuccessfulResponse($response){
		$res = new SimpleXMLElement($response);
		$statuses = $res->xpath("//entry/status");
		
		foreach($statuses as $status){
			$st = strval($status);
			$st = strtolower($st);
			$st = trim($st);
			if( $st !== "success" ){
				return false;
			}
		}
		
		return true;
	}
	public static function checkValidPublishType($type, $action = "publish"){
		$res = false;
		$typename = trim($type);
		$typename = strtolower($typename);
		switch($typename){
			case "candidate":
				$res = $typename;
				break;
			case "production":
				$res = $typename;
				break;
			default:
				$res = false;
				break;
		}
		return $res;
	}
	public static function createErrorResponse($error, &$output, $params = array() ){
		$attrs = "";
		foreach($params as $k=>$v){
			$attrs .= ' ' . $k . '="' . $v . '"';
		}
		$output = "<response " . $attrs . "><entry><status>error</status><message>".$error."</message></entry></response>";
		return false;
	}
	public static function createSuccessResponse(&$output, $params = array()){
		$attrs = "";
		foreach($params as $k=>$v){
			$attrs .= ' ' . $k . '="' . $v . '"';
		}
		$output = "<response " . $attrs . "><entry><status>success</status><message></message></entry></response>";
		return true;
	}
	public static function checkValidPublishRelease($id,$tostate,$action="publish"){
		//Check if given id is of valid type
		if( is_numeric($id) === false || $id <= 0){
			return "Release id is not valid";
		}
		
		//Find given release
		$releases = new Repository_Model_MetaProductReleases();
		$releases->filter->id->equals($id);
		if( count($releases) === 0 ){
			return "Could not retrieve release.";
		}
		$release = $releases->items[0];
		
		//Check valid state for given release
		$state = $release->getCommRepoState();
		if( !$state ){
			return "Could not retrieve release state.";
		}
		$statename = strtolower( trim($state->name) );
		switch($statename){
			case "production":
				if( $action == "publish"){
					$statename = "Release is already in production.";
				}else{
					$statename = true;
				}
				break;
			case "candidate":
				$statename = true;
				break;
			case "unverified":
				if( $action == "publish"){
					$statename = true;
				} else {
					$statename = "Release is in " . $statename . " state.";
				}
				break;
			default:
				$statename = "Release is in " . $statename . " state.";
				break;
		}
		if($statename !== true){
			return $statename;
		}
		
		$releasenotes = true;
		$statename = strtolower( trim($state->name) );
		switch($statename){
			case "candidate":
			case "unverified":
				if($action == "publish" && trim($release->releaseNotes) == "" && strtolower( trim($tostate) ) == "production"){
					$releasenotes = "Release notes are mandatory.";
				}
				break;
			default:
				break;
		}
		if( $releasenotes !== true ){
			return $releasenotes;
		}
		//In case of publishing an update into production, the parent (base) must also be published into production
		if( strtolower( trim($tostate) ) == "production" ){
			$parent = $release->getParentRelease();
			if($parent && $parent->currentStateId != 2){
				return "Base / major release must be published into production before any other release in the same series can do the same.";
			}
		}
		$poas = $release->getPOAs();
		if( count($poas) === 0 ){
			return "Release must have at least one package to publish.";
		}
		
		foreach($poas as $poa){
			$packs = $poa->getPackages();
			if( count($packs) === 0 ){
				return "Release must have at least one package to publish.";
			}
		}
		
		return $release;
	}
	
	public static function buildRepositories($releaseid, &$output){
		$params = array("releaseid"=> $releaseid,"action"=>"buildrepositories");
		//Find given release
		$releases = new Repository_Model_MetaProductReleases();
		$releases->filter->id->equals($releaseid);
		if( count($releases) === 0 ){
			return self::createErrorResponse("Could not retrieve release.", $output);
		}
		$release = $releases->items[0];
		
		//Check valid state for given release
		$state = $release->getCommRepoState();
		if( !$state ){
			return self::createErrorResponse("Could not retrieve release state.", $output,$params);
		}
		
		$statename = strtolower(trim($state->name));
		switch($statename){
			case "production":
			case "candidate":
				break;
			default:
				return self::createErrorResponse("The release must be published as candidate or into production in order to build repositories", $output, $params);
				break;
		}
		
		$poas = $release->getPOAs();
		if( count($poas) === 0 ){
			return self::createErrorResponse("Release must have at least one package in order to build repositories.",$output,$params);
		}
		foreach($poas as $poa){
			$packs = $poa->getPackages();
			if( count($packs) === 0 ){
				return self::createErrorResponse("Release must have at least one package in order to build repositories.",$output,$params);
			}
		}
		
		//YUM build repositories
		$url = self::getBackendUrl() . "yum/action/create/release/" . $releaseid;
		$responsetext = web_get_contents($url);
		if( !$responsetext ){
			$release->currentStateId = $prevstateid;
			$release->save();
			return self::createErrorResponse("Could not connect to community repository backend.", $output, $params);
		}
		
		$success = self::checkSuccessfulResponse($responsetext);
		if( !$success ){
			$release->currentStateId = $prevstateid;
			$release->save();
			$output = $responsetext;
			return false;
		}
		
		//APT build repositories
		$url = self::getBackendUrl() . "apt/action/create/release/" . $releaseid;
		$responsetext = web_get_contents($url);
		if( !$responsetext ){
			$release->currentStateId = $prevstateid;
			$release->save();
			return self::createErrorResponse("Could not connect to community repository backend.", $output, $params);
		}
		
		$success = self::checkSuccessfulResponse($responsetext);
		if( !$success ){
			$release->currentStateId = $prevstateid;
			$release->save();
			$output = $responsetext;
			return  false;
		}
		
		$releases = new Repository_Model_MetaProductReleases();
		$releases->filter->id->equals($releaseid);
		$release = $releases->items[0];
		$release->timestampLastProductionBuild = date("Y-m-d H:i:s",time());
		$release->save();
		
		return self::createSuccessResponse($output,$params);
	}
	public static function buildRepofiles($releaseid, &$output){
		$params = array("releaseid"=> $releaseid,"action"=>"buildrepositories");
		//Find given release
		$releases = new Repository_Model_MetaProductReleases();
		$releases->filter->id->equals($releaseid);
		if( count($releases) === 0 ){
			return self::createErrorResponse("Could not retrieve release.", $output);
		}
		$release = $releases->items[0];
		
		//Check valid state for given release
		$state = $release->getCommRepoState();
		if( !$state ){
			return self::createErrorResponse("Could not retrieve release state.", $output,$params);
		}
		
		$statename = strtolower(trim($state->name));
		switch($statename){
			case "production":
			case "candidate":
				break;
			default:
				return self::createErrorResponse("The releaase must be published as candidate or into production in order to build repository files", $output, $params);
				break;
		}
		
		$poas = $release->getPOAs();
		if( count($poas) === 0 ){
			return self::createErrorResponse("Release must have at least one package in order to build repository files.",$output,$params);
		}
		foreach($poas as $poa){
			$packs = $poa->getPackages();
			if( count($packs) === 0 ){
				return self::createErrorResponse("Release must have at least one package in order to build repository files.",$output,$params);
			}
		}
		
		//YUM/APT build repofiles
		$url = self::getBackendUrl() . "repofiles/action/regenerate/" . $releaseid;
		$responsetext = web_get_contents($url);
		if( !$responsetext ){
			$release->currentStateId = $prevstateid;
			$release->save();
			return self::createErrorResponse("Could not connect to community repository backend.", $output, $params);
		}
		
		$success = self::checkSuccessfulResponse($responsetext);
		if( !$success ){
			$release->currentStateId = $prevstateid;
			$release->save();
			$output = $responsetext;
			return false;
		}
		
		$releases = new Repository_Model_MetaProductReleases();
		$releases->filter->id->equals($releaseid);
		$release = $releases->items[0];
		$release->timestampLastProductionBuild = date("Y-m-d H:i:s",time());
		$release->save();
		return self::createSuccessResponse($output, $params);
	}
	public static function publish($releaseid, $type, &$output){
		$params = array("releaseid"=>$releaseid, "action"=>"unpublish", "type"=>$type);
		$typename = self::checkValidPublishType($type);
		if( $typename === false ){
			return self::createErrorResponse("The provided type is invalid for publishing." , $output, $params);
		}
		
		$params["type"] = $typename;
		$release = self::checkValidPublishRelease($releaseid,$typename,"publish");
		if( is_string($release) ){
			return self::createErrorResponse($release, $output);
		}
		
		$url = self::getBackendUrl() . "release/action/publish/" . $releaseid . "/" . $typename;
		$responsetext = web_get_contents($url);
		if( !$responsetext ){
			return self::createErrorResponse("Could not connect to community repository backend.", $output, $params);
		}
		
		$success = self::checkSuccessfulResponse($responsetext);
		if( !$success ){
			$output = $responsetext;
			return false;
		}
		$prevstateid = $release->currentStateId;
		$stateid = $prevstateid;
		switch($typename){
			case "production":
				$stateid = 2;
				break;
			case "candidate":
				$stateid = 3;
				break;
		}
		$release->currentStateId = $stateid;
		$release->save();
		
		$success = self::buildRepositories($releaseid, $output);
		if( $success !== true){
			return false;
		}
		
		$success = self::buildRepofiles($releaseid, $output);
		if( $success !== true ){
			return false;
		}
		
		$releases = new Repository_Model_MetaProductReleases();
		$releases->filter->id->equals($releaseid);
		$release = $releases->items[0];
		$release->timestampLastProductionBuild = date("Y-m-d H:i:s",time());
		$release->save();
		if( $release->currentStateId == 2 ){
			$params["lastproductionbuild"] = $release->timestampLastProductionBuild;
		}
		RepositoryServices::AppDBSyncUpdateRelease($releaseid);
		return self::createSuccessResponse($output, $params);
	}
	public static function canUnpublishMajorRelease($releaseid,&$parentrelease){
		$rels = new Repository_Model_MetaProductReleases();
		$rels->filter->id->equals($releaseid);
		if( count($rels->items) === 0 ){
			return "Could not find product release";
		}
		
		$rel = $rels->items[0];
		$parent = $rel->getParentRelease();
		if( !$parent && $rel->currentStateId == 2){
			$rels = new Repository_Model_MetaProductReleases();
			$rels->filter->parent_id->equals($releaseid)->and($rels->filter->currentStateId->equals(2));
			if( count($rels->items) > 0){
				return "Cannot unpublish this release since there are already update releases in production associated with it";
			}
		}
		$parentrelease = $parent;
		return true;
	}
	public static function unpublish($releaseid,$type, &$output){
		$parentrelease = null;
		$params = array("releaseid"=>$releaseid, "action"=>"unpublish", "type"=>$type);
		$typename = self::checkValidPublishType($type, $action="unpublish");
		if( $typename === false ){
			return self::createErrorResponse("The provided type is invalid for un-publishing." , $output, $params);
		}
		$params["type"] = $typename;
		
		$canUnpublish = self::canUnpublishMajorRelease($releaseid,$parentrelease);
		if( $canUnpublish !== true ){
			return self::createErrorResponse($canUnpublish, $output , $params);
		} 
		
		$release = self::checkValidPublishRelease($releaseid, $typename,"unpublish");
		if( is_string($release) ){
			return self::createErrorResponse($release, $output, $params);
		}
		
		$url = self::getBackendUrl() . "release/action/unpublish/" . $releaseid . "/" . $typename;
		$responsetext = web_get_contents($url);
		if( !$responsetext ){
			return self::createErrorResponse("Could not connect to community repository backend.", $output , $params);
		}
		
		$success = self::checkSuccessfulResponse($responsetext);
		if( !$success ){
			$output = $responsetext;
			return false;
		}
		
		
		if( $parentrelease !== null && $release->currentStateId == 2){
			$success = self::buildRepositories($releaseid, $output);
			if( $success !== true){
				return false;
			}
		}
		
		$prevstateid = $release->currentStateId;
		$stateid = 1; //unverified
		$release->currentStateId = $stateid;
		$release->save();
		
		RepositoryServices::AppDBSyncUpdateRelease($releaseid);
		if( $parentrelease !== null && $prevstateid  == 2){
			$success = self::buildRepofiles($parentrelease->id, $output);
			if( $success !== true ){
				return false;
			}
		}

		return self::createSuccessResponse($output, $params);
	}
	
	public static function renameReleaseVersion($id,$to, &$output){
		$to = strtolower(trim($to));
		$params = array("releaseid"=>$id, "action"=>"rename", "to"=>$to);
		$rels = new Repository_Model_MetaProductReleases();
	
		$rels->filter->id->equals($id);
		if( count($rels->items) === false ){
			return self::createErrorResponse("Cannot retrieve release.", $output);
		}

		$rel = $rels->items[0];

		//Check if other releases with same name under the same series
		$rels = new Repository_Model_MetaProductReleases();
		$rels->filter->repoAreaId->equals($rel->repoAreaId)->and($rels->filter->displayVersion->equals(trim($to))->and($rels->filter->id->notequals($id)));
		if( count($rels->items) > 0){
			return self::createErrorResponse("There is another release under the current series with the same display version.",$output);
		}

		$errs = array();
		//Display version validation
		$len = strlen( trim($to) );
		if( preg_match('/^\./', $to) || preg_match('/\.$/', $to) ) {
			$errs[] = 'Value must not start or end with "&lt;b&gt;.&lt;/b&gt;" character.';
		}
		if( preg_match('/[\ \n\t]/', $to) ) {
			$errs[] = 'No white spaces allowed.';
		}
		if( $len < 3 || $len > 20 ){
			$errs[] = 'Value must be between 3 to 20 characters long.';
		}
		if( !preg_match('/[A-Za-z0-9]+/', $to) ) {
			$errs[] = 'Value must contain alphanumeric characters.';
		}
		if (!preg_match('/^[A-Za-z0-9\.\_\-]+$/',$to) ) {
			$errs[] = 'Value contains invalid characters. Only . _ - symbols are allowed.';
		}
		if( count($errs) > 0 ){
			return self::createErrorResponse($errs[0], $output);
		}
		
		$from = $rel->displayVersion;
		$params["from"] = $from;
		
		$url = self::getBackendUrl() . "release/action/rename/" . $id . "/displayversion/" . $from . "/" . $to;		
                $responsetext = web_get_contents($url);
		if( !$responsetext ){
			return self::createErrorResponse("Could not connect to community repository backend.", $output , $params);
		}
		$rel->displayVersion = $to;
		$rel->save();
		RepositoryServices::AppDBSyncUpdateRelease($rel->id);
		return self::createSuccessResponse($output,$params);
		
	}
	public static function renameSeriesName($id,$to, &$output){
		$to = strtolower(trim($to));
		$params = array("seriesid"=>$id, "action"=>"rename", "to"=>$to);
		$repos = new Repository_Model_MetaProductRepoAreas();
		$repos->filter->id->equals($id);
		if( count($repos->items) == 0 ){
			return self::createErrorResponse("Cannot retrieve series", $output);
		}
		$repo = $repos->items[0];

		$repos = new Repository_Model_MetaProductRepoAreas();
		$repos->filter->name->equals(trim($to));
		if( count($repos->items) > 0 ){
			return self::createErrorResponse("There are other series with the same name.",$output,$params);
		}

		$swid = $repo->swid;

		$errs = array();
		$len = strlen( trim($to) );
		if( preg_match('/^\./', $to) || preg_match('/\.$/', $to) ) {
			$errs[] = 'Value must not start or end with "&lt;b&gt;.&lt;/b&gt;" character.';
		}
		if( preg_match('/[\ \n\t]/', $to) ) {
			$errs[] = 'No white spaces allowed.';
		}
		if( $len < 2 || $len > 20 ){
			$errs[] = 'Value must be between 2 to 20 characters long.';
		}
		if( !preg_match('/[A-Za-z0-9]+/', $to) ) {
			$errs[] = 'Value must contain alphanumeric characters.';
		}
		if (!preg_match('/^[A-Za-z0-9\.\_\-]+$/',$to) ) {
			$errs[] = 'Value contains invalid characters. Only . _ - ( ) symbols are allowed.';
		}


		if( count($errs) > 0 ){
			return self::createErrorResponse($errs[0], $output);
		}
		
		$from = $repo->name;
		$params["from"] = $from;
		
		$url = self::getBackendUrl() . "release/action/rename/" . $id . "/seriesname/" . $from . "/" . $to;
		$responsetext = web_get_contents($url);
		if( !$responsetext ){
			return self::createErrorResponse("Could not connect to community repository backend.", $output , $params);
		}
		
		$repo->name = $to;
		$repo->save();
		$releases = $repo->getReleases();
		if( count($releases) > 0 ){
			foreach($releases as $rel){
				RepositoryServices::AppDBSyncUpdateRelease($rel->id);
			}
		}
		
		return self::createSuccessResponse($output,$params);
	}
	public static function removeRelease($id, $userid, &$output){
		$params = array("releaseid"=>$id, "action"=>"remove");
		$release = Repository::getReleaseById($id);
		if( $release === false ){
			return self::createErrorResponse("Cannot retrieve release", $output);
		}
		$repoareaid = $release->repoAreaId;
		//Check if release is in production 
		if( $release->currentStateId == 2){
			return self::createErrorResponse("Cannot remove release published into production", $output, $params);
		}
		//Check if it is a major release and has updates in the same series
		if( $release->parentId == 0){
			$children = $release->getChildren();
			if( $children !== null && count($children)>0){
				return self::createErrorResponse("Cannot remove base release in a series with update releases", $output, $params);
			}
		}
		$url = self::getBackendUrl() . "release/action/remove/" . $id . "/release";
		$responsetext = web_get_contents($url);
		if( !$responsetext ){
			return self::createErrorResponse("Could not connect to community repository backend.", $output , $params);
		}
		$delres = Repository::call_delete_release($id, $userid);
		if( $delres !== true ){
			return self::createErrorResponse($delres, $output , $params);
		}
		
		//Check if parent repository area has any releases left.
		//If not then remove repository area as well.
		$repoarea = Repository::getRepositoryAreaById($repoareaid);
		if( $repoarea ){
			$releases = $repoarea->getReleases();
			if(count($releases) == 0 ){
				$output2 = "";
				$delres = RepositoryBackend::removeSeries($repoareaid, $userid, $output2);
				if( $delres == false){
					return self::createErrorResponse("Could not remove empty series.", $output , $params);
				}
			}
		}
		$params["seriesid"] = $repoareaid;
		
		RepositoryServices::AppDBSyncDeleteRelease($id);
		return self::createSuccessResponse($output, $params);
	}
	
	public static function removeSeries($id, $userid, &$output){
		$params = array("seriesid"=>$id, "action"=>"remove");
		$series = Repository::getRepositoryAreaById($id);
		if( $series === false ){
			return self::createErrorResponse("Cannot retrieve series", $output);
		}
		
		$url = self::getBackendUrl() . "release/action/remove/" . $id . "/series";
		$responsetext = web_get_contents($url);
		if( !$responsetext ){
			return self::createErrorResponse("Could not connect to community repository backend.", $output , $params);
		}
		$rels = array();
		$repo = Repository::getRepositoryAreaById($id);
		if( $repo ){
			$releases = $repo->getReleases();
			if( count($releases) > 0 ){
				foreach($releases as $release){
					$rels[] = $release->id;
				}
			}
		}
		$delres = Repository::call_delete_repo_area($id, $userid);
		if( $delres !== true ){
			return self::createErrorResponse($delres, $output , $params);
		}
		for($i=0; $i<count($rels); $i+=1){
			RepositoryServices::AppDBSyncDeleteRelease($rels[$i]);
		}
		return self::createSuccessResponse($output, $params);
	}
	
	public static function calculatePackageVersionIndex($package){
		$pck = null;
		if( is_numeric($package) ){
			$pcks = new Repository_Model_MetaPoaReleasePackages();
			$pcks->filter->id->equals($package);
			if( count($pcks->items) == 0 ){
				return false;
			}
			$pck = $pcks->item[0];
		} else {
			$pck = $package;
		}
		
		$ptype = strtolower(trim($pck->pkgType));
		if( $ptype == "rpm" ||  $ptype == "deb") {
			$url = self::getBackendUrl() . "package/action/calcversionindex/" . $pck->id;
			$response = web_get_contents($url);
			if( !$response ){
				return false;
			}
		}
		return true;
	}
}

class RepositoryServices{
	
	private static function AppDBSyncPush($vars){
		$url = "https://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/apps/syncrelease";
		$postdata = "";
		foreach($vars as $k=>$v){
			$postdata .= $k . "=" . $v . "&";
		}
		$postdata = trim($postdata, "&");
		
		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_POST, 1);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $postdata);
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt( $ch, CURLOPT_HEADER, 0);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0);		
		$response = curl_exec( $ch );
		curl_close($ch);
		return $response;
	}
	private static function collectReleaseData($releaseid){
		$vars = array();
		$releases = new Repository_Model_MetaProductReleases();
		$releases->filter->id->equals($releaseid);
		if( count($releases->items)  == 0 ) {
			return "Could not retrieve release entry";
		}
		$release = $releases->items[0];
		
		$series = $release->getRepoArea();
		if( !$series ){
			return "Could not retrieve series entry for given release";
		}
		
		$vars["series"] = $series->name;
		$vars["swid"] = $series->swId;
		$vars["release"] = $release->displayVersion;
		$vars["state"] = $release->currentStateId;
		$vars["manager"] = $release->insertedBy;
		if( $release->timestampLastProductionBuild != "0000-00-00 00:00:00"){
			$vars["publishedon"] = $release->timestampLastProductionBuild;
		}
		if( $release->timestampInserted != "0000-00-00 00:00:00"){
			$vars["addedon"] = $release->timestampInserted;
		}
		if($release->timestampLastUpdated != "0000-00-00 00:00:00"){
			$vars["lastupdated"] = $release->timestampLastUpdated;
		}
		return $vars;
	}
	
	public static function AppDBSyncDeleteRelease($releaseid, $userid = ""){
		$vars = array();
		$vars["releaseid"] = $releaseid;
		$vars["action"] = "delete";
		$vars["manager"] = $userid;
		
		$result = RepositoryServices::AppDBSyncPush($vars);
		return ( ($result===true)?true:"Could not push data to AppDB service" );
	}
	public static function AppDBSyncUpdateRelease($releaseid, $userid = ""){
		$vars = array();
		$vars = RepositoryServices::collectReleaseData($releaseid);
		if(is_array($vars) == false ){
			return $vars;
		}
		$vars["releaseid"] = $releaseid;
		$vars["action"] = "update";
		$vars["manager"] = ( (is_numeric($userid))?$userid:$vars["manager"] );
		
		$result = RepositoryServices::AppDBSyncPush($vars);
		return ( ($result===true)?true:"Could not push data to AppDB service" );
	}
	
	public static function AppDBSyncInsertRelease($releaseid, $userid = ""){
		$vars = array();
		$vars = RepositoryServices::collectReleaseData($releaseid);
		if(is_array($vars) == false ){
			return $vars;
		}
		$vars["releaseid"] = $releaseid;
		$vars["action"] = "insert";
		$vars["manager"] = ( (is_numeric($userid))?$userid:$vars["manager"] );
		
		$result = RepositoryServices::AppDBSyncPush($vars);
		return ( ($result===true)?true:"Could not push data to AppDB service" );
	}
	
	public static function AppDBSyncInitData(){
		global $application;
		$db = $application->getBootstrap()->getResource('db');
		$db->setFetchMode(Zend_Db::FETCH_BOTH);
		$db->beginTransaction();
		
		try{
			$db->query("ALTER TABLE app_releases DISABLE TRIGGER rtr_app_releases_after;");
			$db->query("ALTER TABLE app_releases DISABLE TRIGGER rtr_app_releases_before;");
			$db->query("DELETE FROM app_releases;");
			
			$rels = new Repository_Model_MetaProductReleases();
			if( count($rels->items)>0 ){
				foreach($rels->items as $rel){
					$appreldata = self::collectReleaseData($rel->id);
					$appreldata["releaseid"] = $rel->id;
					$appreldata["action"] = "insert";
					CommunityRepository::syncSoftwareRelease($appreldata);
					
				}
			}
			$db->query("ALTER TABLE app_releases ENABLE TRIGGER rtr_app_releases_after;");
			$db->query("ALTER TABLE app_releases ENABLE TRIGGER rtr_app_releases_before;");
			$db->commit();
		}catch(Exception $e){
			$db->rollback();
			error_log("[APPDBSYNC_INIT]:" . $e->getMessage());
		}
	}
}
Repository::__initClass();
