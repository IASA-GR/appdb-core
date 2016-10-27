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
	require_once('repository.php');
}
class Repository_ContactsController extends Zend_Controller_Action {
	
	public function init(){
        ob_start();
		ob_get_clean();
		ob_end_clean();
		/* Initialize action controller here */
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$this->session = new Zend_Session_Namespace('default');
		header('Access-Control-Allow-Origin: *');
    }
	
	public function contactimageAction(){
		$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
		header('PRAGMA: NO-CACHE');
        header('CACHE-CONTROL: NO-CACHE');
		header('Content-type: image/png');
		ob_start();
		$text = new textPNG;
		
		$id = $this->_getParam("id");
		$pseudoid = $this->_getParam("pseudoid");
		$msg = "";
       
		if( is_numeric($id) ){
			$r = new Repository_Model_MetaContacts();			
			$r->filter->id->equals($id);
			if( count($r->items) > 0 ){
				$msg = $r->items[0]->email;
			}

		} else if( trim($pseudoid)!=="" && preg_match("/^[0-9a-zA-Z]+$/", $pseudoid) >0 ) {
			$r = new Repository_Model_VMetaProductRepoAreaContacts();
			$r->filter->id->equals($pseudoid);
			if( count($r->items) > 0 ){
				$msg = $r->items[0]->email;
			}
		} else {
			$msg = $id;
		}
		
		$this->view->text = $text;
		$text->msg = $msg;
		$text->size = '9';
		ob_end_clean();
		$text->draw();
	}
	
	public function removeAction(){
		$id = $this->_getParam("id");
		error_log("entered remove...");
		if( $_SERVER['REQUEST_METHOD'] !== "POST" || 
			$_SERVER["Repository_Enabled"] !== 'true' ||
			(is_numeric($id) === false ) ||
			(is_numeric($this->session->userid) === false)
			) {
			header("Status: 404 Not Found");
			return;
		}
		
		header("Content-Type: text/xml");
		$res = RepositoryContacts::remove($id, $this->session->userid);
		error_log("removing " . $id . " for user " . $this->session->userid);
		if( $res !== true ){
			echo "<response error='" . $res . "'></response>";
			return;
		}
		echo "<response success='true'>OK</response>";
	}
	
	public function addAction(){
		$data = array("assocId" => $this->_getParam("associatedid"),
			"assocEntity" => $this->_getParam("associatedtype"),
			"externalId" => $this->_getParam("externalid"),
			"contactTypeId" => $this->_getParam("contacttype"),
			"firstname" => $this->_getParam("firstname"),
			"lastname" => $this->_getParam("lastname"),
			"email" => $this->_getParam("email"));
		if( !$data["assocEntity"] ) $data["assocEntity"] = 'release';
		if( !$data["externalId"] ) $data["externalId"] = 0;
		if( !$data["contactTypeId"] ) $data["contactTypeId"] = 1;
		
		if( $_SERVER['REQUEST_METHOD'] !== "POST" || 
			$_SERVER["Repository_Enabled"] !== 'true' ||
			(is_numeric($data["assocId"]) === false ) ||
			(is_numeric($this->session->userid) === false) ||
			(trim($data["email"]) === "" ) || 
			(trim($data["firstname"]) === "" ) ||
			(trim($data["lastname"]) === "")
			) {
			header("Status: 404 Not Found");
			return;
		}
		
		header("Content-Type: text/xml");
		$res = RepositoryContacts::add($data, $this->session->userid);
		if( $res !== true ){
			echo "<response error='" . $res . "'></response>";
			return;
		}
		echo "<response success='true' id='".$data->id."'>OK</response>";
	}
	
	public function updateAction(){
		$data = array("assocId" => $this->_getParam("assocId"),
			"assocEntity" => ( $this->_getParam("assocEntity") || "release" ),
			"externalId" => ( $this->_getParam("externalId") || 0 ),
			"contactTypeId" => ($this->_getParam("contactTypeId") || 1 ),
			"firstname" => $this->_getParam("firstname"),
			"lastname" => $this->_getParam("lastname"),
			"email" => $this->_getParam("email"));
		
		if( $_SERVER['REQUEST_METHOD'] !== "POST" || 
			$_SERVER["Repository_Enabled"] !== 'true' ||
			(is_numeric($data["assocId"]) === false ) ||
			(is_numeric($this->session->userid) === false) ||
			(trim($data["email"]) === "" ) || 
			(trim($data["firstname"]) === "" ) ||
			(trim($data["lastname"]) === "")
			) {
			header("Status: 404 Not Found");
			return;
		}
		
		header("Content-Type: text/xml");
		$res = RepositoryContacts::update($data, $this->session->userid);
		if( $res !== true ){
			echo "<response error='" . $res . "'></response>";
			return;
		}
		echo "<response success='true'>OK</response>";
	}
}
?>
