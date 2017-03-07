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

require_once('email_configuration.php');
require_once('email_service.php');

class AccountConnect {
	//Check if a call to AccountConnect is valid
	//It is only available for signed in accounts without a profile.
	public static function isValid($session){
		//Check if session is filled
		if( !$session || isset($session->userid) === false || trim($session->authUid) == "" || trim($session->authSource) == "" ) return false;
		if( $session->userid > 0 ) return false;
		return true;
	}
	
	//Checks if the current user account is connected in a profile.
	//Useful to inform current session if a change happened from another session,
	//if the user has opened more than one sessions with the current account
	//Returns false or the connected profile
	public static function isConnected($session){
		
		$uid = trim($session->authUid);
		$source = str_replace( "-sp", "", trim($session->authSource) );
		
		$uaccounts = new Default_Model_UserAccounts();
		$f1 = new Default_Model_UserAccountsFilter();
		$f2 = new Default_Model_UserAccountsFilter();
		$f1->accountid->overrideEscapeSeq("");
		$f1->accountid->equals($uid);
		$f2->account_type->equals($source);
		$uaccounts->filter->chain($f1, "AND");
		$uaccounts->filter->chain($f2, "AND");
		if( count($uaccounts->items) === 0 ){
			return false;
		}
		$uaccount = $uaccounts->items[0];
		$researcher = $uaccount->getResearcher();
		
		return $researcher;
	}
	
	//Checks if the current user has a user account with the given values.
	//Returns false or the connected user account
	public static function isConnectedTo($session,$uid,$accounttype){
		$userid = trim($session->userid);
		$uid = trim($uid);
		$source = str_replace( "-sp", "", trim($accounttype) );
		
		$uaccounts = new Default_Model_UserAccounts();
		$f1 = new Default_Model_UserAccountsFilter();
		$f2 = new Default_Model_UserAccountsFilter();
		$f3 = new Default_Model_UserAccountsFilter();
		
		$f1->researcherid->equals($userid);
		$f2->accountid->overrideEscapeSeq("");
		$f2->accountid->equals($uid);
		$f3->account_type->equals($source);
		$uaccounts->filter->chain($f1, "AND");
		$uaccounts->filter->chain($f2, "AND");
		$uaccounts->filter->chain($f3, "AND");
		if( count($uaccounts->items) > 0 ){
			return $uaccounts->items[0];
		}
		return false;
	}
	
	//Retrieves pending connection based on account uid and type
	private static function getPendingConnection($accountuid, $accounttype){
		$paccounts = new Default_Model_PendingAccounts();
		$f1 = new Default_Model_PendingAccountsFilter();
		$f2 = new Default_Model_PendingAccountsFilter();
		$f3 = new Default_Model_PendingAccountsFilter();
		$f4 = new Default_Model_PendingAccountsFilter();
		
		$f1->accountid->overrideEscapeSeq("");
		$f1->accountid->equals($accountuid);
		$f2->account_type->equals($accounttype);
		$f3->resolved->equals(false);
		$f4->setExpr("pending_accounts.addedon > NOW() - '30 minutes'::INTERVAL");
		$paccounts->filter->chain($f1, "AND");
		$paccounts->filter->chain($f2, "AND");
		$paccounts->filter->chain($f3, "AND");
		$paccounts->filter->chain($f4, "AND");
		$paccounts->filter->orderBy("addedon DESC");
		if( count($paccounts->items) === 0 ){
			return null;
		}
		return $paccounts->items[0];
	}
	
	//Setup session as a peding connection account
	public static function updateSessionAsPending($session, $pending){
		$session->accountStatus = "pendingconnect";
		$researcher = $pending->getResearcher();
		$session->accountPendingId = $pending->id;
		$session->accountPendingProfileId = $researcher->id;
		$session->accountPendingProfileName = $researcher->name;
	}
	
	//Removes pending connection for current account.
	public static function cancelPendingConnection($session){
		if( self::isValid($session) === false ) return false;
		$uid = trim($session->authUid);
		$source = str_replace( "-sp", "", trim($session->authSource) );
		$pendingaccount = self::getPendingConnection($uid, $source);
		if( $pendingaccount !== null ){
			$pends = new Default_Model_PendingAccounts();
			if( count($pends->items) > 0 ){
				$pends->remove($pendingaccount);
			}
		}
		if( $session->accountStatus === "pendingconnect" ){
			unset($session->accountStatus);
		}
		unset($session->accountPendingId);
		unset($session->accountPendingProfileId);
		unset($session->accountPendingProfileName);
	}
	
	//Checks if there is a pending connection request for this account
	//Useful to inform current session if a change happened from another session,
	//if the user has opened more than one session with the current account.
	//Returns true or false
	public static function isPending($session){
		if( self::isValid($session) === false ) return false;
		$uid = trim($session->authUid);
		$source = str_replace( "-sp", "", trim($session->authSource) );
		
		$pendingaccount = self::getPendingConnection($uid, $source);
		if( $pendingaccount === null ){
			//Clear session related variables
			if( $session->accountStatus === "pendingconnect" ){
				unset($session->accountStatus);
			}
			unset($session->accountPendingId);
			unset($session->accountPendingProfileId);
			unset($session->accountPendingProfileName);
			return false;
		}		
		//In case it is pending set the account status in current session as "pendingconnect"
		self::updateSessionAsPending($session, $pendingaccount);
		return true;
	}
	
