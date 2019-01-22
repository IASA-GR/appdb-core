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

require_once('email_service.php');

class APIKeyRequests{
	public static function getMailBody($user,$apikey, $message){
		$res = "";
		$res .= "User [1] requests permissions regarding API key [2]\n";
		$res .= "=====================================================\n\n";
		
		$res .= $message;
		
		return $res;
	}
	
	public static function sendPermissionsRequest($userid, $apikeyid, $msg){
		//Get sender
		$users = new Default_Model_Researchers();
		$users->filter->id->equals($userid);
		if( $users->count() == 0 ) {
			return "Sender not found";
		}
		$user = $users->items[0];
		
		//Get sender's primary email
		$contacts = new Default_Model_Contacts();
		$contacts->filter->isprimary->equals(true)->and($contacts->filter->researcherid->equals($userid));
		if( $contacts->count() == 0 ) {
			return "Sender has no primary e-mail set";
		}
		
		//Check if e-mail has value
		$useremail = $contacts->items[0]->data;
		if( trim($useremail) == '' ){
			return "Sender has no valid primary e-mail set";
		}
		
		//Get api key
		$apikeys = new Default_Model_APIKeys();
		$apikeys->filter->id->equals($apikeyid)->and($apikeys->filter->ownerid->equals($userid));
		if( count($apikeys) == 0 ) {
			return "Api key not found";
		}
		$apikey = $apikeys->items[0];
		
		//Get Appdb administrators
		$recipients = array();
		$admins = new Default_Model_Researchers();
		$agmf = new Default_Model_ActorGroupMembersFilter();
		$agmf->groupid->numequals(-1); // admins
		$admins->filter->chain($agmf, "AND");
		if( count($admins->items) == 0 ) {
			return "";
		}
		//Get admins primary emails
		$admins = $admins->items;
		foreach($admins as $admin){
			$contacts = new Default_Model_Contacts();
			$contacts->filter->isprimary->equals(true)->and($contacts->filter->researcherid->equals($admin->id))->and($contacts->filter->contacttypeid->equals(7));
			if( count($contacts->items) == 0 ) {
				continue;
			}
			if(trim($contacts->items[0]->data) !== ''){
				$recipients[] = $contacts->items[0]->data;
			}
		}
		$recipients = array_unique($recipients);
		if( count($recipients) == 0 ){
			return "";
		}
		
		$textbody = self::getMailBody($user, $apikey, $msg);
		//Get text body and also set html body
		$body = preg_replace("/\</","&lt;", $textbody);
		$body = preg_replace("/\>/","&gt;", $body);
		$body = preg_replace("/\n/", "<br/>", $body);
		$body = preg_replace("/\t/", "<span style='padding-left:10px;'></span>", $body);
		$body = preg_replace("/\[1\]/", "<a href='http://" .  $_SERVER[APPLICATION_UI_HOSTNAME'] . "?p=" . base64_encode("/people/details?id=".$user->id) . "' target='_blank' title='View person's entry in EGI AppDB' >" . $user->firstname . " " . $user->lastname . "</a>"   , $body);
		$body = preg_replace("/\[2\]/", "<b>" . $apikey->key . "</b>", $body);
		$body = "<html><head></head><body>" . $body . "</body></html>";
		
		$textbody = preg_replace("/\t/", "   ", $textbody);
		$textbody = preg_replace("/\[1\]/", $user->firstname . " " . $user->lastname . " [id: " . $user->id . ", url: http://" .  $_SERVER['APPLICATION_UI_HOSTNAME'] . "?p=" . base64_encode("/people/details?id=".$user->id) . "]", $textbody);
		$textbody = preg_replace("/\[2\]/", $apikey->key, $textbody);
		
		$subject = "EGI AppDB: API Permissions request from user " . $user->firstname . " " . $user->lastname;
		
		//sendMultipartMail($subject,$recipients, $textbody, $body, 'appdb-reports@iasa.gr', 'enadyskolopassword',$useremail);
		EmailService::sendReport($subject, $recipients, $textbody, $body, $useremail);
		return true;
	}
}
?>
