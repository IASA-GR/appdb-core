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

class ResController extends AbstractActionController 
{
	public function __construct() {
//		$this->_helper->layout->disableLayout();
//		$this->_helper->viewRenderer->setNoRender();
		$this->view = new ViewModel();
		$this->view->setTerminal(true);
	}

	public function jsAction() {
		$headers = apache_request_headers();
		$f = GET_REQUEST_PARAM($this, 'f');
		if ( $f != '' ) {
			if ( substr($f, -4) === '.php' ) {
				header('Location: http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on")?"s":"") . '://' . $_SERVER['HTTP_HOST'] . '/' . $f);
				return;
			}
			header('Content-Type:application/javascript');
			$f = $_SERVER['APPLICATION_PATH'] . "/../public/" . $f;
			if ( substr($f, -3) === '.js' ) $f = substr($f, 0 , -3);
			if ( substr($f, -4) === '.jgz' ) $f = substr($f, 0 , -4);
			if ( ( isset($headers['Accept-Encoding']) ) && ( strpos($headers['Accept-Encoding'], 'gzip') !== false ) ) {
				if ( file_exists($f . '.jgz') ) {
					$f = $f . '.jgz';
					header('Content-Encoding: gzip');
				} else {
					$f = $f . '.js';
				}
			} else {
				$f = $f . '.js';
			}
		}
		echo file_get_contents($f);
	}

	public function cssAction() {
		$headers = apache_request_headers();
		$f = GET_REQUEST_PARAM($this, 'f');
		if ( $f != '' ) {
			if ( substr($f, -4) === '.php' ) {
				header('Location: http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on")?"s":"") . '://' . $_SERVER['HTTP_HOST'] . '/' . $f);
				return;
			}
			header('Content-Type:text/css');
			$f = $_SERVER['APPLICATION_PATH'] . "/../public/" . $f;
			if ( substr($f, -4) === '.css' ) $f = substr($f, 0 , -4);
			if ( substr($f, -4) === '.cgz' ) $f = substr($f, 0 , -4);
			if ( ( isset($headers['Accept-Encoding']) ) && ( strpos($headers['Accept-Encoding'], 'gzip') !== false ) ) {
				if ( file_exists($f . '.cgz') ) {
					$f = $f . '.cgz';
					header('Content-Encoding: gzip');
				} else {
					$f = $f . '.css';
				}
			} else {
				$f = $f . '.css';
			}
		}
		echo file_get_contents($f);
	}

	public function zipAction() {
		$f = $this->params()->fromQuery('f');
		if ( $f != '' ) {
			if ( substr($f, -4) === '.php' ) {
				header('Location: http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on")?"s":"") . '://' . $_SERVER['HTTP_HOST'] . '/' . $f);
				return DISABLE_LAYOUT($this);
			} elseif ( substr($f, -4) === '.zip' ) {
				$f = $_SERVER['APPLICATION_PATH'] . "/../../public/" . $f;
				header('Access-Control-Allow-Origin: *');
				header('Content-Type: application/octet-stream');
				echo file_get_contents($f);	
			}
		}
		return DISABLE_LAYOUT($this, true);
	}
}
