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

class ChangelogController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
	{
		$this->_helper->layout->disableLayout();
        trackPage('/changelog');
		
		$wiki = ApplicationConfiguration::app('wiki');
		$haswiki = ($wiki==="")?false:true;
		$currentwiki = "/pages/about/changelog"; 
		if( $haswiki ){
			$currentwiki = $wiki . "main:about:changelog";
			$this->view->currentwiki = $currentwiki;
			return;
		}
		$this->_helper->viewRenderer->setNoRender();
        $s=file_get_contents(APPLICATION_PATH . "/../CHANGELOG");
		$s=str_replace("\n","<br/>",$s);
		$s=preg_replace("/EGI RT #(\d+)/",'<a target="_blank" href="https://rt.egi.eu/guest/Ticket/Display.html?id=${1}">EGI RT #${1}</a>',$s);
		$s=preg_replace("/GGUS #(\d+)/",'<a target="_blank" href="https://gus.fzk.de/ws/ticket_info.php?ticket=${1}">GGUS #${1}</a>',$s);
		
		echo $s;
    }

	public function featuresAction() {
		$this->_helper->layout->disableLayout();
	}

}

