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

class Repository_RepositoryareaController extends Zend_Controller_Action {
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
	
	public function listAction(){
		$this->_helper->viewRenderer->setNoRender();
		$swid = ( ( isset($_GET["swid"]) )?$_GET["swid"]:null );
		
		if( $_SERVER['REQUEST_METHOD'] != "GET" || 
			$_SERVER["Repository_Enabled"] !== 'true' || 
			is_numeric($swid) === false 
			) {
			header("Status: 404 Not Found");
			return;
		}
		
		$params = array();
		if( $swid ){
			$params["swid"] = $swid;
		}
		$rl = new RestRepositoryAreaList( $params );
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
	}
	
	public function itemAction(){
		$id = ( ( isset($_GET["id"]) )?$_GET["id"]:null );
		$swid = ( ( isset($_GET["swid"]) )?$_GET["swid"]:null );
		
		if( $_SERVER['REQUEST_METHOD'] != "GET" || 
			$_SERVER["Repository_Enabled"] !== 'true' ||
			(is_numeric($swid) == false && is_numeric($id) == false )
			) {
			header("Status: 404 Not Found");
			return;
		}
	
		if( $id == null && $swid !== null) {
			$rl = new RestRepositoryAreaLatestItem( array("swid" => $swid) );
		}else{
			$rl = new RestRepositoryAreaItem( array("id" => $id) );
		}
		
		$res= $rl->getRawData();
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
			trim($pname) == ""
			) {
			header("Status: 404 Not Found");
			echo RepositoryError::toXML("Not allowed");
			return;
		}
		
		$namemap = array(
				"installationnotes" => "installationNotes",
				"additionaldetails" => "additionalDetails",
				"knownissues" => "knownIssues",
				"description" => "description"
		);
		$name = $namemap[$pname];
		if( $_SERVER['REQUEST_METHOD'] == "GET"){
			$rl = new RestRepositoryAreaPropertyItem( array("id" => $id, "name"=>$name) );
			$res = $rl->get();
			
		} else if($_SERVER['REQUEST_METHOD'] == "POST"){
			$rl = new RestRepositoryAreaPropertyItem( array("id" => $id, "name" => $name, "value" => $value));
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
		}
	}
}
?>