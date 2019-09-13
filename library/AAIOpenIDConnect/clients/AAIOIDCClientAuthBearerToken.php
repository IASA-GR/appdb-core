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
 * Implementation of client authentication based on bearer token of
 * HTTP Authorization request header and of configured origin
 */
class AAIOIDCClientAuthBearerToken extends AAIOIDCClientAuth {
    private $_valid_ips = array();
    private $_token = '';

    public function __construct($params) {
        parent::__construct($params);
        $this->_valid_ips = $this->getParam('valid_ips', true);
        $this->_token = $this->getParam('token', '');
    }

    public function isAuthenticated() {
        if ($this->_token) {
            $expectedToken = 'Bearer ' . trim($this->_token);
            foreach (getallheaders() as $name => $value) {
                if ($name === 'Authorization' && trim($value) === $expectedToken) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    public function isRequestOrigin() {
        $clientIp = $this->get_client_ip();
        if (count($this->_valid_ips) > 0) {
            foreach($this->_valid_ips as $ip) {
                if($ip === $clientIp) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }
}