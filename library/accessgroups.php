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

require_once('userrequests.php');
 
class AccessGroups{
	/**
	 * Helper function to retrieve a user's profile.
	 * 
	 * @param Default_Model_Researcher|integer $user Either the user's profile object or the user's profile id.
	 * @return Default_Model_Researcher|null
	 */
	private static function getUser($user){
		if( $user === null ) {
			return null;
		} else if( is_numeric($user) ){
			$userid = intval($user);
			$users = new Default_Model_Researchers();
			$users->filter->id->equals($userid);
			if( count($users->items) === 0 ){
				return null;
			}
			$user = $users->items[0];
		}
		return $user;
	}
	/**
	 * Retrieve user's access group list.
	 * 
	 * @param \Default_Model_Researcher|integer $user User id or instanceof Default_Model_Researcher object.
	 * @return \Default_Model_ActorGroupMember[]|false The access group array of the given user. Returns false on error.
	 */
	public static function getUserAccessGroups($user=null){
		$user = self::getUser($user);
		if( $user instanceof Default_Model_Researcher ){
			return $user->getActorGroups();
		}
		return false;
	}
	/**
	 * Checks if two access groups are equal.
	 * 
	 * @param \Default_Model_ActorGroupMember $source
	 * @param \Default_Model_ActorGroupMember $target
	 * @retrun boolean
	 */
	public static function equalAccessGroups($source=null,$target=null){
		if( is_null($source) || is_null($target) ){
			return false;
		}
		
		if( trim($source->group->id) !== trim($target->group->id) ){
			return false;
		}
		if( trim($source->payload) !== trim($target->payload) ){
			return false;
		}
		if( trim($source->actorguid) !== trim($target->actorguid) ){
			return false;
		}
		return true;
	}
	/**
	 * Check if user belongs to all given access groups.
	 * 
	 * @access public
	 * @param \Default_Model_Researcher $user User id or instanceof Default_Model_Researcher object.
	 * @param integer[] $accessgroups Array of access group ids.
	 * @return boolean True:if user belongs to all given access groups, False: if user belongs to some or none of the given access groups.
	 */
	public static function inAllAccessGroups( $user=null, $accessgroups = array() ){
		if( ( is_array($accessgroups) && count($accessgroups) === 0 ) || is_null($accessgroups) ){
			return false;
		}
		
		if( is_array($accessgroups) === false ){
			$accessgroups = array($accessgroups);
		}
		$userAccessGroups = self::getUserAccessGroups($user);
		if( count($userAccessGroups) === 0){
			return false;
		}
		$hasAll = true;
		foreach($accessgroups as $ac){
			$found = false;
			foreach($userAccessGroups as $uag){
				if( trim($uag->groupid) === trim($ac) ){
					$found = true;
					break;
				}
			}
			if( $found === false ){
				$hasAll = false;
			}
		}
		return $hasAll;
	}
	/**
	 * Check if user belongs at least in one of the given acccess groups.
	 * 
	 * @access public
	 * @param \Default_Model_Researcher|integer $user User id or instanceof Default_Model_Researcher object.
	 * @param \Default_Model_ActorGroupMember[]|integer[] $accessgroups Array of access group ids or instance of Default_Model_ActorGroupMembers.
	 * @return boolean True:if user belongs to all given access groups, False: if user belongs to some or none of the given access groups.
	 */
	public static function inSomeAccessGroups( $user=null, $accesstgroups=array() ){
		if( ( is_array($accessgroups) && count($accessgroups) === 0 ) || is_null($accessgroups) ){
			return false;
		}
		
		if( is_array($accessgroups) === false ){
			$accessgroups = array($accessgroups);
		}
		$userAccessGroups = self::getUserAccessGroups($user);
		if( count($userAccessGroups) === 0){
			return false;
		}
		$hasSome = false;
		foreach($userAccessGroups as $uag){
			if( in_array($uaq->group->id, $accessgroups) === true){
				$hasSome = true;
				break;
			}
		}
		return $hasSome;
	}
	/**
	 * Retrieves an actor group based on its guid.
	 * 
	 * @param string $guid
	 * @return Default_Model_ActorGroup|null
	 */
	public static function getGroupByGUID($guid){
		if( trim($guid) === "" ) {
			return null;
		}
		$groups = new Default_Model_ActorGroups();
		$groups->filter->guid->equals($guid);
		if( count($groups->items) > 0 ){
			return $groups->items[0];
		}
		return null;
	}
	public static function getGroupById($id){
		if( trim($id) === "" ) {
			return null;
		}
		$groups = new Default_Model_ActorGroups();
		$groups->filter->id->equals($id);
		if( count($groups->items) > 0 ){
			return $groups->items[0];
		}
		return null;
	}
	private static function cancelAccessGroupRequest($sourceUser, $targetUser, $id){
		error_log("[AccessGroups::cancelAccessGroupRequest]: Canceling user request with id: " . $id);
		
		$userrequests = new Default_Model_UserRequests();
		$userrequests->filter->id->equals($id);
		if( count($userrequests->items) === 0 ){
			return false;
		}
		$userrequest = $userrequests->items[0];
		
		//Cancel only if request is in the state "submitted". 
		//Any other case means that in the meantime another 
		//user has accepted or rejected it.
		if( $userrequest->stateid === 1 ){
			$userrequest->actorguid = $targetUser->guid;
			$userrequest->stateid = 4;
			$userrequest->save();
		}
		
		return true;
		
	}
	private static function rejectAccessGroupRequest($sourceUser, $targetUser, $id){
		error_log("[AccessGroups::rejectAccessGroupRequest]: Rejecting user request with id: " . $id);
		
		$userrequests = new Default_Model_UserRequests();
		$userrequests->filter->id->equals($id);
		if( count($userrequests->items) === 0 ){
			return false;
		}
		
		$userrequest = $userrequests->items[0];
		
		//Reject only if request is in the state "submitted". 
		//Any other case means that in the meantime another 
		//user has accepted,rejected or cancelled it.
		if( $userrequest->stateid === 1 ){
			$userrequest->actorguid = $sourceUser->guid;
			$userrequest->stateid = 3;
			$userrequest->save();
		}
		
		error_log("[AccessGroups::rejectAccessGroupRequest]: Sending reject email to user " . $targetUser->cname);
		$group = self::getGroupByGUID($userrequest->targetguid);
		UserRequests::sendEmailResponseAccessGroupsNotification($targetUser,$group, 3);
		return true;
	}
	private static function acceptAccessGroupRequest($sourceUser, $targetUser, $id){
		error_log("[AccessGroups::acceptAccessGroupRequest]: Accepting user request with id: " . $id);
		
		$userrequests = new Default_Model_UserRequests();
		$userrequests->filter->id->equals($id);
		if( count($userrequests->items) === 0 ){
			return false;
		}
		$userrequest = $userrequests->items[0];
		
		//Accept only if request is in the state "submitted". 
		//Any other case means that in the meantime another 
		//user has accepted,rejected or cancelled it.
		if( $userrequest->stateid === 1 ){
			$userrequest->actorguid = $sourceUser->guid;
			$userrequest->stateid = 2;
			$userrequest->save();
		}else{
			return true;
		}
		
		error_log("[AccessGroups::acceptAccessGroupRequest]: Sending accept email to user " . $targetUser->cname);
		$group = self::getGroupByGUID($userrequest->targetguid);
		UserRequests::sendEmailResponseAccessGroupsNotification($targetUser,$group, 2);
		return true;
	}
	/**
	 * Returns an array with the user request id and the access group id.
	 * 
	 * @param \Default_Model_Researcher|integer $user The user with access groups requests.
	 * @return array
	 */
	public static function getAccessGroupRequests($user,$groupid=null){
		$result = array();
		if( $user===null ) return $result;
		$user = self::getUser($user);
		$userrequests = new Default_Model_UserRequests();
		$f1 = new Default_Model_UserRequestsFilter();
		$f2 = new Default_Model_UserRequestsFilter();
		$f3 = new Default_Model_UserRequestsFilter();
		$f4 = new Default_Model_UserRequestsFilter();
		
		$f1->stateid->equals(1);//submitted
		$f2->userguid->equals($user->guid); //from given user
		$f3->typeid->equals(3);//access group request
		
		$userrequests->filter->chain($f1, "AND");
		$userrequests->filter->chain($f2, "AND");
		$userrequests->filter->chain($f3, "AND");
		
		if( $groupid !== null ){
			$group = self::getGroupById($groupid);
			if( $group == null ){
				return $result;
			}
			$f4->targetguid->equals($group->guid);
			$userrequests->filter->chain($f4, "AND");
		}
		
		if( count($userrequests->items) === 0 ){
			return $result;
		}
		
		foreach($userrequests->items as $userrequest){
			$group = self::getGroupByGUID($userrequest->targetguid);
			if( $group === null) {
				continue;
			}
			$result[] = array("requestid"=>$userrequest->id, "groupid" => $group->id);
		}
		return $result;
	}
	/**
	 * Check if $sourceUser can perform an access group action for $targetUser based on $accesspermissions
	 * 
	 * @param Default_Model_Researcher $sourceUser User profile object.
	 * @param Default_Model_Researcher $targetUser User profile object.
	 * @param text $action The action to check can take values of "canAdd","canRemove","canRequest","canCancel","canAcceptReject".
	 * @param integer $groupId The id of the action access group.
	 * @param {id, name, canAdd, canRemove, canRequest, canAcceptReject, hasRequest}[] $accesspermissions Array of $sourceUser's access groups permissions upon $targetUser.
	 * @return boolean True if $sourceUser can perform access group action upon $targetUser
	 */
	private static function canPerformAction($sourceUser, $targetUser, $action, $groupid, $accesspermissions){
		$group = null;
		//Find group permission entry from access permissions 
		foreach($accesspermissions as $ap){
			if( trim($ap["id"]) === trim($groupid) ){
				$group = $ap;
				break;
			}
		}
		
		//If group not found then no permissions
		if( $group === null ){
			return false;
		}
		$action = strtolower( trim($action) );
		switch($action){
			case "cancel":
				if( $sourceUser->id === $targetUser->id && $group["canRequest"] === true && is_numeric($group["hasRequest"]) === true) {
					return true;
				}
				return false;
			case "request":
				if( $sourceUser->id === $targetUser->id && $group["canRequest"] === true && $group["hasRequest"] === false) {
					return true;
				}
				return false;
			case "include":
				if( $group["canAdd"] === true && self::inAllAccessGroups($targetUser, array($group["id"])) === false  ){
					return true;
				}
				return false;
			case "exclude":
				if( $group["canRemove"] === true && self::inAllAccessGroups($targetUser, array($group["id"])) === true ) {
					return true;
				}
				return false;
			case "accept":
				if( $group["canAcceptReject"] === true && $group["hasRequest"] !== false ) {
					return true;
				}
				return false;
			case "reject":
				if( $group["canAcceptReject"] === true && $group["hasRequest"] !== false ) {
					return true;
				}
				return false;
			default:
				return false;
		}
	}
	/**
	 * 
	 * @param Default_Model_Researcher|integer $sourceUser The user to check the edit access group permissions of another user.
	 * @param Default_Model_Researcher|integer $targetUser The user whose access groups will be edited.
	 * @param {requestid,groupid}[] $userquests An array with submitted $targetUser access group requests.
	 * @return {id, name, canAdd, canRemove, canRequest, canAcceptReject, hasRequest}[] An array of the $sourceUser permissions to edit the access groups of $targetUser.
	 */
	public static function getAccessGroupsPermissions($sourceUser, $targetUser, $userrequests=array()){
		/* Return array with the groups the target is able to have. Filled with the permissions of the source or target for these groups.
		 * id: access group id
		 * name: access group name
		 * canAdd: source can add target to this access group
		 * canRemove: source can remove target from this access group
		 * canAcceptReject: accept or reject targets requests from $sourceUser to be included in this group
		 * canRequest: target can request to be included in this access group
		 * hasRequest: target user's access group request id if any.the user access group request id if any made by the target user. By default false. 
		 */
		$result = array();
		if( $sourceUser === null || $targetUser === null ){ return array(); }
		
		$sourceUser = self::getUser($sourceUser);
		$targetUser = self::getUser($targetUser);
		$sameuser = false;
		if( $sourceUser->id === $targetUser->id){
			$sameuser = true;
		}
		
		if( self::inAllAccessGroups($sourceUser,array("-1")) === true ) {
			//Administator can do anything except requesting for access group(no reason to do so)
			//$sourceIsAdmin = self::inAllAccessGroups($sourceUser,array("-1"));
			$result = array(
				array("id"=>"-1", "name"=>"AppDB Administrator", "canAdd"=>true, "canRemove"=>(($sameuser)?true:false),"canRequest"=>false, "canAcceptReject"=>true, "hasRequest"=>false),
				array("id"=>"-2", "name"=>"Managers", "canAdd"=>true, "canRemove"=>true,"canRequest"=>false, "canAcceptReject"=>true, "hasRequest"=>false),
				array("id"=>"-3", "name"=>"National Representatives", "canAdd"=>true, "canRemove"=>true, "canRequest"=>false, "canAcceptReject"=>true, "hasRequest"=>false),
			);
			if( Supports::datasets() ) {
				$result[] = array("id"=>"-19", "name"=>"Dataset Managers", "canAdd"=>true, "canRemove"=>true, "canRequest"=>false, "canAcceptReject"=>true, "hasRequest"=>false);
			}
		}else if( self::inAllAccessGroups($sourceUser,array("-2")) === true ){
			//Manager can do anything except removing an other manager
			$result = array(
				array("id"=>"-2", "name"=>"Managers", "canAdd"=>true, "canRemove"=>(($sameuser)?true:false),"canRequest"=>false, "canAcceptReject"=>true, "hasRequest"=>false),
				array("id"=>"-3", "name"=>"National Representatives", "canAdd"=>true, "canRemove"=>true, "canRequest"=>false, "canAcceptReject"=>true, "hasRequest"=>false),
			);
			if( Supports::datasets() ) {
				//Managers can edit datasets by default
				$result[] = array("id"=>"-19", "name"=>"Dataset Managers", "canAdd"=>true, "canRemove"=>true, "canRequest"=>false, "canAcceptReject"=>true, "hasRequest"=>false);
			}
		}else if (self::inAllAccessGroups ($sourceUser, array ("-3")) === true ) {
			//NILS can add other NILS, remove their self or request to become managers
			$sourceCountry = $sourceUser->countryid;
			$targetCountry = $targetUser->countryid;
			$samecountry = ( (trim($targetCountry) === trim($sourceCountry))?true:false );
			$result = array(
				array("id"=>"-2", "name"=>"Managers", "canAdd"=>false, "canRemove"=>false, "canRequest"=>(($sameuser)?true:false), "canAcceptReject"=>false, "hasRequest"=>false),
				array("id"=>"-3", "name"=>"National Representatives", "canAdd"=>(($samecountry)?true:false), "canRemove"=>(($sameuser)?true:false), "canRequest"=>false, "canAcceptReject"=>(($samecountry)?true:false), "hasRequest"=>false),
			);
			
			if( Supports::datasets() ) {
				//A NIL can request to be added to group of dataset managers
				$result[] = array("id"=>"-19", "name"=>"Dataset Managers", "canAdd"=>false, "canRemove"=>false, "canRequest"=>(($sameuser)?true:false), "canAcceptReject"=>false, "hasRequest"=>false);
			}
		}else{
			//All other users can only request to become managers or NILs
			$result = array(
				array("id"=>"-2", "name"=>"Managers", "canAdd"=>false, "canRemove"=>false, "canRequest"=>(($sameuser)?true:false), "canAcceptReject"=>false, "hasRequest"=>false),
				array("id"=>"-3", "name"=>"National Representatives", "canAdd"=>false, "canRemove"=>false, "canRequest"=>(($sameuser)?true:false), "canAcceptReject"=>false, "hasRequest"=>false),
			);
			if( Supports::datasets() ) {
				//Anyone can request to be added to group of dataset managers
				$result[] = array("id"=>"-19", "name"=>"Dataset Managers", "canAdd"=>false, "canRemove"=>false, "canRequest"=>(($sameuser)?true:false), "canAcceptReject"=>false, "hasRequest"=>false);
			}
		}
		
		//Check if user requests are given
		if( is_null($userrequests) || count($userrequests) === 0 ){
			$userrequests = self::getAccessGroupRequests($targetUser);
		}
		
		//Fill results with user access groups requests
		foreach($userrequests as $ur){
			for($i=0; $i<count($result);  $i+=1){
				if( $result[$i]["id"] === trim($ur["groupid"]) ){
					$result[$i]["hasRequest"] = $ur["requestid"];
					break;
				}
			}
		}
		
		return $result;
	}
	/**
	 * Handles access group actions based on $action.
	 * 
	 * @param Default_Model_Researcher|integer $sourceUser User profile object or id.
	 * @param Default_Model_Researcher|integer $targetUser User profile object or id.
	 * @param text $action The handled action which can take values of "include","exclude","request","cancel","accept" or "reject".
	 * @param integer[] $groupIds The ids of the access groups.
	 * @param {id, name, canAdd, canRemove, canRequest, canAcceptReject, hasRequest}[] $accesspermissions Optional array of $sourceUser's access groups permissions.
	 * @return boolean|string True on success, text message on error, False on unknown error.
	 */
	public static function handleUserGroupAction($sourceUser, $targetUser, $action, $groupIds = array(), $accesspermissions = null){
		if( is_array($groupIds) === false ) {
			if( is_numeric($groupIds) === true ) {
				$groupIds = array($groupIds);
			} else {
				return true;
			}
		}
		if( count($groupIds) === 0 || trim($action) === "" ) { return true; }
		
		$action = strtolower( trim($action) );
		$sourceUser = self::getUser($sourceUser);
		$targetUser = self::getUser($targetUser);
		
		if( $sourceUser === null || $targetUser === null ){ return false; }
		
		if( $accesspermissions === null ) {
			$accesspermissions = self::getAccessGroupsPermissions($sourceUser, $targetUser);
		}
		
		switch( $action ) {
			case "include": //include to groups of ids
				return self::includeUserInGroups($sourceUser, $targetUser, $groupIds, $accesspermissions);
			case "exclude": //exclude from groups of ids
				return self::excludeUserInGroups($sourceUser, $targetUser, $groupIds, $accesspermissions);
			case "request": //make request to be included in group ids (same user only)
				return self::requestForGroups($sourceUser, $targetUser, $groupIds, $accesspermissions);
			case "cancel": //cancel user's request to be included in group ids (same user only)
				return self::cancelRequestForGroups($sourceUser, $targetUser, $groupIds, $accesspermissions);
			case "accept": //accept a user's request to be included in group ids
				return self::acceptRequestForGroups($sourceUser, $targetUser, $groupIds, $accesspermissions);
			case "reject": //reject a user's request to be included in group ids 
				return self::rejectRequestForGroups($sourceUser, $targetUser, $groupIds, $accesspermissions);
			default:
				return false;
		}
	}
	/**
	 * Include $targetUser in access groups given by $groupids by the $sourceUser.
	 * 
	 * @param Default_Model_Researcher|integer $sourceUser User profile object or id.
	 * @param Default_Model_Researcher|integer $targetUser User profile object or id.
	 * @param integer[] $groupIds The ids of the access groups.
	 * @param {id, name, canAdd, canRemove, canRequest, canAcceptReject, hasRequest}[] $accesspermissions Optional array of $sourceUser's access groups permissions.
	 * @return boolean|string True on success, text message on error, False on unknown error.
	 */
	private static function includeUserInGroups($sourceUser, $targetUser, $groupIds, $accesspermissions) {
		$res = array();
		foreach($groupIds as $gid){
			$g = array($gid=>self::canPerformAction($targetUser, $targetUser, "include", $gid, $accesspermissions));
			$res[] = $g;
			if( $g[$gid] !== true ){
				continue;
			}
			//Check if user is already in access group, then ignore
			if( self::inAllAccessGroups($targetUser, array($gid)) === true ){
				continue;
			}
			$actormember = new Default_Model_ActorGroupMember();
			$actormember->groupID = $gid;
			$actormember->actorGUID = $targetUser->guid;
			if( trim($gid) === "-3"){
				$actormember->payload = $targetUser->countryID;
			}
			$actormember->save();
			
			//if targetuser has a pending reqeust to join current group, then accept the request
			foreach($accesspermissions as $ap){
				if( trim($ap["id"]) === trim($gid) && is_numeric($ap["hasRequest"]) && intval($ap["hasRequest"]) > 0 ) {
					self::acceptAccessGroupRequest($sourceUser, $targetUser, intval($ap["hasRequest"]));
				}
			}
		}
		return $res;
	}
	/**
	 * Exclude $targetUser from access groups given by $groupids by the $sourceUser.
	 * 
	 * @param Default_Model_Researcher|integer $sourceUser User profile object or id.
	 * @param Default_Model_Researcher|integer $targetUser User profile object or id.
	 * @param integer[] $groupIds The ids of the access groups.
	 * @param {id, name, canAdd, canRemove, canRequest, canAcceptReject, hasRequest}[] $accesspermissions Optional array of $sourceUser's access groups permissions.
	 * @return boolean|string True on success, text message on error, False on unknown error
	 */
	private static function excludeUserInGroups($sourceUser, $targetUser, $groupIds, $accesspermissions) {
		$res = array();
		foreach($groupIds as $gid){
			$g = array($gid=>self::canPerformAction($targetUser, $targetUser, "exclude", $gid, $accesspermissions));
			$res[] = $g;
			if( $g[$gid] !== true ) {
				continue;
			}
			
			$actormembers = new Default_Model_ActorGroupMembers();
			$f1 = new Default_Model_ActorGroupMembersFilter();
			$f2 = new Default_Model_ActorGroupMembersFilter();
			$f3 = new Default_Model_ActorGroupMembersFilter();
			$f1->groupid->equals($gid);
			$f2->actorid->equals($targetUser->guid);
			$actormembers->filter->chain($f1, "AND");
			$actormembers->filter->chain($f2, "AND");
			if( trim($gid) === "-3" ){
				$f3->payload->equals(trim($targetUser->countryID));
				$actormembers->filter->chain($f3, "AND");
			}
			if( count($actormembers->items) > 0 ){
				$am = $actormembers->items[0];
				$actormembers->remove($am);
			}
		}
		return $res;
	}
	/**
	 * Requests of $targetUser to be included in the access groups given by $groupids. ($sourceUser must be $targetUser).
	 * 
	 * @param Default_Model_Researcher|integer $sourceUser User profile object or id.
	 * @param Default_Model_Researcher|integer $targetUser User profile object or id.
	 * @param integer[] $groupIds The ids of the access groups.
	 * @param {id, name, canAdd, canRemove, canRequest, canAcceptReject, hasRequest}[] $accesspermissions Optional array of $sourceUser's access groups permissions.
	 * @return boolean|string True on success, text message on error, False on unknown error.
	 */
	private static function requestForGroups($sourceUser, $targetUser, $groupids, $accesspermissions) {
		if( $sourceUser->id !== $targetUser->id){
			return "Cannot make a user request on behalf of another user";		
		}
		if( is_array($groupids) === false ){
			if( is_numeric($groupids) === false ){
				return false;
			}else{
				$groupids = array($groupids);
			}	
		}
		$res = array();
		foreach($groupids as $gid){
			$g = array($gid=>self::canPerformAction($targetUser, $targetUser, "request", $gid, $accesspermissions));
			$res[] = $g;
			if( $g[$gid] !== true ){
				continue;
			}
			
			//if request exists for this group then return true.
			$ur = self::getAccessGroupRequests($targetUser, $gid);
			if( count($ur) > 0){
				return true;
			}
			
			//If group id does not exist ignore
			$group = self::getGroupById($gid);
			if( $group === null ){
				continue;
			}
			$userrequest = new Default_Model_UserRequest();
			$userrequest->typeid = 3;
			$userrequest->userguid = $targetUser->guid;
			$userrequest->targetguid = $group->guid;
			$userrequest->stateid = 1;
			
			$userrequest->save();
			
			//Dispatch mail to user and managers, appdb administrators and associated NILs
			UserRequests::sendEmailAccessGroupRequestNotifications($targetUser, $group);
		}
		return true;
	}
	/**
	 * Cancel requests of $targetUser to be included in the access groups given by $groupids. ($sourceUser must be $targetUser)
	 * 
	 * @param Default_Model_Researcher|integer $sourceUser User profile object or id.
	 * @param Default_Model_Researcher|integer $targetUser User profile object or id.
	 * @param integer[] $groupIds The ids of the access groups.
	 * @param {id, name, canAdd, canRemove, canRequest, canAcceptReject, hasRequest}[] $accesspermissions Optional array of $sourceUser's access groups permissions.
	 * @return boolean|string True on success, text message on error, False on unknown error.
	 */
	private static function cancelRequestForGroups($sourceUser, $targetUser, $groupids, $accesspermissions) {
		if( $sourceUser->id !== $targetUser->id){
			return "Cannot cancel a user request on behalf of another user";
		}
		$res = array();
		foreach($groupids as $gid){
			$g = array($gid=>self::canPerformAction($targetUser, $targetUser, "cancel", $gid, $accesspermissions));
			$res[] = $g;
			if( $g[$gid] !== true ){
				continue;
			}
			
			//if requests do not exist for this group then ignore.
			$ur = self::getAccessGroupRequests($targetUser, $gid);
			if( count($ur) === 0){
				continue;
			}
			
			//Get access group object
			$group = self::getGroupById($gid);
			if( $group === null ){
				continue;
			}
			
			//cancel each request for this group
			foreach($ur as $req){
				self::cancelAccessGroupRequest($sourceUser, $targetUser, $req["requestid"]);
			}
		}
		return true;	
	}
	/**
	 * Accept requests of $targetUser to be included in the access groups given by $groupids.
	 * 
	 * @param Default_Model_Researcher|integer $sourceUser User profile object or id.
	 * @param Default_Model_Researcher|integer $targetUser User profile object or id.
	 * @param integer[] $groupIds The ids of the access groups.
	 * @param {id, name, canAdd, canRemove, canRequest, canAcceptReject, hasRequest}[] $accesspermissions Optional array of $sourceUser's access groups permissions.
	 * @return boolean|string True on success, text message on error, False on unknown error.
	 */
	private static function acceptRequestForGroups($sourceUser, $targetUser, $groupids, $accesspermissions) {
		if( is_array($groupids) === false ){
			if( is_numeric($groupids) === false ){
				return "no access groups given";
			}else{
				$groupids = array($groupids);
			}
		}
		$res = array();
		foreach($groupids as $gid){
			$g = array($gid=>self::canPerformAction($targetUser, $targetUser, "accept", $gid, $accesspermissions));
			$res[] = $g;
			if( $g[$gid] !== true ){
				continue;
			}
			
			//if requests do not exist for this group then ignore.
			$ur = self::getAccessGroupRequests($targetUser, $gid);
			if( count($ur) === 0){
				continue;
			}
			
			//Get access group object
			$group = self::getGroupById($gid);
			if( $group === null ){
				continue;
			}
			//Include user in group(implicitly accepted by the function includeUserInGroups)
			self::includeUserInGroups($sourceUser, $targetUser, array($gid), $accesspermissions);
		}
		return true;
	}
	/**
	 * Reject requests of $targetUser to be included in the access groups given by $groupids.
	 * 
	 * @param Default_Model_Researcher|integer $sourceUser User profile object or id.
	 * @param Default_Model_Researcher|integer $targetUser User profile object or id.
	 * @param integer[] $groupIds The ids of the access groups.
	 * @param {id, name, canAdd, canRemove, canRequest, canAcceptReject, hasRequest}[] $accesspermissions Optional array of $sourceUser's access groups permissions.
	 * @return boolean|string True on success, text message on error, False on unknown error.
	 */
	private static function rejectRequestForGroups($sourceUser, $targetUser, $groupids, $accesspermissions) {
		if( is_array($groupids) === false ){
			if( is_numeric($groupids) === false ){
				return "no access groups given";
			}else{
				$groupids = array($groupids);
			}
		}
		$res = array();
		foreach($groupids as $gid){
			$g = array($gid=>self::canPerformAction($targetUser, $targetUser, "reject", $gid, $accesspermissions));
			$res[] = $g;
			if( $g[$gid] !== true ){
				continue;
			}
			
			//if requests do not exist for this group then ignore.
			$ur = self::getAccessGroupRequests($targetUser, $gid);
			if( count($ur) === 0){
				continue;
			}
			
			//Get access group object
			$group = self::getGroupById($gid);
			if( $group === null ){
				continue;
			}
			
			//reject each request for this group
			foreach($ur as $req){
				self::rejectAccessGroupRequest($sourceUser, $targetUser, $req["requestid"]);
			}
		}
		return true;
	}
}
?>
