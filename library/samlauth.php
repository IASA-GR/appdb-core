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
 
require_once('accountconnect.php');

class SamlAuth{
	const LIB_AUTOLOAD = '/var/simplesamlphp.sp/lib/_autoload.php';
	
	//Check SimpleSAML if user is authenticated by another service
	public static function isAuthenticated(){
		require_once(SamlAuth::LIB_AUTOLOAD);
		$source=null;

		$config = SimpleSAML_Configuration::getInstance();
		$t = new SimpleSAML_XHTML_Template($config, 'core:authsource_list.tpl.php');
		$t->data['sources'] = SimpleSAML_Auth_Source::getSourcesMatch('-sp');

		foreach ($t->data['sources'] as &$_source) {
			$as = new SimpleSAML_Auth_Simple($_source);
			if($as->isAuthenticated()){
				$source=$as;
				break;
			}
		}
		if( $source === null ){
			return false;
		}
		return $source;
	}
	
	//Create a Researcher model for new user with session data retrieved by SAML Auth
	public static function initNewUserProfile($session){
		$newuser = new Default_Model_Researcher();
		$newuser->id = -1;
		$newuser->firstname = $session->userFirstName;
		$newuser->lastname = $session->userLastName;
		$newuser->positionTypeID = $session->userRole;
		$newuser->countryID = $session->userCountryID;
		if( trim($session->userPrimaryEmail) !== "" ){
			$contact = new Default_Model_Contact();
			$contact->contacttypeid = 7;
			$contact->data = $session->userPrimaryEmail;
			$session->userContacts = array($contact);
		}
		return $newuser;
	}
	
	//Retrieve profiles that the new account might correspond to.
	public static function getConnectableProfileIds($session){
		$profiles = array();
		$email = $session->userPrimaryEmail;
		if( trim($email) !== ""){
			//Search by email for NOT-deleted users 
			$contacts = new Default_Model_Contacts();
			$contacts->filter->data->ilike($email);
			if( count($contacts->items) > 0 ){
				$contact = $contacts->items[0];
				$user = $contact->getResearcher();
				//Check if profile is NOT deleted
				if( !$user->deleted  ){ 
					array_push($profiles, $user->id);
				}
			}
		}
		//Search by first and last name for NOT-deleted
		$users = new Default_Model_Researchers();
		$f1 = new Default_Model_ResearchersFilter();
		$f2 = new Default_Model_ResearchersFilter();
		$f1->firstname->ilike($session->userFirstName);
		$f2->lastname->ilike($session->userLastName);
		$users->filter->chain($f1, "AND");
		$users->filter->chain($f2, "AND");
		if( count($users->items) > 0 ){
			foreach($users->items as $user){
				if( !$user->deleted ){
					array_push($profiles, $user->id);
				}
			}
		}
		//Search by last and first name
		$users = new Default_Model_Researchers();
		$f1 = new Default_Model_ResearchersFilter();
		$f2 = new Default_Model_ResearchersFilter();
		$f1->firstname->ilike($session->userLastName);
		$f2->lastname->ilike($session->userFirstName);
		$users->filter->chain($f1, "AND");
		$users->filter->chain($f2, "AND");
		if( count($users->items) > 0 ){
			foreach($users->items as $user){
				if( !$user->deleted ){
					array_push($profiles, $user->id);
				}
			}
		}
		
		return $profiles;
	}
	
	//Get user account entry for given values
	public static function getUserAccount($uid, $accounttype){
		$useraccounts = new Default_Model_UserAccounts();
		$f1 = new Default_Model_UserAccountsFilter();
		$f2 = new Default_Model_UserAccountsFilter();
		$f1->accountid->_escape_seq = "";
		$f1->accountid->equals($uid);
		$f2->account_type->equals($accounttype);
		$useraccounts->filter->chain($f1, "AND");
		$useraccounts->filter->chain($f2, "AND");
		if( count( $useraccounts->items ) > 0 ){
			return $useraccounts->items[0];
		}
		return null;
	}
	
	//Get researcher entry for the given user account entry
	public static function getUserByAccount($useraccount){
		if( is_null($useraccount) ) return null;
		$ppl = new Default_Model_Researchers();
		$ppl->viewModerated = true;
		$ppl->filter->id->equals($useraccount->researcherid);
		if( count($ppl->items) > 0 ){
			$researcher = $ppl->items[0];
			return $researcher;
		}
		return null;
	}
	
