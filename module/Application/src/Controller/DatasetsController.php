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

class DatasetsController extends AbstractActionController{
	//put your code here
	public function init(){
		$this->_helper->layout->disableLayout();
		$this->session = new Zend_Session_Namespace('default');
	}
	
	public function indexAction(){
		 
	}
	
	public function detailsAction(){
		
	}
	
	public function nameavailableAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		debug_log('[DatasetsController::nameavailableAction]: ' . $_GET["n"]);
		if($this->session->userid===null || isset($_GET["n"])===false){
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
			$this->getResponse()->setHeader("Status","403 Forbidden");
			return;
		}
      	$name = $this->getRequest()->getParam("n");
		$id = $this->getRequest()->getParam("id");
		if( is_numeric($id) && intval($id)<=0 ){
			$id = 0;
		}
		$res = Datasets::nameAvailability($name, $id);
		header('Content-type: text/xml');
		if ( $res === true ) {
            echo "<response>OK</response>";
		} else {
			echo "<response error='".htmlentities("The given name is already used by another dataset.")."' >";
			echo "<search>";
			echo htmlentities($name);
			echo "</search>";
			foreach($res as $k=>$v){
				echo "<" . $k . ">";
				echo htmlentities($v);
				echo "</" . $k . ">";
			}
			echo "</response>";
		}
	}
}