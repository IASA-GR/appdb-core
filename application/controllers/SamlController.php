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

class SamlController extends Zend_Controller_Action
{

    public function init()
    {
		$this->_helper->layout->disableLayout();
        $this->session = new Zend_Session_Namespace('default');
    }

	
	public function logoutAction() {
			$referer = "https://" . $_SERVER["HTTP_HOST"] . "/saml/logout";
			//In case of external service using AppDB as a SP
			if( isset($_GET['callbackUrl']) && trim($_GET['callbackUrl']) ) {
				$referer = trim($_GET['callbackUrl']);
			}
			
			if( isset($this->session) && $this->session->developsession === true ){
				$this->_helper->redirector('loggedout');
				return;
			}
			require_once(SamlAuth::LIB_AUTOLOAD);
			$source=$this->_getParam("source");
			if($source == null){
					$source="";
			}
			$config = SimpleSAML_Configuration::getInstance();
			$t = new SimpleSAML_XHTML_Template($config, 'core:authsource_list.tpl.php');
			$t->data['sources'] = SimpleSAML_Auth_Source::getSourcesMatch('-sp');
			
			$this->session->samlattrs=null;
			$this->session->samlauthsource=null;
			
			foreach($t->data['sources'] as $s){
				$as = new SimpleSAML_Auth_Simple($s);
				if( $as->isAuthenticated() ) {
					$as->logout($referer);
				}
			}
			//In case of external service using AppDB as a SP
			if( isset($_GET['callbackUrl']) && trim($_GET['callbackUrl']) ) {
				SamlAuth::logout($this->session);
				$this->_helper->layout->disableLayout();
				$this->_helper->viewRenderer->setNoRender();
				header('Location: ' . trim($_GET['callbackUrl']));
				return;
			}
			//Will reach this code after all sources are logged out
			$this->_helper->redirector('loggedout');
	}