	//Retrieves user profile based on user account id and type
	public static function getUserByAccountValues($uid, $accounttype){
		$useraccount = self::getUserAccount($uid, $accounttype);
		if( $useraccount !== null ){
			$researcher = self::getUserByAccount($useraccount);
			return $researcher;
		}
		return null;
	}
	
	//Retrieves user accounts based on researcher id
	public static function getUserAccountsByUser($userid, $asArray = false){
		if( $userid == null || is_numeric($userid) === false || intval($userid) <=0 ){
			return array();
		}
		$result = array();
		
		$useraccounts = new Default_Model_UserAccounts();
		$useraccounts->filter->researcherid->equals($userid);
		if( count($useraccounts->items) > 0 ){
			if( $asArray === true ){ //check if requested as associative array (for client consuption)
				for($i = 0; $i<count($useraccounts->items); $i+=1){
					$ua = $useraccounts->items[$i];
					array_push( $result, array(
						"id" => trim($ua->id),
						"uid" => trim($ua->accountid),
						"source" => trim($ua->accounttypeid),
						"name" => trim($ua->accountname),
						"state" => trim($ua->getState()->name),
						"idptrace" => implode("\n", $ua->getIDPTrace())
					));
				}
			}else{
				$result = $useraccounts->items;
			}
		}
		
		return $result;
	}
	
	//Get user credentials for this session
	public static function getUserCredentials($userid){
		$creds = new Default_Model_UserCredentials();
		$f1 = new Default_Model_UserCredentialsFilter();
		$f2 = new Default_Model_UserCredentialsFilter();
		$f3 = new Default_Model_UserCredentialsFilter();
		$f1->researcherid->equals($userid);
		$f2->sessionid->equals(session_id());
		$f3->token->equals($_COOKIE["SimpleSAMLAuthToken"]);
		$creds->filter->chain($f1, "AND");
		$creds->filter->chain($f2, "AND");
		$creds->filter->chain($f3, "AND");
		if( count($creds->items) > 0 ){
			return $creds->items[0];  
		}
		return null;
	}
	
	//Called from SamlAuth::logout and removes user credentials from database
	public static function clearUserCredentails($session){
		if( is_null($session) || isset($session->userid) === false || is_numeric($session->userid)===false || intval($session->userid) <=0 ) return;
		if( isset($_COOKIE["SimpleSAMLAuthToken"]) === false ) return;
		$cred = self::getUserCredentials($session->userid);
		if( $cred === null ) return;
		$creds = new Default_Model_UserCredentials();
		$creds->filter->id->equals($cred->id);
		if( count($creds->items) > 0 ){
			$creds->remove($cred);
		}
	}
	//Saves and returns the new user session credentials
	//Setups session accordingly
	public static function setupSamlUserCredentials($user, $session = null){
		$userid = $user->id;
		//Remove existing user credentials
		$oldcred = self::getUserCredentials($userid);
		if( $oldcred !== null ) {
			$creds = new Default_Model_UserCredentials();
			if( count($creds->items) > 0 ){
				$creds->remove($oldcred);
			}
			//$oldcred->remove();
		}
		
		//Create new user credentials
		$cred = new Default_Model_UserCredential();
		$cred->researcherid = $userid;
		$cred->sessionid = session_id();
		$cred->token = $_COOKIE["SimpleSAMLAuthToken"];
		$cred->save();
		
		//Save to session
		if( $session !== null ){
			$session->authCredSessionId = $cred->sessionid;
			$session->authCredSamlAuthToken = $cred->token;
			$session->authCredAddedOn = $cred->addedon;
			$session->authCredId = $cred->id;
		}
		
		return $cred;
	}
	
