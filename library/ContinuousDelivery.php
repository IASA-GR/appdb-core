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

class CD {
    private $_applicationId;
    private $_userId;
    private $_cdendpoint;
    private $_data;
    
    private function userCanEdit() {
        if ($this->_userId === null) {
            return true;
        }
        $apps = new Default_Model_Applications();
        $apps->filter->id->numeq($this->_applicationId);
        $apps->filter->ownerId->numeq($this->_userId);
        if (count($apps->items)) {
            return true;
        }
        if (userIsAdmin($this->_userId)) {
            //return true;
        }
        return false;
    }
    
    private function getUserAccessToken() {
        if ($this->_userId === null) {
            return null;
        }

        $accessTokens = array();
        $users = new Default_Model_Researcher();
        $users->filter->id->numeq($this->_userId);

        if (count($users->items) > 0) {
            $user = $users->items[0];
            $accessTokens = $user->getAccessTokens();
        }

        if (count($accessTokens) > 0) {
            for($i = 0; i<count($accessTokens); $i+=1) {
                $tok = $accessTokens[$i];
                if (trim($tok->type) === 'personal') {
                    return $tok->token;
                }
            }
        }
        
        return null;
    }
    
    function __construct($applicationId, $userId = null) {
        $this->_applicationId = $applicationId;
        $this->_userId = $userId;
        $this->_cdendpoint = ApplicationConfiguration::service('cd.endpoint');
        $this->_cdenabled = ApplicationConfiguration::service('cd.enabled');
        if ($this->_cdenabled === false) {
            throw new Exception('Continuous Delivery service is not enabled');
        }

        if (trim($this->_applicationId) === '') {
            throw new Exception('No application ID provided');
        }
    }
    
    private function throwException($error = null) {
        if ($error !== null && trim($error) !== '') {
            $checkError = 'Failed to connect to';
            if (substr($error, 0, strlen($checkError)) === $checkError) {
                $error = 'Continuous Delivery backend service cannot be reached. Please try again later.';
            } else if ($error === 'Recv failure: Connection reset by peer') {
                $error = 'Continuous Delivery backend service cannot be reached. Please try again later.';
            }
            
            throw new Exception($error);
        }
    }

    private function getLoadDataDefaults($props = array()) {
        if (!isset($props['includeActorStatus'])) {
            $props['includeActorStatus'] = $this->_userId;
        }
        
        return $props;
    }
    
    public function loadData($props = array()) {
        if (is_array($props) === false) {
            $props = array();
        }
        $props = $this->getLoadDataDefaults($props);
        $qstr = array();
        foreach ($props as $key => $value) {
            $qstr[] = $key . '=' . $value;
        }
        if (count($qstr) > 0) {
            $qstr = '?' . implode('&', $qstr);
        } else {
            $qstr = '';
        }
        
        
        //  Initiate curl
        $url = $this->_cdendpoint . '/apps/' . $this->_applicationId . $qstr;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cache-Control: no-cache'));
        $result = curl_exec($ch);
        $error  = null;
        if ($result === false) {
             $error = curl_error($ch);
        }
        
        curl_close($ch);
        if ($error !== null) {
            $this->throwException($error);
            return;
        }
        $this->_data = json_decode($result, true);
        
        return $this;
    }
    
    public function getData() {
        return $this->_data;
    }
    
    public function getUrl() {
        if ($this->data === null || isset($this->data['url']) === false) {
            return null;
        }
        
        return trim($this->_data['url']);
    }
    
    
    public function getDefaultActorId() {
        if ($this->data === null || isset($this->data['defaultActorId']) === false) {
            return null;
        }
        
        return intval($this->_data['defaultActorId']);
    }
    
    public function getEnabled() {
        if ($this->data === null || isset($this->data['enabled']) === false) {
            return null;
        }
        
        return boolval($this->_data['enabled']);
    }
    
    public function getPaused() {
        if ($this->data === null || isset($this->data['paused']) === false) {
            return null;
        }
        
        return boolval($this->_data['paused']);
    }
    
    public function setProps($props = array()) {error_log('PROPS: ' . var_export($props, true));
        if ($this->_userId) {
            if ($this->userCanEdit() === false) {
                $this->throwException('Not authorized to change continuous delivery properties.');
                return;
            } else {
                $props['_actorId'] = $this->_userId;
            }
        }
        $data_string = json_encode($props);
        $url = $this->_cdendpoint . '/apps/' . $this->_applicationId . '/props';
        $ch = curl_init($url);            
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/json',                                                                                
            'Content-Length: ' . strlen($data_string))                                                                       
        );                   
        
    
        // Execute
        $error = null;
        $result = curl_exec($ch);
        
        if ($result === false) {
             $error = curl_error($ch);
        }

        // Closing
        curl_close($ch);
        
        //Check for errors
        if ($error !== null) {
            $this->throwException($error);
            return;
        }

        // Will dump a beauty json :3
        $result = json_decode($result, true);
        if ($result && isset($result['error'])) {
            $this->throwException($result['error']);
            return;
        }

        $this->loadData();
        
        return $this;
    }
    
    public function start($props = array()) {
        if ($this->_userId) {
            if ($this->userCanEdit() === false) {error_log('NOT AUTHORIZED');
                $this->throwException('Not authorized to perform continuous delivery actions.');
            }
            
            $props['triggerType'] = 2;
            $props['triggerBy'] = $this->_userId;
        }
        $data_string = json_encode($props);
        $url = $this->_cdendpoint . '/apps/' . $this->_applicationId . '/start';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/json',                                                                                
            'Content-Length: ' . strlen($data_string))                                                                       
        );

        // Execute
        $error = null;
        $result = curl_exec($ch);
        
        if ($result === false) {
             $error = curl_error($ch);
        }
        // Closing
        curl_close($ch);
        
        //Check for errors
        if ($error !== null) {
            $this->throwException($error);
        }
        
        // Will dump a beauty json :3
        $result = json_decode($result, true);
        if ($result && isset($result['error'])) {
            $this->throwException($result['error']);
        }

        $this->loadData();

        return $this;
    }
    
    public function cancel($reason = '') {
        $props = array();
        if ($this->_userId) {
            if ($this->userCanEdit() === false) {
                $this->throwException('Not authorized to perform continuous delivery actions.');
            }
            
            $props['actorId'] = $this->_userId;
        }
        
        if (trim($reason) !== '') {
            $props['reason'] = trim($reason);
        }

        $data_string = json_encode($props);
        $url = $this->_cdendpoint . '/apps/' . $this->_applicationId . '/cancel';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/json',                                                                                
            'Content-Length: ' . strlen($data_string))                                                                       
        );

        // Execute
        $error = null;
        $result = curl_exec($ch);
        
        if ($result === false) {
             $error = curl_error($ch);
        }
        // Closing
        curl_close($ch);
        
        //Check for errors
        if ($error !== null) {
            $this->throwException($error);
        }

        // Will dump a beauty json :3
        $result = json_decode($result, true);
        if ($result && isset($result['error'])) {
            $this->throwException($result['error']);
        }

        $this->loadData();

        return $this;
    }
}