	//Create a request and send confirmation email
	public static function requestAccountConnection($session, $profile){
		if( self::isValid($session) === false ) return false;
		$uid = trim($session->authUid);
		$source = str_replace( "-sp", "", trim($session->authSource) );
		
		$ispending = self::isPending($session);
		if( $ispending === true ){
			return true;
		}
		
		//Save pending account entry
		$pending = new Default_Model_PendingAccount();
		$pending->researcherid = $profile->id;
		$pending->accountid = $uid;
		$pending->accountType = $source;
		$pending->accountName = trim($session->userFirstName . " " . $session->userLastName);
		$pending->save();
		
		//make sure you get the pending account item data from race conditions
		$try_count = 0;
		while( $try_count < 10 ){
			$paccounts = new Default_Model_PendingAccounts();
			$paccounts->filter->id->equals($pending->id);
			if( count($paccounts->items) > 0 ){
				$pending = $paccounts->items[0];
				break;
			}
			$try_count += 1;
			sleep(1);
		}
		
		self::updateSessionAsPending($session, $pending);
		self::sendConfirmationEmail($session, $pending);
		
		return true;
	}
	
	//Dispatch an email with the confirmation code to the profile primary email 
	//the pending connection reqeest refers to.
	private static function sendConfirmationEmail($session, $pending){
		$researcher = $pending->getResearcher();
		$email = $researcher->getPrimaryContact();
		
		$accounttype = str_replace("-", " ", trim($pending->accountType));
		$accountname = ( ( trim($pending->accountName) === "" )?$pending->accountID:$pending->accountName );
		
		$res = "Dear " . $researcher->name . ",\n\n";

		$res .= "    a request has been made to connect the " .  $accounttype . " account of " . $accountname . "\n";
		$res .= "to your profile in the EGI Applications Database [1].\n";
		$res .= "If it is really you the one that made this request and you wish to proceed with the account connection\n";
		$res .= " - go to the EGI Applications Database Portal[1] and\n";
		$res .= " - sign in with the same " . $accounttype . " account.\n";
		$res .= "The system will prompt you with a form where you should enter the confirmation code bellow:\n\n";
		$res .= "   Confirmation Code: " . $pending->code . "\n\n";
		$res .= "Note: The confirmation code expires 30 minutes after this message was sent.\n\n";
		$res .= "If you are not the one that made this request, then please report the incident by replying to this message.\n\n";	

		$res .= "Best regards,\n";
		$res .= "EGI AppDB team\n";
		$res .= "\n\n__________________________________________________\n";
		$res .= "[1]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"];
		
		$subject = "EGI AppDB: Request to connect " . $accounttype . " account to your profile";
		$text = $res;
		$body = $body = preg_replace("/\n/", "<br/>", $res);
		$body = "<div>" . $body . "</div>";
		
		//DEVELOPMENT CODE
		if(ApplicationConfiguration::isProductionInstance() === FALSE  ){
			error_log("\nSending to: " . $email);
			error_log("\n\n" . $res);
		}
		$recipients = array($email);
		//sendMultipartMail($subject,$recipients,$text,$body,'appdb-reports@iasa.gr','enadyskolopassword','appdb-support@iasa.gr',null, false,array("Precedence"=>"bulk"));
		EmailService::sendBulkReport($subject, $recipients, $text, $body, EmailConfiguration::getSupportAddress());
	}
	
	//Connect the given profile id to the user account information given
	public static function connectAccountToProfile($profileid, $id, $type, $name = null, $idptrace = array()){
		//Check if this user account is already connected to a profile
		$user = SamlAuth::getUserByAccountValues($id, $type);
		if( $user !== null ){
			return;
		}
		
		$uaccount = new Default_Model_UserAccount();
		$uaccount->researcherID = $profileid;
		$uaccount->accountID = $id;
		$uaccount->accountTypeID = $type;
		$uaccount->accountName = $name;
		$uaccount->IDPTrace = $idptrace;
		$uaccount->save();
		
		$try_count = 0;
		while($try_count < 10){
			$uaccounts = new Default_Model_UserAccounts();
			$uaccounts->filter->id->equals($uaccount->id);
			if( count($uaccounts->items) > 0 ){
				break;
			}
			$try_count += 1;
			sleep(1);
		}
		
	}
	
	//Check if given code is the same as the penfing connection request
	//If not retrun false. If true resolve pending request, connect the account
	//update the session and return true
	public static function submitPendingConnectionCode($session, $code){
		if( self::isValid($session) === false ) return false;
		$uid = trim($session->authUid);
		$source = str_replace( "-sp", "", trim($session->authSource) );
		$paccount = self::getPendingConnection($uid, $source);
		if( !$paccount ) return false;
		
		if( trim($paccount->code) !== trim($code) ){
			return false;
		}
		
		$paccount->resolved = true;
		$paccount->resolvedOn = 'NOW()';
		$paccount->save();
		
		self::connectAccountToProfile( $paccount->researcherid, $paccount->accountID, $paccount->accountType, $paccount->accountName, $session->idptrace );
		
		unset($session->isNewUser);
		unset($session->accountStatus);
		unset($session->accountPendingId);
		unset($session->accountPendingProfileId);
		unset($session->accountPendingProfileName);
		
		SamlAuth::setupSamlAuth($session);
		return true;
	}
	
	public static function disconnectAccount($session, $account){
		if( is_null($account) ) return;
		if( $session->userid !== $account->researcherid ) return;
		$accs = new Default_Model_UserAccounts();
		$accs->filter->id->equals($account->id);
		if( count($accs->items) > 0 ){
			$accs->remove($accs->items[0]);
		}
	}
}
?>
