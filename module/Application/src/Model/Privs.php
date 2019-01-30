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
?>
<?php
namespace Application\Model;

class Privs
{
	protected $_db;
	protected $_actor;
	
	public function __construct($actor)
	{
		global $application;
		$this->_db = $application->getBootstrap()->getResource('db');
		$this->_actor = $actor;
	}

	private function queryPriv($actionID, $actor, $target)
	{
//		return true;
		if ( $actionID === null ) {
			$action = " AND (actionID > 3)";
        } elseif ( $action === -1 ) {
            // any app-related permissions
			$action = " AND (actionid = ANY(app_actions()))";
		} else $action = " AND actionid = $actionID";		
		if ( $target !== null ) {
			if ( $target->guid === null ) return true;
			$res = $this->_db->query("SELECT EXISTS (SELECT * FROM permissions WHERE actor = '".$actor->guid . "'" .$action." AND (object = '".$target->guid."' OR object IS NULL)) AS result;")->fetchAll();
			$row = $res[0];
			if ( $row['result'] == "1" ) {
				return true;
			} else {
				return false;
			}
		} else {
			$res = $this->_db->query("SELECT EXISTS (SELECT * FROM permissions WHERE actor = '".$actor->guid. "'" .$action." AND object IS NULL) AS result;")->fetchAll();
			$row = $res[0];
			if ( $row['result'] == "1" ) {
				return true;
			} else {
				return false;
			}
		}
	}

	public function canModifyApp($target) {
		return $this->queryPriv(-1, $this->_actor, $target);
	}

	public function hasAccess($target) {
		return $this->queryPriv(null, $this->_actor, $target);
	}

	public function canGrantPrivilege($target)
	{
		return $this->queryPriv(1, $this->_actor, $target);
	}
	
	public function canRevokePrivilege($target)
	{
		return $this->queryPriv(2, $this->_actor, $target);
	}
	
	public function canInsertApplication()
	{
		return $this->queryPriv(3, $this->_actor, null);
	}
	
	public function canDeleteApplication($target)
	{
		return $this->queryPriv(4, $this->_actor, $target);
	}
	
	public function canModifyApplicationName($target)
	{
		return $this->queryPriv(5, $this->_actor, $target);
	}
	
	public function canModifyApplicationDescription($target)
	{
		return $this->queryPriv(6, $this->_actor, $target);
	}
	
	public function canModifyApplicationAbstract($target)
	{
		return $this->queryPriv(7, $this->_actor, $target);
	}
	
	public function canModifyApplicationLogo($target)
	{
		return $this->queryPriv(8, $this->_actor, $target);
	}
	
	public function canModifyApplicationStatus($target)
	{
		return $this->queryPriv(9, $this->_actor, $target);
	}
	
	public function canModifyApplicationCategory($target)
	{
		return $this->queryPriv(26, $this->_actor, $target);
	}
	
	public function canModifyApplicationDiscipline($target)
	{
		return $this->queryPriv(10, $this->_actor, $target);
	}
	
	public function canModifyApplicationSubdiscipline($target)
	{
		return $this->queryPriv(11, $this->_actor, $target);
	}
	
	public function canModifyApplicationCountry($target)
	{
		return $this->queryPriv(12, $this->_actor, $target);
	}
	
	public function canModifyApplicationVO($target)
	{
		return $this->queryPriv(13, $this->_actor, $target);
	}
	
	public function canModifyApplicationURLs($target)
	{
		return $this->queryPriv(14, $this->_actor, $target);
	}
	
	public function canModifyApplicationDocuments($target)
	{
		return $this->queryPriv(15, $this->_actor, $target);
	}
	
	public function canModifyApplicationMiddleware($target)
	{
		return $this->queryPriv(20, $this->_actor, $target);
	}

	public function canAssociatePersonToApplication($target)
	{
		return $this->queryPriv(16, $this->_actor, $target);
	}
	
	public function canDisassociatePersonFromApplication($target)
	{
		return $this->queryPriv(17, $this->_actor, $target);
	}
	
	public function canModifyPersonPositionType($target)
	{
		return $this->queryPriv(18, $this->_actor, $target);
	}

	public function canMarkRespected()
	{
		return $this->queryPriv(19, $this->_actor, null);
	}

	public function canEditPersonProfile($target)
	{
		return $this->queryPriv(21, $this->_actor, $target);
	}

	public function canBulkReadSensitiveData()
	{
		return $this->queryPriv(22, $this->_actor,null);
	}

	public function canGrantOwnership($target)
	{
		return $this->queryPriv(23, $this->_actor,$target);
	}

