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

class HarvestController  extends AbstractActionController
{

    public function init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
        $this->session = new \Zend\Session\Container('base');
	}
	
	/*
	 * Search harvest archives 
	 */
	public function searchAction(){
		$archiveid = $this->getRequest()->getParam("archive");
		$search = $this->getRequest()->getParam("search");
		$limit =  $this->getRequest()->getParam("limit");
		
		if( is_numeric($limit) === false || $limit < 1 ){
			$limit = 100;
		}
		
		header('Content-type: text/xml');
		echo "<records>";
		$res = harvest::search($archiveid, $search, $limit);
		foreach($res as $r){
			echo $r;
		}
		echo "</records>";
	}
	
	public function initrelationsAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$updateonly  = $this->getRequest()->getParam("updateonly"); 
		set_time_limit(300);
		// Prevent malicious calls
		if ( localRequest() ) {
			ob_start();
			flush();
			ob_flush();
			$unrelateold = true;
			if( trim($updateonly) === "true" ){
				$unrelateold = false;
			}
			$res = HarvesterInitRelations::initResearchersOrganizations($unrelateold);
			if( $res !== true ){
				echo "ERROR: " . $res;
			}else{
				echo "SUCCESS";
			}
			ob_end_flush();
		}
	}
}