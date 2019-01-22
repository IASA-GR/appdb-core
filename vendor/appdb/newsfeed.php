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
 
require_once('filterParser.php');
require_once('email_service.php');

class NewsEventType {
	const E_INSERT = 1;
	const E_UPDATE = 2;
	const E_DELETE = 4;
	const E_INSERT_COMMENT = 8;
	const E_INSERT_CONTACT = 16;
	const E_ROLE_REQUEST = 32;
	const E_ROLE_VERIFIED = 64;

	public static function has($n,$m) {
		return (($n & $m)==$m);
	}
}

class NewsDeliveryType {
	const D_NO_DIGEST = 1;
	const D_DAILY_DIGEST = 2;
	const D_WEEKLY_DIGEST = 4;
	const D_MONTHLY_DIGEST = 8;

	public static function has($n,$m) {
		return ($n & $m)==$m;
	}
}
 
class NewsFeed {
    public static function createNewsRequest($name='',$title='',$action='',$type='app',$flt='',$from='',$to='',$length='',$offset=''){
        $res = new stdClass();
		$dname = '';
        if(is_numeric($action)){
            $act = array();
            if(NewsEventType::has($action,NewsEventType::E_INSERT)){
                $act[] = "insert";
				$act[] = "insertvav";
            }
            if(NewsEventType::has($action,NewsEventType::E_UPDATE)){
                $act[] = "update";
				$act[] = "updaterel";
				$act[] = "updatevav";
            }
            if(NewsEventType::has($action,NewsEventType::E_INSERT_COMMENT)){
                $act[] = "insertcmm";
            }
            if(NewsEventType::has($action,NewsEventType::E_INSERT_CONTACT)){
                $act[] = "insertcnt";
            }
            $action = $act;
        }else{
            if(trim($action)===""){
                $action = array();
            }else{
                $action = explode(",", $action);
            }
        }
        switch(strtolower($type)){
			case "app-entry":
			  $dname = "AppDB - Software latest news";
			  $type="app-entry";
			  break;
			case "app":
				$dname = "AppDB - Software latest news";
				$type="app";
				break;
			case "ppl":
				$dname = "AppDB - People latest news";
				$type = "ppl";
				break;
			case "doc":
				$dname = "AppDB - Publications latest news";
				break;
			default:
				$type = "";//array("applications","people");
				$dname = "AppDB - Software and People latest news";
				break;
		}
		$res->name = ($name=='')?$dname:$name;
		$res->action = $action;
		$res->type = $type;
        $res->title = $title;
		$res->filter = $flt;
        $res->length = $length;
        $res->offset = $offset;
        if($to===''){
            $res->to = "EXTRACT(EPOCH FROM NOW())";
        }else{
            $res->to = "EXTRACT(EPOCH FROM NOW()) - " . $to;
        }
        if($from==='') { 
            $res->from = '0';
        } else {
            $res->from = "EXTRACT(EPOCH FROM NOW()) - " . $from;
        }
        return $res;
    }
    /* Parses url and checks for:
	// name :  display name for feed
	// action : insert,delete,update
	// type : application, people
	// flt : <the filtering>*/
    public static function parseUrl(){
		$action = urldecode((isset($_GET["a"])?$_GET["a"]:""));
        $type = urldecode((isset($_GET["t"])?$_GET["t"]:""));
        $title = ((isset($_GET["title"])?base64_decode($_GET["title"]):""));
		$flt = ((isset($_GET["f"])?base64_decode($_GET["f"]):''));
		$name = urldecode((isset($_GET["n"])?$_GET["n"]:''));
        $length = (isset($_GET['len'])?$_GET["len"]:'');
        $offset = (isset($_GET['ofs'])?$_GET["ofs"]:'');
        $from = (isset($_GET["from"])?$_GET["from"]:'');
        $to = (isset($_GET["to"])?$_GET["to"]:'');
        if(is_numeric($action)===true){
            $action = intval($action);
        }
		return self::createNewsRequest($name, $title, $action, $type, $flt,$from,$to,$length,$offset);
	}
    /* 
     * Returns the loaded aggregated_news model based on the user request query
     */
    public static function getNews($request){
        $t = $request;
		if ( $t->type == "app-entry") $t->type="app";
		$t->filter = str_replace("\xe2\x80\x9d",'"',$t->filter);
		if(trim($t->filter)!=='') {
			if($t->type==='ppl') {
				$t->filter = FilterParser::normalizeFilter($t->filter, FilterParser::NORM_PPL, $err);
				$f = FilterParser::getPeople($t->filter);
			} else if($t->type==='app') {
				$t->filter = FilterParser::normalizeFilter($t->filter, FilterParser::NORM_APP, $err);
				$f = FilterParser::getApplications($t->filter);
			} 
		}
		$news = new Default_Model_AggregateNews();
		
		if ( $t->length != '' )  $news->filter->limit($t->length);
		if ( $t->offset != '' ) $news->filter->offset($t->offset);
        
		$nf = new Default_Model_AggregateNewsFilter();
        if (count($t->action) >0) {
			if( $t->type == "app" && in_array("update", $t->action) == true ){
				$t->action[] = "updaterel";
			}
            for( $i=0; $i < count( $t->action ) ; $i+=1){
                if ( isset ( $a ) ) {
                    $a = $a->or( $nf->action->equals( trim( $t->action[$i] ) ) );
                } else {
                    $a = $nf->action->equals( trim( $t->action[$i] ) );
                }
            }
            $news->filter = $news->filter->chain($nf,"AND");
		}
        if ( trim($t->type) != '') {
			$nf->subjecttype->equals(trim($t->type));
			$news->filter = $news->filter->chain($nf,"AND");
		}

        $nf->timestamp->between(array($t->from,$t->to));
		$news->filter = $news->filter->chain($nf,"AND");
		if(isset($f)){
			if (is_array($f->expr())) {
				$f->SetExpr("(" . implode(") AND (", $f->expr()). ")");
			}
			if ( $f->expr() != '' ) {
				$nf = $nf->chain($f,"AND");
			}
		}
		$news->filter = $news->filter->chain($nf,"AND");
		debug_log($news->filter->expr());
		
		$news->filter->orderBy('timestamp DESC');
        $news->refresh();
        return $news;
    }
	public static function getPrimaryNameFromData($d){
		$len = count($d);
		if($len!=0){
			for($i=0; $i<$len; $i+=1){
				if($d[$i]->isPrimary == "true"){
					return $d[$i]->name;
				}
			}
		}
		return "software";
	}
	public static function getPrimaryIdFromData($d){
		$len = count($d);
		if($len!=0){
			for($i=0; $i<$len; $i+=1){
				if($d[$i]->isPrimary == "true"){
					return $d[$i]->id;
				}
			}
		}
		return "-1";
	}
    public static function parsePubEntry($e){
        
    }
    public static function parsePeopleEntry($e){
        switch($e->action){
            case "update":
				$link = "http://".$_SERVER['APPLICATION_UI_HOSTNAME'] . "?p=".base64_encode("/people/details?id=".$e->subjectid);
				$summary = $e->subjectname . " has updated his/her" . (($fields!=='')?" ".$fields:" profile information").".";
				$summaryHTML = "<table><tbody><tr><td><img src='http://".$_SERVER['APPLICATION_UI_HOSTNAME'] . "/people/getimage?id=".$e->subjectid."' width='30' alt=''/></td><td>".$summary."</td></tr></tbody></table>";
				break;
			case "insert":
				$link = "http://".$_SERVER['APPLICATION_UI_HOSTNAME'] . "?p=".base64_encode("/people/details?id=".$e->subjectid);
				$summary = $e->subjectname . " has registered with the AppDB";
				$summaryHTML = "<table><tbody><tr><td><img src='http://".$_SERVER['APPLICATION_UI_HOSTNAME'] . "/people/getimage?id=".$e->subjectid."' width='30' alt=''/></td><td>".$summary."</td></tr></tbody></table>";
				break;
        }
    }
	public static function parseApplicationReleaseEntry($e, $data, $type="application"){
		$result = array();
		$relfields = array();
		$tfields = explode(",", $e->fields);
		$tmp = null;
		for($i=0; $i<count($tfields); $i++){
			$tmp = explode(":",($tfields[$i]));
			if( isset($relfields[$tmp[1]]) ){
				$relfields[$tmp[1]]["events"][] = $tmp[0];
			}else{
				$relfields[$tmp[1]] = array("events"=>array($tmp[0]),"data"=>null);
			}
		}
		foreach($relfields as $k=>$v){
			$rels = new Default_Model_AppReleases();
			$rels->filter->releaseid->equals($k);
			if( count($rels) > 0 ){
				$relfields[$k]["data"] = $rels->items[0];
			}
		}
		foreach($relfields as $k=>$v){
			$d = $v["data"];
			if( in_array("state", $v["events"]) == false || ($d !== null && $d->state == 1) ){
				continue;
			}
			$d = $v["data"];
			$state = "into production";
			if( $d->state == 3){
				$state = "as a candidate";
			}
			$title = "New release of '".$data->name."'" . " " . $type . ": ".  $d->release;
			$html = "<div class='newsitem'>";
			$summary = "Release '" . $d->release . "' of series '" . $d->series . "' has been published " . $state . " at " . $d->publishedon;
			$textsummary = $summary;
			$link = "http://".$_SERVER['APPLICATION_UI_HOSTNAME'] . "/store/software/" . $data->cname . "/releases/" . $d->series . "/" . $d->release;
			$html .= "<div class='description'><img src='http://".$_SERVER['APPLICATION_UI_HOSTNAME'] . "/apps/getlogo?id=".$e->subjectid."' width='30' alt=''/>";
			$html .= "</div>";
			
			$html .= "<div class='summary'>".$summary."</div>";
			$html .="</div>";
			$result[] = array("title"=>$title,"summary"=>$summary,"textsummary"=>$textsummary,"html"=>$html,"link"=>$link,"publishedon"=>date('c',strtotime($d->publishedon)));
		}
		usort($result, "compareReleaseDates");
		return $result;
	}
	public static function parseVApplianceEntry($e, $data, $type="application"){
		$result = array();
		$relfields = array();
		$tfields = explode(",", $e->fields);
		$tmp = null;
		for($i=0; $i<count($tfields); $i++){
			$tmp = explode(":",($tfields[$i]));
			if( isset($relfields[$tmp[1]]) ){
				$relfields[$tmp[1]]["events"][] = $tmp[0];
			}else{
				$relfields[$tmp[1]] = array("events"=>array($tmp[0]),"data"=>null);
			}
		}
		foreach($relfields as $k=>$v){
			$rels = new Default_Model_VAversions();
			$rels->filter->id->equals($k);
			if( count($rels) > 0 ){
				$relfields[$k]["data"] = $rels->items[0];
			}
		}
		foreach($relfields as $k=>$v){
			if( in_array("published", $v["events"]) == false ){
				continue;
			}
			$d = $v["data"];
			$title = "Published Version '" . $d->version . "' of '".$data->name."' virtual appliance";
			$html = "<div class='newsitem'>";
			$summary = "A new version '" . $d->version . "' of " . $data->name . " virtual appliance has been published.";
			$textsummary = $summary;
			$link = "http://".$_SERVER['APPLICATION_UI_HOSTNAME'] . "/store/software/" . $data->cname . "/vaversion/latest";
			$html .= "<div class='description'><img src='http://".$_SERVER['APPLICATION_UI_HOSTNAME'] . "/apps/getlogo?id=".$e->subjectid."' width='30' alt=''/>";
			$html .= "</div>";
			
			
			$html .= "<div class='summary'>".$summary."</div>";
			$html .="</div>";
			
			$result[] = array("title"=>$title,"summary"=>$summary,"textsummary"=>$textsummary,"html"=>$html,"link"=>$link,"publishedon"=>date('c',strtotime($d->createdon)), "timestamp"=>$d->createdon);
		}
		usort($result, "compareReleaseDates");
		return $result;
	}
    public static function parseApplicationEntry($e){
        $title = '';
		$summary = '';
		$summaryHTML = '';
		$textsummary='';
        $html = "<div class='newsitem'>";
		$link = '';
        $tmp = '';
        $name = $e->subjectname;
        $typetype = "application";
		$data = json_decode(str_replace("\xe2\x80\x9d",'"',$e->subjectdata));
		if ( ! is_null($data) ) $data = $data->application;
		$fields = $e->fields;
		if(trim($fields)!=='' && ($e->action === "insertcnt" || $e->action === "insertcmm")){
            $fields = explode(",", $fields);
            $tmp = null;
            for($i=0; $i<count($fields); $i++){
                $tmp = explode(":",($fields[$i]));
                $fields[$i] = array("id"=>$tmp[0],"name"=>$tmp[1]);
            }
        }
		
		if(isset($data->category)){
			$primaryid = "" . self::getPrimaryIdFromData($data->category);
			$typetype = strtolower("" . self::getPrimaryNameFromData($data->category));
			if ($typetype != "software" ) $typetype = substr($typetype,0,strlen($typetype) - 1);
        }
        switch($e->action){
            case "update":
                $title = "`".$name."'" . " " . $typetype . " has been updated";
				$link = "http://".$_SERVER['APPLICATION_UI_HOSTNAME'] . "?p=".base64_encode("/apps/details?id=".$e->subjectid);
                $html .= "<div class='description'><img src='http://".$_SERVER['APPLICATION_UI_HOSTNAME'] . "/apps/getlogo?id=".$e->subjectid."' width='30' alt=''/>";
				if($data->description!==''){
					$html .= "<span class='title'>Description: </span><span>".$data->description."</span>";
				}
                $html .= "</div>";
                $summary = "`".$name."'" . " " . $typetype . " has updated information". (($fields!=='')?" regarding its ".$fields:"").".";
				$textsummary = $summary;
                $html .= "<div class='summary'>".$summary."</div>";
				if(is_null($data->discipline)===false && $data->tool!=='true'){
					$dName = array();
					foreach($data->discipline as $d) {
						$dName[] = $d->name;
					}
					$html .= "<div class='discipline'><span class='title'>Discipline".(count($dName)>1?"s":"").": <span><span>" . implode(", ", $dName) . "</span></div>";
				}
                break;
			case "updaterel":
				return self::parseApplicationReleaseEntry($e, $data, $typetype);
			case "updatevav":
			case "insertvav":
				return self::parseVApplianceEntry($e, $data, $typetype);
            case "insert":
                $title = "New " . $typetype . " " ."`".$name."'" ;
				$link = "http://".$_SERVER['APPLICATION_UI_HOSTNAME'] . "?p=".base64_encode("/apps/details?id=".$e->subjectid);
				$html .= "<div class='description'><img src='http://".$_SERVER['APPLICATION_UI_HOSTNAME'] . "/apps/getlogo?id=".$e->subjectid."' width='30' alt=''/>";
                if($data->description!==''){
					$html .= "<span class='title'>Description: </span><span>".$data->description."</span></div>";
				}
                $html .= "</div>";
                $summary = "`".$name."'" . " " . $typetype . " has been registered";
                if(is_null($data->discipline)===false && $data->tool!=='true'){
					$dName = array();
					foreach($data->discipline as $d) {
						$dName[] = $d->name;
					}
					$summary .= " under the `" . implode(", ", $dName) . "' discipline".(count($dName)>1?"s":"").". ";
				}
				$textsummary = $summary;
				$html .=  "<div class='summary'>".$summary."</div>";
                break;
			case "insertcnt":
                $title = "New contact".(count($fields>0)?"s":"")." added to " . "`".$name."'" . " " . $typetype . ".";
                $link = "http://".$_SERVER['APPLICATION_UI_HOSTNAME'] . "?p=".base64_encode("/apps/details?id=".$e->subjectid);
                $html .= "<div class='description'><img src='http://".$_SERVER['APPLICATION_UI_HOSTNAME'] . "/apps/getlogo?id=".$e->subjectid."' width='30' alt=''/>";
                if($data->description!==''){
					$html .= "<span class='title'>Description: </span><span>".$data->description."</span></div>";
				}
                $html .= "</div>";
                $summary = "";
				$textsummary = "";
                if(count($fields)===1){
                    $summary .= "<a href='http://".$_SERVER['APPLICATION_UI_HOSTNAME'] . "?p=" .base64_encode("/people/details?id=".$fields[0]["id"])."' target='_blank'>". trim($fields[0]["name"])."</a> has been added as a contact";
					$textsummary = trim($fields[0]["name"]) . " has been added as a contact";
                }else{
                    $summary = "";
                    foreach($fields as $f){
                        $summary .= "<a href='http://".$_SERVER['APPLICATION_UI_HOSTNAME'] . "?p=" .base64_encode("/people/details?id=".$f["id"])."' target='_blank'>". trim($f["name"])."</a>, ";
						$textsummary .=  trim($f["name"]) . ", ";
                    }
                    $summary = substr($summary,0,-2);
                    $summary .= " have been added as contacts";
					$textsummary = substr($textsummary,0,-2);
                    $textsummary .= " have been added as contacts";
                }
				$html .= "<div class='summary'>".$summary."</div>";
                break;
            case "insertcmm":
                $title = "New comment" . ((count($fields)>1)?"s":"") . " on " . "`".$name."'" . " " . $typetype . ".";
                $link = "http://".$_SERVER['APPLICATION_UI_HOSTNAME'] . "?p=".base64_encode("/apps/details?id=".$e->subjectid);
                $html .= "<div class='description'><img src='http://".$_SERVER['APPLICATION_UI_HOSTNAME'] . "/apps/getlogo?id=".$e->subjectid."' width='30' alt=''/>";
                if($data->description!==''){
					$html .= "<span class='title'>Description: </span><span>".$data->description."</span></div>";
				}
                $html .= "</div>";
				$summary .= ((count($fields)>1)?"N":"A n")."ew comment" . ((count($fields)>1)?"s have":" has") . " been posted by ";
				$textsummary = $summary;
				if ( ! is_array($fields) ) {
					$fields_tmp = array();
					$fields_tmp[] = $fields;
					$fields = $fields_tmp;
				}
				foreach($fields as $f){
					$summary .= "<a href='http://".$_SERVER['APPLICATION_UI_HOSTNAME'] . "?p=" .base64_encode("/people/details?id=".$f["id"])."' target='_blank'>". $f["name"]."</a>, ";
					$textsummary .=  $f["name"] . ", ";
				}
				$summary = substr($summary,0,-2);
				$textsummary = substr($textsummary,0,-2);
				$html .= "<div class='summary'>".$summary."</div>";
                break;
        }
        $html .="</div>";
        return array(array("title"=>$title,"summary"=>$summary,"textsummary"=>$textsummary,"html"=>$html,"link"=>$link));
	}
    public static function parseEntry($e){
        $res = array();
        switch($e->subjecttype){
	    case "app-entry":
	     $res = self::parseApplicationEntry($e);
	      break;
            case "app":
                $res = self::parseApplicationEntry($e);
                break;
            case "ppl":
                $res = self::parsePeopleEntry($e);
                break;
            case "doc":
                break;
        }
        return $res;

	}
    public static function getEmailDigest($delivery,$userid,$subjecttype){
        $res = array();
        $from = '';//30*24*3600

        if($delivery == NewsDeliveryType::D_DAILY_DIGEST){
            $from = 24 * 3600;
        } else if($delivery == NewsDeliveryType::D_MONTHLY_DIGEST) {
            $from = 30 * 24 * 3600;
        } else if ($delivery == NewsDeliveryType::D_WEEKLY_DIGEST) {
            $from = 7 *24 * 3600;
        }
        $mails = new Default_Model_MailSubscriptions();
        $mails->filter->researcherid->equals($userid)->and($mails->filter->delivery->hasbit($delivery))->and($mails->filter->subjecttype->equals($subjecttype));
        
        if(count($mails->items)==0){
            return array();
        }
        $mails->refresh();
        foreach($mails->items as $m){
            $req = self::createNewsRequest('', $m->name, $m->events, $m->subjecttype, $m->flt,$from,'');
            $news = array();
            $flatnews = self::getNews($req);
            if($flatnews->count()>0){
                $fl = $flatnews->items;
                foreach($fl as $fn){
                    $news[] = array("parsed"=>self::parseApplicationEntry($fn), "item"=>$fn);
                }
                $res[] = array("news"=>$news,"subscription"=>$m);
            }
        }
        return $res;
    }
    public static function getMailForUser($userid=null,$delivery='',$mc=null,$subjecttype="app"){
		if(is_null($userid)){
			return null;
		}
		$mailData = array();
		if($delivery=='' || is_numeric($delivery)==false){
			$delivery = NewsDeliveryType::D_DAILY_DIGEST | NewsDeliveryType::D_WEEKLY_DIGEST | NewsDeliveryType::D_MONTHLY_DIGEST;
		}
        $news = NewsFeed::getEmailDigest($delivery,$userid,$subjecttype);
        if(count($news)==0){
            return null;
        }
        $mailData["news"] = $news;
		$mailData["server"] = "http://".$_SERVER['APPLICATION_UI_HOSTNAME'] . "/";
		$mailData["delivery"] = $delivery;
		if($delivery == NewsDeliveryType::D_DAILY_DIGEST){
            $mailData["digest"] = "daily";
        } else if($delivery == NewsDeliveryType::D_WEEKLY_DIGEST){
            $mailData["digest"] = "weekly";
        } else if($delivery == NewsDeliveryType::D_MONTHLY_DIGEST){
            $mailData["digest"] = "monthly";
        }
        $us = new Default_Model_Researchers();
        $us->filter->id->equals($userid);
        if(count($us->items)>0){
            $u = $us->items[0];
            $mailData["unsubscribeall"]=$mailData["server"] . "news/unsubscribeall?id=".$userid."&pwd=".md5($u->mailUnsubscribePwd);
            $mailData["unsubscribedigestall"] =$mailData["server"] . "news/unsubscribeall?id=".$userid."&delivery=" . $delivery . "&pwd=".md5($u->mailUnsubscribePwd);
			$mailData["externalurl"] = $mailData["server"] . "news/mail?id=" . $userid . "&delivery=" . $delivery . "&h=" . md5('' . $u->id . ':' . $u->mailUnsubscribePwd . ':' . $delivery );
        }else{
           $mailData["unsubscribeall"]= null;
           $mailData["unsubscribedigestall"] = null;
		   $mailData["externalurl"] = null;
        }

		$text = self::getTextMail($mailData);
		$mc->mailAction($mailData);
		$html = $mc->view->render("news/mail.phtml");
		return array("html"=>$html,"text"=>$text);
	}
	public static function getTextMail($data=null){
		$res = "";
		$nl = "\r\n";
		$t = "\t";
		$res .= "To view in html format from your web browser use the url bellow : " .$nl . " " . $data["externalurl"] . $nl  .$nl;
		
		$subtitle = ucwords($data["digest"] . " news digest");
		$res .= str_repeat("=",strlen($subtitle)) . $nl;
		$res .= $subtitle  .$nl;
		$res .= str_repeat("=",strlen($subtitle)) . $nl;

		$subnews = $data["news"];
		foreach($subnews as $subnew){
			$res .= $nl;
			$sub = $subnew["subscription"];
			$news = $subnew["news"];
			$newscount = count($news);
			$title = "[ " . $sub->name . " ]" . $nl;
			$utitle = " ( Unsubscription url : " . $data["server"] ."news/unsubscribe?id=".$sub->id."&delivery=".$data["delivery"]."&pwd=".md5($sub->unsubscribePassword) . " )" . $nl;
			$res .= $title . $utitle;
			$res .= str_repeat("-",strlen($utitle)) . $nl;
			$nc = 1;
			foreach($news as $new){
				$parsedall = $new["parsed"];
				foreach ($parsedall as $parsed) {
					$item = $new["item"];
					$res .= "  " . $nc . ") " . $parsed["title"] .$nl;
					$res .= "  " . str_repeat(" ", strlen($nc . ") ")+1) . $parsed["textsummary"] . $nl;
					$res .= "  " . str_repeat(" ", strlen($nc . ") ")+1) ."For details visit : "  . $parsed['link'] . $nl;
					$nc += 1;
				}
			}
		}
		$res .= $nl.$nl;

		$res .= "To unsubscribe from all " .strtoupper($data["digest"]) . " e-mail notifications visit the URL : " . $nl . "     " . $data["unsubscribedigestall"] .$nl;
		$res .= "To unsubscribe from ALL e-mail notifications visit the URL : " . $nl . "     " . $data["unsubscribeall"]  .$nl;
		$res .= "Alternatively, you could login to the EGI Applications Database and manage your subscriptions from your profile preferences tab." .$nl;
		return $res;
	}