	public function loginAction() {
			require_once(SamlAuth::LIB_AUTOLOAD);
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender();
			$isAuth=false;
			$source="";

			$config = SimpleSAML_Configuration::getInstance();
			$t = new SimpleSAML_XHTML_Template($config, 'core:authsource_list.tpl.php');
			$t->data['sources'] = SimpleSAML_Auth_Source::getSourcesMatch('-sp');

			foreach ($t->data['sources'] as &$_source) {
					$as = new SimpleSAML_Auth_Simple($_source);
					if($as->isAuthenticated()){
							$isAuth=true;
							$source=$_source;
							break;
					}
			}
			if(!$isAuth){
					print('<p><a href="https://'.$_SERVER["SERVER_NAME"].'/help/saml?source=appdb-multi-sp">Multi</a></p>');
					print('<p><a href="https://'.$_SERVER["SERVER_NAME"].'/help/saml?source=egi-sso-ldap-sp">EGI-SSO</a></p>');
					print('<p><a href="https://'.$_SERVER["SERVER_NAME"].'/help/saml?source=x509-sp">Digital Certificates</a></p>');
					print('<p><a href="https://'.$_SERVER["SERVER_NAME"].'/help/saml?source=dev-env-sp">Development</a></p>');
					print('<p><a href="https://'.$_SERVER["SERVER_NAME"].'/help/saml?source=facebook-sp">Facebook</a></p>');
					print('<p><a href="https://'.$_SERVER["SERVER_NAME"].'/help/saml?source=linkedin-sp">LinkedIn</a></p>');
					print('<p><a href="https://'.$_SERVER["SERVER_NAME"].'/help/saml?source=google-sp">Google+</a></p>');
					print('<p><a href="https://'.$_SERVER["SERVER_NAME"].'/help/saml?source=twitter-sp">Twitter</a></p>');
					print('<p><a href="https://'.$_SERVER["SERVER_NAME"].'/help/saml?source=windowslive-sp">WindowsLiveID</a></p>');
			}else{
					print "<p>You are already authenticated with your ".$source." account.</p>";
					print('<p><a href="https://'.$_SERVER["SERVER_NAME"].'/help/samllogout?source='.$source.'">Logout</a></p>');
			}
	}
	public function loggedoutAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
        SamlAuth::logout($this->session);
		header('Location: https://'.$_SERVER['HTTP_HOST']);
	}
	
	public function samlAction() {
			require_once(SamlAuth::LIB_AUTOLOAD);
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender();

			//In case of external service using AppDB as a SP
			if(isset($_GET['callbackUrl']) && trim($_GET['callbackUrl']) !== '') {
				$this->session->authreferer = trim($_GET['callbackUrl']);
			}else if( isset($this->session->authreferer) === false ){
				$this->session->authreferer = (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : '');
			}
                        $source=$this->_getParam("source");
                        if($source == null){
				$source="";
			} else if ($source === 'local-guest-sp'){
                                if(!ApplicationConfiguration::isProductionInstance() && ApplicationConfiguration::saml('guest.uid', false)) {
                                        $this->session->isNewUser = false;
                                        $this->session->userIsGuest = true;
                                        $this->session->samlattrs = array(
                                            "idp:uid" => array( ApplicationConfiguration::saml('guest.uid') ),
                                            "idp:entitlement" => array()
                                        );
                                        $this->session->samlauthsource = ApplicationConfiguration::saml('guest.source', 'egi-aai-sp');
                                        $this->_helper->redirector('postauth');

                                        return;
                                }
                        }
			
			//Check if user is already logged in
			if( SamlAuth::isAuthenticated() !== false && $this->session->isNewUser !== true ){
				/*if( isset($this->session->authreferer) && trim($this->session->authreferer) !== ""){
					$this->session->authreferer = str_replace("http://", "https://", $this->session->authreferer);
					header("Location: " . $this->session->authreferer);
				}else{
					header("Location: " . "https://" . $_SERVER['HTTP_HOST']);
				}
				return;*/
			}else if( isset($this->session) && $this->session->isNewUser === true ){
				header("Location: " . "https://" . $_SERVER['HTTP_HOST']);
				return;
			}
			
				
			$config = SimpleSAML_Configuration::getInstance();
			$t = new SimpleSAML_XHTML_Template($config, 'core:authsource_list.tpl.php');
			$t->data['sources'] = SimpleSAML_Auth_Source::getSourcesMatch('-sp');
			if(!in_array($source,$t->data['sources'])){
				header("Location: " . "https://" . $_SERVER['HTTP_HOST']);
				exit;
			}

			$as = new SimpleSAML_Auth_Simple($source);
			if(!$as->isAuthenticated()) {
					$as->requireAuth();
			}
			$attributes = $as->getAttributes();
			$uid = $attributes['idp:uid'][0];
			$_SESSION['identity'] = $uid;
			$_SESSION['logouturl'] = $as->getLogoutURL();
			$this->session->samlattrs=$attributes;
			$this->session->samlauthsource=$source;
			$this->_helper->redirector('postauth');
	}

	public function connectAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$referer = trim($this->session->connectreferer);
		if( trim($referer) === "" ){
			$referer = $_SERVER["HTTP_REFERER"];
			$this->session->connectreferer = $referer;
		}
		if( trim($referer) === "" ){
			$referer = "https://" . $_SERVER["HTTP_HOST"];
		}
		
		//check if user is loggedin
		if( isset($this->session->userid)===false || is_numeric($this->session->userid) === false || intval($this->session->userid) <= 0 ){
			header("Location: " . $referer);
			unset($this->session->connectreferer);
			return;
		}
		
		//Check if source is given
		$source= trim($this->_getParam("source"));
		if($source == ""){
			header("Location: " . $referer);
			unset($this->session->connectreferer);
			return;
		}
		$authsource = str_replace( "-sp", "", strtolower(trim($source)) );
		$connectsource = str_replace("-sp", "-connect", $source);
		
		require_once(SamlAuth::LIB_AUTOLOAD);
		
		//Initialize SAML
		$config = SimpleSAML_Configuration::getInstance();
		$t = new SimpleSAML_XHTML_Template($config, 'core:authsource_list.tpl.php');
		$t->data['sources'] = SimpleSAML_Auth_Source::getSourcesMatch('-connect');
		if( !in_array($connectsource, $t->data['sources']) ){
			header("Location: " . $referer);
			unset($this->session->connectreferer);
			$this->session->userError = array("title"=>"Could not proceed with user account connection", "message"=> "You tried to connect to a " . $authsource . " account. This type of connection is not supported.");
			exit;
		}
		//Check if SAML Authentication user account for connection is already authenticated
		$as = new SimpleSAML_Auth_Simple($connectsource);
		//In case a user is already authenticated with the source logout and redirect here again
		if( $as->isAuthenticated() ) {
			$as->logout( 'https://'.$_SERVER["SERVER_NAME"].'/saml/connect?source=' . $source );
			return;
		}
		//SAML Authentication new user account for connection
		//$as = new SimpleSAML_Auth_Simple($connectsource);
		//Do the login
		$as->login(array(
			"ReturnTo" => "https://" . $_SERVER["HTTP_HOST"] . "/saml/postconnect?source=" . $source,
			"ErrorUrl" => "https://" . $_SERVER["HTTP_HOST"] . "/saml/postconnecterror?source=" . $source
		));
		return;
	}
	public function postconnectAction(){
		$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
		$referer = trim($this->session->connectreferer);
		if( trim($referer) === "" ){
			$referer = $_SERVER["HTTP_REFERER"];
			$this->session->connectreferer = $referer;
		}
		if( trim($referer) === "" ){
			$referer = "https://" . $_SERVER["HTTP_HOST"];
		}
		//check if user is loggedin
		if( isset($this->session->userid)===false || is_numeric($this->session->userid) === false || intval($this->session->userid) <= 0 ){
			header("Location: " . $referer );
			return;
		}
		//Check if source is given
		$source= trim($this->_getParam("source"));
		if($source == ""){
			header("Location: https://" . $_SERVER["HTTP_HOST"]);
			return;
		}
		$this->session->connectdaccountsource = $source;
		$authsource =  str_replace( "-sp", "", strtolower(trim($source)) );
		$connectedsource = str_replace( "-sp", "-connect", strtolower(trim($source)) );
		
		require_once(SamlAuth::LIB_AUTOLOAD);
		
		//Initialize SAML
		$config = SimpleSAML_Configuration::getInstance();
		$t = new SimpleSAML_XHTML_Template($config, 'core:authsource_list.tpl.php');
		$t->data['sources'] = SimpleSAML_Auth_Source::getSourcesMatch('-connect');
		if(!in_array($connectedsource,$t->data['sources'])){
			header("Location: " . $referer );
			return;
		}
		
		//SAML Authentication new user account for connection
		$as = new SimpleSAML_Auth_Simple($connectedsource);
		
		$attributes = $as->getAttributes();
		$uid = $attributes['idp:uid'][0];
		if( trim($uid) == "" ){
			$this->session->userError = array("title" => "New Account Connection", "message"=>"Could not connect with new user account. Not enough information returned from account provider.");
			$this->_helper->redirector('postconnected');
			return;
		}
		//Check if user is already connected to the requested account
		//If true redirect the user to the previous location (referer)
		$uaccount = AccountConnect::isConnectedTo($this->session, $uid, $authsource);
		if( $uaccount !== false ){
			$this->_helper->redirector('postconnected');
			return;
		}else {
			//Check if this account is already connected to another profile
			$user = SamlAuth::getUserByAccountValues($uid, $authsource);
			if( $user !== null && $user->id != $this->session->userid ){
				$this->session->userError = array("title" => "Could not connect to " . str_replace("-"," ",$authsource) . " account", "message" => "The " . str_replace("-"," ",$authsource) . " account you tried to connect your profile to is already connected to another user profile.");
				$this->_helper->redirector('postconnected');
				return;
			}
		}
		
		//Build account name for user account
		$userFirstName = ( ( isset($attributes["idp:givenName"]) === true && count($attributes["idp:givenName"]) > 0 )?$attributes["idp:givenName"][0]:"" );
		$userLastName = ( ( isset($attributes["idp:sn"]) === true && count($attributes["idp:givenName"]) > 0 )?$attributes["idp:sn"][0]:"" );
		$userFullName = trim($userFirstName . " " . $userLastName);
		$idptrace = ( ( isset($attributes["idp:traceidp"]) === true && count($attributes["idp:traceidp"]) > 0 )?$attributes["idp:traceidp"]:array() );
		if( $userFullName === "" ){
			$userFullName = null;
		}

		//Do the account connection
		AccountConnect::connectAccountToProfile($this->session->userid, $uid, $authsource, $userFullName, $idptrace);

		//Update connected user accounts
		$this->session->currentUserAccounts = SamlAuth::getUserAccountsByUser($this->session->userid, true);
		
		//redirect to post connected action to logout connected account
		$this->_helper->redirector('postconnected');
	}
	
	//Called after postconnect to logout currently conected account
	public function postconnectedAction(){
		$this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
		$source = $this->session->connectdaccountsource;
		$referer = trim($this->session->connectreferer);
		$connectedsource = str_replace( "-sp", "-connect", strtolower(trim($source)) );
		
		if( trim($referer) === "" ){
			$referer = $_SERVER["HTTP_REFERER"];
			$this->session->connectreferer = $referer;
		}
		if( trim($referer) === "" ){
			$referer = "https://" . $_SERVER["HTTP_HOST"];
		}
		unset($this->session->connectreferer);
		unset($this->session->connectdaccountsource);
		
			
		require_once(SamlAuth::LIB_AUTOLOAD);
		
		//Get SAML Authentication new user account for connection (-connect) and perform logout
		$as = new SimpleSAML_Auth_Simple($connectedsource);
		$as->logout($referer);
	}
	
	public function postauthAction() {
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender();
				
			$inited = SamlAuth::setupSamlAuth($this->session);
			
			//Check and redirect if user account is blocked
			if( $this->session->accountStatus === "blocked" ){
				$this->_helper->redirector('blockedaccount');
				return;
			}
			
			//Check and redirect if user is deleted
			if( $this->session->userDeleted === true ){
				$this->_helper->redirector('deletedprofile');
				return;
			}
			
			//No need any more. Referer is stored in $inited variable
			unset($this->session->authreferer);
			
			if( $inited !== false && $this->session->isNewUser !== true ){ 
				//Found user and a url referer. Redirect to referer
				$this->_helper->layout->disableLayout();
				$this->_helper->viewRenderer->setNoRender();
				header("Location: " . $inited);
			}else if( $this->session->isNewUser === true ){
				$this->session->authreferer = $inited;
				// new user. First login. Redirect to new user account page
				$this->_helper->redirector('newaccount');
			}else if( $this->session->userid !== null &&  $this->session->userid > -1){ 
				//Found user, but no url referer. Redirect to home page
				$this->_helper->layout->disableLayout();
				$this->_helper->viewRenderer->setNoRender();
				header("Location: " . "https://" . $_SERVER['HTTP_HOST']);
			}else{ 
				//No user. perform logout.
				$this->_helper->redirector('loggedout');
			}
	}
	public function deletedprofileAction(){
		if( $this->session->userDeleted !== true ){
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender();
			header("Location: " . "https://" . $_SERVER['HTTP_HOST']);
			return;
		}
		$this->_helper->layout->disableLayout();
		
		//Store all useful session variables for displaying the view.
		$this->view->delAccounts = SamlAuth::getUserAccountsByUser($this->session->userid);
		$this->view->currentAccount = SamlAuth::getCurrentAccount($this->session);
		
		$this->view->deletedById = $this->session->userDeletedById;
		$this->view->deletedByName = $this->session->userDeletedByName;
		$this->view->deletedByCName = $this->session->userDeletedByCName;
		$this->view->deletedOn = $this->session->userDeletedOn;
		$this->view->authSource = $this->session->authSource;
		$this->view->fullName = $this->session->fullName;
		$this->view->authUid = $this->session->authUid;
		$this->view->returnUrl = $this->session->authreferer;
		if( trim($this->view->returnUrl) === "" ){
			$this->view->returnUrl = "https://" . $_SERVER["HTTP_HOST"];
		}
		
		//Clear session
		SamlAuth::logout($this->session);
	}
	public function blockedaccountAction(){
		if( strtolower(trim($this->session->accountStatus)) !== "blocked" ){
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender();
			header("Location: " . "https://" . $_SERVER['HTTP_HOST']);
			return;
		}
		$this->_helper->layout->disableLayout();
		
		//Store all useful session variables for displaying the view.
		$this->view->userid = $this->session->userid;
		$this->view->authSource = $this->session->authSource;
		$this->view->fullName = $this->session->fullName;
		$this->view->authUid = $this->session->authUid;
		$this->view->returnUrl = $this->session->authreferer;
		$this->session->accountStatus = "";
		//Clear session
		SamlAuth::logout($this->session);
	}
	public function newaccountAction(){
		$referer = $this->_getParam("r");
		if( trim($referer) !== "" ){
			$this->session->authreferer = $referer;
		}
		if( $this->session->isNewUser !== true && $this->session->userid !== -1){
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender();
			header("Location: " . "https://" . $_SERVER['HTTP_HOST']);
			return;
		}
		if( AccountConnect::isConnected($this->session) !== false ){
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender();
			SamlAuth::setupSamlAuth($this->session);
			header("Location: " . "https://" . $_SERVER['HTTP_HOST']);
			return;
		}
		
		$this->_helper->layout->enableLayout();
		//check if pending so the session will be updated accrdingly
		
		AccountConnect::isPending($this->session);
		$this->view->session = $this->session;
	}
	public function newprofileAction(){
		$this->_helper->layout->disableLayout();
		if( $this->session->isNewUser !== true && $this->session->userid !== -1){
			$this->_helper->viewRenderer->setNoRender();
			header("Location: " . "https://" . $_SERVER['HTTP_HOST']);
			return;
		}
		$profiles = array( SamlAuth::initNewUserProfile($this->session) );
		$this->view->profiles = $profiles;
		$this->view->session = $this->session;
		
		//Add helper data for possible editing of a new profile
		//Create position types list
		$ptypes = new Default_Model_PositionTypes();
		$ptypes->filter->orderBy('ord');
		$positiontypes = array( 'ids' => array(), 'vals' => array() );
		for($i=0; $i<count($ptypes->items); $i+=1){
			$ptype = $ptypes->items[$i];
			array_push( $positiontypes["ids"], stripslashes($ptype->id) );
			array_push( $positiontypes["vals"], stripslashes($ptype->description));
		}
		$this->view->positionTypes = json_encode($positiontypes);
		
		//Create countries list
		$cntrs = new Default_Model_Countries();
		$cntrs->filter->orderBy('name');
		$countries = array('ids' => array(), 'vals' => array() );
		for($i=0; $i<count($cntrs->items); $i+=1){
			$country = $cntrs->items[$i];
			array_push( $countries['ids'], $country->id );
			array_push( $countries['vals'], $country->name );
		}
		$this->view->countries = json_encode($countries);
		
		//Create contacttype list
		$ctypes = new Default_Model_ContactTypes();
		$ctypes->filter->orderBy('description');
		$contactTypes = array('ids' => array(), 'vals' => array() );
		for($i=0; $i<count($ctypes->items); $i+=1){
			$ctype = $ctypes->items[$i];
			array_push( $contactTypes['ids'], $ctype->id );
			array_push( $contactTypes['vals'], $ctype->description );
		}
		$this->view->contactTypes = json_encode($contactTypes);
	}
	public function createnewprofileAction(){
		$this->_helper->layout->disableLayout();
		if( $this->session->userid !== -1 || $this->session->isNewUser !== true){
			$this->_helper->viewRenderer->setNoRender();
			header("HTTP/1.0 404 Not Found");
			return;
		}
		
		$firstname = ( isset($_POST["firstName"])?trim($_POST["firstName"]):null );
		$lastname = ( isset($_POST["lastName"])?trim($_POST["lastName"]):null );
		$institution = ( isset($_POST["institution"])?trim($_POST["institution"]):null );
		$countryid = ( isset($_POST["countryID"])?intval($_POST["countryID"]):null );
		$positiontypeid = ( isset($_POST["positionTypeID"])?intval($_POST["positionTypeID"]):null );
		
		$error = array();
		
		if( $firstname === null ) array_push($error, "Invalid user first name given");
		if( $lastname === null ) array_push($error, "Invalid user last name given");
		//if( $institution === null ) array_push($error, "Invalid user institute given");
		if( $countryid === null ) array_push($error, "Invalid user country given");
		if( $positiontypeid === null ) array_push($error, "Invalid user role given");
		
		if( count($error) > 0 ){
			//todo: Add Error handler
			$this->view->error = $error;
			return;
		}
		
		//Collect user information
		$entry = new Default_Model_Researcher();
		$entry->lastName = $lastname;
		$entry->firstName = $firstname;
		$entry->institution = "";
		$entry->countryID = $countryid;
		$entry->positionTypeID = $positiontypeid;
		
		//Collect user contacts
		$conts = array();
		foreach ($_POST as $key => $value) {
			if(trim($value) === "" ) continue;
			if ( (substr($key,0,7) === "contact") && (substr($key,0,11) !== "contactType") ) {
				$cnum = substr($key,7);
				$cont = new Default_Model_Contact();
				$cont->data = $value;
				$cont->contactTypeID = $_POST['contactType'.$cnum];
				if( is_numeric($cont->contactTypeID) === false ) array_push($error, "Invalid contact type given");
				if( trim($value) === "" ) array_push($error, "Empty contact value given");
				if( count( $error ) > 0 ) continue;
				array_push($conts, $cont);
			}
		}
		
		//Collect user relations
		$relations = array();
		foreach($_POST as $key=>$value){
			if(trim($value) === "" ) continue;
			if ( strtolower(trim($key)) === "organization" ) {
				$data = json_decode($value);
				$relations[] = array(
					"id" => trim(strval($data->id)),
					"targetguid" => trim(strval($data->targetguid)),
					"parentid" => null
				);
			}
		}
		
		if( count($error) > 0 ){
			//todo: Add error handler
			$this->view->error = $error;
			return;
		}
		
		//Check if user account has been registered in the meanwhile
		$uid = $this->session->authUid;
		$source = $this->session->authSource;
		$useraccounts = new Default_Model_UserAccounts();
		$f1 = new Default_Model_UserAccountsFilter();
		$f2 = new Default_Model_UserAccountsFilter();
		$f1->accountid->equals($uid)->or($f1->accountid->overrideEscapeSeq("")->equals($uid));
		$f2->accounttype->equals($source);
		$useraccounts->filter->chain($f1, "AND");
		$useraccounts->filter->chain($f2, "AND");
		
		if( count($useraccounts->items) > 0 ){
			array_push($error, "User account is already registered");
			$this->view->error = $error;
			return;
		}
		
		//Everything is ok. Continue with saving new profile
		//Save entry
		$entry->save();
		//Save entry contacts
		for( $i=0; $i<count($conts); $i+=1 ){
			$cont = $conts[$i];
			$cont->researcherID = $entry->id;
			$cont->save();
		}
		
		//extract IDP Trace in case it is returned from SAML
		$attrs = $this->session->samlattrs;
		$idptrace = array();
		if(isset($attrs['idp:traceidp']) && is_array($attrs['idp:traceidp'])) {
			$idptrace = $attrs['idp:traceidp'];
		}
		
		//Save user account
		$useraccount = new Default_Model_UserAccount();
		$useraccount->researcherid = $entry->id;
		$useraccount->accountid = $this->session->authUid;
		$useraccount->accounttypeid = str_replace("-sp","",$this->session->authSource);
		$useraccount->IDPTrace = $idptrace;
		$useraccount->save();
		
		//Save user relations (organization)
		if( $entry && count($relations) > 0 ){
			//ensure permissions are built
			$try_counter = 0;
			while($try_counter < 25 ){
				$try_counter += 1;
				$confs = db()->query("SELECT data FROM config WHERE var = 'permissions_cache_dirty';")->fetchAll();
				if( count($confs) > 0 ){
					$conf = $confs[0];
					if( (isset($conf["data"]) && trim($conf["data"]) === '0') ){
						break;
					}
				}
				sleep(1);
			}
			
			//Refetch entry (user) to retrieve guid
			$us = new Default_Model_Researchers();
			$us->filter->id->numequals($entry->id);
			if( count($us->items) > 0 ){
				$u = $us->items[0];
				EntityRelations::syncRelations($u->guid, $u->id, $relations);
			}
		}
		
		//Setup new session
		if( $entry ){
			//ensure race condition 
			$try_counter = 0;
			while($try_counter < 10 ){
				$try_counter += 1;
				$ppl = new Default_Model_Researchers();
				$ppl->filter->id->equals($entry->id);
				if( count($ppl->items) > 0 ){
					break;
				}
				sleep(1);
			}
	
			unset($this->session->isNewUser);
			$this->session->userid = $entry->id;
			
			SamlAuth::setupSamlAuth($this->session);
		}
		
		$this->view->session = $this->session;
		$this->view->error = array();
	}
	
	public function connectableprofilesAction(){
		$this->_helper->layout->disableLayout();
		if( $this->session->isNewUser !== true && $this->session->userid !== -1){
			$this->_helper->viewRenderer->setNoRender();
			header("Location: " . "https://" . $_SERVER['HTTP_HOST']);
			return;
		}
		$profileids = SamlAuth::getConnectableProfileIds($this->session);
		$this->view->profileids = $profileids;
		$this->view->session = $this->session;
	}
	public function cancelregistrationAction(){
		$redirecturl = SamlAuth::cancelRegistrationProcess($this->session);
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		header("Location: " . $redirecturl);
	}
	public function sendconfirmationcodeAction(){
		$this->_helper->layout->disableLayout();
		if( $this->session->isNewUser !== true && $this->session->userid !== -1){
			$this->_helper->viewRenderer->setNoRender();
			header("Location: " . "https://" . $_SERVER['HTTP_HOST']);
			return;
		}
		
		$error = null;
		$profilename = null;
		$id = ( ( isset($_POST["id"]) && is_numeric($_POST["id"]) )?intval($_POST["id"]):null );
		$accounttype =  str_replace("-sp", "", trim($this->session->authSource));
		$accounttype = ( ( $accounttype === "" )?null:$accounttype );
		$accountname = trim($this->session->authUid);
		$accountname = ( ( $accountname === "" )?null:$accountname );
		
		$this->view->session = $this->session;
		$this->view->id = trim($id);
		$this->view->accounttype = trim($accounttype);
		$this->view->accountname = trim($accountname);
		$this->view->profilename = trim($profilename);
		$this->view->implicitconnect = false;
		$this->view->implicitpending = false;
		
		//Check for invalid data
		if( $id === null ){
			$this->view->error = "No profile information given";
			return;
		}
		if( $accounttype === null ){
			$this->view->error = "No account type is given";
			return;
		}
		if( $accountname === null ){
			$this->view->error = "No account information given";
			return;
		}
				
		//Check if account is already pending for connection to a profile implicitly or through a different session
		//In this case the view should inform the user and autorefresh to display the confirmation form.
		if( AccountConnect::isPending($this->session) === true ) {
			$this->view->error = "Your account seems to be waiting for connection approval for another profile";
			$this->view->implicitpending = false;
			//Update session so user will be redirected to the appropriate form
			SamlAuth::setupSamlAuth($this->session);
			return;
		}
		
		//Check if current account is already connected to a profile implicitly or through a different session
		//In this case the view should inform the user and autorefresh to the portal
		if( AccountConnect::isConnected($this->session) !== false ){
			$this->view->error = "Your account is already connected";
			$this->view->implicitconnect = true;
			//Update session so user will auto login on page refresh
			SamlAuth::setupSamlAuth($this->session);
			return;
		}
		
		//Find profile for connection
		$profile = null;
		$ppl = new Default_Model_Researchers();
		$ppl->filter->id->equals($id);
		if( count($ppl->items) > 0 ){
			//Profile found
			$profile = $ppl->items[0];
			$this->view->profilename = $profile->firstName . " " . $profile->lastName;
		}else { 
			//profile not found
			$this->view->error = "Requested profile not found";
			return;
		}
		
		//Procceed with sending the request
		AccountConnect::requestAccountConnection($this->session, $profile);
		$this->view->session = $this->session;
		$this->view->error = null;
		return;
	}
	
	public function submitconfirmationcodeAction(){
		$this->_helper->layout->disableLayout();
		if( $this->session->isNewUser !== true && $this->session->userid !== -1){
			$this->_helper->viewRenderer->setNoRender();
			header("Location: " . "https://" . $_SERVER['HTTP_HOST']);
			return;
		}
		$this->view->error = null;
		$this->view->session =  $this->session;
		$this->view->expired = false;
		$code = ( (isset($_POST["confirmationcode"]) === true)?trim($_POST["confirmationcode"]):null );
		if( $code === null ){
			$this->view->error = "No confirmation code given";
			return;
		}
		
		//Check if current account is already connected to a profile implicitly or through a different session
		//In this case the view should inform the user and autorefresh to the portal
		if( AccountConnect::isConnected($this->session) !== false ){
			//Update session so user will auto login on page refresh
			SamlAuth::setupSamlAuth($this->session);
			$this->view->session =  $this->session;
			return;
		}
		
		//Check if account is not pending, which means the request has timedout.
		//In this case the view should inform the user and autorefresh to display the confirmation form.
		if( AccountConnect::isPending($this->session) === false ) {
			$this->view->error = "Your connection  request has expired";
			$this->view->expired = true;
			//Update session so user will be redirected to the appropriate form
			SamlAuth::setupSamlAuth($this->session);
			return;
		}
		
		$result = AccountConnect::submitPendingConnectionCode($this->session, $code);
		if( $result !== true ){
			$this->view->error = "Given code is not correct";
			return;
		}
		
		$this->view->session =  $this->session;
	}
	public function disconnectaccountAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		if( $_SERVER['REQUEST_METHOD'] !== 'POST' ||
			isset($this->session->userid) === false  ||
			is_numeric($this->session->userid) === false ||
			intval($this->session->userid) <= 0 ||
			isset($_POST["id"]) === false ||
			is_numeric($_POST["id"]) === false ||
			intval($_POST["id"]) <= 0 ){
				header("HTTP/1.0 404 Not Found");
				return;
		}
		
		$result = array();
		$id = intval($_POST["id"]);
		$uaccs = SamlAuth::getUserAccountsByUser($this->session->userid);
		if( count($uaccs) === 1){
			$result = array("error" => "Cannot remove last user account");
		}else if( count($uaccs) > 1 ){
			for($i=0; $i<count($uaccs); $i+=1){
				$ua = $uaccs[$i];
				if( $ua->id === $id ) {
					AccountConnect::disconnectAccount($this->session, $ua);
					break;
				}
			}
			$result= SamlAuth::getUserAccountsByUser($this->session->userid, true);
			$this->session->currentUserAccounts = $result;
		}
		
		header('Content-type: application/json');
		echo json_encode($result);
	}

        private function getUserVOInfo($uid, $infoType = 'memberships') {
            switch($infoType) {
                case "contacts":
                    $q = "SELECT
                        vos.id as id,
                        vos.name AS name,
                        domains.name AS discipline,
                        egiaai.vo_contacts.role AS role 
                       FROM egiaai.vo_contacts
                        INNER JOIN vos ON vos.name = egiaai.vo_contacts.vo
                        INNER JOIN domains ON domains.id = vos.domainid
                       WHERE
                        deleted = FALSE AND
                        egiaai.vo_contacts.puid = ?";
                    break;
                case "memberships":
                    $q = "SELECT
                        vos.id as id,
                        vos.name AS name,
                        domains.name AS discipline
                       FROM egiaai.vo_members
                        INNER JOIN vos ON vos.name = egiaai.vo_members.vo
                        INNER JOIN domains ON domains.id = vos.domainid
                       WHERE
                        deleted = FALSE AND
                        egiaai.vo_members.puid = ?";
                    break;
                default:
                    return array();
            }

            return db()->query($q, array($uid))->fetchAll();
        }
	public function isloggedinAction(){
		if(trim($_SERVER['REQUEST_METHOD']) === "GET"){
			if ($this->session->isLocked()) {
				$this->session->unLock();
			}
			session_write_close();
		}
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$res = "0";
		header('Access-Control-Allow-Origin: *');
		if ( $this->session && isset($this->session->developsession) && $this->session->developsession === true ) {
			if( $this->session->userid ){
				$res = "1";
			}
		}
                if( $this->session && $this->session->userIsGuest && $this->session->userid) {
                    $res = "1";
                    $source = false;
                }
		if( $res === "0" ) {
			$source = SamlAuth::isAuthenticated();
		}
		if( $source !== false ){
			$res = "1";
			if( isset($_GET['profile']) && $_GET['profile'] === 'attributes' && $this->isAllowedProfileDataDomain()) {
				header('Content-type: application/json');
				$attrs = $source->getAttributes();
				if ($attrs && count($attrs) > 0) {
					$sourceIdentifier = false;
					$uid = false;
					$userAccount = false;
					try {
						if (isset($attrs['idp:sourceIdentifier']) && count($attrs['idp:sourceIdentifier']) === 1) {
							$sourceIdentifier = $attrs['idp:sourceIdentifier'][0];
							$sourceIdentifier = str_replace('-sp', '', $sourceIdentifier);
						}

						if (isset($attrs['idp:uid']) && count($attrs['idp:uid']) === 1) {
							$uid = $attrs['idp:uid'][0];
						}

						if ($sourceIdentifier && $uid) {
							$userAccount = SamlAuth::getUserAccount($uid, $sourceIdentifier);
						}

						if ($userAccount) {
                                                        if ($sourceIdentifier === 'egi-aai' || ($sourceIdentifier === 'egi-sso-ldap' && ApplicationConfiguration::isEnviroment('production') === false)) {
                                                            $attrs['entitlements'] = array('vo' => array('contacts' => $this->getUserVOInfo($uid, 'contacts') ,'memberships' => $this->getUserVOInfo($uid, 'memberships')));
                                                        } else {
                                                            $attrs['entitlements'] = array('vo' => array('contacts' => VoAdmin::getVOContacts($userAccount->researcherid) ,'memberships' => VoAdmin::getUserMembership($userAccount->researcherid)));
                                                        }
							$uaccounts = array();
							$alluseraccounts = SamlAuth::getResearcherUserAccounts($userAccount->researcherid);
							foreach($alluseraccounts  as $uaccount) {
								$uaccounts[] = array('type' => $uaccount->accountTypeID, 'uid' => $uaccount->accountID);
							}
							$attrs['appdb:accounts'] = $uaccounts;
							$researcher = $userAccount->getResearcher();
							if ($researcher) {
                                                                $currentHostName = 'https://'.$_SERVER['HTTP_HOST'];
                                                                $attrs['appdb:cname'] = $researcher->cname;
                                                                $attrs['appdb:firstName'] = $researcher->firstname;
                                                                $attrs['appdb:lastName'] = $researcher->lastname;
                                                                $attrs['appdb:refs'] = array(
                                                                  "profile" => $currentHostName .'/store/person/' . $researcher->cname,
                                                                  "image" => $currentHostName .'/people/getimage?id=' . $researcher->id
                                                                );
								$appdbGroups = array();
								$actorGroups = $researcher->getActorGroups();
								if ( count($actorGroups) > 0 ) {
									foreach($actorGroups as $actorGroup) {
										if ($actorGroup->id) {
											$group = $actorGroup->getGroup();
											$appdbGroups[] = array('id' => $group->id, 'name' => $group->name);
										}
									}
								}
								$attrs['appdb:roles'] = $appdbGroups;
							}
						}
					}catch(Exception $ex) {
						$attrs['error'] = $ex->getMessage();
					}
				}

				echo json_encode($attrs);
				return;
			}
		}
		
		echo $res;
	}
	
	public function entitydescriptorAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$source = trim($this->_getParam("source"));
		header('Access-Control-Allow-Origin: *');
		header("Content-type: application/samlmetadata+xml");
		header("Content-Disposition: attachment; filename=" . $source );
		
		echo web_get_contents("https://" . $_SERVER['HTTP_HOST'] . "/auth/module.php/saml/sp/metadata.php/" . $source);
	}
	
	/**
	 * Checks if requestor is allowed to view saml user information.
	 * This function is based on saml.profile.allow values in application.ini. 
	 * 
	 * @return boolean
	 */
	private function isAllowedProfileDataDomain() {
		$ref = (isset($_SERVER['HTTP_REFERER']) && trim($_SERVER['HTTP_REFERER'])!=='')?trim($_SERVER['HTTP_REFERER']):'';

		if( $ref === '' ) {
			return false;
		}
		
		$allowed = explode(';', ApplicationConfiguration::saml('profile.allow', ''));
		if( count($allowed) === 0 ) {
			return false;
		}
		
		if( count($allowed) === 1 ) {
			if( $allowed[0] === '' ) {
				return false;
			} else if($allowed[0] === '*') {
				return true;
			}
		}
		
		$url = parse_url($ref);
		$domain = $url['scheme'] . '://' . $url['host'];
		
		foreach($allowed as $allow) {
			$pregallow = '/^' . str_replace('_________', '\w+', preg_quote( str_replace('*', '_________', trim($allow)), '/')) .'$/';
			$matches = null;
			
			preg_match($pregallow, $domain, $matches);
			if( count($matches) > 0 ) {
				return true;
			}
		}
		
		return false;
		
	}
}
