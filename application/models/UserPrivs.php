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
class Default_Model_UserPrivs
{
	protected $_actor;
	protected $_items;
	protected $_session;
	protected $_privStamp;
		
	public function __construct($actor) {
		$this->_actor = $actor;
		$this->_session = new Zend_Session_Namespace('default');
		if ($this->_session->userid == $actor->id) {
			$this->_items = $this->_session->privs;
		}
		$this->_privStamp = $this->_session->privStamp;
		// TODO: remove line below which effectively disables cache, and add permissions refresh link in GUI where appropriate
		$this->refresh();
	}

	public function refresh() {
		global $application;
		$db = $application->getBootstrap()->getResource('db');
		$db->setFetchMode(Zend_Db::FETCH_NUM);
		$perms = $db->query("SELECT actionid, object FROM permissions WHERE actor = '".$this->_actor->guid . "'")->fetchAll();
		$a = array();
		foreach($perms as $perm) {
			$b = array("actionID" => $perm[0], "object" => $perm[1]);
			$a[] = $b;
		}
		$this->_items = $a;
		$this->_privStamp = time();
		if ($this->_session->userid == $this->_actor->id) {
			$this->_session->privs = $this->_items;
			$this->_session->privStamp = $this->_privStamp;
		}
	}

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid Permission property: '$name'");
		}
		$this->$method($value);
	}

	public function __get($name)
	{
		$method = 'get' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid Permission property: '$name'");
		}
		return $this->$method();
	}

	public function getItems() {
		if ($this->_items === null) $this->refresh();
		return $this->_items;
	}

	// this function should return whatever the app_actions SQL function returns
	public static function appActions() {
		$actions = array();
//		for($i=4; $i<=17; $i++) $actions[] = $i;
//		$actions[] = 20;
//		$actions[] = 23;
//		$actions[] = 24;
//		$actions[] = 26;
//		$actions[] = 30;
//		$actions[] = 31;
		db()->setFetchMode(Zend_Db::FETCH_NUM);
		$res = db()->query("SELECT UNNEST(app_actions())")->fetchAll();
		foreach($res as $r) {
			$actions[] = $r[0];
		}
		return $actions;
	}

	public function queryPriv($actionID, $target) {
		if ( ($this->_actor === null) || ($this->_actor->id === null) ) return false;

		// admin access
		if ( ($this->_actor !== null) && userIsAdminOrManager($this->_actor->id) ) return true;

		// refresh privs if stale for more than 600s (10m)
		if ( ($this->_items === null) || ($this->_privStamp === null) || ((time() - $this->_privStamp)>600) ) $this->refresh();
		if (is_object($target)) $target = $target->guid;
		if ( ($target === null) && ( ($actionID === null) || in_array($actionID, Default_Model_UserPrivs::appActions()) ) ) return true; //GUID is NULL, meaning this is a new entry: ALLOW
		$actions = array();
		if ($actionID === null) {
			// any app-related permission
			for($i=4; $i<=17; $i++) $actions[] = $i;
			$actions[] = 20;
			$actions[] = 23;
			$actions[] = 24;
			$actions[] = 26;
		} else {
			$actions[] = $actionID;
		}
		for($i=0; $i<count($this->_items); $i++) {
			$item = $this->_items[$i];
			if ( ( in_array($item['actionID'], $actions) ) && ( (!isset($item['object'])) || ($item['object'] === $target) || (isnull($item['object']) ) ) ) {
				return true;
			}
		}
		return false;
	}

	protected function grantRevoke($action, $actor, $target, $user, $mode = "grant") {
		if ( ($this->_actor === null) || ($this->_actor->id === null) ) return false;
		if ( $this->_session->userid === null ) return false;
		if (is_null($target)) {
			return false;
		} elseif (is_object($target)) {
			$t = $target->guid;
		} else {
			$t = $target;
		}
		db()->setFetchMode(Zend_Db::FETCH_NUM);
		$res = db()->query("SELECT " . $mode . "_privilege($action, '" . $this->_actor->guid . "', '$t', " . $this->_session->userid. ")")->fetchAll();
		if (count($res) > 0) {
			$res = $res[0];
			return $res[0];
		} else {
			return false;
		}
	}

	public function grantAccess($action, $target) {
		return $this->grantRevoke($action, $this->_actor, $target, $this->_session->userid, "grant");
	}

	public function revokeAccess($action, $target) {
		return $this->grantRevoke($action, $this->_actor, $target, $this->_session->userid, "revoke");
	}

	public function hasAccess($target) {
		return $this->queryPriv(null, $target);
	}


	public function canGrantRevokePrivilege($target)
	{
		return $this->queryPriv(1, $target);
	}

	public function canGrantPrivilege($target)
	{
		return $this->canGrantRevokePrivilege($target);
	}
	
	public function canRevokePrivilege($target)
	{
		return $this->canGrantRevokePrivilege($target);
	}
	
	public function canInsertApplication()
	{
		return $this->queryPriv(3, null);
	}
	
	public function canDeleteApplication($target)
	{
		return $this->queryPriv(4, $target);
	}
	
	public function canModifyApplicationName($target)
	{
		return $this->queryPriv(5, $target);
	}
	
	public function canModifyApplicationDescription($target)
	{
		return $this->queryPriv(6, $target);
	}
	
	public function canModifyApplicationAbstract($target)
	{
		return $this->queryPriv(7, $target);
	}
	
	public function canModifyApplicationLogo($target)
	{
		return $this->queryPriv(8, $target);
	}
	
	public function canModifyApplicationStatus($target)
	{
		return $this->queryPriv(9, $target);
	}
	
	public function canModifyApplicationDiscipline($target)
	{
		return $this->queryPriv(10, $target);
	}
	
	public function canModifyApplicationSubdiscipline($target)
	{
		return $this->queryPriv(11, $target);
	}
	
	public function canModifyApplicationCountry($target)
	{
		return $this->queryPriv(12, $target);
	}
	
	public function canModifyApplicationVO($target)
	{
		return $this->queryPriv(13, $target);
	}
	
	public function canModifyApplicationURLs($target)
	{
		return $this->queryPriv(14, $target);
	}
	
	public function canModifyApplicationDocuments($target)
	{
		return $this->queryPriv(15, $target);
	}
	
	public function canModifyApplicationMiddleware($target)
	{
		return $this->queryPriv(20, $target);
	}

	public function canAssociatePersonToApplication($target)
	{
		return $this->queryPriv(16, $target);
	}
	
	public function canDisassociatePersonFromApplication($target)
	{
		return $this->queryPriv(17, $target);
	}
	
	public function canModifyPersonPositionType($target)
	{
		return $this->queryPriv(18, $target);
	}

	public function canMarkRespected()
	{
		return $this->queryPriv(19, null);
	}

	public function canEditPersonProfile($target)
	{
		return $this->queryPriv(21, $target);
	}

	public function canBulkReadSensitiveData()
	{
		return $this->queryPriv(22, null);
	}

	public function canGrantOwnership($target)
	{
		return $this->queryPriv(23, $target);
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
//		return $this->queryPriv(24, $target);
	}

	public function canManageUserRequest($target)
	{
		return $this->queryPriv(25, $target);
	}

	public function canModifyApplicationCategory($target)
	{
		return $this->queryPriv(26, $target);
	}

	public function canSync($target)
	{
		return $this->queryPriv(27, $target);
	}

	public function canUseDisseminationTool()
	{
		return $this->queryPriv(28, null);
	}

	public function canEditFAQs()
	{
		error_log("canEditFAQs() is obsolete; please use canManageWiki()");
		return $this->canManageWiki();
	}

	public function canManageWiki()
	{
		return $this->queryPriv(29, null);
	}

	public function canAdminWiki()
	{
		return $this->queryPriv(35, null);
	}

	public function canModifyApplicationLanguage($target) {
		return $this->queryPriv(31, $target);
	}

	public function canManageReleases($target) {
		return $this->queryPriv(30, $target);
	}
	public function canManageVAs($target) {
		return $this->queryPriv(32, $target);
	}
	public function canModifyApplicationLicenses($target){
		return $this->queryPriv(33, $target);
		//return true;
	}

	public function canAccessVAPrivateData($target){
		return $this->queryPriv(34, $target);
	}

	public function canViewVOWideImageList($target){
		//return $this->queryPriv(36, $target);
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
		//return $this->queryPriv(37, $target);
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
	
	public function canEditRelatedProjects($target){
		return $this->queryPriv(40, $target);
	}
	
	public function canEditRelatedOrganizations($target){
		return $this->queryPriv(41, $target);
	}
	public function canEditRelatedSoftware($target){
		return $this->queryPriv(42, $target);
	}
	public function canEditRelatedVappliances($target){
		return $this->queryPriv(43, $target);
	}
	public function canManageContextScripts($target){
		return $this->queryPriv(44, $target);
	}
	public function canManageDatasets($target = null){
		return $this->queryPriv(45, $target);
	}
}
