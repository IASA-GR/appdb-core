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

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AbuseController extends AbstractActionController
{
	# reson enum
	const REASON_OTHER = 0;
	const REASON_COPYRIGHT_ISSUE = 1;
	const REASON_INAPPROPRIATE_CONTENT = 2;
	const REASON_INVALID_DATA = 3;
	const REASON_OUT_OF_DATE = 4;
	const REASON_BROKEN_LINK = 5;
	const REASON_SPELLING = 6;
	
	public function __construct() {
		$this->session = new \Zend\Session\Container('base');
	}

	public function indexAction() {
		return DISABLE_LAYOUT($this);
	}

	public function moderatecommentAction() {
		if ($this->session->userid !== null) {
			if (userIsAdminOrManager($this->session->userid)) {
				$id = GET_REQUEST_PARAM($this, "id");
				if ( GET_REQUEST_PARAM($this, "moderate") == 0 ) $moderate = "0"; else $moderate = "1";
				$ratings = new Default_Model_AppRatings();
				$ratings->filter->id->equals($id);
				if ( count($ratings->items) > 0 ) {
					$rating = $ratings->items[0];
					$rating->moderated = $moderate;
					$rating->save();
					echo '{"id":"'.$rating->id.'","comment":"'.base64_encode($rating->comment).'"}';
				}
			}
		}
		return DISABLE_LAYOUT($this);
	}

	public function reportAction() {
		return DISABLE_LAYOUT($this);
	}

	public function submitAction() {
		if ($this->session->userid !== null) {
			$type = GET_REQUEST_PARAM($this, "type");
			$entryID = GET_REQUEST_PARAM($this, "entryID");
			$comment = GET_REQUEST_PARAM($this, "comment");
			$reason = GET_REQUEST_PARAM($this, "reason");
			switch($reason) {
				case self::REASON_OTHER:
					$reason_str = 'Other';
					break;
				case self::REASON_COPYRIGHT_ISSUE:
					$reason_str = 'Copyright Issue';
					break;
				case self::REASON_INAPPROPRIATE_CONTENT:
					$reason_str = 'Inappropriate Content';
					break;
				case self::REASON_INVALID_DATA:
					$reason_str = 'Invalid or False Data';
					break;
				case self::REASON_OUT_OF_DATE:
					$reason_str = 'Out of Date Information';
					break;
				case self::REASON_BROKEN_LINK:
					$reason_str = 'Broken Link';
					break;
				case self::REASON_SPELLING:
					$reason_str = 'Typo/Misspelling';
					break;
				default:
					$reason_str = 'Other';
					break;
			};
			$offender = "id=$entryID";
			$subject = "AppDB report";
			if ( ApplicationConfiguration::isEnviroment("production") ) {
				$to = EmailConfiguration::getList('ucst');
			} else {
				$to = EmailConfiguration::getList('debug');
			}
			$offenderApp = "";
			$body =	"EGI Applications Database Abuse Report \n\n".
				"A user has submitted a report concerning a".(in_array(strtolower(substr($type,0,1)),array('a','e','i','o','u'))?"n":"")." ".$type."\n\n";
			$body  = "--------------------------------------------------\n";
			$body .= "Please do not reply, this is an automated message.\n";
			$body .= "--------------------------------------------------\n\n";
			if ( $type == "application" ) {
				$subject .= " - problem on content";
				$body .= "EGI Applications Database problem report on content \n\n";
				$body .= "A user has submitted a problem report concerning the software ";
				
				$apps = new Application\Model\Applications();
				$apps->filter->id->equals($entryID);
				if ( count($apps->items) > 0 ) {
					$offender = $apps->items[0]->name.' (http://'.$_SERVER['HTTP_HOST'].'/?p='.base64_encode('/apps/details?id='.$apps->items[0]->id).")";
					$body .= $apps->items[0]->name . " with id: " . $apps->items[0]->id;
				}

			} elseif ( $type == "comment" ) {
				$subject .= " - abuse on comment";
				$body .= "EGI Applications Database abuse report on a comment\n\n";
				$body .= "A user has submitted an abuse report concerning the software ";
				$aprs = new Default_Model_AppRatings();
				$aprs->filter->id->equals($entryID);
				if ( count($aprs->items) > 0 ) {
					$cid = $apps->items[0]->id;
					$apps = new Application\Model\Applications();
					$apps->filter->id->equals($aprs->items[0]->appid);
					if ( count($apps->items[0]) > 0 ) {
						$body .= $apps->items[0]->name . " with id: " . $apps->items[0]->id;
						$rs = new Default_Model_Researchers();
						$rs->filter->id->equals($aprs->items[0]->submitterid);
						if ( count($rs->items) > 0 ) { 
							$commentPersonName = $rs->items[0]->firstname.' '.$rs->items[0]->lastname;
						} else $commentPersonName = 'a guest user';
						$commentDate = new DateTime($aprs->items[0]->submittedon);
						$commentDate = $commentDate->format('Y-m-d H:i');
						$offenderApp = "Offensive software entry: " . $apps->items[0]->name.' (http://'.$_SERVER['HTTP_HOST'].'/?p='.base64_encode('/apps/details?id='.$apps->items[0]->id).")\n";
						$offender = "submitted by $commentPersonName on $commentDate (Comment ID: $entryID)";
					}
				}
			}
			$body = $body. "\n\n";
			$body .= $offenderApp .
				"Offensive ".$type." entry: ".$offender."\n".
				'Submitter: '.$this->session->fullName.' (http://'.$_SERVER['HTTP_HOST'].'/?p='.base64_encode('/people/details?id='.$this->session->userid).")\n".
				"Reason: ".$reason_str."\n\n".
				"Description: ".$comment."\n";
			//sendMultipartMail($subject, $to, $body,"<pre>".$body."</pre>", 'appdb-reports@iasa.gr', 'enadyskolopassword');
			EmailService::sendReport($subject, $to, $body, "<pre>".$body."</pre>");
		}
		return DISABLE_LAYOUT($this, true);
	}

}