	//Prefills session data from the SAML response, to be used for the creation of a new profile.
	public static function setupSamlNewUserSession($session, $accounttype){
		$attrs = $session->samlattrs;
		//initialize session data
		$session->authCredSessionId = session_id();
		$session->authCredSamlAuthToken = $_COOKIE["SimpleSAMLAuthToken"];
		$session->authCredId = null;
		$session->userid = -1;
		$session->isNewUser = true;
		$session->username = $attrs["idp:uid"][0];
		$session->usercname = "";
		$session->userFirstName = ( ( isset($attrs["idp:givenName"]) === true && count($attrs["idp:givenName"]) > 0 )?$attrs["idp:givenName"][0]:"" );
		$session->userLastName = ( ( isset($attrs["idp:sn"]) === true && count($attrs["idp:givenName"]) > 0 )?$attrs["idp:sn"][0]:"" );
		$session->userFullName = $session->userFirstName . " " . $session->userLastName;
		$session->fullName = $session->userFullName;
		$session->userRole = 4;
		$session->userCountryID = 0;
		$session->userCountryName = "";
		$session->userPrimaryEmail = ( ( isset($attrs["idp:mail"]) === true && count($attrs["idp:mail"]) > 0 )?$attrs["idp:mail"][0]:"" );
		if( isset($session->accountStatus) === false ){
			$session->accountStatus = "new";
		}
		//Check invalid emails from social media user accounts
		if( trim($session->userPrimaryEmail) === "" ||  strtolower(trim($session->userPrimaryEmail)) === strtolower(trim($session->username . "@" . $accounttype . ".com")) ){
			unset($session->userPrimaryEmail);
		}
		
		//Check if user has pending connection
		AccountConnect::isPending($session);
		//collect session data for new user based on saml source
		switch($accounttype){
			case "x509":
				break;
			case "egi-sso-ldap":
				break;
			case "facebook":
				break;
			case "linkedin":
				break;
			case "twitter":
				break;
			case "google":
				break;
			default:
				break;
		}
	}
	
	//Clears any transaction variables before authedication setup
	//called from SamlAuth::setupSamlSession
	public static function clearSession($session){
		unset($session->isNewUser);
		unset($session->accountStatus);
		unset($session->accountPendingId);
		unset($session->accountPendingProfileId);
		unset($session->accountPendingProfileName);
		unset($session->userDeleted);
		unset($session->userDeletedById);
		unset($session->userDeletedByName);
		unset($session->userDeletedByCName);
		unset($session->userDeletedOn);
		unset($session->currentAccountSource);
		unset($session->currentAccountId);
		unset($session->currentAccountUid);
		unset($session->currentAccountName);
		unset($session->currentUserAccounts);
	}
	
	//Fill session data based on the user profile.
	public static function setupSamlSession($session, $account, $user ){
		self::clearSession($session);
		
		$session->userid = $user->id;
		$session->userguid = $user->guid;
		$session->username = $user->cname;
		$session->usercname = $user->cname;
		$session->userfullName = $user->name;
		$session->userFirstName = $user->firstname;
		$session->userLastName = $user->lastname;
		$session->fullName = $user->name;
		$session->userRole = $user->positionTypeID;
		$session->userCountryID = $user->countryID;
		$session->userCountryName = $user->country->name;
		$session->userDeleted = $user->deleted;
		
		//Setup session variables in case of deleted profile
		if( $session->userDeleted == true ){
			$session->userDeletedById = $user->delInfo->deleter->id;
			$session->userDeletedByName = $user->delInfo->deleter->name;
			$session->userDeletedByCName = $user->delInfo->deleter->cname;
			$session->userDeletedOn = $user->delInfo->deletedOn;
		}else{
			//load current account in session
			$session->currentUserAccount = array(
				"id" => $account->id,
				"source" => $account->accounttypeid,
				"uid" => $account->accountid,
				"name" => $account->accountname,
				"idptrace" => implode("\n", $account->getIDPTrace())
			);
			//load available user accounts
			$session->currentUserAccounts = self::getUserAccountsByUser($user->id, true);
		}
		//collect session data for new user based on saml source
		$accounttype = strtolower( trim($account->accounttypeid) );
		switch($accounttype){
			case "x509":
				break;
			case "egi-sso-ldap":
				break;
			case "facebook":
				break;
			case "linkedin":
				break;
			case "twitter":
				break;
			case "google":
				break;
			default:
				break;
		}
	}
	
