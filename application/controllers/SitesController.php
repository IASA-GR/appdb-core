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

require_once('gocdb.php');
require_once('externdatanotif.php');

class SitesController extends Zend_Controller_Action{
	//put your code here
	public function init(){
	
	}
	
	public function indexAction(){
		 $this->_helper->layout->disableLayout();
	}
	
	public function detailsAction(){
		 $this->_helper->layout->disableLayout();
		 if ( $this->_getParam("id") != null ) {
			 $this->view->id = trim($this->_getParam("id"));
		 }
		 $this->view->dialogCount = $this->_getParam('dc');
		 
		 $sites = new Default_Model_Sites();
		 $sites->filter->id->equals($this->view->id);
		 if( count($sites->items) > 0 ){
			 $this->view->entry = $sites->items[0];
		 }else{
			 $this->view->entry = null;
		 }
	}
	
	public function syncsitesAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$islocal = localRequest();
		
		$update =  ( $this->_getParam("update") != null )?$this->_getParam("update"):"true";
		$update = ( strtolower(trim($update)) === "false" )?false:true;
		
		$force =  ( $this->_getParam("force") != null )?$this->_getParam("force"):"true";
		$force = ( strtolower(trim($force)) === "true" )?true:false;
		
		if( !$islocal ){
			header('HTTP/1.0 404 Not Found');
			header("Status: 404 Not Found");
			return;
		}
		header('Content-type: text/xml');
		echo '<' . '?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
		$result = Gocdb::syncSites( $update, $force );
		db()->query("REFRESH MATERIALIZED VIEW CONCURRENTLY sites;");
		db()->query("SELECT request_permissions_refresh();");
		db()->query("REFRESH MATERIALIZED VIEW site_services_xml;");
		db()->query("REFRESH MATERIALIZED VIEW site_service_images_xml;");
		if( is_array($result) ){
			echo "<result success='true'";
			if( isset($result["inserted"]) ){
				echo " inserted='" . $result["inserted"] . "'";
			}
			if( isset($result["updated"]) ){
				echo " updated='" . $result["updated"] . "'";
			}
			if( isset($result["deleted"]) ){
				echo " deleted='" . $result["deleted"] . "'";
			}
			echo " />";
			return;
		}
		
		$error_message = trim($result);
		if( is_string($result) === false ) {
			$error_message = 'Unknown error';
		}
		ExternalDataNotification::sendNotification('Sites::syncSites', $error_message, ExternalDataNotification::MESSAGE_TYPE_ERROR);
		echo "<result success='false' error='" . htmlspecialchars($error_message, ENT_QUOTES). "' />";
	}
}
