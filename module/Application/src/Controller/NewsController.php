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

class NewsController extends AbstractActionController
{
    public function __construct()
    {
		$this->view = new ViewModel();
        $this->session = new \Zend\Session\Container('base');
		if (strtoupper($this->getRequest()->getMethod()) == "GET") {
			if ($this->session->getManager()->getStorage()->isLocked()) {
				$this->session->getManager()->getStorage()->unLock();
			}
			session_write_close();
		}
    }

    public function indexAction() {
		return DISABLE_LAYOUT($this);
    }

    public function news2Action()
    {
	}

	private function getnews() {
		$docs = new Default_Model_AppDocuments();
        $this->view->docs = $docs->refresh();
        $apps = new Application\Model\Applications();
        $this->view->apps = $apps->refresh();
        $ppl = new Default_Model_Researchers();
        $this->view->ppl = $ppl->refresh();
		$news = new Default_Model_AggregateNews();
		$len = GET_REQUEST_PARAM($this, 'len');
		$ofs = GET_REQUEST_PARAM($this, 'ofs');
		if ( $ofs == '' ) $ofs=0;
		$news->filter->limit($len);
		$news->filter->offset($ofs);
		$f = new Default_Model_AggregateNewsFilter();
		if ( trim(GET_REQUEST_PARAM($this, 'event')) != '') {
			$f->action->equals(trim(GET_REQUEST_PARAM($this, 'event')));
			$news->filter->chain($f,"AND");
		}
		if ( trim(GET_REQUEST_PARAM($this, 'filter')) != '') {
			$flt = trim(GET_REQUEST_PARAM($this, 'filter'));
			if( $flt === "vapp" ) {
				$flt = "app";
			}

			$f->subjecttype->equals($flt);
			$news->filter->chain($f,"AND");
			if( trim(GET_REQUEST_PARAM($this, 'filter')) === "vapp" ) {
				$vappflt = new Application\Model\ApplicationsFilter();
				$vappflt->metatype->numequals(1);
				$news->filter->chain($vappflt,"AND");
			} elseif (trim(GET_REQUEST_PARAM($this, 'filter')) === "app") {
				$vappflt = new Application\Model\ApplicationsFilter();
				$vappflt->metatype->numequals(0);
				$news->filter->chain($vappflt,"AND");
			}
		}
		if ( GET_REQUEST_PARAM($this, 'from') != '' ) {
			$from = "EXTRACT(EPOCH FROM '".GET_REQUEST_PARAM($this, 'from')."'::timestamp)";
		} else {
			$from = '0';
		}
		if ( GET_REQUEST_PARAM($this, 'to') != '' ) {
			$to = "EXTRACT(EPOCH FROM '".GET_REQUEST_PARAM($this, 'to')."'::timestamp)";
		} else {
			$to = 'EXTRACT(EPOCH FROM NOW())';
		}
		$f->timestamp->between(array($from,$to));
		$news->filter->chain($f,"AND");
		$f->action->notequals("delete");
		$news->filter->chain($f,"AND");
		debug_log('[NewsController::getnews]: ' . $news->filter->expr());
		$news->filter->orderBy('timestamp DESC');
		$this->view->entries = $news->refresh();
		$this->view->event = GET_REQUEST_PARAM($this, 'event');
		$this->view->filter = GET_REQUEST_PARAM($this, 'filter');
		$this->view->from = GET_REQUEST_PARAM($this, 'from');
		$this->view->to = GET_REQUEST_PARAM($this, 'to');
	}

