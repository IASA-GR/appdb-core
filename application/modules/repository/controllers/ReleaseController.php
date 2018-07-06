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

if ( $_SERVER["Repository_Enabled"] === 'true') {
	include_once('../library/repository.php');
}

class Repository_ReleaseController extends Zend_Controller_Action {
	public function init(){
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
		
        $this->session = new Zend_Session_Namespace('default');
		if(trim($_SERVER['REQUEST_METHOD']) === "GET"){
			if ($this->session->isLocked()) {
				$this->session->unLock();
			}
			session_write_close();
		}
		header('Access-Control-Allow-Origin: *');
    }
	
	public function itemAction(){
		$view = strtolower( ( ( isset($_GET["view"]) )?$_GET["view"]:"data" ) );
		$view = (( $view == "data" || $view == "html")?$view:"data");
		
		$id = ( ( isset($_GET["id"]) )?$_GET["id"]:null );
		$swid = ( ( isset($_GET["swid"]) )?$_GET["swid"]:null );
		
		if( $_SERVER['REQUEST_METHOD'] != "GET" || 
			$_SERVER["Repository_Enabled"] !== 'true' ||
			($view=="html" &&  is_numeric($this->session->userid) == false ) || 
			(is_numeric($swid) == false && is_numeric($id) == false )
			) {
			header("Status: 404 Not Found");
			return;
		}
	
		if( $id == null && $swid !== null) {
			$rl = new RestProductReleaseLatestItem( array("swid" => $swid) );
		}else{
			$rl = new RestProductReleaseItem( array("id" => $id) );
		}
		
		$res= $rl->getRawData();
		if( $view == "data" ) {
			$this->_helper->viewRenderer->setNoRender();
			if( $rl->getError() != RestErrorEnum::RE_OK ){
				switch( $rl->getError() ){
					case RestErrorEnum::RE_ACCESS_DENIED:
						break;
				}
				header("Status: 404 Not Found");
				echo $rl->getError() ;
				return;
			}else{
				header("Content-Type: text/xml");
				echo $rl->get($res);
			}
		}else{
			$this->view->item = $res;
		}
	}
	
	public function listAction(){
		
		$view = strtolower( ( ( isset($_GET["view"]) )?$_GET["view"]:"data" ) );
		$view = (( $view == "data" || $view == "html")?$view:"data");
			
		$swid = ( ( isset($_GET["swid"]) )?$_GET["swid"]:null );
		$parentid = ( ( isset($_GET["parentid"]) )?$_GET["parentid"]:0 );
		
		if( $_SERVER['REQUEST_METHOD'] != "GET" || 
			$_SERVER["Repository_Enabled"] !== 'true' ||
			is_numeric($swid) == false
			) {
			header("Status: 404 Not Found");
			return;
		}
		if( $view == "data" ){
			$this->_helper->viewRenderer->setNoRender();
			$rl = new RestProductReleaseList(array("swid" => $swid, "parentid" => $parentid));
			$res= $rl->get();
			if( $rl->getError() != RestErrorEnum::RE_OK ){
				switch($rl->getError() ){
					case RestErrorEnum::RE_ACCESS_DENIED:
						break;
				}
			}else{
				header("Content-Type: text/xml");
				echo $res;
			}
		} else {
			$this->view->id = $swid;
			$this->view->session = $this->session;
			$this->view->releases = Repository::getProductReleaseList($swid);
			$this->view->hasReleases = ( (count($this->view->releases) === 0)?false:true);
		}
	}
	
