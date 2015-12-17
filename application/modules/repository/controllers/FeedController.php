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
class Repository_FeedController extends Zend_Controller_Action {
	
	public function init(){
        ob_start();
		ob_get_clean();
		ob_end_clean();
		/* Initialize action controller here */
        $this->_helper->layout->disableLayout();
		$this->session = new Zend_Session_Namespace('default');
		header('Access-Control-Allow-Origin: *');
    }
	
	public function productionAction(){
		header('Content-type: text/xml');
		header('Pragma: public');
		header('Cache-control: private');
		header('Expires: -1');

		$res = Repository::getReleaseFeed();
		if( isset($_GET["proxy"]) && trim($_GET["proxy"]) !== "" ){
			$this->view->proxypath = trim($_GET["proxy"]);
		}else{
			$app = $app = Zend_Registry::get("app");
			$this->view->proxypath = (isset($app["commrepoProxy"]))?$app["commrepoProxy"]:"";
		}
		if( trim($this->view->proxypath) === "" ){
			$this->view->proxypath = $_SERVER["SERVER_NAME"] . "/commrepo/api";
		}
		
		$this->view->items = $res;
	}
	
}