	//Autoconnects an egisso account to an existing x509 account if exists.
	public static function connectEgiToX509($session){
		//check if there exists an user_accounts.accountid = idp:userCertificateSubject and user_account.account_type = "x509"
		//If true add x509 to the user_accounts with the same researcher id and return the profile
		//If false return null
		$attrs = $session->samlattrs;
		$source = strtolower(trim($session->samlauthsource));
		$uid = ( (isset($attrs["idp:uid"])== true && count($attrs["idp:uid"]) > 0 )?$attrs["idp:uid"][0]:"");
		$ucert = ( (isset($attrs["idp:userCertificateSubject"])== true && count($attrs["idp:userCertificateSubject"]) > 0 )?$attrs["idp:userCertificateSubject"][0]:"");
		
		//Check if current source is indeed egi-sso-ldap-sp
		if( $source !== "egi-sso-ldap-sp") return null;
		
		//Check if there is a profile with an userCertificateSubject x509 account
		$researcher = self::getUserByAccountValues($ucert, "x509");
		if( $researcher === null ) return null;
		
		//Save this egi account to the found researcher profile
		$uaccount = new Default_Model_UserAccount();
		$uaccount->researcherid = $researcher->id;
		$uaccount->accountid = $uid;
		$uaccount->accounttypeid = "egi-sso-ldap";
		$uaccount->save();
		
		return $researcher;
	}
	
	//Autoconnects a x509 account to an existing egi sso account if exists.
	public static function connectX509ToEgi($session){
		//check if there exists an user_accounts.accountid = idp:egiuid and user_account.account_type = "egi-sso-ldap"
		//If true add x509 to the user_accounts with the same researcher id and return the profile
		//If false return null
		$attrs = $session->samlattrs;
		$source = strtolower(trim($session->samlauthsource));
		$uid = ( (isset($attrs["idp:uid"])== true && count($attrs["idp:uid"]) > 0 )?$attrs["idp:uid"][0]:"");
		$egiuid = ( (isset($attrs["idp:egiuid"])== true && count($attrs["idp:egiuid"]) > 0 )?$attrs["idp:egiuid"][0]:"");
		
		//Check if current source is indeed x509-sp
		if( $source !== "x509-sp") return null;
		
		//Check if there is a profile with an egiuid egi sso account type
		$researcher = self::getUserByAccountValues($egiuid, "egi-sso-ldap");
		
		//Failed to retrieve a profile for relative to specific account
		if( $researcher === null ) return null;
		
		//tSave this egi account to the found researchr profile
		$uaccount = new Default_Model_UserAccount();
		$uaccount->researcherid = $researcher->id;
		$uaccount->accountid = $uid;
		$uaccount->accounttypeid = "x509";
		$uaccount->save();

		return $researcher;
	}
	
	//Collect and store implicit user accounts from current one.
	//E.g. if a user signed in with an egi sso account there might be a usercertificate subject. 
	//In this case store it as a x509 user account in the current profile.
	public static function harvestSamlData($session, $user){
		$attrs = $session->samlattrs;
		$egiuid = ( (isset($attrs["idp:egiuid"])== true && count($attrs["idp:egiuid"]) > 0 )?$attrs["idp:egiuid"][0]:"");
		$ucert = ( (isset($attrs["idp:userCertificateSubject"])== true && count($attrs["idp:userCertificateSubject"]) > 0 )?$attrs["idp:userCertificateSubject"][0]:"");
		//collect egi sso ldap user account (possibly from x509 user account)
		if( trim($egiuid) !== "" ){
			$uacs = new Default_Model_UserAccounts();
			$f1 = new Default_Model_UserAccountsFilter();
			$f2 = new Default_Model_UserAccountsFilter();
			$f3 = new Default_Model_UserAccountsFilter();
			$f1->researcherid->equals($user->id);
			$f2->account_type->equals("egi-sso-ldap");
			$f3->accountid->_escape_seq = "";
			$f3->accountid->equals($egiuid);
			$uacs->filter->chain($f1, "AND");
			$uacs->filter->chain($f2, "AND");
			$uacs->filter->chain($f3, "AND");
			if( count($uacs->items) === 0 ){
				$uacc = new Default_Model_UserAccount();
				$uacc->researcherid = $user->id;
				$uacc->accountid = $egiuid;
				$uacc->accounttypeid = "egi-sso-ldap";
				$uacc->save();
			}
		}
		
		//collect x509 user account (possibly from egi sso user account)
		if( trim($ucert) !== "" ){
			$uacs = new Default_Model_UserAccounts();
			$f1 = new Default_Model_UserAccountsFilter();
			$f2 = new Default_Model_UserAccountsFilter();
			$f3 = new Default_Model_UserAccountsFilter();
			$f1->researcherid->equals($user->id);
			$f2->account_type->equals("x509");
			$f3->accountid->_escape_seq = "";
			$f3->accountid->equals($ucert);
			$uacs->filter->chain($f1, "AND");
			$uacs->filter->chain($f2, "AND");
			$uacs->filter->chain($f3, "AND");
			if( count($uacs->items) === 0 ){
				$uacc = new Default_Model_UserAccount();
				$uacc->researcherid = $user->id;
				$uacc->accountid = $ucert;
				$uacc->accounttypeid = "x509";
				$uacc->save();
			}
		}
	}
	