	private function getnews2() {
		$docs = new Default_Model_AppDocuments();
        $this->view->docs = $docs->refresh();
        $apps = new Application\Model\Applications();
        $this->view->apps = $apps->refresh();
        $ppl = new Default_Model_Researchers();
        $this->view->ppl = $ppl->refresh();
		$news = new Default_Model_MyNews();
		$len = GET_REQUEST_PARAM($this, 'len');
		$ofs = GET_REQUEST_PARAM($this, 'ofs');
		if ( $ofs == '' ) $ofs=0;
		$news->limit = $len;
		$news->offset = $ofs;
		$fields = array();
		$fields[] = 'subjecttype';
		$news->filter->_fields = $fields;
        if ( trim(GET_REQUEST_PARAM($this, 'event')) != '') $news->event = trim(GET_REQUEST_PARAM($this, 'event'));
        if ( trim(GET_REQUEST_PARAM($this, 'filter')) != '') $news->filter->subjecttype->equals(trim(GET_REQUEST_PARAM($this, 'filter')));
		if ( GET_REQUEST_PARAM($this, 'from') != '' ) $news->from = GET_REQUEST_PARAM($this, 'from');
		if ( GET_REQUEST_PARAM($this, 'to') != '' ) $news->to = GET_REQUEST_PARAM($this, 'to');
		$this->view->entries = $news->refresh();
		$this->view->event = GET_REQUEST_PARAM($this, 'event');
		$this->view->filter = GET_REQUEST_PARAM($this, 'filter');
		$this->view->from = GET_REQUEST_PARAM($this, 'from');
		$this->view->to = GET_REQUEST_PARAM($this, 'to');
	}

