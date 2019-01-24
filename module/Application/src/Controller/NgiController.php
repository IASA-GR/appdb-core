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

class NgiController extends AbstractActionController
{

    public function init()
    {
        /* Initialize action controller here */
    }

    private function paging(&$entries, $offset, $length, $total) {
        $this->view->lastPage=false;
        $segment = array();
        for($i=$offset; $i<=$offset+$length; $i++) {
			if ( $i > count($entries)-1 ) {
					$this->view->lastPage=true;
					break;
			}
			array_push($segment,$entries[$i]);
        }
        $this->view->entries = $segment;
        $this->view->offset = $offset;
        $this->view->length = $length;
        $this->view->pageCount = ceil($total / ($length+1));
        $this->view->currentPage = floor($offset / ($length+1));
        $this->view->total = $total;
    }

	public function detailsAction()
	{
		$this->_helper->layout->disableLayout();
		$ngis = new Default_Model_NGIs();
		$ngis->filter->id->equals($this->getRequest()->getParam('id'));
		if ( $ngis->count() >= 1 ) {			
			$this->view->entry = $ngis->items[0];
		}
		$this->view->dialogCount = $this->getRequest()->getParam('dc');
	}

	private function pgBool($v) {
		if ( ($v === 1) || ($v === '1') || ($v === "true") || ($v === true) ) {
			return 'true';
		} else return 'false';
	}

	public function indexAction()
    {
		$this->_helper->layout->disableLayout();
		$offset = $this->getRequest()->getParam('ofs');
		$length = $this->getRequest()->getParam('len');
		if ( $offset === null) $offset = 0;
		if ( $length === null ) $length = 23;
		$ngis = new Default_Model_NGIs();
		$f1 = new Default_Model_NGIsFilter();
		$f2 = new Default_Model_NGIsFilter();
		if ( ($this->getRequest()->getParam('eu') !== '' ) && ($this->getRequest()->getParam('eu') !== null) ) {
			if ($this->getRequest()->getParam('eu') == "1") {
				$f1->countryid->notequals(null);
			} else {
				$f1->countryid->equals(null);
			}
			$this->view->european = $this->getRequest()->getParam('eu');		
		}
		if ( $this->getRequest()->getParam('filter') != '' ) {
			$f2->any->ilike('%'.$this->getRequest()->getParam('filter').'%');
			$this->view->ngiFilter = $this->getRequest()->getParam('filter');
		}
		if ($f1->expr() != "") $ngis->filter->chain($f1,'AND');
		if ($f2->expr() != "") $ngis->filter->chain($f2,'AND');
		$entries = $ngis->items;
		$total = count($entries);
		if($length>0) {
			$this->paging($entries, $offset, $length, $total);
		} else {
			$this->view->entries = $entries;
			$this->view->total = $total;
		}
    }

    public function getlogoAction()
    {
		$this->_helper->layout->disableLayout();
		$id = $this->getRequest()->getParam('id');
		$ngis = new Default_Model_NGIs();
		$ngis->filter->id->equals($id);
		$ngi = $ngis->items[0];
		if (isnull($ngi->logo)) {
			$this->_helper->viewRenderer->setNoRender();
			$isocode = strtolower($ngi->country->ISOCode);
			$isocode = explode("/",$isocode);
			$isocode = $isocode[0];
			$img = 'flags/'.trim($isocode).".png";
			header('PRAGMA: NO-CACHE');
			header('CACHE-CONTROL: NO-CACHE');
			header('Content-type: image/png');
			readfile('images/'.$img);
		} else {
			$this->view->logo = pg_unescape_bytea($ngi->logo);
		}
    }

}
