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
include('../library/AAIOpenIDConnect/AAIOpenIDConnect.php');
include('../library/AAIOpenIDConnect/clients/AAIOIDCClient.php');

function getGuestUser() {
    return array(
      "idp:uid" => array(ApplicationConfiguration::saml('guest.uid'))  
    );
}

class AaioidcController extends Zend_Controller_Action
{
    private $_uid="";
    private $_client = null;
    private $_isAuthenticated = false;
    private $_error = null;
    private $_errorDescription = null;

    public function init()
    {
        $this->session = new Zend_Session_Namespace('default');
        if ($this->session->userid && $this->session->authUid) {
            $this->_isAuthenticated = true;
            $this->_uid = $this->session->authUid;
        }        
    }
    
    /**
     * Helper function to retrieve query-string parameters from the URL
     * 
     * @param   string    $name     The name of the query key
     * @param   string    $default  Optional. The default value if none is found in the URL
     * @return  string              The value of the given query key
     */
    private function getQueryParam($name, $default = '') {
        $res = strtolower((isset($_GET[$name]) && trim($_GET[$name]) !== '') ? trim($_GET[$name]) : '');

        if ($res === '' && $default !== '') {
            return $default;
        }

        return $res;
    }

    /**
     * Helper function to sanitize XML attributes
     * 
     * @param   string|array $input   Attribute text to sanitize
     * @return  string          Sanitized text
     */
    private function sanitize($input) {
        if (is_string($input)) {
            $input = htmlspecialchars($input, ENT_QUOTES, "UTF-8");
        } else if (is_array($input)) {
            foreach($input as $key => $value) {
                $input[$key] = $this->sanitize($value);
            }
        }

        return $input;
    }

    /**
     * Helper function to create and send a JSON response
     * 
     * @param   int     $status HTTP Status
     * @param   array   $body   Associative array to be encoded as JSON response body
     * @return  void
     */
    private function sendResponse($status, $body=array()) {
        header('HTTP/1.1 '.$status, true, $status);
        header('Content-Type: application/json');

        if (count($body) > 0) {
            echo json_encode($body, JSON_PRETTY_PRINT);
        }
    }

    /**
     * Helper function to retrieve existing AppDB user (researcher)
     * with the given EGI AAI UID
     * 
     * @param   string                      $uid    EGI AAI UID
     * @return  /Default_Model_Researcher
     */
    private function getResearcherByUID($uid) {
        $researcher = null;
        $uaccounts = new Default_Model_UserAccounts();
        $f1 = new Default_Model_UserAccountsFilter();
        $f1->accountid->equals($uid);
        $uaccounts->filter->chain($f1, "AND");

        if( count($uaccounts->items) > 0 ){
            $uaccount = $uaccounts->items[0];
            $researcherID = $uaccount->researcherid;

            if ($researcherID) {
                $researchers = new Default_Model_Researchers();
                $f1 = new Default_Model_ResearchersFilter();
                $f1->id->numequals($researcherID);
                $researchers->filter->chain($f1, "AND");
                if(count($researchers->items) > 0) {
                    $researcher = $researchers->items[0];
                }
            }
        }

        return $researcher;
    }

