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

class ApplicationMessage {
	public static function getTextBody($app, $user, $useremail, $recipient, $message){
		$res = "";
		$res .= "The [1] user, [2] sent you the message bellow\n";
		$res .= "regarding the software [3].\n";
		$res .= "If you would like to send a reply, then please do so by directly using\n";
		$res .= "his/her personal email address: [ " . $useremail . " ]\n";
		$res .= "\n\n";
		
		$res .= $message;
		
		return $res;
	}
	public static function sendMessage($appid,$userid,$recipientid,$message){
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
		$useremail = $contacts->items[0]->data;
		
		//Get recipient
		$recipients = new Default_Model_Researchers();
		$recipients->filter->id->equals($recipientid);
		if( $recipients->count() == 0 ) { 
			return "Recipient not found";
		}
		$recipient = $recipients->items[0];
		
		//Get recipient's primary email
		$contacts = new Default_Model_Contacts();
		$contacts->filter->isprimary->equals(true)->and($contacts->filter->researcherid->equals($recipientid));
		if( $contacts->count() == 0 ) {
			return "Recipient has no primary e-mail set";
		}
		$recipientmail = $contacts->items[0]->data;
		
		//Get application
		$applications = new Default_Model_Applications();
		$applications->filter->id->equals($appid);
		if( $applications->count() == 0 ) {
			return "Software not found";
		}
		$app = $applications->items[0];
		
		//Decode message
		$message = base64_decode($message);
		if( strlen(trim($message)) === 0) {
			return "Message is empty";
		}
		
		//Get text body and also set html body
		$textbody = self::getTextBody($app,$user,$useremail,$recipient,$message);
		$body = preg_replace("/\</","&lt;",$textbody);
		$body = preg_replace("/\>/","&gt;",$body);
		$body = preg_replace("/\n/", "<br/>", $body);
		$body = preg_replace("/\t/", "<span style='padding-left:10px;'></span>",$body);
		$body = preg_replace("/\[1\]/", "<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "' target='_blank' title='Visit EGI Applications Database' >EGI AppDB</a>"   , $body);
		$body = preg_replace("/\[2\]/", "<a href='http://" .  $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("//people/details?id=".$user->id) . "' target='_blank' title='View person's entry in EGI AppDB' >" . $user->firstname . " " . $user->lastname . "</a>"   , $body);
		$body = preg_replace("/\[3\]/", "'<a href='http://" .  $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/apps/details?id=".$app->id) . "' target='_blank' title='View software entry in EGI AppDB' >" . $app->name . "</a>'"   , $body);
		$body = "<html><head></head><body>" . $body . "</body></html>";
		
		$textbody = preg_replace("/\t/", "   ",$textbody);
		$textbody = preg_replace("/\[1\]/", "EGI AppDB [1]",$textbody);
		$textbody = preg_replace("/\[2\]/", $user->firstname ." " . $user->lastname . " [2]", $textbody);
		$textbody = preg_replace("/\[3\]/", "'" . $app->name . "' [3]",$textbody);
		$textbody .= "\n\n________________________________________________________________________________________________________\n";
		$textbody .= "[1]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "\n";
		$textbody .= "[2]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/people/details?id=". $user->id) . "\n";
		$textbody .= "[3]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/apps/details?id=".$app->id) . " \n";
		
		$subject = "EGI Applications Database: " . $user->firstname . " " . $user->lastname . " sent you a message";
		
		if(trim($useremail) === ''){
			$useremail = false;
		}
		//sendMultipartMail($subject,$recipientmail, $textbody, $body, 'appdb-reports@iasa.gr', 'enadyskolopassword',$useremail, null, false, array("From"=>$useremail));
		EmailService::sendReportAsUser($useremail, $subject, $recipientmail, $textbody, $body);
	}
}
?>
