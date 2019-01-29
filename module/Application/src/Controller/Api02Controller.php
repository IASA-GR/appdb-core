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

class Api02Controller extends AbstractActionController
{
    public $routeVerb;
    public $apiHelper;
    private $notFound;
    private $gone;
    private $params;

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array(), $params = null) {
        if ( ! is_null($params) ) $this->params = $params;
        parent::__construct($request, $response, $invokeArgs);
    }

    public function init()
    {
       if ( $this->params === null ) $this->params = $this->_getAllParams();
       $this->params['version'] = "0.2";
       $this->notFound = false;
       $this->_helper->layout->disableLayout();
       $this->_helper->contextSwitch()
               ->addActionContext('rest', array('xml','json'))
               ->addActionContext('schema','xml')
				->setAutoJsonSerialization(false)
               ->initContext();
       $this->routeVerb = strtoupper($this->getRequest()->getMethod());
        if(GET_REQUEST_PARAM($this, "action")!="redirect"){
            $this->InitAPIHelper();
        }
    }
    private function Gone(){
        $this->getResponse()->clearAllHeaders();
        $this->getResponse()->setRawHeader("HTTP/1.0 410 Gone");
        $this->getResponse()->setHeader("Status","410 Gone");
		$this->_helper->viewRenderer->setNoRender();
        $this->gone = true;
    }
    private function NotFound(){
        $this->getResponse()->clearAllHeaders();
        $this->getResponse()->setRawHeader("HTTP/1.0 404 Not Found");
        $this->getResponse()->setHeader("Status","404 Not Found");
        $this->_helper->viewRenderer->setNoRender();
        $this->notFound = true;
    }
	private function InitAPIHelper(){
		$apiver = GET_REQUEST_PARAM($this, "version");
		if ($apiver == "0.1") {
			$this->Gone();
		} else {
		   if ( $apiver === null ) $apiver = "0.2";
		   $file = '../library/api/'.$apiver.'/AppdbAPIHelper.php';
		   if(file_exists($file)){
					@require_once($file);
					$this->apiHelper = new AppdbAPIHelper();
					$this->view->apihelper = $this->apiHelper;
                    $this->view->apiver = $apiver;
					$this->apiHelper->InitHelper($this,$this->params);
		   } else {
				$this->NotFound();
			}
		}
    }
    /*##########ACTIONS##########*/
    public function restAction(){
        if($this->notFound){
			echo "<font style='font-size:20pt'><b>HTTP/1.0 Error 404 - Not Found</b></font><p/>";
			echo "The requested resource does not exist.";
            return;
		}
		if($this->gone) {
			echo "<font style='font-size:20pt'><b>HTTP/1.0 Error 410 - Gone</b></font><p/>";
			echo "This version of the API has been deprecated and is no longer available.<br/>Please consider using a newer version.";
			return;
		}
        /* POST,DELETE,PUT should be handled bellow as cases */
        switch($this->routeVerb){
            case "GET":
               /* for ajax cross-domain requests */
                header('Cache-control: no-cache');
				header('Content-type: text/xml');			# NOTE: Content-type is already set to 'application/xml' by Zend
															# due to implied 'format=xml' in the query string, and this has no effect
				header('Access-Control-Allow-Origin: *');
                header('Access-Control-Request-Method: GET');
                $this->getAction();
                break;
            case "POST":
                $this->getAction();
                break;
            default:
                $this->NotFound();
        }
    }
	public function redirectAction() {
		header('Location: ' . "http://".GET_REQUEST_PARAM($this, "url"));
		return DISABLE_LAYOUT($this, true);
    }
    public function errorAction(){
        
    }
    public function schemaAction(){
        header('Cache-control: no-cache');
        header('Content-type: text/xml');			# NOTE: Content-type is already set to 'application/xml' by Zend
                                                    # due to implied 'format=xml' in the query string, and this has no effect
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Request-Method: GET');
        $x = GET_REQUEST_PARAM($this, "xsdname");
        if(isset($x)){
            $this->view->Type = "entry";
            $this->view->Entry = $this->apiHelper->GetSchemas($x);
            if(!isset($this->view->Entry)){
                $this->NotFound();
            }
        }else{
            $this->view->Type = "list";
            $this->view->Entries = $this->apiHelper->GetSchemas();
        }
    }
    public function getAction(){
        session_unset();
        $this->apiHelper->CallGet();
    }
    public function updateAction(){
    }
    public function addAction() {
    } 
    public function deleteAction() {
    }
}

