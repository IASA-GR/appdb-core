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

/**
 * Generic authorization class based on a
 * client's AAIOIDC configuration
 * 
 */
abstract class AAIOIDCClientAuth {
    private $_params = array();

    public function __construct($params) {
        $this->_params = (array) $params;
    }

    protected function getParam($name, $asArray = false) {
        $val = '';
        if(isset($this->_params[$name])){
            $val = trim($this->_params[$name]);
        }

        if ($asArray === true) {
            if ($val === '') {
                $val = array();
            } else {
                $val = explode(';', $val);
            }
        }

        return $val;
    }

    protected function get_client_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
           $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = '';

        return $ipaddress;
    }

    /**
     * Checks if a request from a client is authenticated
     */
    abstract public function isAuthenticated();

    /**
     * Checks if a request from a client is from the
     * configured origin (eg IPs)
     */
    abstract public function isRequestOrigin();

    /**
     * Creates a concrete AAIOIDCClientAuth instance
     * 
     * @param   array               $config     The client auth configuration object
     * @return  \AAIOIDCClientAuth              Concrete implementation of AAIOIDCClientAuth
     */
    static public function getImplementation($config) {
        $config = (array) $config;
        $type = (isset($config['type'])) ? $config['type'] : null;
        $params = (isset($config['params'])) ? $config['params'] : array();

        if($type === null) {
            return null;
        }

        $className = 'AAIOIDCClientAuth' . $type;

        require_once(__DIR__ . '/' . $className . '.php');

        $classImpl = new $className($params);

        return $classImpl;
    }
}
