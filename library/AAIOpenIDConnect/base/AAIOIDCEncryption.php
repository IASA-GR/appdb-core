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
 * Base class for encrypting AAIOIDCStorage contents
 */
abstract class AAIOIDCEncryption {
    private $_params = null;
    /**
     * @param array $parameters    A dictionary(key/value pairs) of
     *                              parameters for the encryption algorithm
     */
    public function __construct(Array $parameters) {
        $this->_params = $parameters;
    }

    /**
     * Query for encryption parameter values
     * 
     * @param   string $name    Encryption parameter name
     * @return  any             Encryption parameter value
     */
    public function getParameter($name) {
        if (is_array($this->_params) && isset($this->_params[$name])) {
            return $this->_params[$name];
        }

        return null;
    }

    /**
     * Encrypt a given data content
     * 
     * @param   string $content The content to be encrypted
     * @return  string          Encrypted content
     */
    public function encrypt($content) {
        if (trim($content) === '') {
            return trim($content);
        }

        return $this->_encrypt(trim($content));
    }

    /**
     * Decrypt a given data content
     * 
     * @param   string $content The content to be decrypted
     * @return  string          Decrypted content
     */
    public function decrypt($content) {
        if (trim($content) === '') {
            return trim($content);
        }

        return $this->_decrypt(trim($content));
    }

    abstract protected function _encrypt($content);

    abstract protected function _decrypt($content);
}