	public function canModifyApplicationTags($target)
	{
		if (is_null($target)) {
			return false;
		} elseif (is_object($target)) {
			$target = $target->guid;
		} 

		if (is_null($this->_actor) || (is_null($this->_actor->id))) return false;

		db()->setFetchMode(Zend_Db::FETCH_NUM);
		$res = db()->query("
SELECT 
	CASE tagpolicy
		WHEN 0 THEN
			(SELECT (owner = (SELECT id FROM researchers WHERE guid = '" . $this->_actor->guid ."')) OR (addedby = (SELECT id FROM researchers WHERE guid = '" . $this->_actor->guid . "')))
		WHEN 1 THEN
			(SELECT EXISTS (SELECT * FROM researchers_apps WHERE researcherid = (SELECT id FROM researchers WHERE guid = '" . $target . "')))
		WHEN 2 THEN
			TRUE
	END
FROM applications WHERE guid = '" . $target ."';
"
		)->fetchAll();
		if (count($res) > 0) {
			$res = $res[0];
			return $res[0];
		} else {
			return false;
		}
//		return $this->queryPriv(24, $this->_actor,$target);
	}

	public function canManageUserRequest($target)
	{
		return $this->queryPriv(25, $this->_actor,$target);
	}

	public function canSync($target)
	{
		return $this->queryPriv(27, $this->_actor,$target);
	}

	public function canUseDisseminationTool()
	{
		return $this->queryPriv(28, $this->_actor, null);
	}

	public function canEditFAQs()
	{
		error_log("canEditFAQs() is obsolete; please use canManageWiki()");
		return $this->canManageWiki();
	}

	public function canManageWiki()
	{
		return $this->queryPriv(29, $this->_actor, null);
	}

	public function canAdminWiki()
	{
		return $this->queryPriv(35, $this->_actor, null);
	}

	public function canManageReleases($target)
	{
		return $this->queryPriv(30, $this->_actor, $target);
	}

	public function canManageVAs($target)
	{
		return $this->queryPriv(32, $this->_actor, $target);
	}

	public function canModifyApplicationLanguage($target) {
		return $this->queryPriv(31, $this->_actor, $target);
	}
	public function canModifyApplicationLicenses($target){
		//TODO: Add permission
		return $this->queryPriv(33, $this->_actor, $target);
		return true;
	}

	public function canAccessVAPrivateData($target){
		return $this->queryPriv(34, $this->__actor, $target);
	}

	public function canViewVOWideImageList($target){
		//return $this->queryPriv(36, $this->_actor, $target);
		if ( ($this->_actor === null) || ($this->_actor->id === null) ) return false;
		// admin access
		if ( ($this->_actor !== null) && userIsAdminOrManager($this->_actor->id) ) return true;
		$db->setFetchMode(Zend_Db::FETCH_NUM);
		$res = db()->query("query_vowide_img_list_view_perm(?, ?)", array($this->_actor->id, $target))->fetchAll();
		if (count($res) == 0) {
			return false;
		} else {
			$res = $res[0];
			return filter_var($res[0], FILTER_VALIDATE_BOOLEAN);
		}
	}

	public function canManageVOWideImageList($target){
		//return $this->queryPriv(37, $this->_actor, $target);
		if ( ($this->_actor === null) || ($this->_actor->id === null) ) return false;
		// admin access
		if ( ($this->_actor !== null) && userIsAdminOrManager($this->_actor->id) ) return true;
		$db->setFetchMode(Zend_Db::FETCH_NUM);
		$res = db()->query("query_vowide_img_list_manage_perm(?, ?)", array($this->_actor->id, $target))->fetchAll();
		if (count($res) == 0) {
			return false;
		} else {
			$res = $res[0];
			return filter_var($res[0], FILTER_VALIDATE_BOOLEAN);
		}
	}

	public function canEditProject($target){
		return $this->queryPriv(38, $this->_actor, $target);
	}
	
	public function canEditOrganization($target){
		return $this->queryPriv(39, $this->_actor, $target);
	}
	
	public function canEditRelatedProjects($target){
		return $this->queryPriv(40, $this->_actor, $target);
	}
	
	public function canEditRelatedOrganizations($target){
		return $this->queryPriv(41, $this->_actor, $target);
	}
	public function canEditRelatedSoftware($target){
		return $this->queryPriv(42, $this->_actor, $target);
	}
	public function canEditRelatedVappliances($target){
		return $this->queryPriv(43, $this->_actor, $target);
	}
	public function canManageContextScripts($target){
		return $this->queryPriv(44, $this->_actor, $target);
	}
	public function canManageDatasets($target){
		return $this->queryPriv(45, $this->_actor, $target);
	}
	
	public function applications()
	{
		$rs = $this->_db->query("SELECT id FROM applications WHERE guid IN (SELECT object FROM permissions WHERE actor = '".$this->_actor->guid."');")->fetchAll();
		$ids = array();
		foreach ($rs as $row) {
			$ids[] = $row['id'];
		}
		if ( count($ids)>0 ) {
			$apps = new Applications();
			$apps->filter->orderBy('name');
			$apps->filter->id->in($ids);
			return $apps->refresh();
		} else return null;
	}
}
