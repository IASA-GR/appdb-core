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

class UserRequests {
	//Used only in development mode
	public static function getDevelopmentRecipients($extramail=''){
		$recipients = EmailConfiguration::getDevelopmentRecepients();
		if($extramail !== ''){
			$recipients[] = $extramail;
		}
		return array_unique($recipients);
	}
	
	//Retreive the primary emails of related appication contacts
	public static function getRelatedEmails($application,$requestorid){
		$receivers = array();
		$countries = array();
		
		//adding administrators and managers
		$uitems = getAdminsAndManagers();
		foreach($uitems as $i){
			$receivers[] = $i->id;
		}
		
		//Fetching primary e-mail accounts
		$emails = array();
		$contacts = new Default_Model_Contacts();
		$contacts->filter->isprimary->equals(true)->and($contacts->filter->researcherid->in($receivers));
		$conts = $contacts->items;
		foreach($conts as $i){
			if($i->data != '' && is_null($i->data) == false ){
				$emails[] = $i->data;
			}
		}
		
		//Filter out invalid values, such as duplicate or empty values
		$emails = array_unique($emails);
		return $emails;
	}
	
	public static function getAccessGroupRecipientsForGroup($groupid,$payload=null){
		$actors = new Default_Model_ActorGroupMembers();
		$f1 = new Default_Model_ActorGroupMembersFilter();
		$f2 = new Default_Model_ActorGroupMembersFilter();
		
		$f1->groupid->equals($groupid);
		$actors->filter->chain($f1, "AND");
		if( is_numeric($payload) && intval($payload) > 0 ){
			$f2->payload->equals($payload);
			$actors->filter->chain($f2, "AND");
		}
		$result = array();
		if( count($actors->items) > 0 ){
			foreach($actors->items as $actor){
				$result[] = $actor->actorguid;
			}
		}
		return $result;
	}
	public static function getAccessGroupRecipients($group, $payload=null){
		$admins =  array();
		$managers = array();
		$nils = array();
		
		switch($group->id){
			case "-1":
				$admins = self::getAccessGroupRecipientsForGroup("-1");
				break;
			case "-2":
				$admins = self::getAccessGroupRecipientsForGroup("-1");
				$managers = self::getAccessGroupRecipientsForGroup("-2");
				break;
			case "-3":
				$admins = self::getAccessGroupRecipientsForGroup("-1");
				$managers = self::getAccessGroupRecipientsForGroup("-2");
				$nils = self::getAccessGroupRecipientsForGroup("-3", $payload);
				break;
			default:
				error_log("[UserRequests:::sendEmailAccessGroupRequestNotifications]: Cannot send notifications for access group id: ". $group->id);
				return;
		}
		//Since a user can belong to many access groups
		//prefer the following order: ardmins, managers and NILS
		$uniqmanagers = array();
		foreach($managers as $m){
			if( in_array($m, $admins) === false ){
				$uniqmanagers[] = $m;
			}
		}
		$uniqnils = array();
		foreach($nils as $n){
			if( in_array($n, $admins) === false && in_array($n, $managers) === false ){
				$uniqnils[] = $n;
			}
		}
		//load group data 
		$userguids = array();
		foreach($admins as $a){
			$userguids[] = array("groupid"=> "-1", "groupname"=> "AppDB Administrators", "userguid"=> $a);
		}
		foreach($uniqmanagers as $m){
			$userguids[] = array("groupid"=> "-2", "groupname"=> "Managers", "userguid"=> $m);
		}
		foreach($uniqnils as $n){
			$userguids[] = array("groupid"=> "-3", "groupname"=> "National Representatives", "payload"=> $payload, "userguid"=> $n);
		}
		
		$users = array();
		foreach($userguids as $u){
			$researchers = new Default_Model_Researchers();
			$researchers->filter->guid->equals($u["userguid"]);
			if( count($researchers->items) === 0 ){
				continue;
			}
			$researcher = $researchers->items[0];
			$user = $u;
			$user["id"] = $researcher->id;
			$user["user"] = $researcher;
			$user["countryname"] = $researcher->getCountry()->name;
			$contacts = new Default_Model_Contacts();
			$contacts->filter->isprimary->equals(true)->and($contacts->filter->researcherid->equals($researcher->id)->and($contacts->filter->contacttypeid->equals(7)));
			if( count($contacts->items) === 0){
				continue;
			}
			$contact = $contacts->items[0];
			$user["email"] = $contact->data;
			
			$users[] = $user;
		}
		return $users;
		
	}
	public static function getAccessGroupEmailTextBody($userdata, $group, $payloaddata=null){
		$user = $userdata["user"];
		$gname = $userdata["groupname"];
		if( trim($userdata["groupid"]) === "-3" && $payloaddata!==null){
			$gname .= " of " . $payloaddata;
		}
		$groupname = $group->name;
		if( $group->id == "-3" && trim($payloaddata) !== ""){
			$groupname .= " of " . $payloaddata;
		}
		$username = $user->firstname . " " . $user->lastname;
		$res  = "Dear " . $username . ",\n\n";
		
		$res .= "\tUser [1] has requested to be included in " . $groupname . " access group.\n";
				
		$res .= "Since you are included in the " . $gname .  " group, you are authorized either to accept or reject this request.\n";
		$res .= "In order to do so, please follow the steps bellow:\n";
		$res .= "\t* login to the [2]\n";
		$res .= "\t* go to your profile, by clicking your name on the upper right corner of the page\n" ;
		$res .= "\t* select 'Pending requests' tab.\n\n";
		
		$res .= "Best regards,\n";
		$res .= "EGI Applications Database team";
		
		return $res;
	}
	public static function sendEmailAccessGroupRequestNotifications($user, $group, $payload=null){
		$country = $user->getCountry();
		$countryid = $country->id;
		$countryname = $country->name;
		$recipients = self::getAccessGroupRecipients($group, $countryid);
		$username = $user->firstname . " " . $user->lastname;
		
		$subject = "EGI Applications Database: User request to join access group " . $group->name;
		foreach($recipients as $recipient){
			$textbody = self::getAccessGroupEmailTextBody($recipient, $group, $countryname);
			$body = preg_replace("/\n/", "<br/>", $textbody);
			$body = preg_replace("/\t/", "<span style='padding-left:10px;'></span>",$body);
			$body = preg_replace("/\[1\]/","<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/person/" . $user->cname . "' target='_blank' title='Visit person's entry in EGI AppDB'>" . $username . "</a>", $body);
			$body = preg_replace("/\[2\]/","<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "' target='_blank' title='Visit EGI Applications Database'>EGI Applications Database</a>",$body);
			$body = "<html><head></head><body>" . $body . "</body></html>";
			$textbody = preg_replace("/\t/", "   ",$textbody);
			$textbody = preg_replace("/\[1\]/", $username ." [1]",$textbody);
			$textbody = preg_replace("/\[2\]/", "EGI Applications Database [2]",$textbody);
			$textbody .= "\n\n________________________________________________________________________________________________________\n";
			$textbody .= "[1]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "/store/person/" . $user->cname . "\n";
			$textbody .= "[2]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "\n";
			//SEND TO APPLICATION OWNER
			if ( ApplicationConfiguration::isProductionInstance() === FALSE ) {
				error_log("SENDING TO: " . $recipient["email"] );
				error_log("SUBJECT: " . $subject);
				error_log("MESSAGE: " . $textbody);
			}else{
				//sendMultipartMail($subject,array($recipient["email"]), $textbody, $body, 'appdb-reports@iasa.gr', 'enadyskolopassword');
				EmailService::sendReport($subject, array($recipient["email"]), $textbody, $body);
			}
		}		
	}
	public static function getResponseAccessGroupsEmailBody($user, $group, $stateid){
		$state = ($stateid==2)?"accepted":"rejected";
		$username = $user->firstname . " " . $user->lastname;
		
		$res = "Dear " . $username . ",\n\n";
		$res .= "\twe inform you that the request you submitted to join " . $group->name . " access group has been " . $state . ".\n\n";
		
		if ( $stateid != 2 ) {
			$res .= "If you need further clarifications on this, please do not hesitate to contact us, by forwarding this email to either the EGI UCST team (ucst@egi.eu) or to the EGI AppDB team (appdb-support@iasa.gr)\n\n";
		}		
		$res .= "Best regards,\n";
		$res .= "EGI AppDB team";
		
		return $res;
	
	}
	//Build and send email to the requestor when a user accepts or rejects his request
	public static function sendEmailResponseAccessGroupsNotification($user,$group, $stateid=0){
		$recipient = self::getUserPrimaryEmail($user->id);
		$recipients = array();
		if( $recipient !== '' ){
			$recipients = array($recipient);
		}
		if(($stateid == 2 || $stateid == 3) && count($recipients)>0 ) {
			$state = ($stateid==2)?"Accepted":"Rejected";
			$subject = "EGI Applications Database: " . $state . " request to join " . $group->name . " access group response";
			
			$textbody = self::getResponseAccessGroupsEmailBody($user, $group, $stateid);
			$body = preg_replace("/\n/", "<br/>", $textbody);
			$body = preg_replace("/\t/", "<span style='padding-left:10px;'></span>",$body);
			$body = "<html><head></head><body>" . $body . "</body></html>";
			$textbody = preg_replace("/\t/", "   ",$textbody);
			if( ApplicationConfiguration::isProductionInstance() === FALSE ) {
				error_log("SENDING TO: " . $recipients );
				error_log("SUBJECT: " . $subject);
				error_log("MESSAGE: " . $textbody);
			}else{
				//sendMultipartMail($subject,$recipients, $textbody, $body, 'appdb-reports@iasa.gr', 'enadyskolopassword');
				EmailService::sendReport($subject, $recipients, $textbody, $body);
			}
		}
	}
	
	//Retrieve the primary email contact from a userid
	public static function getUserPrimaryEmail($userid){
		$emails = array();
		$contacts = new Default_Model_Contacts();
		$contacts->filter->isprimary->equals(true)->and($contacts->filter->researcherid->equals($userid));
		$conts = $contacts->items;
		foreach($conts as $i){
			if($i->data != '' && is_null($i->data) == false ){
				$emails[] = $i->data;
			}
		}
		return (count($emails)>0)?$emails[0]:'';
	}
	
	//Produce the text body of the email to send to the owner of the related application
	public static function getOwnerEmailTextBody($user,$app,$message,$type='joinapplication'){
		$researcher = $app->researcher;
		$owner = $researcher->firstname . " " . $researcher->lastname;
		
		$res  = "Dear " . $owner . ",\n\n";
		
		if( $type == 'joinapplication'){
			$res .= "\tUser [1] has requested to be included as a contact in the software [2].\n";
		}else if( $type == 'releasemanager'){
			$res .= "\tUser [1] has requested to be granted as a software release manager for the software [3].\n\n";
		}		
		$res .= "Since you are registered as the entry owner of this software, you may accept or reject this request.\n";
		$res .= "In order to do so, please follow the steps bellow::\n";
		$res .= "\t* login to the [3]\n";
		$res .= "\t* go to your profile, by clicking your name on the upper right corner of the page\n" ;
		$res .= "\t* select 'Pending requests' tab.\n\n";
		
		$res .= "Best regards,\n";
		$res .= "EGI Applications Database team";
		
		return $res;
	}
	
	//Produces the text body of the email to send to the related contacts(Managers,Admins)
	public static function getEmailTextBody($user,$app,$message, $type='joinapplication') {
		$res = "Dear AppDB Manager,\n\n";
		
		$res .= "This is an informative message from [1]. \n";
		$res .= "No action is required by you.\n\n";
		if( $type == 'joinapplication'){
			$res .= "User [2] requested to be included as a contact in the software [3] as a contact.\n\n";
		}else if( $type == 'releasemanager'){
			$res .= "User [2] requested to be granted as a software release manager for the software [3].\n\n";
		}
		$res .= "The software owner [4] has already been informed and been asked either to accept or reject the request.\n" ;
		$res .= "In case you would like to review the request, or override the respone, please follow the steps bellow:\n";
		$res .= "\t* login to the [1]\n";
		$res .= "\t* go to your profile, by clicking your name on the upper right corner of the page\n" ;
		$res .= "\t* select 'Pending requests' tab.\n\n";
		
		$res .= "Best regards,\n";
		$res .= "EGI Applications Database team";
		
		return $res;
	}
	
	//Build and send email notification 
	public static function sendEmailRequestNotifications($user,$app,$message,$type='joinapplication'){
		$recipients = array();
		$subject = "EGI Applications Database: User requests to join software " . $app->name;
		if( $type == 'releasemanager'){
			$subject = "EGI Applications Database: User requests to become " . $app->name . " release manager";
		}
		//SEND TO APPLICATION OWNER
		if ( $_SERVER["APPLICATION_UI_HOSTNAME"] !== "appdb.egi.eu" ){
			$recipients = self::getDevelopmentRecipients(self::getUserPrimaryEmail($user->id));//add requestor for testing development
		}else{
			$recipients[] = self::getUserPrimaryEmail($app->addedby);
		}
		$textbody = self::getOwnerEmailTextBody($user, $app, $message, $type);
		$body = preg_replace("/\n/", "<br/>", $textbody);
		$body = preg_replace("/\t/", "<span style='padding-left:10px;'></span>",$body);
		$body = preg_replace("/\[1\]/","<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/people/details?id=". $user->id) ."' target='_blank' title='Visit person's entry in EGI AppDB'>" . $user->firstname . " " . $user->lastname . "</a>", $body);
		$body = preg_replace("/\[2\]/","'<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/apps/details?id=".$app->id) ."' target='_blank' title='Visit software entry in EGI AppDB'>" . $app->name . "</a>'", $body);
		$body = preg_replace("/\[3\]/","<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "' target='_blank' title='Visit EGI Applications Database'>EGI Applications Database</a>",$body);
		$body = "<html><head></head><body>" . $body . "</body></html>";
		$textbody = preg_replace("/\t/", "   ",$textbody);
		$textbody = preg_replace("/\[1\]/", $user->firstname . " " . $user->lastname ." [1]",$textbody);
		$textbody = preg_replace("/\[2\]/", "'" . $app->name ."' [2]",$textbody);
		$textbody = preg_replace("/\[3\]/", "EGI Applications Database [3]",$textbody);
		$textbody .= "\n\n________________________________________________________________________________________________________\n";
		$textbody .= "[1]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/people/details?id=". $user->id) . "\n";
		$textbody .= "[2]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/apps/details?id=".$app->id) . " \n";
		$textbody .= "[3]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "\n";
		
		//SEND TO APPLICATION OWNER
		if ( ApplicationConfiguration::isProductionInstance() === FALSE ){
			error_log("SENDING TO: " . implode(" , ", $recipients) );
			error_log("SUBJECT: " . $subject );
			error_log("MESSAGE: " . $textbody);
		}else{
			//sendMultipartMail($subject,$recipients, $textbody, $body, 'appdb-reports@iasa.gr', 'enadyskolopassword');
			EmailService::sendReport($subject, $recipients, $textbody, $body);
		}
		//SEND TO MANAGERS AND ADMINISTRATORS
		if ( ApplicationConfiguration::isProductionInstance() === FALSE ){
			$recipients = self::getDevelopmentRecipients(self::getUserPrimaryEmail($user->id));//add requestor for testing development
		}else{
			$recipients = self::getRelatedEmails($app, $user->id);
		}
		
		$owner =  $app->researcher->firstname . " " . $app->researcher->lastname;
		$ownerid = $app->researcher->id;
		$textbody = self::getEmailTextBody($user, $app, $message, $type);
		$body = preg_replace("/\n/", "<br/>", $textbody);
		$body = preg_replace("/\t/", "<span style='padding-left:10px;'></span>",$body);
		$body = preg_replace("/\[1\]/","<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "' target='_blank' title='Visit EGI Applications Database'>EGI Applications Database</a>",$body);
		$body = preg_replace("/\[2\]/","<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/people/details?id=". $user->id) ."' target='_blank' title='Visit person's entry in EGI AppDB'>" . $user->firstname . " " . $user->lastname . "</a>", $body);
		$body = preg_replace("/\[3\]/","'<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/apps/details?id=".$app->id) ."' target='_blank' title='Visit software entry in EGI AppDB'>" . $app->name . "</a>'", $body);
		$body = preg_replace("/\[4\]/","<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/people/details?id=". $ownerid) . "' target='_blank' title='Visit owner's entry in EGI AppDB'>" . $owner  ."</a>",$body);
		$body = "<html><head></head><body>" . $body . "</body></html>";
		$textbody = preg_replace("/\t/", "   ",$textbody);	
		$textbody = preg_replace("/\[1\]/", "EGI Applications Database [1]",$textbody);
		$textbody = preg_replace("/\[2\]/", $user->firstname . " " . $user->lastname ." [2]",$textbody);
		$textbody = preg_replace("/\[3\]/", "'" . $app->name ."' [3]",$textbody);
		$textbody = preg_replace("/\[4\]/", $owner ." [4]",$textbody);
				
		$textbody .= "\n\n________________________________________________________________________________________________________\n";
		$textbody .= "[1]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "\n";
		$textbody .= "[2]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/people/details?id=". $user->id) . "\n";
		$textbody .= "[3]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/apps/details?id=".$app->id) . " \n";
		$textbody .= "[4]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/people/details?id=".$ownerid) . " \n";
		
		//SEND TO APPLICATION OWNER
		if ( ApplicationConfiguration::isProductionInstance() === FALSE ) {
			error_log("SENDING TO: " . implode(" , ", $recipients) );
			error_log("SUBJECT: " . $subject );
			error_log("MESSAGE: " . $textbody);
		} else {
			//sendMultipartMail($subject,$recipients, $textbody, $body, 'appdb-reports@iasa.gr', 'enadyskolopassword');
			EmailService::sendReport($subject, $recipients, $textbody, $body);
		}
	}
	
	//Build the text body of the email for responding to an action over a user request.
	public static function getResponseEmailBody($user, $app, $stateid, $type='joinapplication'){
		$state = ($stateid==2)?"accepted":"rejected";
		$username = $user->firstname . " " . $user->lastname;
		
		$res = "Dear " . $username . ",\n\n";
		if( $type == 'joinapplication'){
			$res .= "\twe inform you that the request you submitted to join software [1] has been " . $state . ".\n\n";
		}else if( $type == 'releasemanager'){
			$res .= "\twe inform you that the request you submitted to be granted as a software release manager for software [1] has been " . $state . ".\n\n";
		}
		if ( $stateid != 2 ) {
			$res .= "If you need further clarifications on this, please do not hesitate to contact us, by forwarding this email to either the EGI UCST team (ucst@egi.eu) or to the EGI AppDB team (appdb-support@iasa.gr)\n\n";
		}		
		$res .= "Best regards,\n";
		$res .= "EGI AppDB team";
		
		return $res;
	}
	
	//Build and send email to the requestor when a user accepts or rejects his request
	public static function sendEmailResponseNotification($user,$app, $stateid=0, $type='joinapplication'){
		$recipients = self::getUserPrimaryEmail($user->id);
		if(($stateid == 2 || $stateid == 3) && count($recipients)>0 ) {
			$state = ($stateid==2)?"Accepted":"Rejected";
			$subject = "EGI Applications Database: " . $state . " request to join software " . $app->name . " response";
			if($type == 'releasemanager'){
				$subject = "EGI Applications Database: " . $state . " request to manage releases for software " . $app->name . " response";
			}
			$textbody = self::getResponseEmailBody($user, $app, $stateid, $type);
			$body = preg_replace("/\n/", "<br/>", $textbody);
			$body = preg_replace("/\t/", "<span style='padding-left:10px;'></span>",$body);
			$body = preg_replace("/\[1\]/", "'<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/apps/details?id=".$app->id) . "' target='_blank' title='View software entry in EGI AppDB'>" . $app->name . "</a>'", $body);
			$body = "<html><head></head><body>" . $body . "</body></html>";
			$textbody = preg_replace("/\t/", "   ",$textbody);
			$textbody = preg_replace("/\[1\]/","'" . $app->name . "' [1]",$textbody);
			$textbody .= "\n\n________________________________________________________________________________________________________\n";
			$textbody .= "[1]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/apps/details?id=".$app->id) . " \n";
			if( ApplicationConfiguration::isProductionInstance() === FALSE ) {
				error_log("SENDING TO: " . $recipients );
				error_log("SUBJECT: " . $subject);
				error_log("MESSAGE: " . $textbody);
			}
			//sendMultipartMail($subject,$recipients, $textbody, $body, 'appdb-reports@iasa.gr', 'enadyskolopassword');
			EmailService::sendReport($subject, $recipients, $textbody, $body);
		}
	}
}
?>