	public function reportAction()
	{
		if ( userIsAdminOrManager($this->session->userid) || userIsNIL($this->session->userid, $this->session->userCountryID) ) {
			if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
			} elseif ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
				$this->getnews();
			}
			return DISABLE_LAYOUT($this);
		} else {
			DISABLE_LAYOUT($this);
			return SET_NO_RENDER($this, " ");
		}
	}

    public function newsAction()
    {
		if ( $this->session->userid !== null ) {
			$this->getnews();
		}
		return DISABLE_LAYOUT($this);
	}

	public function atomAction() {
        $r = NewsFeed::parseUrl();
		if( $r->type === "app" ){
			if(in_array("update", $r->action) === true ){
				$r->action[] = "updaterel";
				$r->action[] = "updatevav";
			}
			if( in_array("insert", $r->action) === true ){
				$r->action[] = "insertrel";
				$r->action[] = "insertvav";
			}
		} else {
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 404 Not Found");
			$this->getResponse()->setHeader("Status","404 Not Found");
			DISABLE_LAYOUT($this);
			return SET_NO_RENDER($this, NULL, 404); 
		}
        $r->length = 10;
		$news = NewsFeed::getNews($r);
		$this->view->request = $r;
		$this->view->count = $news->count();
		$this->view->entries = $news->items;
		return DISABLE_LAYOUT($this);
		
	}

    public function rssAction(){
        $r = NewsFeed::parseUrl();
		if( $r->type === "app" ){
			if(in_array("update", $r->action) === true ){
				$r->action[] = "updaterel";
				$r->action[] = "updatevav";
			}
			if( in_array("insert", $r->action) === true ){
				$r->action[] = "insertrel";
				$r->action[] = "insertvav";
			}
		}
        $r->length = 10;
		$news = NewsFeed::getNews($r);
		$this->view->request = $r;
		$this->view->count = $news->count();
		$this->view->entries = $news->items;
		return DISABLE_LAYOUT($this);
	}

	public function unsubscribeallAction() {
		$this->view->result = "error";
		$pwd = GET_REQUEST_PARAM($this, 'pwd');
		$id = GET_REQUEST_PARAM($this, 'id');
        $delivery = GET_REQUEST_PARAM($this, 'delivery');
        $delivery = ($delivery=='')?-1:intval($delivery);
        $validated = false;
		if ( ($id != '') && ($pwd != '') ) {
			$rs = new Default_Model_Researchers();
			$rs->filter->id->equals($id);
			if ( count($rs->items) > 0 ) {
                if(md5($rs->items[0]->mailUnsubscribePwd) == $pwd){
                    $validated = true;
                    $mail = new Default_Model_MailSubscriptions();
                    if($delivery!=-1){
                        $mail->filter->researcherid->equals($id)->and($mail->filter->delivery->hasbit($delivery));
                        $items = $mail->items;
                        foreach ( $items as $i ) {
                            if($i->delivery===$delivery){
                                $mail->remove($i);
                            }else{
                                $i->delivery = $i->delivery ^ $delivery;
                                $i->save();
                            }
                        }
                    }else{
                        $mail->filter->researcherid->equals($id);
                        $items = $mail->items;
                        foreach ( $items as $i ) {
                            $mail->remove($i);
                        }
                    }
                    $this->view->result = "ok";
                }
			}else{
                $this->view->result = "none";
            }
			if (($validated !== true) &&($this->view->result == "error")) {
				$this->getResponse()->clearAllHeaders();
				$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
				$this->getResponse()->setHeader("Status","403 Forbidden");
				DISABLE_LAYOUT($this, true);
				return SET_NO_RENDER($this, NULL, 403);
            }
             $this->view->server = "http://". $_SERVER['APPLICATION_UI_HOSTNAME'] . "/";
             $this->view->digest = null;
             if($delivery!=''){
                 switch($delivery){
                     case '2':
                         $this->view->digest = "daily";
                         break;
                     case '4':
                         $this->view->digest = "weekly";
                         break;
                     case '8':
                         $this->view->digest = "monthly";
                         break;
                     default :
                         $this->view->digest = null;
                         break;
                 }
             }
		}
		return DISABLE_LAYOUT($this);
	}

	public function unsubscribeAction() {
		$pwd = GET_REQUEST_PARAM($this, 'pwd');
        $delivery = GET_REQUEST_PARAM($this, 'delivery');
        $delivery = ($delivery=='')?-1:intval($delivery);
		$validated = false;
        $result = "error";
		$mail = new Default_Model_MailSubscriptions();
		$mail->filter->id->equals(GET_REQUEST_PARAM($this, 'id'));
		if ( count($mail->items) > 0 ) {
			$m = $mail->items[0];
			if ( md5($m->unsubscribePassword) === $pwd) {
                $validated = true;
                $this->view->name = $m->name;
                $eee = $m->delivery;
                if($delivery>-1 && $delivery!=$m->delivery && NewsDeliveryType::has($m->delivery, $delivery)){
                    $m->delivery = $m->delivery ^ $delivery;
                    $m->save();
                }else if( $delivery == $m->delivery || GET_REQUEST_PARAM($this, "src")=="ui"){
                    $mail->remove($m);
                }
                $this->view->item = $m;
                $result = "ok";
            }
		} else {
            $result = "none";
        }
        if ( $validated !== true &&  $result == "error") {
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
			$this->getResponse()->setHeader("Status","403 Forbidden");
			return DISABLE_LAYOUT($this, true);
		}
        if(GET_REQUEST_PARAM($this, "src")=="ui"){
			DISABLE_LAYOUT($this, true);
			return SET_NO_RENDER($this, "<response></response>");
        }

        if ($delivery == NewsDeliveryType::D_DAILY_DIGEST){
            $this->view->digest = "daily";
        } else if ($delivery == NewsDeliveryType::D_WEEKLY_DIGEST){
            $this->view->digest = "weekly";
        } else if ($delivery == NewsDeliveryType::D_MONTHLY_DIGEST){
            $this->view->digest = "montly";
        } else {
            $this->view->digest = "";
        }
        $this->view->server = "http://" . $_SERVER['APPLICATION_UI_HOSTNAME'] . "/";
        $this->view->result = $result;
		$this->view->validated = $validated;
		return DISABLE_LAYOUT($this);
	}

	public function subscribeAction() {
		$fhash = 0;
		$flt = '';
		if( trim(GET_REQUEST_PARAM($this, 'flt')) != '' ) {
			$flt = base64_decode(GET_REQUEST_PARAM($this, 'flt'));
		}
		$responseText = '';
		if ( $this->session->userid !== null ) {
			$m = new Default_Model_MailSubscription();
			$mail = new Default_Model_MailSubscriptions();
			if (GET_REQUEST_PARAM($this, 'id')) { //update
				$mail->filter->id->equals(GET_REQUEST_PARAM($this, 'id'));
				$mail->refresh();
				$m = $mail->items[0];
				$m->name = GET_REQUEST_PARAM($this, 'name');
				$m->researcherid = $this->session->userid;
				$m->flt = $flt;
				$flt = str_replace('"', '”', $flt);
				$m->flthash = getFltHash($flt);
				$m->subjectType = GET_REQUEST_PARAM($this, 'subjecttype');
				$m->events = GET_REQUEST_PARAM($this, 'events');
				$m->delivery = GET_REQUEST_PARAM($this, 'delivery');
				$m->save();
				header ("Content-Type:text/xml");
				$responseText = '<' . '?xml version="1.0" encoding="utf-8"?'.'>';
				$responseText .= "<response />";
			} else { //insert new
				$m->name = GET_REQUEST_PARAM($this, 'name');
				$m->researcherID = $this->session->userid;
				$m->flt = $flt;
				$m->flthash = num_to_string($fhash);
				$m->subjectType = GET_REQUEST_PARAM($this, 'subjecttype');
				$m->events = GET_REQUEST_PARAM($this, 'events');
				$m->delivery = GET_REQUEST_PARAM($this, 'delivery');
				$mail->add($m);
				$mail = new Default_Model_MailSubscriptions();
				$mail->filter->researcherid->equals($this->session->userid)->and($mail->filter->flthash->equals(getFltHash($flt)));
				header ("Content-Type:text/xml");
				$responseText = '<' . '?xml version="1.0" encoding="utf-8"?'.'>';
				if($mail->count()>0){
					$mail->refresh();
					$m = $mail->items[0];
					$resonseText .= "<response id='" . $m->id . "' name='" . htmlspecialchars($m->name) . "' events='" . $m->events . "' delivery='" . $m->delivery . "'  unsubscribe_pwd='" . md5($m->unsubscribePassword) . "' />";
				} else {
					$responseText .= "<response error='Could not insert subscription' />";
				}
				NewsFeed::SendSubscriptionVerificationTextMail($m, $this->session->userid);
				DISABLE_LAYOUT($this);
				return SET_NO_RENDER($this, $responseText);
			}
		} else {
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
			$this->getResponse()->setHeader("Status","403 Forbidden");
			DISABLE_LAYOUT($this);
			return SET_NO_RENDER($this, NULL, 403);
		}
		return DISABLE_LAYOUT($this, true);
	}

	public function getsubscriptionAction(){
		debug_log('sub:' . $_GET['subjecttype'] . ' flt: '. base64_decode($_GET['flt']));
        $fhash = 0;
		$subjecttype="app";
		$responseText = '';
		if( isset($_GET["subjecttype"]) && trim($_GET["subjecttype"]) !=="" ) {
			$subjecttype=$_GET["subjecttype"];
		}
		if ( $this->session->userid !== null && isset($_GET['flt']) ) {
			$m = new Default_Model_MailSubscriptions();
			$flt = trim(base64_decode(GET_REQUEST_PARAM($this, 'flt')));
			$flt = str_replace('"', '”', $flt);
			$fhash = getFltHash($flt);
			$m->filter->researcherid->equals($this->session->userid)->and($m->filter->subjecttype->equals($subjecttype))->and($m->filter->flthash->equals($fhash));
			debug_log($m->filter->expr());
			header ("Content-Type:text/xml");
			$responseText = '<' . '?xml version="1.0" encoding="utf-8"?'.'>';
			if($m->count()>0){
				$m->refresh();
				$s = $m->items[0];
				$responseText .= "<response id='" . $s->id . "' name='" . htmlspecialchars($s->name). "' events='" . $s->events . "' delivery='" . $s->delivery . "'  unsubscribe_pwd='" . md5($s->unsubscribePassword) . "' ></response>";
			} else {
				$responseText .= "<response />";
			}
			DISABLE_LAYOUT($this);
			return SET_NO_RENDER($this, $responseText);
		} else {
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
			$this->getResponse()->setHeader("Status","403 Forbidden");
			DISABLE_LAYOUT($this);
			return SET_NO_RENDER($this, NULL, 403);
		}
		return DISABLE_LAYOUT($this, true);
	}

	public function getrolesubscriptionAction(){
		if($this->session->userid===null){
		    $this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
			$this->getResponse()->setHeader("Status","403 Forbidden");
			DISABLE_LAYOUT($this);
			return SET_NO_RENDER($this, NULL, 403);
		}
		$m = new Default_Model_MailSubscriptions();
		$m->filter->researcherid->equals($this->session->userid)->and($m->filter->subjecttype->equals("ppl"))->and($m->filter->events->hasbit(NewsEventType::E_ROLE_REQUEST)->or($m->filter->events->hasbit(NewsEventType::E_ROLE_VERIFIED)));
		if ($m->count()==0) {
			header ("Content-Type:text/xml");
		    $responseText = "<response ><response>";
			return SET_NO_RENDER($this, $responseText);
		} else {
		    $m->refresh();
		    $mi = $m->items[0];
			header ("Content-Type:text/xml");
		    $responseText = "<response id='" . $mi->id . "' name='" . $mi->name . "' subjecttype='". $mi->subjecttype . "' events='" . $mi->events . "' researcherid='" .$mi->researcherid . "' delivery='" . $mi->delivery . "' unsubscribe_pwd='" . md5($mi->unsubscribePassword) . "' ></response>";
			return SET_NO_RENDER($this, $responseText);
		}
		return DISABLE_LAYOUT($this, true);
	}

	public function dispatchmailAction() {
		if ( GET_REQUEST_PARAM($this, "subjecttype") != '' ) {
			$subjecttype = GET_REQUEST_PARAM($this, "subjecttype");
		} else {
			$subjecttype = "app";
		}
		// Prevent malicious calls
		if ( localRequest() ) {
			$type = GET_REQUEST_PARAM($this, "delivery");
			if ( $type != '' ) {
				switch($type) {
					case NewsDeliveryType::D_DAILY_DIGEST: $digest = "Daily"; break;
					case NewsDeliveryType::D_WEEKLY_DIGEST: $digest = "Weekly"; break;
					case NewsDeliveryType::D_MONTHLY_DIGEST: $digest = "Monthly"; break;
				}
				$subject = "AppDB ".$digest." News Digest";
				$mail = new Default_Model_MailSubscriptions();
				$mail->filter->delivery->hasbit($type)->and($mail->filter->subjecttype->equals($subjecttype));
				$items = $mail->items;
				$rs = new Default_Model_Contacts();
				$rids = array();
				foreach ( $items as $i ) {
					if ( in_array($i->researcherid,$rids) == false ) {
						$rids[] = $i->researcherid;
						$data = NewsFeed::getMailForUser($i->researcherID, $type,$this,$subjecttype);

						if(count($data)==0){
							$body = '';
							$textbody = '';
						}else{
							$body = $data["html"];
							$textbody = $data["text"];
						}

						if ( $body != '' ) {
							$rs->filter->researcherid->equals($i->researcherid)->and($rs->filter->contacttypeid->equals(7))->and($rs->filter->isprimary->equals(true));
							if ( count($rs->refresh()->items) > 0 ) {
								$to = $rs->items[0]->data;
								//sendMultipartMail($subject, $to, $textbody, $body, 'appdb-reports@iasa.gr', 'enadyskolopassword');
								EmailService::sendReport($subject, $to, $textbody, $body);
								error_log("[NewsController::dispatchmailAction]: Sending $digest digest mail to ($to) with subject: $subject");
							}
						}
					}
				}
			}
		} else {
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
			$this->getResponse()->setHeader("Status","403 Forbidden");
			DISABLE_LAYOUT($this, true);
			return SET_NO_RENDER($this, NULL, 403);
		}
		return DISABLE_LAYOUT($this, true);
	}

	public function mailAction($data=null){
		if(is_null($data)){
			$hash = GET_REQUEST_PARAM($this, "h");
			$id = GET_REQUEST_PARAM($this, "id");
			$delivery = GET_REQUEST_PARAM($this, "delivery");
			$subjecttype = GET_REQUEST_PARAM($this, "subjecttype");
			if( !$subjecttype) {
				$subjecttype = "app";
			}
			//In case of an external request the action needs the researcher's id , the delivery to be queried and a hash number for protection
			if( $id == '' || $delivery == '' || $hash == ''){
				DISABLE_LAYOUT($this);
				return $this->NotFound();
			}

			//Normalize id and delivery parameters
			if( is_numeric($delivery) == true && is_numeric($id) == true ) {
				$delivery = intval($delivery);
				$id = intval($id);
			}else{
				DISABLE_LAYOUT($this);
				return $this->NotFound();
			}

			//Search for the requested user
			$us = new Default_Model_Researchers();
			$us->filter->id->equals($id);
			if(count($us->items)==0){
				DISABLE_LAYOUT($this);
				return $this->NotFound();
			}

			$u = $us->items[0];
			//Check if hash value is correct for the user
			$calchash = md5('' . $u->id . ':' . $u->mailUnsubscribePwd . ':' . $delivery );
			if($calchash != $hash){
				DISABLE_LAYOUT($this);
				return $this->NotFound();
			}

			$news = NewsFeed::getEmailDigest($delivery,GET_REQUEST_PARAM($this, "id"),$subjecttype);
			if(count($news)==0){
				return DISABLE_LAYOUT($this, true);
			}
			$this->view->delivery = $delivery;
			$this->view->server = "http://" . $_SERVER['APPLICATION_UI_HOSTNAME'] . "/";
			$this->view->news = $news;
			$this->view->unsubscribeall = $this->view->server . "news/unsubscribeall?id=".GET_REQUEST_PARAM($this, "id")."&pwd=".md5($u->mailUnsubscribePwd);
			$this->view->unsubscribedigestall = $this->view->server."news/unsubscribeall?id=".GET_REQUEST_PARAM($this, "id")."&delivery=" . $delivery . "&pwd=".md5($u->mailUnsubscribePwd);

			if($this->view->delivery == NewsDeliveryType::D_DAILY_DIGEST){
				$this->view->digest = "daily";
			} else if($this->view->delivery == NewsDeliveryType::D_WEEKLY_DIGEST){
				$this->view->digest = "weekly";
			} else if($this->view->delivery == NewsDeliveryType::D_MONTHLY_DIGEST){
				$this->view->digest = "monthly";
			}
			$this->view->isExternalRequest = true;
		}else{
			$this->view->isExternalRequest = false;
			$this->view->externalurl = $data["externalurl"];
			$this->view->delivery = $data["delivery"];
			$this->view->digest = $data["digest"];
			$this->view->server = $data["server"];
			$this->view->news = $data["news"];
			$this->view->unsubscribeall = $data["unsubscribeall"];
			$this->view->unsubscribedigestall = $data["unsubscribedigestall"];
		}
		return DISABLE_LAYOUT($this);
	}

	public function dispatchrolerequestmailAction(){
		// OBSOLETE
		DISABLE_LAYOUT($this);
        return $this->NotFound();
	}

    public function rolerequestmailAction($person=null){
		// OBSOLETE ----------
		DISABLE_LAYOUT($this);
        return $this->NotFound();
	}

	public function disseminationAction() {
		$ok = false;
		if ( $this->session->userid !== null ) {
			$users = new Default_Model_Researchers();
			$users->filter->id->equals($this->session->userid);
			$user = null;
			if ( count($users->items) >0 ) $user = $users->items[0];
			if ( $user !== null && $user->privs->canUseDisseminationTool() ) {
				$ok = true;
			}
		}
		if ( ! $ok ) {
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
			$this->getResponse()->setHeader("Status","403 Forbidden");
			DISABLE_LAYOUT($this);
			return SET_NO_RENDER($this, '<div><h3>No permission to view content</h3></div>', 403);
		}
		return DISABLE_LAYOUT($this);
	}

	public function dispatchdisseminationAction() {
		$ok = false;
		if ( $this->session->userid !== null ) {
			$users = new Default_Model_Researchers();
			$users->filter->id->equals($this->session->userid);
			$user = null;
			if ( count($users->items) >0 ) $user = $users->items[0];
			if ( $user !== null) if ( $user->privs->canUseDisseminationTool() ) {
				$ok = true;
				$ds = new Default_Model_DisseminationEntry();
				$ds->composerID = $this->session->userid;
				$ds->message = GET_REQUEST_PARAM($this, "message");
				$ds->filter = GET_REQUEST_PARAM($this, "filter");
				$ds->recipients = "{".GET_REQUEST_PARAM($this, "recipients")."}";
				$ds->save();
				if ( $ds->id == "" ) {
					$this->getResponse()->clearAllHeaders();
					$this->getResponse()->setRawHeader("HTTP/1.0 500 Internal Server Error");
					$this->getResponse()->setHeader("Status","500 Internal Server Error");
					DISABLE_LAYOUT($this);
					return SET_NO_RENDER($this, NULL, 500);
				}
			}
		}
		if ( ! $ok ) {
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
			$this->getResponse()->setHeader("Status","403 Forbidden");
			DISABLE_LAYOUT($this);
			return SET_NO_RENDER($this, '<div><h3>No permission to view content</h3></div>', 500);
		}
		return DISABLE_LAYOUT($this, true);
	}

	private function NotFound(){
		$this->getResponse()->clearAllHeaders();
		$this->getResponse()->setRawHeader("HTTP/1.0 404 Not Found");
		$this->getResponse()->setHeader("Status","404 Not Found");
		return SET_NO_RENDER($this, NULL, 404);
	}

	public function senddisseminationAction() {
		$ok = false;
		if ( $this->session->userid !== null ) {
			$users = new Default_Model_Researchers();
			$users->filter->id->equals($this->session->userid);
			$user = null;
			if ( count($users->items) >0 ) $user = $users->items[0];
			if ( $user !== null && $user->privs->canUseDisseminationTool() ) {
				$ok = true;
				$d = new Default_Model_DisseminationEntry();
				$onlyToMe = GET_REQUEST_PARAM($this, "onlytome");
				if ( $onlyToMe === "true" || $onlyToMe === true || $onlyToMe === 1 || $onlyToMe === "1" ) {
					$onlyToMe = true;
				} else {
					$onlyToMe = false;
				}
				$rs = new Default_Model_Researchers();
				if ( ! $onlyToMe ) {
					$d->message = GET_REQUEST_PARAM($this, "message");
					$d->subject = GET_REQUEST_PARAM($this, "subject");
					$d->composerid = $this->session->userid;
					$d->filter = GET_REQUEST_PARAM($this, "flt");
					$rs->filter = FilterParser::getPeople($d->filter, false);
					$ids = array();
					$adrs = array();
					$count = count($rs->items);
					if ( $count > 0 ) {
						for ( $i=0; $i < $count; $i++ ) {
							$ids[] = $rs->items[$i]->id;
							$ccount = count($rs->items[$i]->contacts);
							if ( $ccount > 0 ) {
								for ( $j=0; $j < $ccount; $j++ ) {
									if ( ( $rs->items[$i]->contacts[$j]->contactTypeID == 7 ) && ( $rs->items[$i]->contacts[$j]->isPrimary ) ) {
										$adrs[] = $rs->items[$i]->contacts[$j]->data;
										break;
									}
								}
							}
						}
					}
					$d->recipients = $ids;
					$d->save();
				}
				$textbody = GET_REQUEST_PARAM($this, "textmessage");
				//Create informative text footer to append in textbody
				$footer = "\n\n----------------------------------------------------------------------------------\n";
				$footer .= "UNSUBSCRIBE: To unsubscribe from this notification list follow the following steps:\n";
				$footer .= "\t 1. visit http://" . $_SERVER['APPLICATION_UI_HOSTNAME'] . "\n";
				$footer .= "\t 2. login with your EGI SSO account by clicking on the upper right corner of the page\n";
				$footer .= "\t 3. open your profile by clicking your name on the upper right corner of the page\n";
				$footer .= "\t 4. select the 'Preferences' tab and uncheck the option to receive e-mail news messages\n\n";
				$footer .= "REPORT PROBLEM: " . EmailConfiguration::getSupportAddress();

				$textbody .= $footer;

				$body = GET_REQUEST_PARAM($this, "message");
				//Adjust text footer to html format to append to html message
				$footer = preg_replace("/\n/", "<br/>", $footer);
				$footer = preg_replace("/\t/", "<span style='padding-left:10px;'></span>",$footer);
				$body .= $footer;

				$subject = GET_REQUEST_PARAM($this, "subject");
				// send the message to selected recipients
				if ( ! $onlyToMe ) {
					//sendMultipartMail($subject, $adrs, $textbody, $body, 'appdb-reports@iasa.gr', 'enadyskolopassword',"appdb-support@iasa.gr", null, false , array("Precedence"=>"bulk") );
					EmailService::sendBulkReport($subject, $adrs, $textbody, $body, EmailConfiguration::getSupportAddress());
				}

				// also send message to sender
				$adrs = array();
				$rs->filter->id->equals($this->session->userid);
				$rs->refresh();
				if ( count($rs->items[0]->contacts) > 0 ) {
					for ( $i=0; $i < count($rs->items[0]->contacts); $i++ ) {
						if ( $rs->items[0]->contacts[$i]->isPrimary ) {
							$adrs[] = $rs->items[0]->contacts[$i]->data;
							break;
						}
					}
					if ( count($adrs) > 0 ) {
						//sendMultipartMail(($onlyToMe?"PREVIEW: ":"").$subject, $adrs, $textbody, $body, 'appdb-reports@iasa.gr', 'enadyskolopassword',"appdb-support@iasa.gr", null, false , array("Precedence"=>"bulk"));
						EmailService::sendBulkReport(($onlyToMe?"PREVIEW: ":"").$subject, $adrs, $textbody, $body, EmailConfiguration::getSupportAddress());
					}
				}

				// also send message to appdb itself
				if ( ! $onlyToMe ) {
					$adrs = array();
					$adrs[] = EmailConfiguration::getSupportAddress();
					//sendMultipartMail($subject, $adrs, $textbody, $body, 'appdb-reports@iasa.gr', 'enadyskolopassword',"appdb-support@iasa.gr", null, false , array("Precedence"=>"bulk"));
					EmailService::sendBulkReport($subject, $adrs, $textbody, $body, EmailConfiguration::getSupportAddress());
				}
			}
		}
		if ( ! $ok ) {
			$this->view->error = "Only managers and administrators are authorized to view this content";
		}
		return DISABLE_LAYOUT($this, true);
	}


	function refreshaggnewsAction() {
		if ( localRequest() ) {
			db()->query("REFRESH MATERIALIZED VIEW aggregate_news;");
		} else {
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
			$this->getResponse()->setHeader("Status","403 Forbidden");
			DISABLE_LAYOUT($this);
			return SET_NO_RENDER($this, '<div><h3>No permission to view content</h3></div>', 403);
		}
		return DISABLE_LAYOUT($this, true);
	}

	function notifyusersAction() {
		if (file_exists($_SERVER['APPLICATION_PATH'] . "/notify_users_message.phtml")) {
			$msg = file_get_contents($_SERVER['APPLICATION_PATH'] . "/notify_users_message.phtml");
			$msg = trim($msg);
			if (strlen($msg) == 0) {
				DISABLE_LAYOUT($this);
				return SET_NO_RENDER($this, NULL, 204);
			} else {
				DISABLE_LAYOUT($this);
				return SET_NO_RENDER($this, $msg);
			}
		} else {
			DISABLE_LAYOUT($this);
			return SET_NO_RENDER($this, NULL, 204);
		}
		return DISABLE_LAYOUT($this, true);
	}
}
