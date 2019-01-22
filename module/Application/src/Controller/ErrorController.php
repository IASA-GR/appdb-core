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

class ErrorController extends AbstractActionController
{

	public function invalidrestresourceAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$entry = RestAPIHelper::wrapResponse("", null, null, -1, null, null, RestErrorEnum::RE_INVALID_RESOURCE, null);
		$this->getResponse()->clearAllHeaders();
		header('Content-type: text/xml');
		header("HTTP/1.0 400 Bad Response");
		header("Status: 400 Bad Response");
		header("X-AppDB-REST-Resource-Invalid: 1");
		echo $entry;
	}

    public function errorAction()
    {
		$this->_helper->layout->disableLayout();

        $errors = $this->_getParam('error_handler');
        error_log('[ErrorController::errorAction]: ' . $errors->exception);
		
		switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                break;
        }
    }


}

