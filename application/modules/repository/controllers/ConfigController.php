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
class Repository_ConfigController extends Zend_Controller_Action {
	public function init(){
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$this->session = new Zend_Session_Namespace('default');
		header('Access-Control-Allow-Origin: *');
    }
	
	public function listAction(){
		
		if( $_SERVER['REQUEST_METHOD'] != "GET" || 
			$_SERVER["Repository_Enabled"] !== 'true'
			) {
			header("Status: 404 Not Found");
			return;
		}
		
		$rl = new RestRepoConfigList(array("id"=>array(12)));
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
}
?>