	public static function sendSubscriptionVerificationTextMail($subscription){
		$actions = array();
		$delivery = array();
		$users = new Default_Model_Researchers();
		$subject = "EGI AppDB: Email subscription verification";
		$body = "";
		$nl = "\r\n";
		$t = "\t";

		//Find subscriber in researchers
		$users->filter->id->equals($subscription->researcherid);
		$users->refresh();
		if(count($users->items) == 0){
			error_log("[appdb:Subscription Verification Email] : Could not find user with id = " . $subscription->researcherID . " . Delivery cancelled.");
			return ;
		}
		$user = $users->items[0];

		//Get event types of subscriptions
		if( NewsEventType::has($subscription->events,  NewsEventType::E_INSERT) == true ) $actions[] = "new software";
		if( NewsEventType::has($subscription->events,  NewsEventType::E_UPDATE) == true ) $actions[] = "software updates";
		if( NewsEventType::has($subscription->events,  NewsEventType::E_INSERT_CONTACT) == true ) $actions[] = "new contacts";
		if( NewsEventType::has($subscription->events,  NewsEventType::E_INSERT_COMMENT) == true ) $actions[] = "new comments";
		
		//Get delivery types of subscriptions
		if( NewsDeliveryType::has($subscription->delivery, NewsDeliveryType::D_DAILY_DIGEST) == true ) $delivery[] = "daily";
		if( NewsDeliveryType::has($subscription->delivery, NewsDeliveryType::D_WEEKLY_DIGEST) == true ) $delivery[] = "every monday";
		if( NewsDeliveryType::has($subscription->delivery, NewsDeliveryType::D_MONTHLY_DIGEST) == true ) $delivery[] = "every 1st day of the month";

		$body = "Dear " . $user->firstName . " " . $user->lastName . "," . $nl.$nl;
		$body .= "Your request to receive e-mail notifications about '" . $subscription->name . "' has been processed. " .$nl.$nl;

		//Render actions (events)
		$body .= "You will be notified for" ;
		if(count($actions) == 1){
			$body .= " " . $actions[0];
		}else{
			$ac = count($actions);
			for($i=0; $i<$ac; $i+=1){
				$body .= " " . $actions[$i];
				if( $i == $ac-2 ){
					$body .= (($ac>2)?",":"") ." and";
				}else if( $i < $ac-1 ){
					$body .= ",";
				}
			}
		}
		$body .= "." . $nl;

		//Render delivery
		$body .= "The delivery of notifications will take place";
		if( count($delivery) == 1 ) {
			$body .= " " . $delivery[0];
		}else{
			$dc = count($delivery);
			for($i=0; $i<$dc; $i+=1){
				$body .= " " . $delivery[$i];
				if( $i == $dc-2 ){
					$body .= (($dc>2)?",":"") ." and";
				}else if( $i <$dc-1 ){
					$body .= ",";
				}
			}
		}
		$body .= "." .$nl.$nl;

		$body .= 'If no new software registrations (or updates of existing software) occur within the given delivery time span, no e-mail will be sent.'.$nl.$nl;
		$body .= "Sincerely," .$nl;
		$body .= "EGI AppDB notifications service".$nl;
		$body .= "website: http://" . $_SERVER['APPLICATION_UI_HOSTNAME'] ."/";
		
		//Get primary e-mail contact of subscriber and send e-mail
		$rs = new Default_Model_Contacts();
		$rs->filter->researcherid->equals($subscription->researcherid)->and($rs->filter->contacttypeid->equals(7))->and($rs->filter->isprimary->equals(true));
		if ( count($rs->refresh()->items) > 0 ) {
			$to = $rs->items[0]->data;
			//sendMultipartMail($subject, $to, $body,'', 'appdb-reports@iasa.gr', 'enadyskolopassword');
			EmailService::sendReport($subject, $to, $body);
			error_log("[appdb:Subscription Verification Email]: Sending subscription verification to " . $to );
		}else{
			error_log("[appdb:Subscription Verification Email]: Cannot find a primary e-mail for user with id = " . $subscription->researcherid);
		}
	}
    public function getSubscribers($obj=null,$action=127,$delivery=15){
        if(is_null($obj)){
            return array();
        }
        $cls = get_class($obj);
        switch($cls){
            case "Default_Model_Researcher":
                $type='app';
                break;
            case "Default_Model_Applciation":
                $type='ppl';
                break;
            default :
                return array();
        }
        $act = array();
        if(NewsEventType::has($action,NewsEventType::E_INSERT)){
            $act[] = "insert";
        }
        if(NewsEventType::has($action,NewsEventType::E_UPDATE)){
            $act[] = "update";
        }
        if(NewsEventType::has($action,NewsEventType::E_INSERT_COMMENT)){
            $act[] = "insertcmm";
        }
        if(NewsEventType::has($action,NewsEventType::E_INSERT_CONTACT)){
            $act[] = "insertcnt";
        }

        $subs = new Default_Model_MailSubscriptions();
        $subs->filter->type->equals($type)->and($subs->filter->events->hasbit($event))->and($subd->filter->delivery->hasbit($delivery));
        if($subs->count()==0){
            return array();
        }
        $subs = $subs->items;
        foreach($subs as $sub){
            switch($type){
		case "app-entry":
		   $f = FilterParser::getApplications($sub->flt);
		   break;
                case "app":
                    $f = FilterParser::getApplications($sub->flt);
                    break;
                case "ppl":
                    $f = FilterParser::getPeople($sub->flt);
                    break;
            }
            $news = new Default_Model_AggregateNews();
            $nf = new Default_Model_AggregateNewsFilter();
            if (count($act) >0) {
                for( $i=0; $i < count( $act ) ; $i+=1){
                    if ( isset ( $a ) ) {
                        $a = $a->or( $nf->action->equals( trim( $act[$i] ) ) );
                    } else {
                        $a = $nf->action->equals( trim( $act[$i] ) );
                    }
                }
                $news->filter = $news->filter->chain($nf,"AND");
            }
            $nf->subjecttype->equals(trim($type));
			$news->filter = $news->filter->chain($nf,"AND");
            $news->filter->orderBy('timestamp DESC');
            $news->refresh();
        }
        return array();
	}
}
?>