	public function managerAction(){
		if ( $_SERVER["Repository_Enabled"] !== 'true') {
			$this->_helper->viewRenderer->setNoRender();
			header("Status: 404 Not Found");
			return;
		}
		$swid = $this->_getParam("id");
		$dtype = $this->_getParam("datatype");
		
		if( trim($dtype) != "" ){
			$this->_helper->viewRenderer->setNoRender();
			$dtype = strtolower($dtype);
			switch($dtype){
				case "basereleases":
					$res = Repository::getProductBaseRelease($swid);
					echo '<?xml version="1.0" encoding="UTF-16" standalone="yes"?>';
					echo "<repository swid='" . $swid . "' datatype='release' datasubtype='base' >"; 
					for($i=0; $i<count($res); $i++){
						echo "<release id='" . $res[$i]["id"] . "' displayversion='" . $res[$i]["displayVersion"] . "' repositoryarea='" . $res[$i]["repoAreaName"] . "' />\n";
					}
					echo '</repository>';
					return;
				default:
					echo '<?xml version="1.0" encoding="UTF-16" standalone="yes"?><response error="Unknown request">'; 
					return;
			}
			
		} else {
			$this->view->id = 0;
			$this->view->session = null;
			$this->view->releases = array();
			$this->view->hasReleases = ( (count($this->view->releases) === 0)?false:true);
			$this->view->targets = array();
			$this->view->canManageRelease = false;
			$this->view->hasPendingRequest = false;
			if( is_numeric($swid) ){
				$this->view->id = $swid;
				$this->view->session = $this->session;
				$this->view->canManageRelease = Repository::canManageRelease($swid,$this->session->userid);
				try{
					$this->view->releases = Repository::getProductReleaseList($swid);
					$this->view->hasReleases = ( (count($this->view->releases) === 0)?false:true);
				}catch(Exception $e){
					$this->view->sqlerror = "Could not retrieve releases information.";
					$this->view->sqlerrordescription = $e->getMessage();
				}
				//Check if user has requested for release manager permissions
				if( $this->session->userid && $this->view->canManageRelease == false ){
					//Get current user GUID
					$ps = new Default_Model_Researchers();
					$ps->filter->id->equals($this->session->userid);
					if( count($ps->items) > 0 ){
						$user = $ps->items[0];
						$uguid = $user->guid;
						$apps = new Default_Model_Applications();
						$apps->filter->id->equals($swid);
						if( count($apps->items) > 0 ){
							//Get current software id
							$app = $apps->items[0];
							$urs = new Default_Model_UserRequests();
							$s1 = new Default_Model_UserRequestTypesFilter();
							$s1->name->equals("releasemanager");
							$s2 = new Default_Model_UserRequestsFilter();
							$s2->targetguid->equals($app->guid)->and($s2->userguid->equals($uguid));
							$s4 = new Default_Model_UserRequestStatesFilter();
							$s4->id->equals(1);
							$urs->filter->chain($s1->chain($s2->chain($s4,"AND"),"AND"),"AND");
							if( count($urs->items) > 0 ){
								$this->view->hasPendingRequest = true;
							}
						}
					}
					
				}
			}	
		}
	}
	
	public function newreleaseAction(){
		$this->_helper->layout->disableLayout();
		$userid = $this->session->userid;
		if( is_numeric($userid) == false || $_SERVER["Repository_Enabled"] !== 'true'){
			$this->_helper->viewRenderer->setNoRender();
			header("Status: 404 Not Found");
			return;
		}
		$this->view->error = "";
		if( $_SERVER['REQUEST_METHOD'] == "POST" ) {
			$this->_helper->viewRenderer->setNoRender();
			$swid = ( ( isset($_POST["swid"]) )?$_POST["swid"]:null );
			$swname = ( ( isset($_POST["swname"]) )?$_POST["swname"]:null );
			$repoareaid = ( ( isset($_POST["repoareaname"]) )?$_POST["repoareaname"]:null );
			if( Repository::canManageRelease($swid,$userid) === false){
				header("Status: 404 Not Found");
				return;
			}
			header("Content-Type: text/xml");
			if( is_numeric($swid) == false ){
				echo "<response error='No software id given to create new release.' ></response>";
				return;
			}
			
			$parentid = ( ( isset($_POST["parentid"]) )?$_POST["parentid"]:"" );
			if( trim($parentid) == "" ) $parentid = 0;
			if( is_numeric($parentid) == false ){
				echo "<response error='Release update is not associated with any release.' ></response>";
				return;
			}
			$displayVersion = ( ( isset($_POST["displayversion"]) )?$_POST["displayversion"]:null );
			if( trim($displayVersion) == "" ){
				echo "<response error='No display version given for the release.' ></response>";
				return;
			}
			
			
			$id = Repository::createRelease($swid, $swname, $displayVersion, $repoareaid, $parentid, $userid);
			if( is_numeric($id) == true ){
				Repository::addReleaseByExternalId($id, $this->session->userid, "1");
				Repository::addReleaseByExternalId($id, $this->session->userid, "2");
				$rel = new Repository_Model_MetaProductReleases();
				$rel->filter->id->equals($id);
				if( count($rel->items) > 0 ){
					$r = $rel->items[0];
					$newrel = new RestRepositoryAreaItem(array("id" => $r->repoareaid));
					echo "<response id='".$id."'>";
					echo $newrel->get();
					echo "</response>";
                                        Repository::markSoftwareAsUpdated($swid, $userid);
				} else {
					echo "<response error='Could not retrieve new product release information.'></response>";
				}
			} else if( is_numeric($id) == false ) {
				echo "<response error='" . $id . "'></response>";
			} else {
				echo "<response error='Unkown error occured during release creation.'></response>";
			}
		} else if( $_SERVER['REQUEST_METHOD'] == "GET" ) {
			$swid = $this->_getParam("swid");
			if( is_numeric($swid) == false ){
				$this->view->error = "No software id given";
				return;
			}
			$rtype = strtolower(trim($this->_getParam("releasetype")));
			if( $rtype == "" ) $rtype = "major";
			switch($rtype){
				case "update":
					break;
				case "major":
					break;
				default:
					$this->view->error = "Invalid release type given. The release type can be either major or update.";
					return;
			}
			$this->view->appName = $this->_getParam("name");
			$this->view->baselist = Repository::getProductBaseReleases($swid);
			$this->view->type = $rtype;
		} else {
			$this->_helper->viewRenderer->setNoRender();
			header("Status: 404 Not Found");
			return;
		}
	}
	
