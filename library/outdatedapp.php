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

class OutdatedApplication{
	public static function getTemplateBody($ownername){
		$res = "Dear " . $ownername .",\n";
		$res .= "\n";
		$res .= "According to our records the profile of your software [1] in the \n";
		$res .= "[2] has not been updated during the past ".$_SERVER["validation_period"]."\n";
		$res .= "or more. This is an invitation to you to login to the Applications Database \n";
		$res .= "and update the profile of your software or to confirm that the profile is \n";
		$res .= "still up to date. Both the update and the validation can be done on the \n";
		$res .= "profile page of your software.\n";
		$res .= "Your cooperation would help us in the maintenance of the database and\n";
		$res .= "would help the users distinguish up to date information from outdated\n";
		$res .= "entries.\n";
		$res .= "\n";
		$res .= "Best regards,\n";
		$res .= "The AppDB development team\n";
		return $res;
	}
	public static function getTemplateListBody($ownername){
		$res = "Dear " . $ownername .",\n";
		$res .= "\n";
		$res .= "According to our records the profiles of your [1] listed bellow in the \n";
		$res .= "[2] have not been updated during the past ".$_SERVER["validation_period"]."\n";
		$res .= "or more.\n\n";
		$res .= "[*]";
		$res .= "\n";
		$res .= "This is an invitation to you to login to the Applications Database \n";
		$res .= "and update the profiles of your software or to confirm that the profiles are \n";
		$res .= "still up to date. Both the update and the validation can be done on the \n";
		$res .= "profile pages of your software.\n";
		$res .= "Your cooperation would help us in the maintenance of the database and\n";
		$res .= "would help the users distinguish up to date information from outdated\n";
		$res .= "entries.\n";
		$res .= "\n";
		$res .= "Best regards,\n";
		$res .= "The AppDB development team\n";
		return $res;
	}
	
	public static function sendMessages($isReminder = false){
		$items = db()->query("SELECT * FROM nonvalidated_apps_per_owner")->fetchAll();
		foreach($items as $item){
			$text = "";
			$body = "";
			if(is_null($item["contact"])){
				error_log("[OutdatedApps]: No contact info for " . $item["ownerid"] .":" . $item["firstname"] . " " . $item["lastname"] );
				//TODO: Case where the owner of the application has no contact point
				continue;
			}
			$recipients = array($item["contact"]);
			$subject = "Notification:";
			if(isnull($item["lastsent"]) == false) {
				$subject = "Reminder:";
			}
			$subject .= "EGI AppDB outdated software profile";
			
			$appids = $item["appids"];
			$appids = explode(";", $appids);
			$appnames = $item["appnames"];
			$appnames = explode(";", $appnames);
			if( count($appids) == 0 ) continue;
			if( count($appids) == 1 ) {
				$template = self::getTemplateBody($item["firstname"] . " " . $item["lastname"]);
				$body = preg_replace("/\[1\]/","<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/apps/details?id=".$appids[0]) . "' target='_blank'>".$appnames[0]."</a>",$template);
				$body = preg_replace("/\[2\]/","<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"]."' target='_blank'>EGI Applications Database</a>", $body);
				$body = "<html><head></head><body><pre>" . $body . "</pre></body></html>";
				$text = preg_replace("/\[1\]/","" . $appnames[0] . " [1]",$template);
				$text = preg_replace("/\[2\]/", "EGI Applications Database [2]",$text);
				$text .= "\n\n________________________________________________________________________________________________________\n";
				$text .= "[1]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/apps/details?id=".$appids[0]) . "\n";
				$text .= "[2]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"];
			} else {
				$template = self::getTemplateListBody($item["firstname"] . " " . $item["lastname"]);
				$listpermalink = "http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode('{"url":"/apps","query":{"flt":"=application.owner:' . $item["ownerid"] . ' +=application.validated:false"},"ext":{"isBaseQuery":true,"append":true,"filterDisplay":"Search outdated...","mainTitle":"Outdated entries"}}');
				$body = preg_replace("/\[1\]/","<a href='".$listpermalink."' target='_blank'>software</a>",$template);
				$body = preg_replace("/\[2\]/","<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"]."' target='_blank'>EGI Applications Database</a>", $body);
				$body = "<html><head></head><body><pre>" . $body . "</pre></body></html>";
				$text = preg_replace("/\[1\]/","software [1]",$template);
				$text = preg_replace("/\[2\]/", "EGI Applications Database [2]",$text);
				$text .= "\n\n________________________________________________________________________________________________________\n";
				$text .= "[1]. ".$listpermalink." \n";
				$text .= "[2]. http://" . $_SERVER["APPLICATION_UI_HOSTNAME"];
				
				$bodylist = "";
				$textlist = "";
				
				//Make unique arrays of application ids and names
				$unames = array();
				$uids = array();
				for($i=0; $i<count($appids); $i+=1){
					if( in_array($appids[$i], $uids) === false ){
						$uids[] = $appids[$i];
						$unames[] = $appnames[$i];
					}
				}
				$appids = $uids;
				$appnames = $unames;
				
				for($i=0; $i<count($appids); $i+=1){
					$bodylist .= "<div style='padding-left:10px;'>-<a href='http://" . $_SERVER["APPLICATION_UI_HOSTNAME"] . "?p=" . base64_encode("/apps/details?id=".$appids[$i]) . "' target='_blank'>". $appnames[$i] . "</a></div>";
					$textlist .= "\t- " . $appnames[$i] . "\n";
				}
				$body = preg_replace("/\[\*\]/", $bodylist, $body);
				$text = preg_replace("/\[\*\]/", $textlist, $text);
			}
			
			if( ApplicationConfiguration::isProductionInstance() === FALSE ) {
				error_log("SENDING OUTDATED TO:");
				error_log("SUBJECT:" . $subject);
				error_log("RECIPIENTS: " . var_export($recipients,true));
				error_log("BODY: " . $text);
				echo "<div style='background-color:lightgrey;border:1px solid black'><b>subject:</b>".$subject."</div><div style='background-color:lightgrey;margin-bottom:10px;border:1px solid black'><b>TO:</b>".implode(",",$recipients)."</div><div style='background-color:lightgreen;border:1px solid black;'>". $body . "</div><div style='background-color:#99DBFF;margin-bottom:10px;border:1px solid black'><pre>" . $text . "</pre></div>";	
			} else {
				//sendMultipartMail($subject,$recipients,$text,$body,'appdb-reports@iasa.gr','enadyskolopassword','appdb-support@iasa.gr',null, false,array("Precedence"=>"bulk"));
				EmailService::sendBulkReport($subject, $recipients, $text, $body, EmailConfiguration::getSupportAddress());
			}
		}
		if ( APPLICATION_ENV === "production" ) db()->query("INSERT INTO app_validation_log (appid) SELECT DISTINCT id FROM applications, (SELECT string_to_array(array_to_string(array_agg(array_to_string(ids,',')),','),',') as ids FROM (SELECT string_to_array(appids, ';') as ids FROM (SELECT * FROM nonvalidated_apps_per_owner) AS t) as tt) as ttt WHERE id::text = ANY(ttt.ids)")->fetchAll();
		return true;
	}
}
?>