	//Return user account entry for current session user
	public static function getCurrentAccount($session){
		if( isset($session->userid) == false || $session->userid == null ){
			return null;
		}
		$attrs = $session->samlattrs;
		$source = strtolower(trim($session->samlauthsource));
		$uid = ( isset($attrs["idp:uid"])?$attrs["idp:uid"][0]:"");
		if( trim($uid) == "" ) return null;
		$accounttype = str_replace("-sp","",$source);
		
		$useraccounts = new Default_Model_UserAccounts();
		$f1 = new Default_Model_UserAccountsFilter();
		$f2 = new Default_Model_UserAccountsFilter();
		$f1->accountid->_escape_seq = "";
		$f1->accountid->equals($uid);
		$f2->account_type->equals($accounttype);
		$useraccounts->filter->chain($f1, "AND");
		$useraccounts->filter->chain($f2, "AND");
		if( count( $useraccounts->items ) > 0 ){
			return $useraccounts->items[0];
		}
		return null;
	}
	
	//Checks if given user account is not active(id: 1) and updates session accordingly
	public static function setupUserAccountStatus($session, $useraccount){
		if( is_null($useraccount) === true ) return null;
		switch(trim($useraccount->stateid)){
			case "2":
				//User account is blocked
				$session->accountStatus = "blocked";
				break;
			case "1":
			default:
				//All ok
				break;
		}
	}
	//Helper function to create entitlement role mappings
	//based on appdb ini configuration file
	public static function getEGIAAIRoleMappings($key) {
		$res = array();
		$roles =  explode('\n', ApplicationConfiguration::saml('egiaai.entitlements.' . $key, ''));
		
		foreach($roles as $role) {
			$role = explode('=', $role);
			if( count($role) <= 1) {
				continue;
			}
			
			$local = $role[0];
			$remote = explode(';', $role[1]);
			
			if( count($remote) === 0) {
				continue;
			}
		
			$res = array_merge($res, array_fill_keys($remote, $local));
		}
		
		return $res;
	}
	//Helper function to return vo role mapping from EGI AAI entitlements
	//If no EGI AAI vo role is given it return all of the role mappings
	//If the given role is not found it returns null.
	private static function getEGIAAIVORoleMapping($role = null) {
		$roles = self::getEGIAAIRoleMappings('vo');
		
		if( $role === null ) {
			return $roles;
		}
		
		if( isset($roles[$role]) && trim($roles[$role]) !== "" ) {
			return $roles[$role];
		}
		
		return null;
	}
	//Helper function to return site role mapping from EGI AAI entitlements
	//If no EGI AAI site role is given it return all of the role mappings
	//If the given role is not found it returns null.
	private static function getEGIAAISiteRoleMapping($role = null) {
		$roles = self::getEGIAAIRoleMappings('site');
		
		if( $role === null ) {
			return $roles;
		}
		
		if( isset($roles[$role]) && trim($roles[$role]) !== "" ) {
			return $roles[$role];
		}
		
		return null;
	}
	//Extracts user entitlements from the saml login response if they exist.
	//Returns an array with VO memberships and Site roles
	private static function extractSamlEntitlements($attrs) {
	  $res = array('vos' => array("members" => array(), "contacts" => array()), 'sites' => array(), 'groups' => array());

	  if( !is_array($attrs) || !isset($attrs['idp:entitlement']) ){
		return $res;
	  }

	  $entitlements = $attrs['idp:entitlement'];
	  foreach( $entitlements as $e ){
		$matches = array();

		//Check if entitlement specifies a site role
		//preg_match("/^urn\:(mace\:)?(.*)\:user\-role\:(.*)\:on-entity\:(.*)\:primary\-key:(.*):in\-project:(.*):(.*)$/", $e, $matches);
		preg_match("/^urn\:(mace\:)?(egi\.eu)\:(goc\.egi\.eu)\:([^\:]*)\:([^\:]*)\:([^\:]*)\@(egi\.eu)$/", $e, $matches);
		if( count($matches) === 8) {
			$role = self::getEGIAAISiteRoleMapping($matches[6]);
			if( $role === null ) {
				continue;
			}

			$res['sites'][] = array(
				'scope' => $matches[2],
				'source' => $matches[3],
				'site_key' => $matches[4],
				'site_name' => $matches[5],
				'role' => $role
			);
			continue;
		}
		
		//Check if entitlement specifies groups
		//preg_match("/^urn\:(mace\:)?(.*)\:group:(.*)$/", $e, $matches);
		preg_match("/^urn\:(mace\:)?(egi\.eu)\:(www\.egi\.eu)\:([^\:]*)\:([^\:]*)\@egi\.eu$/", $e, $matches);
		if( count($matches) === 6) {
			$res['groups'][] = array(
				'scope' => $matches[2],
				'source' => $matches[3],
				'group' => $matches[4],
				'role' => $matches[5]
			);
			continue;
		}
		
		//Check if entitlement specifies a vo role
		//preg_match("/^urn\:(mace\:)?(.*)\:vo\:(.*)\:role\:(.*)$/", $e, $matches); 
		preg_match("/^urn\:(mace\:)?(egi\.eu)\:([^\:]*)\:(.*\:)*([^\:]*)\@(.*)$/", $e, $matches);
		if( count($matches) === 7 && $matches[6] !== 'egi.eu') {
		  $scope = $matches[2];
		  $source = $matches[3];
		  $group = $matches[4];
		  $role = self::getEGIAAIVORoleMapping($matches[5]);
		  $voname = $matches[6];
		  
		  if( $role === 'member' ) {
			$res['vos']['members'][] = array('scope' => $scope, 'source' => $source, 'vo' => $voname, 'group' => $group );
		  } else if($role !== null) {
			$res['vos']['contacts'][] = array('scope' => $scope, 'source' => $source, 'vo' => $voname, 'role' => $role, 'group' => $group );
			$res['vos']['members'][] = array('scope' => $scope, 'source' => $source, 'vo' => $voname, 'group' => $group );
		  }
		  continue;
		}
	  }
	  return $res;
	}
	
