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

require_once(__DIR__ . '/AAIOIDCConfiguration.php');
require_once(__DIR__ . '/base/AAIOIDCEncryption.php');

/**
 * Encrypts OpenID connect tokens using OpenSSL
 */
class AAIOIDCEncryptionOpenSSL extends AAIOIDCEncryption {
    private $_method = 'AES-256-CBC';
    private $_secret = null;
    private $_iv = null;

    public function __construct(Array $parameters) {
        parent::__construct($parameters);

        $method = $this->getParameter('method');
        if ($method !== null) {
            $this->_method = $method;
        }

        $secret = $this->getParameter('secret');
        if($secret !== null) {
            $this->_secret = $secret;
        }

        $iv = $this->getParameter('iv');
        if (!$iv) {
            $cfg = new AAIOIDCConfiguration();
            $iv = $cfg->getApplicationApiKey();
        }

        if ($iv !== null) {
            $this->_iv = substr(hash('sha256', $iv), 0, openssl_cipher_iv_length($this->_method));
        }
    }

    protected function _encrypt($content) {
        $encrypted = openssl_encrypt($content, $this->_method, $this->_secret, 0, $this->_iv);
        return base64_encode($encrypted);
    }

    protected function _decrypt($content) {
        $decrypted = openssl_decrypt(base64_decode($content), $this->_method, $this->_secret, 0, $this->_iv);
        return $decrypted;
    }
}