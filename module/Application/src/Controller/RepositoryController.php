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

if ( $_SERVER["Repository_Enabled"] === 'true') {
	include_once(__DIR__ . '/../../../../vendor/repository.php');
}


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class RepositoryController extends AbstractActionController {
	public function init(){
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        $this->session = new Zend_Session_Namespace('default');
    }

	public function managerAction(){
		$this->_helper->layout->disableLayout();
		if ( $_SERVER["Repository_Enabled"] !== 'true') {
			$this->_helper->viewRenderer->setNoRender();
			header("Status: 404 Not Found");
			return;
		}
		$appid = $this->getRequest()->getParam("id");
		$dtype = $this->getRequest()->getParam("datatype");
		
		if( trim($dtype) != "" ){
			$this->_helper->viewRenderer->setNoRender();
			$dtype = strtolower($dtype);
			switch($dtype){
				case "basereleases":
					$res = Repository::getProductBaseRelease($appid);
					echo '<?xml version="1.0" encoding="UTF-16" standalone="yes"?>';
					echo "<repository appdbid='" . $appid . "' datatype='release' datasubtype='base' >"; 
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
			if( is_numeric($appid) ){
				$this->view->id = $appid;
				$this->view->session = $this->session;
				$this->view->releases = Repository::getProductReleaseList($appid);
				$this->view->hasReleases = ( (count($this->view->releases) === 0)?false:true);
				$this->view->targets = Repository::getTargets();
			}	
		}
	}
	
	public function newreleaseAction(){
		$this->_helper->layout->disableLayout();
		if( is_numeric($this->session->userid) == false || $_SERVER["Repository_Enabled"] !== 'true'){
			$this->_helper->viewRenderer->setNoRender();
			header("Status: 404 Not Found");
			return;
		}
		$this->view->error = "";
		if( $_SERVER['REQUEST_METHOD'] == "POST" ) {
			$this->_helper->viewRenderer->setNoRender();
			$appid = ( ( isset($_POST["appid"]) )?$_POST["appid"]:null );
			$repoareaid = ( ( isset($_POST["repoareaname"]) )?$_POST["repoareaname"]:null );
			
			echo '<?xml version="1.0" encoding="UTF-16" standalone="yes"?>';
			if( is_numeric($appid) == false ){
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
			
			
			$id = Repository::createRelease($appid, $displayVersion, $repoareaid, $parentid);
			if( is_numeric($id) == true ){
				echo "<response id='".$id."'>success</response>";
			} else if( is_numeric($id) == false ) {
				echo "<response error='" . $id . "'></response>";
			} else {
				echo "<response error='Unkown error occured during release creation.'></response>";
			}
		} else if( $_SERVER['REQUEST_METHOD'] == "GET" ) {
			$appid = $this->getRequest()->getParam("appid");
			if( is_numeric($appid) == false ){
				$this->view->error = "No software id given";
				return;
			}
			$rtype = strtolower(trim($this->getRequest()->getParam("releasetype")));
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
			$this->view->appName = $this->getRequest()->getParam("name");
			$this->view->baselist = Repository::getProductBaseReleases($appid);
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
			$this->_helper->viewRenderer->setNoRender();
			$appid = ( ( isset($_POST["appid"]) )?$_POST["appid"]:null );
			$repoarea = ( ( isset($_POST["repoarea"]) )?$_POST["repoarea"]:null );
			$parentid = ( ( isset($_POST["parentid"]) )?$_POST["parentid"]:null );
			$displayVersion = ( ( isset($_POST["displayversion"]) )?$_POST["displayversion"]:null );
			
			echo '<?xml version="1.0" encoding="UTF-16" standalone="yes"?>';
			if( is_numeric($appid) == false ){
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
			
			$isValid = Repository::validateNewRelease($appid, $displayVersion, $repoarea, $parentid);
			if( $isValid !== true ){
				$this->printNewReleaseValidationResults($isValid);
				return;
			}
			
			$id = Repository::createRelease($appid, $displayVersion, $repoarea, $parentid);
			if( is_numeric($id) == true ){
				echo "<response id='".$id."'>success</response>";
			} else if( trim($id) == "" ) {
				echo "<response error='" . $id . "'></response>";
			} else {
				echo "<response error='Unkown error occured during release creation.'></response>";
			}
			
		} else if( $_SERVER['REQUEST_METHOD'] == "GET" ){
			$appid = $this->getRequest()->getParam("appid");
			if( is_numeric($appid) == false ){
				$this->view->error = "No software id given";
				return;
			}
			$rtype = strtolower(trim($this->getRequest()->getParam("releasetype")));
			if( $rtype == "" ) $rtype = "major";
			switch($rtype){
				case "update":
					$this->view->majorlist = Repository::getProductMajorReleaseList($appid);
					break;
				case "major":
					$this->view->targets = Repository::getTargets();
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

	private function printNewReleaseValidationResults($errs=true){
		//Start rendering response
		echo '<?xml version="1.0" encoding="UTF-16" standalone="yes"?>';

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
		$appid = $this->getRequest()->getParam("appid");
		$parentid = $this->getRequest()->getParam("parentid");
		$displayversion = $this->getRequest()->getParam("displayversion");
		$repoareaname = $this->getRequest()->getParam("repoareaname");

		if( //Check parameters
			is_numeric($this->session->userid) == false ||
			$_SERVER["Repository_Enabled"] !== 'true'
		) {
			$this->printNewReleaseValidationResults(false);
			return;
		}
		
		$errs = Repository::validateNewRelease($appid, $displayversion, $repoareaname, $parentid);
		$this->printNewReleaseValidationResults($errs);
	}
	
	public function releaselistAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		$content = "release";
		$type = "list";
		$appid = ( ( isset($_GET["appid"]) )?$_GET["appid"]:null );
		$parentid = ( ( isset($_GET["parentid"]) )?$_GET["parentid"]:0 );
		
		if( $_SERVER['REQUEST_METHOD'] != "GET" || 
			$_SERVER["Repository_Enabled"] !== 'true' ||
			is_numeric($appid) == false
			) {
			header("Status: 404 Not Found");
			return;
		}
		$rl = new RestReleaseList(array("appid" => $appid, "parentid" => $parentid));
		$res= $rl->get();
		if( $rl->getError() != RestErrorEnum::RE_OK ){
			switch($rl->getError() ){
				case RestErrorEnum::RE_ACCESS_DENIED:
					break;
			}
		}else{
			header('Content-Type: text/xml');
			echo '<?xml version="1.0" encoding="UTF-16" standalone="yes"?>';
			echo $res;
		}
	}
}
?>