	public function createnewAction(){
		$this->_helper->layout->disableLayout();
		if( is_numeric($this->session->userid) == false || $_SERVER["Repository_Enabled"] !== 'true'){
			$this->_helper->viewRenderer->setNoRender();
			header("Status: 404 Not Found");
			return;
		}
		$this->view->error = "";
		
		
		if( $_SERVER['REQUEST_METHOD'] == "POST" ){
			header("Content-Type: text/xml");
			$this->_helper->viewRenderer->setNoRender();
			$swid = ( ( isset($_POST["swid"]) )?$_POST["swid"]:null );
			$repoarea = ( ( isset($_POST["repoarea"]) )?$_POST["repoarea"]:null );
			$parentid = ( ( isset($_POST["parentid"]) )?$_POST["parentid"]:null );
			$displayVersion = ( ( isset($_POST["displayversion"]) )?$_POST["displayversion"]:null );
			
			echo '<?xml version="1.0" encoding="UTF-16" standalone="yes"?>';
			if( is_numeric($swid) == false ){
				echo "<response error='No software id given to create new release.' ></response>";
				return;
			}
			
			$parentid = ( ( isset($_POST["parentid"]) )?$_POST["parentid"]:"" );
			if( trim($parentid) == "" ) $parentid = 0;
			if( is_numeric($parentid) == false ){
				echo "<response error='Release update is not associated with any release.' ></response>";
				return;
			}
			
			$displayVersion = ( ( isset($_POST["displayversion"]) )?$_POST["displayversion"]:null );
			if( trim($displayVersion) == "" ){
				echo "<response error='No display version given for the release.' ></response>";
				return;
			}
			
			$isValid = Repository::validateNewRelease($swid, $displayVersion, $repoarea, $parentid);
			if( $isValid !== true ){
				$this->printNewReleaseValidationResults($isValid);
				return;
			}
			
			$id = Repository::createRelease($swid, $displayVersion, $repoarea, $parentid);
			if( is_numeric($id) == true ){
                                echo "<response id='".$id."'>success</response>";
                                Repository::markSoftwareAsUpdated($swid, $this->session->userid);
			} else if( trim($id) == "" ) {
				echo "<response error='" . $id . "'></response>";
			} else {
				echo "<response error='Unkown error occured during release creation.'></response>";
			}
			
		} else if( $_SERVER['REQUEST_METHOD'] == "GET" ){
			$swid = $this->_getParam("swid");
			if( is_numeric($swid) == false ){
				$this->view->error = "No software id given";
				return;
			}
			$rtype = strtolower(trim($this->_getParam("releasetype")));
			if( $rtype == "" ) $rtype = "major";
			switch($rtype){
				case "update":
					$this->view->majorlist = Repository::getProductMajorReleaseList($swid);
					break;
				case "major":
					break;
				default:
					$this->view->error = "Invalid release type given. The release type can be either major or update.";
					return;
			}
			$this->view->type = $rtype;
		} else {
			$this->_helper->viewRenderer->setNoRender();
			header("Status: 404 Not Found");
			return;
		}
	}

