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

class ExternalDataNotification {
	const MESSAGE_TYPE_ERROR = 0;
	const MESSAGE_TYPE_WARNING = 1;
	
	private static function getRecipients(){
		return array(EmailConfiguration::getSupportAddress());
	}
	
	public static function createNotificationMessage($serviceName, $message, $message_type) {
		$res["subject"] = "[APPDB SERVICE";
		$body = "";
		if( $message_type === ExternalDataNotification::MESSAGE_TYPE_ERROR ) {
			$res["subject"] .= ' ERROR] From ' . $serviceName . ' service';
			$body = "An error occured on service " . $serviceName . " with message: \n\n";
		} else {
			$res["subject"] .= ' WARNING] From ' . $serviceName . ' service';
			$body = "A warning is raised from service " . $serviceName . " with message: \n\n";
		}
		
		if( trim($message) === '' ) {
			$body .= '[EMPTY MESSAGE]';
		} else {
			$body .= $message;
		}
		
		$res["message"] = $body;
		
		return $res;
	}
	
	public static function sendNotification($serviceName, $message="", $message_type = ExternalDataNotification::TYPE_ERROR){
		$recipients = self::getRecipients();
		$res = self::createNotificationMessage($serviceName, $message, $message_type);
		
		//sendMultipartMail($res['subject'], $recipients, $res['message'], null, 'appdb-reports@iasa.gr', 'enadyskolopassword');
		EmailService::sendReport($res['subject'], $recipients, $res['message']);
		return true;
	}
}
?>
