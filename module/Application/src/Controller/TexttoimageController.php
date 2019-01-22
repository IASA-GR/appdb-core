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

class TexttoimageController extends AbstractActionController
{

    public function personcontactAction()
    {
        header('PRAGMA: NO-CACHE');
        header('CACHE-CONTROL: NO-CACHE');
        header('Content-type: image/png');    	
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $text = new textPNG;
        $c = new Default_Model_Contacts();
        $c->filter->id->equals($this->_getParam('id'));
        $text->msg = $c->items[0]->data; 
        $text->size = '9';
        $this->view->text = $text;
        $text->draw();
    }

    public function indexAction()
    {
        header('PRAGMA: NO-CACHE');
        header('CACHE-CONTROL: NO-CACHE');
        header('Content-type: image/png');    	
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $text = new textPNG;
		$text->msg = $this->_getParam('msg');
		if ( $this->_getParam('b64') == "1" ) $text->msg = base64_decode($text->msg);
        $text->size = '9';
        $this->view->text = $text;
        $text->draw();
    }
	
	public function sitecontactAction(){
		header('PRAGMA: NO-CACHE');
        header('CACHE-CONTROL: NO-CACHE');
        header('Content-type: image/png');    	
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $text = new textPNG;
        $c = new Default_Model_Sites();
		$c->filter->id->equals($this->_getParam("id"));
		if( count($c->items) > 0 ){
			$field = $this->_getParam("type") . "email";
			$text->msg = $c->items[0]->$field; 
		}else{
			$text->msg = "n\a";
		}
		$text->size = '9';
		$text->draw();
	}

}