	//Persist any VO related information from EGI AAI entitlements given for a specific uid in SAML returned attributes
	private static function updateEGIAAIEntitlements($attrs, $entitlements = array()) {
		$vocontacts = array();
		$vomembers = array();
		$puid = ( isset($attrs["idp:uid"])?$attrs["idp:uid"][0]:"");
		$email = ( ( isset($attrs["idp:mail"]) === true && count($attrs["idp:mail"]) > 0 )?$attrs["idp:mail"][0]:"" );
		$firstname = ( ( isset($attrs["idp:givenName"]) === true && count($attrs["idp:givenName"]) > 0 )?$attrs["idp:givenName"][0]:"" );
		$lastname = ( ( isset($attrs["idp:sn"]) === true && count($attrs["idp:givenName"]) > 0 )?$attrs["idp:sn"][0]:"" );
		$name = trim($firstname . ' ' . $lastname);

		//Clear any vo contact and membership information regarding given persisted uid
		db()->query("SELECT clear_egiaai_user_info(?)", array($puid))->fetchAll();
		
		//Check if entitlements have VO specific information
		if($entitlements && isset($entitlements['vos'])) {
			$voentitlements = $entitlements['vos'];

			//Get extracted vo contatcs from vo entitlements
			if(isset($voentitlements['contacts'])) {
				$vocontacts = $voentitlements['contacts'];
			}

			//Get extracted vo memberships from vo entitlements
			if(isset($voentitlements['members'])) {
				$vomembers = $voentitlements['members'];
			}

			//Update the VO memberships for the given EGI AAI persistend uid.
			foreach($vomembers as $vomember) {
				db()->query("SELECT add_egiaai_user_vomember_info(?, ?, ?)", array($puid, $name, $vomember['vo']))->fetchAll();
			}

			//Update the VO contacts for the given EGI AAI persistend uid.
			foreach($vocontacts as $vocontact) {
				db()->query("SELECT add_egiaai_user_vocontact_info(?, ?, ?, ?, ?)", array($puid, $name, $vocontact['vo'], $vocontact['role'], $email))->fetchAll();
			}
		}
	}
	