	public function propertyAction(){
		$this->_helper->viewRenderer->setNoRender();
		$id = $this->_getParam("id");
		$pname = trim(strtolower($this->_getParam("name")));
		$value = $this->_getParam("value");
		if( $_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST["value"]) ){
			$value = trim($_POST["value"]);
		}
		if( $_SERVER["Repository_Enabled"] !== 'true' ||
			is_numeric($id) == false || 
			trim($pname) == "" ||
			Repository::canManageEntity($this->session->userid, $id, "release") == false
			) {
			header("Status: 404 Not Found");
			echo RepositoryError::toXML("Not allowed");
			return;
		}
		$namemap = array(
				"releasenotes" => "releaseNotes",
				"installationnotes" => "installationNotes",
				"additionaldetails" => "additionalDetails",
				"changelog" => "changeLog",
				"description" => "description",
				"knownissues" => "knownIssues",
				"priority" => "priority"
		);
		$name = $namemap[$pname];
		if( $_SERVER['REQUEST_METHOD'] == "GET"){
			$rl = new RestProductReleasePropertyItem( array("id" => $id, "name"=>$name) );
			$res = $rl->get();
			
		} else if($_SERVER['REQUEST_METHOD'] == "POST"){
			$rl = new RestProductReleasePropertyItem( array("id" => $id, "name" => $name, "value" => $value));
			$res = $rl->post();
		} else {
			header("Status: 404 Not Found");
			echo RepositoryError::toXML("Invalid method");
			return;
		}
		
		header("Content-Type: text/xml");
		if ( $res === false ){
			echo RepositoryError::toXML($rl);
		}else{
			echo $res;
                        Repository::markSoftwareAsUpdatedByReleaseId($id, $this->session->userid);
		}
	}
	
	private function printNewReleaseValidationResults($errs=true){
		header("Content-Type: text/xml");
		//Start rendering response
		$xml = '<?xml version="1.0" encoding="UTF-16" standalone="yes"?>';

		if ( $errs === false ){
			header("Status: 404 Not found");
			echo "<response error='No access'>OK</response>";
			return;
		}

		if( $errs === true ){
			echo "<response success='true'>OK</response>";
			return ;
		}

		$result_displayversion = $errs["displayversion"];
		$result_repoarea = $errs["repoarea"];

		//On Success
		if( count($result_displayversion) == 0 && count($result_repoarea) == 0 ){
			echo "<response success='true'>OK</response>";
			return;
		}

		//On Errors
		echo "<response hasErrors='true'>";
		if( count($result_displayversion) > 0 ){
			echo "<displayversion>";
			for($i = 0; $i < count($result_displayversion); $i+=1 ){
				echo "<error value='" . $result_displayversion[$i] . "' ></error>";
			}
			echo "</displayversion>";
		}

		if( count($result_repoarea) > 0 ){
			echo "<repoarea>";
			for($i = 0; $i < count($result_repoarea); $i+=1 ){
				echo "<error value='" . $result_repoarea[$i] . "' ></error>";
			}
			echo "</repoarea>";
		}
		echo "</response>";
	}
	
	public function validatedisplayversionAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		//Get parameters
		$swid = $this->_getParam("swid");
		$parentid = $this->_getParam("parentid");
		$displayversion = $this->_getParam("displayversion");
		$repoareaname = $this->_getParam("repoareaname");

		if( //Check parameters
			is_numeric($this->session->userid) == false ||
			$_SERVER["Repository_Enabled"] !== 'true'
		) {
			$this->printNewReleaseValidationResults(false);
			return;
		}
		
		$errs = Repository::validateNewRelease($swid, $displayversion, $repoareaname, $parentid);
		$this->printNewReleaseValidationResults($errs);
	}
	
	public function appdbsyncinitAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		if( localRequest() == false || 
			$_SERVER["REQUEST_METHOD"] != "GET"
		){
			return;
		}
		
		RepositoryServices::AppDBSyncInitData();
	}
	
}
?>
