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

class PeopleController extends Zend_Controller_Action
{

    public function init()
    {
        $this->session = new Zend_Session_Namespace('default');
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('index', 'xml')
						->addActionContext('details', 'xml')
                      ->initContext();
    }

    public function nodisseminationAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
      	header("Content-Type:text/xml");
        echo "<?xml version='1.0'?>";
        if ($this->session->userid !== null) {
            $rs = new Default_Model_Researchers();
            $rs->filter->id->equals($this->session->userid);
            $r = $rs->items[0];
            if (isset($_GET['value'])) {
                $r->noDissemination = $_GET['value'];
                $r->save();
            }
            echo '<response value="'.($r->noDissemination?"true":"false").'"/>';
        } else {
			echo "<response error='Must be logged in'>unauthorized</response>";
        }
    }

	public function nameexistsAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		if ($this->session->userid !== null) {
			$res = array();
            $lname=$_GET['lname'];
            $fname=$_GET['fname'];
			if(is_null($fname)) $fname = "";
			if(is_null($lname)) $lname = "";
			
			//Get most certain profile
			$rs = new Default_Model_Researchers();
			$f1 = new Default_Model_ResearchersFilter();
			$f2 = new Default_Model_ResearchersFilter();
			$fn1 = $fname . " " . $lname;
			$fn2 = $lname . " " . $fname;
			$f1->firstname->soundslike($fname);
			$f2->lastname->soundslike($lname);
			$rs->filter->chain($f1, "AND");
			$rs->filter->chain($f2, "AND");
			if( count($rs->items) > 0 ){
				$item = $rs->items[0];
				echo '{id: "'.$item->id.'", fullname: "'.$item->fullName.'"}';
				return;
			}
			
			//get possible profiles
			$rs = new Default_Model_Researchers();
			$f = $rs->filter;
			$f->name->soundsLike($fn1)->or($f->name->soundsLike($fn2));
			
			foreach ($rs->items as $item) {
				array_push($res, '{id: "'.$item->id.'", fullname: "'.$item->fullName.'"}');
			}
			
			echo  implode(",", $res);
		}
	}

	public function emailexistsAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		if ($this->session->userid !== null) {
			$cs = new Default_Model_Contacts();
			$f1 = new Default_Model_ContactsFilter();
			$f2 = new Default_Model_ContactsFilter();
			$f1->contacttypeid->equals(7);
			$f2->data->ilike($_GET['email']);
			$cs->filter->chain($f1, "AND");
			$cs->filter->chain($f2, "AND");
			if ( count($cs->items) > 0) {
				$j = 'id: "'.$cs->items[0]->researcherID.'"';
				$rs = new Default_Model_Researchers();
				$rs->filter->id->equals($cs->items[0]->researcherID);
				$j .= ', fullname: "'.str_replace('"','\"',$rs->items[0]->fullName).'"';
				echo '{'.$j.'}';
			}
		}
	}

	private function getFltStr() {
		$f = trim( $this->_getParam('flt') );
		return $f;
	}

	private function cacheimages($items) {
		foreach ($items as $item) {
			$image = base64_decode(pg_unescape_bytea($item->image));
			if (is_string($image) && ($image != '')) {
				$f = fopen(APPLICATION_PATH . "/../cache/ppl-image-".$item->id.".png","w");
				$image = base64_decode(pg_unescape_bytea($item->image));
				fwrite($f, $image);
				fclose($f);
            } 
		}
	}
	
	public function indexAction() {
        trackPage('/people',$this->_getParam("format"));
		$this->_helper->layout->disableLayout();
	}

	public function pplindex( $paging = true, $format = '' )
    {
		if ($format == '' ) $format = $this->_getParam("format");
		$offset = $this->_getParam('ofs');
		$length = $this->_getParam('len');
		if ( $length == "-1" ) $paging = false;
		if ( $offset === null) $offset = 0;
		if ( $length === null ) $length = 23;
		if ( array_key_exists("filter", $_GET )) $this->session->pplFlt = null;
		$fltstr = $this->getFltStr();
		$ppl = new Default_Model_Researchers();
		$this->session->pplFlt = $fltstr;
		$ppl->filter = FilterParser::getPeople($fltstr, ($this->_getParam("fuzzySearch") == '1'));
		if ( $this->_getParam("orderby") != '' ) $orderby = $this->_getParam("orderby"); else $orderby = "firstname";
		if ( $this->_getParam("orderbyOp") != '' ) $orderbyop = $this->_getParam("orderbyOp"); else $orderbyop = "ASC";
		if ( $orderby != '' ) {
			if ($orderby != "unsorted") {
				if ( $orderby == "firstname" ) $ppl->filter->orderBy(array("firstname ".$orderbyop,"lastname ".$orderbyop));
				elseif ( $orderby == "lastname" ) $ppl->filter->orderBy(array("lastname ".$orderbyop,"firstname ".$orderbyop));
				else $ppl->filter->orderBy($orderby." ".$orderbyop);
			}
			$this->view->orderby = $orderby;
			$this->view->orderbyOp = $orderbyop;
		}
		if ( $paging ) {
			$ppl->filter->limit($length+1);
			$ppl->filter->offset($offset);
		}
		$t1 = microtime(true);
		$total = $ppl->count();
		$listType = $this->_getParam("details");
		$ppl->refresh($format, '', $listType);
		$entries = $ppl->items;
		if ( $format == '' ) $this->cacheimages($entries);
		if ( $paging ) {
			$this->view->offset = $offset;
			$this->view->length = $length;
			$this->view->pageCount = ceil($total / ($length+1));
			$this->view->currentPage = floor($offset / ($length+1));
		} else {
			$this->view->offset = 0;
			$this->view->length = $total;
			if ($total != 0) { 
				$this->view->pageCount = ceil($total / ($total)); 
				$this->view->currentPage = floor($offset / ($total));
			} else {
				$this->view->pageCount = 1; 
				$this->view->currentPage = 1; 
			}    
		}
		$this->view->entries = $entries;
		$this->view->total = $total;
		include 'pplaccounting.php';
		$t2 = microtime(true);
		$dt = $t2 - $t1;
		$this->view->searchTime = round($dt,2);
		if ($this->_getParam("fuzzySearch") == '1') $this->view->fuzzySearch = '1';
		return $ppl->items;
    }

    public function getimageAction()
    {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$gender = '';
		if ( ($this->_getParam("id") == "0") || ($this->_getParam("id") == '')) {
			$image = 'NULL';
		} else {
			if ( file_exists(APPLICATION_PATH . "/../cache/ppl-image-".$this->_getParam("id").".png") ) {
                $image = file_get_contents(APPLICATION_PATH . "/../cache/ppl-image-".$this->_getParam("id").".png");
                $gender = '';
			} else {
				$ppl = new Default_Model_Researchers();
				$ppl->filter->id->equals($this->_getParam("id"));
                $image = base64_decode($ppl->items[0]->image);
                $gender = strtolower($ppl->items[0]->gender);
				$this->cacheimages($ppl->items);
			}
		}
        if (empty($image) || ($image == 'NULL') ) {
			switch ($gender) {
            case "female":
			    $image = file_get_contents(APPLICATION_PATH . "/../public/images/" . "person.png");
                break;
            case "robot":
			    $image = file_get_contents(APPLICATION_PATH . "/../public/images/" . "robot.gif");
                break;
            default:
			    $image = file_get_contents(APPLICATION_PATH . "/../public/images/" . "person.png");
            }
		}
		header('Content-type: image/png');
		echo $image;
	}

    public function detailsAction()
    {
        $pplID = $this->_getParam("id");
        trackPage('/people/details?id='.$pplID,$this->_getParam("format"));
        if ( $pplID == '' ) $pplID = $this->session->lastPplID;
        $this->_helper->layout->disableLayout();
		$ppl = new Default_Model_Researchers();
		if ( $this->session->userid !== null ) {
			if ( userIsAdminOrManager($this->session->userid) ) {
				$ppl->viewModerated = true;
			}
		}
		if ( $this->_getParam("id") == "0" ) {
			$this->view->entry = new Default_Model_Researcher();
			$this->view->entry->countryID = '0';
		} else {
			if(is_numeric($pplID) === true ){
				$ppl->filter->id->equals($pplID);
			}else if( substr($pplID, 0, 2) === "s:") {
				$pplCname = substr($pplID, 2);
				$ppl->filter->cname->ilike($pplCname);
			}
			
			$ppl->refresh($this->_getParam('format'), $this->_getParam('userid'));
			if ( count($ppl->items) > 0 ) {
				$this->view->entry = $ppl->items[0];
				$pplID = $this->view->entry->id;
			}
			
			// BEGIN: API logging hack
			$cid = 0; // clientID: 0 --> appDB portal
			if ( isset($_SERVER['REMOTE_ADDR']) && ($_SERVER['REMOTE_ADDR'] != '') ) {
				$src = "'" . $_SERVER['REMOTE_ADDR'] . "'";
			} else {
				$src = '';
			}
			if (is_numeric($this->session->userid) && $this->view->entry) { // only log if we have a valid userid
				$sql = "INSERT INTO ppl_api_log (pplid, timestamp, researcherid, source, ip) VALUES (" . $pplID . ", NOW(), " . $this->session->userid . ", " . $cid .", " . $src . ");";
				db()->query($sql)->fetchAll();
			}
			// END: API logging hack
			if( isnull($this->view->entry->image) === false ){
				$this->view->image = "/people/getimage?id=".$this->view->entry->id."&req=".urlencode($this->view->entry->lastUpdated);
			}
        }
        $this->view->dialogCount=$_GET['dc'];
		$this->view->positionTypes = new Default_Model_PositionTypes();
		$this->view->positionTypes->filter->orderBy('ord');
		$this->view->countries = new Default_Model_Countries();
		$this->view->countries->filter->orderBy('name');
		$this->view->contactTypes = new Default_Model_ContactTypes();
		if ( isnull($this->_getParam("tab")) == false ){
			$this->view->selectedTab = $this->_getParam("tab");
		}

		$this->view->session = $this->session;
	    if ( ($this->session->username !== null) && ($this->session->userid !== null) ) {
            $users = new Default_Model_Researchers();
            $users->filter->id->equals($this->session->userid);
			$this->view->user = $users->items[0];
        } else $this->view->user = null;
		
		//Setup vo membership data
		$this->view->entryVoMemberShip = "[]";
		$this->view->entryRelationsXml = EntityRelations::relationsToXml($this->view->entry->guid);
		if( is_null($this->view->entry) === false && is_numeric($this->view->entry->id) && intval($this->view->entry->id) > 0 ){
			$this->view->entryVoMemberShip = html_entity_decode(VoAdmin::getUserMembership($this->view->entry, true) );
		}
	}
	
	public function details2Action(){
        $this->_helper->layout->disableLayout();
    }

	public function permreqAction()
    {
		if (count($_POST) == 0) {
            $this->view->actions = new Default_Model_Actions();
            $this->view->actions->refresh();
            $this->view->objects = new Default_Model_ObjViews();
            $this->view->objects->refresh();
        } else {
            $ms=new Default_Model_Messages();
            $m=new Default_Model_Message();
            $m->receiverID = 520;
            $m->senderID = 'NULL';
            $acts = new Default_Model_Actions();
            $acts->filter->id->equals($_POST['action']);
            $act = $acts->items[0]->description;
            if ( isnull($_POST['guid']) ) {
                $target = '<i>(ANY)</i>';
            } else {
                $objs = new Default_Model_ObjViews();
                $objs->filter->guid->equals($_POST['guid']);
                $target = $objs->items[0]->name;
                if ( $objs->items[0]->objType == "1" ) {
                    $target = '<a href="#" onclick="showAppDetails(\'apps/details?id='.$objs->items[0]->id.'\');">'.$target.'</a>';
                } elseif ( $objs->items[0]->objType == "2" ) {
                    $target = '<a href="#" onclick="showPplDetails(\'people/details?id='.$objs->items[0]->id.'\');">'.$target.'</a>';
                }
            }
            $m->msg = 'Permission request from <a href="#" onclick="showPplDetails(\'people/details?id='.$this->session->userid.'\');">'.$this->session->fullName.'</a> for <i>'.$act.'</i> on '.$target.'<br/><div align="right"><a href="#" onclick="grantRequest('.$this->session->userid.','.$_POST['action'].',\''.$_POST['guid'].'\');">Grant</a> <a href="#" onclick="denyRequest('.$this->session->userid.','.$_POST['action'].',\''.$_POST['guid'].'\');">Deny</a></div><br/>';
            $m->isRead = "0";
            $ms->add($m);
        }
    }

    public function showimageAction()
    {
		$this->_helper->layout->disableLayout();
		$ppl = new Default_Model_Researchers();
		$ppl->filter->id->equals($this->_getParam("id"));
		if ( count($ppl->items) > 0 ) {
			$person = $ppl->items[0];
            $gender = strtolower($ppl->items[0]->gender);
			if ( isnull($person->image) ) {
                switch ($gender) {
                case "female":
                    $this->view->image = "/images/" . "person.png";
                    break;
                case "robot":
                    $this->view->image = "/images/" . "robot.gif";
                    break;
                default:
                    $this->view->image = "/images/" . "person.png";
                }
			} else {
				$this->view->image = "/people/getimage?id=".$person->id."&req=".urlencode($person->lastUpdated);
			}
		} else $this->view->image= '';
    }

    public function exportAction() {
   		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
        if (array_key_exists("type",$_GET))	$type = $_GET['type']; else $type = 'xml';
		$ppl = new Default_Model_Researchers();
		$ppl->filter = FilterParser::getPeople($this->_getParam("flt"));
        if ( $type == "xml" ) {
            $ppl->refresh("xmlexport", false, $this->session->userid);
        } else {
            $ppl->refresh("csvexport", false, $this->session->userid);
		}
		$s = '';
		foreach($ppl->items as $item) {
			$s = $s . preg_replace("/[\n\r]/", '', $item) . "\n";
		}
		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename=ppl'.time().'.'.$type);
		header('Content-type: text/'.($type==="xml"?"xml":"x-csv"));
		header("Pragma: no-cache");
		header("Expires: 0");
		header('Content-Length: '.strlen($s));
		if ( $type === 'xml' ) {
			echo '<researchers>';
			echo $s;
			echo '</researchers>';

		} else {
			echo '"Firstname","Lastname","Gender","Registered","Institution","Country","Role","Permalink","Software","Contacts"' . "\n";
			echo $s;
		}
    }

    public function export2Action()
    {
        $this->view->isSensitive = true;
    	$this->_helper->layout->disableLayout();
    	$type = $_GET['type'];
		if ( $type == "csv") {
			$this->view->type = 'text/x-csv';
					$this->view->fname = "people".time().".csv";
		} elseif ( $type == "xml" ) {
			$this->view->type = 'text/xml';
					$this->view->fname = "people".time().".xml";
		}
		//Check if user can retrieve sensitive contact information
		if($this->session->userid!==null){
			$users = new Default_Model_Researchers();
			$users->filter->id->equals($this->session->userid);
			$user = $users->items[0];
			if($user->privs->canBulkReadSensitiveData()){
				$this->view->isSensitive=false;
			}
		}
		//Get the current filtered data for the export
		$ppl = $this->pplindex(false);
		$this->view->people = $ppl;
    }
    
    public function leavemessageAction()
    {
        trackPage('/people/leavemessage',$this->_getParam("format"));
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		//validations
		if ( $this->session->userid == null ) {
			echo "";
			return;
		}
		if( !( isset($_POST['msg']) && isset($_POST['receiverID']) ) ){
			echo "";
			return;
		}
		$data = $_POST['msg'];
		$receiverID = $_POST['receiverID'];
		
		if( trim($data) == "" || is_numeric($receiverID) === false){
			echo "";
			return;
		}
		
		//Send message to user
		$ms=new Default_Model_Messages();
		$m=new Default_Model_Message();
		$m->receiverID = $receiverID;
		$m->senderID = $this->session->userid;
		$m->msg = $data;
		$m->isRead = "0";
		$ms->add($m);
		
		//Notify user for inbox message
		sendUserInboxNotification($receiverID, $this->session->fullName);
		echo "ok";
    }

	public function uploadframeAction() {
		$this->_helper->layout->disableLayout();
	}

	
    public function updateAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();    
		$hasEditRights = false;
		$entries = new Default_Model_Researchers();
		if ( $this->session->userid !== null ) {  // there is a user logged in
			$users = new Default_Model_Researchers();
			$users->filter->id->equals($this->session->userid);
			$user = $users->items[0];
			if ( $_POST['id'] == '' ) { // logged in user registering another user (e.g. a manager registering someone else)
					if ( userIsAdminOrManager($this->session->userid) ) {
							$entry = new Default_Model_Researcher(); //prepare new entry
							$entry->dateInclusion = date("Y-m-d");
							$entry->addedBy = $this->session->userid;
							$hasEditRights = true;
					} else $hasEditRights = false; // logged in user has no permission to register other users
			} else {
				$entry = $entries->item($_POST['id']); // this is an update, get existing data
				$hasEditRights = $user->privs->canEditPersonProfile($entry);
			}
		} else {
			 if ($this->session->username !== null)  { // no user logged in, but new user registering own self
				$entry = new Default_Model_Researcher(); //prepare new entry
				$entry->dateInclusion = date("Y-m-d");
				$entry->username = $this->session->username;
                $entry->lastLogin = time();
                $entry->password = $this->session->claimPassword;
                setAuthCookies($this->session->username, $this->session->claimPassword);
                $this->session->claimPassword = null;
				$hasEditRights = true;
			}
		}
		if ( $hasEditRights ) {

			$entry->lastName = $_POST['lastName'];
			$entry->firstName = $_POST['firstName'];
			$entry->gender = $_POST['gender'];
			$entry->institution = $_POST['institution'];
			$oldCountryID = $entry->countryID;
			$entry->countryID = $_POST['countryID'];		
			$oldRoleID = $entry->positionTypeID;
			$entry->positionTypeID = $_POST['positionTypeID'];
			if ($_POST['newimage'] !== "") {
				$imgfile = APPLICATION_PATH."/../public/".$_POST['newimage'];
				if ( file_exists(APPLICATION_PATH . "/../cache/ppl-image-".$entry->id.".png") ) unlink(APPLICATION_PATH . "/../cache/ppl-image-".$entry->id.".png");
				$entry->image = pg_escape_bytea(base64_encode(file_get_contents($imgfile)));
			}

			if ( $this->session->userid === null ) {
				$entries->add($entry);
				$this->session->userid = $entry->id;
				$this->session->user = $entry;
				$this->session->fullname = $entry->firstname." ".$entry->lastname;
			} else if ( $_POST['id'] == '' ) {
				$entries->add($entry);
				$this->session->lastPplID = $entry->id;
			} else $entry->save();
			$entries->filter->id->equals($entry->id);
			$found_new_entry = false;
			$search_new_entry_count = 0;
			while ((! $found_new_entry) || ($search_new_entry_count > 10)) {
				$entries->refresh();
				$found_new_entry = (count($entries->items) > 0);
				if (! $found_new_entry) sleep(1);
				$search_new_entry_count = $search_new_entry_count + 1;
			}
			if (! $found_new_entry) {
				error_log("Could not find new user entry in DB after 10 tries... This should not happen (userid: " . $entry->id . ")");
				return;
			}
			$entry = $entries->items[0];
			$ant = 'his/her';
			if ( ! isnull($entry->gender) ) {
				if ($entry->gender == 'male') $ant = 'his';
				if ($entry->gender == 'female') $ant = 'her';
			}

			$conts = new Default_Model_Contacts();
			$conts->refresh();
			for ($i = count($entry->contacts)-1; $i>=0; $i--) {
				$conts->remove($entry->contacts[$i]);
			}
			foreach ($_POST as $key => $value) {
				if ( (substr($key,0,7) === "contact") && (substr($key,0,11) !== "contactType") ) {
					$cnum = substr($key,7);
					$cont = new Default_Model_Contact();
					$cont->researcherID = $entry->id;
					$cont->data = $value;
					$cont->contactTypeID = $_POST['contactType'.$cnum];
					$conts->add($cont);
				}
			}
		}
		if( $entry && ($this->session->userid == null || $this->session->userid == $entry->id) ){
			$this->session->userid = $entry->id;
			//Reload session data in case of claim or save new account
			$ppl = new Default_Model_Researchers();
			$ppl->filter->id->equals($this->session->userid);
			$user = $ppl->items[0];
			$this->session->user = $user;
			$this->session->fullname = $user->firstname." ".$user->lastname;
			$this->session->userRole = $user->positionTypeID;
			$this->session->userCountryID = $user->countryID;
			$this->session->userCountryName = $user->country->name;
			$this->session->cname = $user->cname;
		}
    }
    
    public function uploadimageAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();    
		$upload_path = APPLICATION_PATH."/../public/upload/pplimage/";         //relative to this file
		$data = "";
		$file= "";
		foreach ($_FILES as $ufiledata) {
            $ufile=$ufiledata;//['uploadedfile'];
            if ( ($ufile['size'] <= 204800) && ($ufile['size'] > 0) ) {
                $file = tempnam($upload_path,'img');
                move_uploaded_file($ufile['tmp_name'], $file);
                $type = exif_imagetype($file);
                if ( $type != '' ) {
                    `convert $file ${file}.png`;
                    $file=$file.".png";
                    list($width, $height) = getimagesize($file);
                    $file=basename($file);
                    $data .='file='.$ufile['name'].',width='.$width.',height='.$height.',filename='.$file;
                }
            } else {
                $file='';
            }
		}
		echo($file);
    }
    
    public function ppllistAction()
    {
    	$this->_helper->layout->disableLayout();
		$this->view->entry = new Default_Model_Researchers();
		$this->view->entry->refresh();
    }

    public function authorizeroleAction()
    {
    	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
		// OBSOLETE		
    }

	public function reflectfilterAction() {
		$s = '<person:filter xmlns:person="http://appdb.egi.eu/api/0.2/person" xmlns:filter="http://appdb.egi.eu/api/0.2/filter">';
		$s .= FilterParser::fieldsToXML("any person country application vo discipline subdiscipline middleware", "person");
		$s .= '</person:filter>';
		$this->view->entries = $s;
	}

	public function validatefilterAction() {
		$this->view->entries = validateFilterActionHelper($this->_getParam("flt"), FilterParser::NORM_PPL);
	}

    public function primarycontactAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $id = $this->_getParam("id");
        $action = $this->_getParam("act");
        $action = strtolower(trim($action));
        if( $action == '' ){ $action = 'get'; } 
        $error = '';
        $res = '';
        $resid = '';
        if($this->session->userid==null || ($action==='set' && $id == '')){
            $this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
			$this->getResponse()->setHeader("Status","403 Forbidden");
            return;
        }
        if( $action === 'set'){
            $p = new Default_Model_Contacts();
            $p->filter->researcherid->equals($this->session->userid)->and($p->filter->id->equals($id));
            if($p->count()===0){
                $error = "The provided contact is not found";
            }else{
               $pi = $p->items[0];
               $pi->isprimary = true;
               $pi->save();
               $resid=$pi->id;
               $res=$pi->data;
            }
        }else{
            $p = new Default_Model_Contacts();
            $p->filter->researcherid->equals($this->session->userid)->and($p->filter->isprimary->equals(true));
            if(count($p->items)===0){
                $error = "The provided contact is not found";
            }else{
                $pi = $p->items[0];
                $resid = $pi->id;
                $res = $pi->data;
            }
        }
        if($error!==''){
            echo "<response error='" . $error . "'></response>";
        }else{
            echo "<response " . (($resid!=='')?"id='".$resid."'":"")." >" . $res . "</response>";
        }
    }
	
	public function userrequestsAction(){
		$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
		$guid = $this->session->userguid;
		$uid = $this->session->userid;
		
		header("Content-Type:text/xml");
		echo "<?xml version='1.0'?".">";
		if( is_null($guid) ){
			echo "<response error='Must be logged in'>unauthorized</response>";
			return;
		}
		
		if( isset($_GET["state"]) && isset($_GET["id"]) ){
			$this->setUserRequestToState($_GET["id"],$_GET["state"]);
			return;
		}
		$urs = new Default_Model_UserRequests();
		$s1 = new Default_Model_UserRequestTypesFilter();
		//$s1->name->equals("joinapplication");
		$s2 = new Default_Model_PermissionsFilter();
		$s2->actor->equals($guid);
		$s3 = new Default_Model_UserRequestStatesFilter();
		$s3->name->equals("submitted");
		$urs->filter->chain($s1->chain($s2->chain($s3,"AND"),"AND"),"AND");
		
		$reqsitems = $urs->items;
		$items = array_merge($reqsitems);
		
		//Fetch user requests for NILs
		if( userIsAdminOrManager($this->session->userid) === false && userIsNIL($this->session->userid) === true ){
			$nilusers = new Default_Model_UserRequests();
			$s1 = new Default_Model_UserRequestTypesFilter();
			$s1->id->numequals(3);
			$s2 = new Default_Model_ResearchersFilter();
			$s2->countryid->equals($this->session->userCountryID);
			$s3 = new Default_Model_UserRequestStatesFilter();
			$s3->name->equals("submitted");
			$s4 = new Default_Model_ActorGroupsFilter();
			$s4->id->numequals(-3);
			$nilusers->filter->chain($s1->chain($s2->chain($s3->chain($s4,"AND"),"AND"),"AND"),"AND");
			if( count($nilusers->items) > 0 ){
				$items = array_merge($items, $nilusers->items);
				$items = array_filter($items, 'uniqueDBObjectFilter');
			}
		}
		$count = count($items);
		if( $count === 0 ){
			echo "<response count='0'></response>";
			return;
		}
		
		$res = "";
		foreach($items as $r){
			$users = new Default_Model_Researchers();
			$users->filter->guid->equals($r->userguid);
			$u = $users->items[0];
			if($r->typeid !== 3 ){
				$app = new Default_Model_Applications();
				$app->filter->guid->equals($r->targetguid);
				$a = $app->items[0];
				//In case of 
				if($r->typeid == 2 ){
					if( ($a->ownerid != $uid) && ($a->addedby != $uid) && (! userIsAdminOrManager($uid)) ) {
						$count = $count -1;
						continue;
					}
				}
				$primarycategory = $a->getPrimaryCategory();
				$isvappliance = "false";
				if( trim($primarycategory->id) === "34" ){
					$isvappliance = "true";
				}
			}
			//error_log("USER COUNTRY: " . var_export($u->country,true));
			$res .= "<userrequest id='" .$r->id . "' created='" . date("c",  strtotime($r->created)) . (($r->lastupdated)?"' lastupdated='" . date("c", strtotime($r->lastupdated)):"") . "' targetguid='" . $r->targetguid . "'>";
			$res .= "<type id='".$r->requestType->id."'>" .$r->requestType->name . "</type>";
			if( $r->typeid !== 3 ){
				$res .= "<application id='".$a->id."' cname='".$a->cname."' isvirtualappliance='" . $isvappliance . "'>" . $a->name . "</application>";
			}
			$res .= "<user id='" . $u->id . "' >";
			$res .= "<name>".   $u->firstName ." " .$u->lastName . "</name>";
			$res .= "<cname>".   $u->cname . "</cname>";
			$res .= "<role>" . $u->positionType->description . "</role>";
			$res .= "<institution>" . $u->institution . "</institution>";
			$res .= "<country id='". $u->countryid ."' isocode='" . $u->country->ISOcode . "' >" . $u->country->name . "</country>";
			$res .= "<message>" . $r->userdata . "</message>";
			$res .= "</user>";
			$res .= "<state id='".$r->requestState->id."'>" . $r->requestState->name . "</state>";
			$res .= "</userrequest>";
		}
		echo "<response count='" . $count . "'>";
		echo $res;
		echo "</response>";
	}
	
	public function setUserRequestToState($reqid,$stateid){
		$err = '';
		$reqs = null;
		$req = null;
		$states = null;
		
		if ( is_numeric($reqid) === false ) {
			$err = 'Invalid user request id given.';
		} else if ( is_numeric($stateid) === false ) {
			$err = 'Invalid state given.';
		} else {
			$reqs = new Default_Model_UserRequests();
			$reqs->filter->id->equals($reqid);
			if ( $reqs->count() === 0 ) {
				$err = 'User request not found.';
			} else {
				$states = new Default_Model_UserRequestStates();
				$states->filter->id->equals($stateid);
				if ( $states->count() === 0 ){
					$err = 'User request state not found.';
				}
			}
		}
		
		if ( $err !== '' ){
			echo "<response error='" . $err . "'></response>";
			return;
		}
		
		db()->beginTransaction();
		try{
			$req = $reqs->items[0];
			$user = new Default_Model_Researchers();
			$user->filter->id->equals($this->session->userid);
			$actorguid = $user->items[0]->guid;
	        $actorid = $user->items[0]->id;	
			
			//Get group id
			if( $req->requestType->name === "accessgroup"){
				$groups = new Default_Model_ActorGroups();
				$groups->filter->guid->equals($req->targetguid);
				$group = $groups->items[0];
				$groupid = $group->id;
			}else{//Get application id
				$apps = new Default_Model_Applications();
				$apps->filter->guid->equals($req->targetguid);
				$app = $apps->items[0];
				$appid = $app->id;
			}
			
			//Get user(requestor) id
			$users = new Default_Model_Researchers();
			$users->filter->guid->equals($req->userguid);
			$user = $users->items[0];
			$userid = $user->id;
			$userguid = $user->guid;
			
			//Check if actor is the owner of the application in case of release manager request
			if($req->requestType->name == "releasemanager"){
				if ( ($app->ownerid != $actorid) && ($app->addedby != $actorid) && (! userIsAdminOrManager($actorid)) ) {
					db()->rollBack();
					echo "<response error='User needs to be owner of the software in order to grant release management privileges to other users.'></response>";
					return;
				}
			}
			if( $req->requestType->name !== "accessgroup"){ //in case of access groups we first include user and then accept or reject
				//NOTE:Must update request state before inserting in order to 
				//prevent database triggers from claiming the request.
				//Update request state
				$trans = 0;

				$req->stateid = $stateid;
				$req->actorguid = $actorguid;
				$req->save();
				$trans = 1;

				if($req->requestType->name == "joinapplication" && $stateid == 2){ //if accepted add to related contacts
					//Set relation between researcher and application(if there is none)
					$resapp = new Default_Model_ResearchersApps();
					$resappfilter = new Default_Model_ResearchersAppsFilter();
					$resapp->filter->appid->equals($appid)->and($resapp->filter->researcherid->equals($userid));
					if( $resapp->count() === 0 ) {
						$resapp = new Default_Model_ResearchersApp();
						$resapp->appid = $appid;
						$resapp->researcherid = $userid;
						$resapp->save();
					}
				}else if($req->requestType->name == "releasemanager" && $stateid == 2){
					$privs = new Default_Model_Privileges();
					$privs->filter->actor->equals($user->guid)->and($privs->filter->actionid->equals(30)->and($privs->filter->object->equals($app->guid)));
					if( count($privs->items) == 0){
						$prv = new Default_Model_Privilege();
						$prv->actor = $user->guid;
						$prv->actionid = 30;
						$prv->object = $app->guid;
						$prv->save();
					}
				}
				db()->commit();
				//Send email notification to requestor
				try{
					UserRequests::sendEmailResponseNotification($user, $app, $stateid, $req->requestType->name);
				}catch(Exception $e){
					error_log("EMAIL ERROR:Could not send email notification to user request response.Details:".$e->getMessage());
				}
			} else if( $req->requestType->name === "accessgroup" && intval($stateid) === 2 ){
				AccessGroups::handleUserGroupAction($this->session->userid, $user, "accept",array($group->id) );
			} else if( $req->requestType->name === "accessgroup" &&  intval($stateid) === 3 ){
				AccessGroups::handleUserGroupAction($this->session->userid, $user, "reject",array($group->id) );
			}
			db()->commit();
		}catch(Exception $e){
			db()->rollBack();
			error_log("Error while setting User request:" . $e->getMessage());
			if($trans == 0){
				echo "<response error='Error while updating user request'>" . $e->getMessage() . "</response>";
			} else if( $trans == 1) {
				echo "<response error='Error while updating software contact association'>" . $e->getMessage() . "</response>";
			} else {
				echo "<response error='Error while processing user request'>" . $e->getMessage() . "</response>";
			}
			return;
		}
		echo "<response id='".$req->id."' state='". $stateid."' ></response>";
	}
	
	public function apikeylistAction(){
		$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
		$uid = $this->session->userid;
		header("Content-Type:text/xml");
		echo "<?xml version='1.0'?" . ">";
		//Check if user is logged in
		if($_SERVER['HTTPS'] != "on"){
			header("HTTP/1.0 403 Forbidden");
			return;
		}
		if ($uid == null) {
			header("HTTP/1.0 403 Forbidden");
			echo "<apikeys error='Not logged in' ></apikeys>";
			return;
		}
		$apiconf = new Zend_Config_Ini('../application/configs/application.ini', $_SERVER['APPLICATION_ENV']);
		$apiconf = $apiconf->api;
		
		//Check if this is a request to generate new filter
		if($_SERVER['REQUEST_METHOD'] == 'PUT'){
			//Check if user has already reached the maximum number of generated api keys
			$userapikeys = new Default_Model_APIKeys();
			$userapikeys->filter->ownerid->equals($uid)->and($userapikeys->filter->authmethods->notequals(0));
			if(count($userapikeys->items)>=$apiconf->maxkeys){
				header("HTTP/1.0 400 Bad Request");
				if($apiconf->maxkeys=="1"){
					echo "<apikeys error='An API key is already generated for the current user.' ></apikeys>";
				}else{
					echo "<apikeys error='Generating more than ".$apiconf->maxnetfilters." API keys per user is not allowed.' ></apikeys>";
				}
				
				return;
			}
			parse_str(file_get_contents("php://input"),$post_vars);
			$netfs = array();
			if(isset($post_vars["netfilters"])) $netfs = $post_vars["netfilters"];
			//Check if number of given net filters are more than allowed
			if(count($netfs)>$apiconf->maxnetfilters){
				header("HTTP/1.0 400 Bad Request");
				if($apiconf->maxnetfilters=="1"){
					echo "<apikeys error='The current API key is already associated with a net filter.' ></apikeys>";
				}else{
					echo "<apikeys error='Associating more than ".$apiconf->maxnetfilters." netfilters per API key is not allowed.' ></apikeys>";
				}
				return;
			}
			//Check netfilters are given for new api key
			if(count($netfs)>0){
				//Check if given netfilters are in use by someone else
				$fs = new Default_Model_APIKeyNetfilters();
				$fsfilter = &$fs->filter;
				$tmpfs = new Default_Model_APIKeyNetfiltersFilter();
				$tmpfs->keyid->equals();
				foreach($netfs as $f){
					$tmpfs = new Default_Model_APIKeyNetfiltersFilter();
					$tmpfs->netfilter->equals($f);
					$fsfilter->chain($tmpfs,"OR");
				}
				if(count($fs->items)>0){
					header("HTTP/1.0 405 Method Not Allowed");
					echo "<apikeys error='Netfilter \"". $fs->items[0]->netfilter ."\" is already used.' ></apikeys>";
					return;
				}
			}
			//Generate new api key
			$apik = new Default_Model_APIKey();
			$apik->ownerid = $uid;
			$apik->save();
			//Check if key is generated
			$newkeyID = $apik->id;
			$apik = new Default_Model_APIKeys();
			$apik->filter->id->equals($newkeyID);
			if(count($apik->items)==0){
				header("HTTP/1.0 500 Internal Server Error");
				echo "<apikeys error='Could not generate new key.' ></apikeys>";
				return;
			}
			//Add netfilters for the newly generated key
			foreach($netfs as $net){
				$apinf = new Default_Model_APIKeyNetfilter();
				$apinf->netfilter = $net;
				$apinf->keyid = $newkeyID;
				$apinf->save();
			}
		} else if($_SERVER['REQUEST_METHOD'] == 'POST'){
			//Check if api key is given
			if(isset($_GET["k"])==false){
				header("HTTP/1.0 405 Method Not Allowed");
				echo "<apikeys error='No key provided.' ></apikeys>";
				return;
			}else{
				//Check if key exists
				$apkeys = new Default_Model_APIKeys();
				$apkeys->filter->id->equals($_GET["k"])->and($apkeys->filter->ownerid->equals($uid));
				if(count($apkeys->items)==0){
					header("HTTP/1.0 404 Not Found");
					echo "<apikeys error='Could not retrieve key' ></apikeys>";
					return;
				}
			}
			//Check new net filters validity
			$nflts = json_decode($_POST["data"]);
			$nflts = $nflts->netfilters;
			$nflts = array_unique($nflts);
			if(count($nflts)>$apiconf->maxnetfilters){
				//if the newly posted net filters are less than the stored filters then 
				//its a deletion, so in case the maximum net filter count is reduced after 
				//the insertion it won't cause a validation error.
				$oldnflts = new Default_Model_APIKeyNetfilters();
				$oldnflts->filter->keyid->equals($_GET["k"]);
				if(count($oldnflts->items)<=count($nflts)){
					header("HTTP/1.0 400 Bad Request");
					if($apiconf->maxnetfilters=="1"){
						echo "<apikeys error='The current API key is already associated with a net filter.' ></apikeys>";
					}else{
						echo "<apikeys error='Associating more than ".$apiconf->maxnetfilters." netfilters per API key is not allowed.' ></apikeys>";
					}
					return;
				}
			}
			for($i=0; $i<count($nflts); $i++){
				if($this->isValidNetFilter($nflts[$i]) === false){
					header("HTTP/1.0 400 Bad Request");
					echo "<apikeys error='Net filter \"" . $nflts[$i] . "\" is not valid' ></apikeys>";
					return;
				}
			}
			
			//Delete old netfilters
			$key = $apkeys->items[0];
			$nflts = new Default_Model_APIKeyNetfilters();
			$nflts->filter->keyid->equals($key->id);
			$nfltsitems = $nflts->items;
			for($i=count($nfltsitems)-1; $i>=0; $i--){
				$nflts->remove($nfltsitems[$i]);
			}
			//Insert new netfilters
			$nflts = json_decode($_POST["data"]);
			$nflts = $nflts->netfilters;
			if(count($nflts)>0){
				for($i=0; $i<count($nflts); $i++){
					if(trim(urldecode($nflts[$i])) == ""){
						continue;
					}
					$nf = new Default_Model_APIKeyNetfilter();
					$nf->netfilter = urldecode($nflts[$i]);
					$nf->keyid = $key->id;
					$nf->save();
				}
			}
		} else if($_SERVER['REQUEST_METHOD'] == 'DELETE'){
			//Check if api key is sent
			if(isset($_GET["k"])==false){
				echo "<apikeys error='No key provided' ></apikeys>";
				return;
			} else{
				$apkeys = new Default_Model_APIKeys();
				$apkeys->filter->id->equals($_GET["k"])->and($apkeys->filter->ownerid->equals($uid));
				if(count($apkeys->items)==0){
					echo "<apikeys error='Could not retrieve key' ></apikeys>";
					return;
				}
			}
			//Delete all netfilters associated with this api key
			$key = $apkeys->items[0];
			$key->authmethods = 0;
			$key->save();
		}
		//Return xml representation of API keys for the current user
		$apikeys = new Default_Model_APIKeys();
		$apikeys->filter->ownerid->equals($uid)->and($apikeys->filter->authmethods->notequals(0));
		$apikeys = $apikeys->items;
		echo "<apikeys count='" . count($apikeys) . "' >";
		if(count($apikeys)>0){
			foreach($apikeys as $apikey){
				echo "<apikey id='" . $apikey->id . "' key='" . $apikey->key . "' ownerid='" . $apikey->ownerid . "' createdon='" . $apikey->createdon . "' authmethods='" . $apikey->authmethods . "' ";
				if($apikey->sysaccountid != null){
					echo "sysaccount='" . $apikey->sysaccountid . "' ";
					$rscs = new Default_Model_Researchers();
					$rscs->filter->id->equals($apikey->sysaccountid);
					if(count($rscs->items)>0){
						echo "sysusername='" . $rscs->items[0]->username . "' ";
						echo "sysdisplayname='" . $rscs->items[0]->lastname . "' ";
					}
				}
				$netfilters = new Default_Model_APIKeyNetfilters();
				$netfilters->filter->keyid->equals($apikey->id);
				$netfilters = $netfilters->items;
				if(count($netfilters)>0){
					echo "netfilters='" . count($netfilters) . "' >";
					foreach($netfilters as $netfilter){
						echo "<netfilter value='" . $netfilter->netfilter . "' ></netfilter>";
					}
				}else{
					echo "netfilters='0'>";
				}
				echo "</apikey>";
			}
		}
		echo "</apikeys>";
	}
	
	private function isValidNetFilter($ip){
		$res = (isIPv4($ip)>0 || isIPv6($ip)>0 || isCIDR($ip)>0 || isCIDR6($ip)>0 );
		if($res==false){
			$res = (preg_match('/^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$/',$ip)>0);
		}
		return $res;
	}
	
	public function accesstokenlistAction(){
		$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
		$uid = $this->session->userid;
		header("Content-Type:text/xml");
		echo "<?xml version='1.0'?>";
		//Check if user is logged in
		if($_SERVER['HTTPS'] != "on"){
			header("HTTP/1.0 403 Forbidden");
			return;
		}
		if ($uid == null) {
			header("HTTP/1.0 403 Forbidden");
			echo "<accesstokens error='Not logged in' ></accesstokens>";
			return;
		}
		
		if($_SERVER['REQUEST_METHOD'] == 'PUT'){ //Generate new access token
			//Create an access token for current user
			$result = AccessTokens::createPersonalAccessToken($uid);
			if( $result !== true ){
				echo "<accesstokens error='".$result."' ></accesstokens>";
				return;
			}
		} else if($_SERVER['REQUEST_METHOD'] == 'POST'){ //Update netfilters of given tokenid
			//Check if acccess token exists
			$tokenid =  ( (isset($_GET["k"]))?intval($_GET["k"]):null );
			$nfltdata = json_decode($_POST["data"]);
			$nflts = array_unique($nfltdata->netfilters);
			$result = AccessTokens::setNetfilters($uid, $tokenid, $nflts);
			if( $result !== true ){
				echo "<accesstokens error='".$result."' ></accesstokens>";
				return;
			}
		} else if($_SERVER['REQUEST_METHOD'] == 'DELETE'){ //Delete given token along with its netfilters
			$tokenid = ( (isset($_GET["k"]))?intval($_GET["k"]):null );
			$result = AccessTokens::removeAccessToken($uid, $tokenid);
			if( $result !== true ){
				echo "<accesstokens error='".$result."' ></accesstokens>";
				return;
			}
		}
		
		//Return xml representation of access tokens for the current user
		$acctokenslist = new Default_Model_AccessTokens();
		$acctokenslist->filter->addedby->equals($uid)->and($acctokenslist->filter->type->like('personal'));
		$acctokens = $acctokenslist->items;
		echo "<accesstokens count='" . count($acctokens) . "' >";
		
		if(count($acctokens) === 0){
			echo "</accesstokens>";
			return;
		}
		
		foreach($acctokens as $acctoken){
			echo "<accesstoken id='" . $acctoken->id . "' token='" . $acctoken->token . "' addedby='" . $acctoken->addedbyid . "' createdon='" . $acctoken->createdon . "' tokentype='" . $acctoken->type . "' ";
			$netfilters = new Default_Model_AccessTokenNetfilters();
			$netfilters->filter->tokenid->equals($acctoken->id);
			$nfilters = $netfilters->items;
			echo "netfilters='" . count($nfilters) . "' >";
			foreach($nfilters as $netfilter){
				echo "<netfilter value='" . $netfilter->netfilter . "' ></netfilter>";
			}
			echo "</accesstoken>";
		}
		
		echo "</accesstokens>";
	}
	
	public function authenticationAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$uid = $this->session->userid;
		header("Content-Type:text/xml");
		echo "<?xml version='1.0'?>";
		//Check if user is logged in
		if($_SERVER['HTTPS'] != "on"){
			header("HTTP/1.0 403 Forbidden");
			return;
		}
		if ($uid == null) {
			header("HTTP/1.0 403 Forbidden");
			echo "<apikeys error='Not logged in' ></apikeys>";
			return;
		}
		if($_SERVER['REQUEST_METHOD'] == 'DELETE'){
			header("HTTP/1.0 400 Bad Request");
			return;
		}
		
		if($_SERVER['REQUEST_METHOD'] == "PUT"){
			parse_str(file_get_contents("php://input"),$post_vars);
			$keyid = null;
			$passwd = null;
			$displayname = null;
			if(isset($post_vars["key"])) $keyid = $post_vars["key"];
			if(isset($post_vars["pwd"])) $passwd = $post_vars["pwd"];
			if(isset($post_vars["name"])) $displayname = $post_vars["name"];
			
			if($keyid === null){
				header("HTTP/1.0 400 Bad Request");
				return;
			}
			if($passwd === null){
				header("HTTP/1.0 400 Bad Request");
				return;
			}
			if($displayname === null){
				header("HTTP/1.0 400 Bad Request");
				return;
			}
			$apikeys = new Default_Model_APIKeys();
			$apikeys->filter->id->equals($keyid)->and($apikeys->filter->ownerid->equals($uid));
			if(count($apikeys->items)==0){
				header("HTTP/1.0 404 Not Found");
				echo "<apikeys error='Could not retrieve key' ></apikeys>";
				return;
			}

			$apikey = $apikeys->items[0];
			if($apikey->ownerid != $uid){
				header("HTTP/1.0 404 Not Found");
				echo "<apikeys error='Could not retrieve key for user' ></apikeys>";
				return;
			}

			if($apikey->sysaccountid != null){
				header("HTTP/1.0 405 Method Not Allowed");
				echo "<apikeys error='Api key is already associated with a system user account' ></apikeys>";
				return;
			}
			
			$users = new Default_Model_Researchers();
			$users->filter->id->equals($uid);
			if(count($users->items)==0){
				header("HTTP/1.0 404 Not Found");
				echo "<apikeys error='Session user not found' ></apikeys>";
				return;
			}
			
			$usercountryid = $users->items[0]->countryid;
			
			$user = new Default_Model_Researcher();
			$uname = "appdb-" . generate_uuid_v4( );
			$user->firstname = "";
			$user->lastname = $displayname;
			$user->institution = "";
			$user->username = $uname;
			$user->password = md5($passwd);
			$user->accountType = 1;
			$user->countryid = $usercountryid;
			$user->positionTypeId = 4;
			$user->save();
			
			$apikeys = new Default_Model_APIKeys();
			$apikeys->filter->id->equals($keyid);
			$apikeys = $apikeys->items[0];
			$apikeys->sysaccountid = $user->id;
			$apikeys->authmethods = 2;
			$apikeys->save();
		}else if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$data = json_decode($_POST["data"]);
			$keyid = $data->keyid;
			
			//Check if api key exists
			$apikeys = new Default_Model_APIKeys();
			$apikeys->filter->id->equals($keyid)->and($apikeys->filter->ownerid->equals($uid));
			if(count($apikeys->items)==0){
				header("HTTP/1.0 404 Not Found");
				echo "<apikeys error='Could not retrieve key' ></apikeys>";
				return;
			}

			//Check if sys account exists
			$apikey = $apikeys->items[0];
			$sysid = $apikey->sysaccountid;
			$rs = new Default_Model_Researchers();
			$rs->filter->id->equals($sysid);
			if(count($rs->items)==0){
				header("HTTP/1.0 404 Not Found");
				echo "<apikeys error='Could not retrieve system user account.' ></apikeys>";
				return;
			}
			
			//Check request type
			if( isset($data->sysdisplayname) ) { //update system user name
				if( trim($data->sysdisplayname)=="" ) {
					header("HTTP/1.0 400 Bad Request");
					echo "<apikeys error='Empty names are not allowed.' ></apikeys>";
					return;
				}
				
				$s = $rs->items[0];
				$s->firstname = "";
				$s->lastname = $data->sysdisplayname;
				$s->save();
			} else if( isset($data->old) ) { //change password
				$s = $rs->items[0];
				if(!$data->new || trim($data->new)==""){
					header("HTTP/1.0 400 Bad Request");
					echo "<apikeys error='Empty value for the new password is not allowed.' ></apikeys>";
					return;
				}
				if($s->password != md5($data->old)){
					header("HTTP/1.0 400 Bad Request");
					echo "<apikeys error='The provided value for the old password is incorrect.' ></apikeys>";
					return;
				}
				$s->password = md5($data->new);
				$s->save();
			} else if( isset($data->msg) ) {
				$msg = base64_decode($data->msg);
				if( trim($msg) == '' ){
					header("HTTP/1.0 400 Bad Request");
					echo "<apikeys error='Empty message is not allowed' ></apikeys>";
					return;
				}
				$res = APIKeyRequests::sendPermissionsRequest($uid, $keyid, $msg);
				if( $res!==true && trim($res) !== '' ){
					header("HTTP/1.0 400 Bad Request");
					echo "<apikeys error='" . $res . "' ></apikeys>";
					return;
				}
			} else {
				header("HTTP/1.0 400 Bad Request");
				return;
			}
		}
		
		//Return xml representation of API keys for the current user
		$apikeys = new Default_Model_APIKeys();
		$apikeys->filter->ownerid->equals($uid)->and($apikeys->filter->authmethods->notequals(0));
		$apikeys = $apikeys->items;
		echo "<apikeys count='" . count($apikeys) . "' >";
		if(count($apikeys)>0){
			foreach($apikeys as $apikey){
				echo "<apikey id='" . $apikey->id . "' key='" . $apikey->key . "' ownerid='" . $apikey->ownerid . "' createdon='" . $apikey->createdon . "' authmethods='" . $apikey->authmethods . "' ";
				if($apikey->sysaccountid != null){
					echo "sysaccount='" . $apikey->sysaccountid . "' ";
					$rscs = new Default_Model_Researchers();
					$rscs->filter->id->equals($apikey->sysaccountid);
					if(count($rscs->items)>0){
						echo "sysusername='" . $rscs->items[0]->username . "' ";
						echo "sysdisplayname='" . $rscs->items[0]->lastname . "' ";
					}
				}
				$netfilters = new Default_Model_APIKeyNetfilters();
				$netfilters->filter->keyid->equals($apikey->id);
				$netfilters = $netfilters->items;
				if(count($netfilters)>0){
					echo "netfilters='" . count($netfilters) . "' >";
					foreach($netfilters as $netfilter){
						echo "<netfilter value='" . $netfilter->netfilter . "' ></netfilter>";
					}
				}else{
					echo "netfilters='0'>";
				}
				echo "</apikey>";
			}
		}
		echo "</apikeys>";
	}
	
	public function alphanumericreportAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		header('Content-type: text/xml');
		$r = getAlphnumericReport("researchers", $_GET["flt"]);
		$len = count($r);
		echo "<report count='".$len."'>";
		for( $i = 0; $i < $len; $i+=1 ) {
			echo "<item count='" . $r[$i]["cnt"] . "' value='" . $r[$i]["typechar"] . "' />";
		}
		echo "</report>";
	}
	
	public function nameavailableAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		if($this->session->userid===null || isset($_GET["cname"])===false){
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
			$this->getResponse()->setHeader("Status","403 Forbidden");
			return;
		}
		$name = $this->_getParam("cname");
		$id = $this->_getParam("id");
		$res = validatePplCName($name, $id);
				
		if ( $res === true ) {
            echo "<response>OK</response>";
		} else {
			echo "<response error='Url name already in use' cname='" . $res . "'></response>";
		}
	}
	
	public function accessgroupsAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$isget = ($_SERVER['REQUEST_METHOD'] === "GET")?true:false;
		$ispost = ($_SERVER['REQUEST_METHOD'] === "POST")?true:false;
		$pdata = ( ( $ispost && isset($_POST["data"]))?$_POST["data"]:null );
		$data = null;
		$profileid = null;
		$profile = null;
		$currentProfile = null;
		$actions = null;
		$notfound = false;
		
		//Check if user is logged in
		if( $this->session->userid===null ) {
			$notfound = true;
		}else{
			$researchers = new Default_Model_Researchers();
			$researchers->filter->id->equals($this->session->userid);
			if( count($researchers->items) > 0 ) {
				$currentProfile = $researchers->items[0];
			}else{
				$notfound = true;
			}
		}
		
		//Parse data from post or get
		if( $ispost && $notfound === false) {
			try{
				$profileid = ( (isset($pdata["id"]) === true)?$pdata["id"]:null );
				$actions = ( (isset($pdata["actions"]) === true)?$pdata["actions"]:array() );
				$actions = ( (is_array($actions))?$actions:array($actions) );
			} catch (Exception $ex) {
				echo '{"error":"Invalid data", "errormessage":"Could not parse given data"}';
				return;
			}
		} else if( $isget && $notfound === false ) {
			$profileid = ( (isset($_GET["id"])?$_GET["id"]:null) );
		}
		
		//Check if http methods have valid data
		if( $isget && is_numeric($profileid)=== false ) {
			$notfound = true;
		} else if ( $ispost && is_numeric($profileid) === false ) {
			$notfound = true;
		}
		
		if( $notfound === true ) {
			$this->getResponse()->clearAllHeaders();
			header('HTTP/1.0 404 Not Found');
			header("Status: 404 Not Found");
			return;
		}
		
		//Fetch researcher profile
		$researchers = new Default_Model_Researchers();
		$researchers->filter->id->equals($profileid);
		if( count($researchers->items) > 0 ) {
			$profile = $researchers->items[0];
		} else {
			echo '{"error":"Invalid data", "errormessage":"User profile not found"}';
			return;
		}
		
		$accessgroupperms = AccessGroups::getAccessGroupsPermissions($currentProfile, $profile);
		if( $ispost ){
			//in case of post
			foreach($actions as $action=>$ids){
				if( is_array($ids)  === false || count($ids) === 0 ){
					continue;
				}
				AccessGroups::handleUserGroupAction($currentProfile, $profile, $action, $ids, $accessgroupperms);
			}
		}
		
		$accessgroupperms = AccessGroups::getAccessGroupsPermissions($currentProfile, $profile);
		$accessgroupslist = array();
		foreach($profile->actorGroups as $g) {
			$accessgroupslist[] = array("id"=> $g->group->id,"name"=>$g->group->name,"payload"=>$g->payload, "suid"=>$g->actorguid);
		}
		
		echo json_encode( array("grouppermissions"=>$accessgroupperms , "accessgroups"=>$accessgroupslist) );
	}
}