    /**
     * Initialize the OIDC client for the given user and service
     * 
     * @param   string  $service    Service(Scope) name under which the tokens will be generated
     * @param   string  $uid        The user EGI AAI UID string.
     * @return  boolean             True on successful initialization, false otherwise
     */
    private function initRequest($service = 'appdb', $uid = null) {
        if ($service) {
            try {
                $uid = ($uid ? $uid : $this->_uid);
                
                if (!trim($uid)) {
                    $this->_error = 1000;
                    $this->_errorDescription = "invalid UID provided";
                    return false;
                }
                
                $this->_client = new AAIOpenIDConnect($service, $uid);
            } catch(Exception $ex) {
                $this->_error = 'Cannot use OIDC service';
                $this->_errorDescription = $ex->getMessage();
                return false;
            }

            if ($this->_client->getError()) {
                $this->_error = $this->sanitize($this->_client->getError());
                $this->_errorDescription = $this->sanitize($this->_client->getErrorDescription());
                $this->_errorDetails = $this->sanitize($this->_client->getErrorDetails());
                return false;
            }

            if ($this->_isAuthenticated && $this->_client->isEnabled() === false) {
                $this->_helper->viewRenderer->setNoRender();
                header('HTTP/1.0 403 Forbidden');
                header("Status: 403 Forbidden");

                return false;
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Start initialization of a service to request refresh/access tokens for 
     * the currently logged in user. 
     *
     * @return void
     */
    public function indexAction(){
        $this->_helper->layout->disableLayout();
        $success = $this->initRequest($this->getQueryParam('service'));

        if ($success === false) {
            return;
        }

        $this->view->isAuthenticated = $this->_isAuthenticated;

        if ($this->_client) {
            $this->view->serviceName = $this->sanitize($this->_client->getServiceName());
            $this->view->serviceLogo = $this->sanitize($this->_client->getServiceLogo());
            $this->view->scopesDescription = $this->_client->getScopesDescription();
        } else {
            $this->_error = 'Cannot proceed';
            $this->_errorDescription = 'Cannot create Open ID Connect client';
        }

        if ($this->_error) {
            $this->view->error = $this->_error;
            $this->view->errorDescription = $this->_errorDescription;
        }

        $this->view->refreshTokenUrl = '/aaioidc/'. $this->getQueryParam('service');
    }

    /**
     * Initialization for setting up a user consent flow to generate
     * refresh and access token for the given service
     * 
     * @param   string $service The name of one of the supported services (scopes) to generate refresh and access token for a user.
     * @return  void
     */
    private function serviceHandler($service) {
        $this->_helper->layout->disableLayout();
        $success = $this->initRequest($service);

        if ($success === false) {
            debug_log('[refreshtokenAction2]: code=' . $this->getQueryParam('code') . ' service=' . $this->getQueryParam('service'));
            return;
        }

        $this->view->isAuthenticated = $this->_isAuthenticated;
        $this->view->error = null;
        $this->view->errorDescription = null;
        $this->view->errorDetails = null;
        $this->view->scopesDescription = $this->_client->getScopesDescription();
        $this->view->serviceName = $this->_client->getServiceName();
        $this->view->serviceLogo = $this->_client->getServiceLogo();

        $this->view->refreshToken = null;
        $this->view->accessToken = null;

        $result = $this->_client->authenticate(ApplicationConfiguration::url('') . "aaioidc/" . $this->_client->getServiceCode());

        if ($result === true) {
            debug_log('[AaioidcController::serviceHandler]: code=' . $this->getQueryParam('code') . ' state=' . $this->getQueryParam('state'));

            $refreshTokenInfo = $this->_client->getUserRefreshTokenInfo();
            $refreshToken = ($refreshTokenInfo !== null && isset($refreshTokenInfo['token'])) ? $refreshTokenInfo['token'] : '';
            $this->view->refreshToken = $this->sanitize($refreshToken);

            $accessTokenInfo = $this->_client->getUserAccessTokenInfo();
            $accessToken = ($accessTokenInfo !== null && isset($accessTokenInfo['token'])) ? $accessTokenInfo['token'] : '';
            $this->view->accessToken = $this->sanitize($accessToken);
        } else {
            debug_log('[AaioidcController::serviceHandler]: code=' . $this->getQueryParam('code') . ' service=' . $this->getQueryParam('service'));
            debug_log('[AaioidcController::serviceHandler][ERROR] ' . $this->_client->getError());
            debug_log('[AaioidcController::serviceHandler][ERROR][DESCRIPTION] ' . $this->_client->getErrorDescription());
            debug_log('[AaioidcController::serviceHandler][ERROR][DETAILS] ' . $this->_client->getErrorDetails());
            $this->view->error = $this->sanitize($this->_client->getError());
            $this->view->errorDescription = $this->sanitize($this->_client->getErrorDescription());
            $this->view->errorDetails = $this->_client->getErrorDetails();
        }
    }

    /**
     * Dedicated endpoint to handle tokens for vmops service
     */
    public function vmopsAction() {
        $this->serviceHandler('vmops');
    }
    /**
    * Dedicated endpoint to handle tokens for vmops monitoring service
    */
    public function vmopsmonitorAction() {
        $this->serviceHandler('vmopsmonitor');
    }
    /**
     * Dedicated endpoint to handle tokens for fedcloud service
     */
    public function fedcloudAction() {
        $this->serviceHandler('fedcloud');
    }
    /**
     * Retrieve an access token for the given user, if any exist
     * 
     * @return void
     */
    public function tokenAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $service = $this->getQueryParam('service');

        if (!$service) {
            return $this->sendResponse(403, array(
                "result" => "error",
                "error" => "No service specified"
            ));
        }
        
        //Retrieve the first valid client with permissions to
        //view access token for specific users
        $validClient = AAIOIDCClient::getFirstValidClient(
                array(AAIOIDCClient::PERM_CAN_VIEW_ACCESS_TOKEN)
        );

        if ($validClient) {
            //Get the user EGI AAI UID to check for access tokens
            $requestUID = $validClient->getRequestUID();

            if (trim($requestUID) == '') {
                return $this->sendResponse(404, array(
                    "result" => "error",
                    "error" => "No user UID specified"
                ));
            }

            //Check if the given UID corresponds to an AppDB user
            $researcher = $this->getResearcherByUID($requestUID);
            if (!$researcher) {
                return $this->sendResponse(404, array(
                    "result" => "error",
                    "error" => "No user found with given UID"
                ));
            }

            //Initializa an OIDC client request for the given service and user
            $success = $this->initRequest($service, $requestUID);

            if ($this->_error) {
                return $this->sendResponse(404, array(
                    "result" => "error",
                    "error" => $this->_error,
                    "description" => $this->_errorDescription
                ));
            } else {
                $accessToken = $this->_client->getUserAccessTokenInfo(false);
                if (!$accessToken) {
                    debug_log('[AAIOIDCController::tokenAction] Access token not found (service: ' . $this->_client->getServiceCode() . ', UID: ' .$this->_client->getUserUID() . ')');
                    debug_log('[AAIOIDCController::tokenAction] Retrying to issue with refresh token (service: ' . $this->_client->getServiceCode() . ', UID: ' .$this->_client->getUserUID() . ')');
                    $accessToken = $this->_client->getUserAccessTokenInfo(true);
                    if (!$accessToken) {
                        debug_log('[AAIOIDCController::tokenAction] Failed to issue new access token (service: ' . $this->_client->getServiceCode() . ', UID: ' .$this->_client->getUserUID() . ')');
                    }
                } 
                
                if(!$accessToken) {
                    return $this->sendResponse(404, array(
                        "result" => "error",
                        "error" => "No access token found"
                    ));
                } else {
                    return $this->sendResponse(200, array(
                        "result" => "success",
                        "data" => $this->sanitize($accessToken),
                        "clientName" => $validClient->getClientName()
                    ));
                }
            }
        } else {
            return $this->sendResponse(403, array(
                "result" => "error",
                "error" => "Forbiden access"
            ));
        }
    }
}
