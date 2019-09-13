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

require_once(__DIR__ .'/AAIOIDCClientAuth.php');

/**
 * Handles the client requests and allows or forbids access
 * based on the AAIOIDC configuration. 
 */
class AAIOIDCClient {
    const PERM_CAN_VIEW_ACCESS_TOKEN = 'view_access_token';
    private $_name = null;
    private $_auth =  null;
    private $_valid_ips = array();
    private $_perms = array();

    public function __construct($name) {
        $this->_name = $name;
        $this->_valid_ips = $this->getConfiguration('valid_ips', true);
        $this->_auth = AAIOIDCClientAuth::getImplementation($this->getConfiguration('auth'));
        $this->_perms = array(
            AAIOIDCClient::PERM_CAN_VIEW_ACCESS_TOKEN => $this->getConfiguration('perms.view_access_tokens', false)
        );
    }

    private function getConfiguration($path, $isArray = false) {
        $prefix = 'aaioidc.clients.' . $this->_name;
        $data = ApplicationConfiguration::service($prefix . '.'  . $path);

        if ($isArray === true) {
            $data = explode(';', $data);
        }

        return $data;
    }

    private function isRequestOrigin() {
        if ($this->_auth) {
            return $this->_auth->isRequestOrigin();
        }

        return true;
    }

    private function isAuthenticated() {
        if ($this->_auth) {
            return $this->_auth->isAuthenticated();
        }

        return true;
    }

    public function getClientName() {
        return $this->_name;
    }

    public function hasPermission($permission) {
        if(! isset($this->_perms[$permission]) || $this->_perms[$permission] !== true) {
            return false;
        }

        return true;
    }

    public function validate() {
        if ($this->isRequestOrigin() && $this->isAuthenticated()) {
            return true;
        }

        return false;
    }

    public function getRequestUID() {
        if(isset($_SERVER['HTTP_X_UID'])) {
            return trim($_SERVER['HTTP_X_UID']);
        }

        return null;
    }

    public static function getFirstValidClient($hasPerms = array()) {
        $clients = ApplicationConfiguration::service('aaioidc.clients');
        foreach($clients as $key => $value) {
            $enabled = ApplicationConfiguration::service('aaioidc.clients.' . $key . '.enabled', true);

            if ($enabled !== true) {
                continue;
            }

            $client = new AAIOIDCClient($key);
            $clientValidation = $client->validate();

            if ($clientValidation === true) {
                foreach($hasPerms as $perm) {
                   if(!$client->hasPermission($perm)) {
                       return null;
                   }
                }

                return $client;
            }
        }

        return null;
    }
}