	//Performs actions after successful SAML Authedication
	//Decides if the authedicated user is a new or an old
	//user and fills the session accordingly.
	//Returns the url before authedication initialization.
	public static function setupSamlAuth($session){
		$attrs = $session->samlattrs;
		$source = strtolower(trim($session->samlauthsource));
		$uid = ( isset($attrs["idp:uid"])?$attrs["idp:uid"][0]:"");

		if( trim($uid) == "" ) return false;
		$accounttype = str_replace("-sp","",$source);
		
		$useraccount = self::getUserAccount($uid, $accounttype);
		$user = self::getUserByAccount($useraccount);
		
		//Handle empty user
		if( $user === null ){
			if( $accounttype === "egi-sso-ldap" ){ 
				//Connect egi sso account to an existing x509 account where idp:userCertificateSubject == x509 accountid
				//and get the related profile. In case of a new user returns null
				$user = self::connectEgiToX509($session);
			}else if ( $accounttype === "x509" ){
				//Connect x509 account to an existing egi sso account where idp:egiuid == egi sso accountid
				//and get the related profile. In case of a new user returns null
				$user = self::connectX509ToEgi($session);
			}
		}

		if(isset($attrs['idp:traceidp'])) {
			$session->idptrace = $attrs['idp:traceidp'];
		} else {
			$session->idptrace = array();
		}
		
		if(isset($attrs['idp:loa'])) {
			$session->loa = $attrs['idp:loa'];
			if(is_array($session->loa) && count($session->loa) > 0) { 
				$session->loa = $session->loa[0];
			}
		}
		
		//Create a new dunmmy user account model
		if( $useraccount === null ){
			$useraccount = new Default_Model_UserAccount();
			$useraccount->accountid = $uid;
			$useraccount->accounttypeid = $accounttype;
			$useraccount->stateid = 1;
			$useraccount->IDPTrace = $session->idptrace;
			if( $user !== null ){
				$useraccount->researcherid = $user->id;
			}
		}
		
		if( $user!==null && $user->id ){
			if($accounttype !== 'egi-aai') {
				self::harvestSamlData($session, $user);
			}
			self::setupSamlSession($session, $useraccount, $user);
			if( $_COOKIE["SimpleSAMLAuthToken"] ){
				self::setupSamlUserCredentials($user, $session);
			}
		}else{
			self::setupSamlNewUserSession($session, $accounttype);
		}
		
		//Store user entitlements
		$session->entitlements = self::extractSamlEntitlements($attrs);
		if($accounttype === 'egi-aai') {
			self::updateEGIAAIEntitlements($attrs, $session->entitlements);
		}

		//Check if user account is blocked and updates session
		self::setupUserAccountStatus($session, $useraccount);
		
		$session->authSource = $source;
		$session->authUid = $uid;
		$session->logoutUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/saml/logout?source=' . $source;
		$session->loginUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/saml/saml?source=' . $source;
		$session->cancelUrl = 'https://' . $_SERVER["HTTP_HOST"] . "/saml/cancelregistration";
		if( trim($session->authreferer) === "" ){
			return 'https://' . $_SERVER['HTTP_HOST'];
		}
		return $session->authreferer;
	}
	
	//Clears the session and any pending account connection
	public static function cancelRegistrationProcess($session){
		$logouturl = ( ( trim($session->logoutUrl)!=="" )?$session->logoutUrl:$session->authreferer );
		if( trim($logouturl) === "" ){
			$logouturl = 'https://' . $_SERVER['HTTP_HOST'];
		}
		AccountConnect::cancelPendingConnection($session);
		self::logout($session);
		return $logouturl;
	}
	
	//Clears session and cookies
	public static function logout($session = null){
		self::clearUserCredentails($session);
		Zend_Session::destroy(true);
		clearAuthCookies();
		@session_regenerate_id(FALSE);
		session_unset();
	}
}
?>
