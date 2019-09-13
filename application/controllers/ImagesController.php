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

class ImagesController extends Zend_Controller_Action {
	function applogoAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		$uri = $_SERVER['REQUEST_URI'];
		$cache = str_replace('/images/applogo/', '', $uri);
		$cache = preg_replace('/\?.*/', '', $cache);
		$type = NULL;
		if (preg_match('/\\.jpg/', $cache) !== false) {
			$type = "jpg";
		} elseif (preg_match('/\\.png/', $cache) !== false) {
			$type = "png";
		}
		if (file_exists(APPLICATION_PATH . "/../cache/" . $cache)) {
			if (! is_null($type)) {
				header('Content-type: image/' . $type);
			}
			echo file_get_contents(APPLICATION_PATH . "/../cache/" . $cache);
		} else {
			$id = str_replace('/images/applogo/55x55/app-logo-', '', $uri);
			$id = preg_replace('/\?req=.*/', '', $id);
			$id = preg_replace('/\\..*/', '', $id);
			error_log("id: " . $id);
			if (is_numeric($id)) {
				error_log("REDIRECT:" .'/apps/getlogo?id=' . urlencode($id));
				return $this->_redirect('/apps/getlogo?id=' . urlencode($id));
			} else {
				header("Status: 404 Page not Found");
			}
		}
	}
}

