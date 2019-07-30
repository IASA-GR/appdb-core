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
 * Generic data object handling and validating OpenID connect tokens
 */
abstract class AAIOIDCToken {
    private $_token = null;
    private $_type = 'unknown_type';
    private $_issued = null;
    private $_issuer = null;
    private $_expires = null;
    private $_user = null;
    private $_service = null;

    public function __construct($data) {
        if(is_array($data)) {
            $data = (object) $data;
        } else if ($data instanceof AAIOIDCToken) {
            $data = (object) $data->toArray();
        } else if (is_string ($data) ){
            $data = (object) json_decode($data);
        } else if (is_object($data) !== true){
            $data = (object) array();
        }

        if (isset($data->type) && trim($data->type)) {
            $this->_type = $data->type;
        } else {
            $this->_type = $this->getTokenType();
        }

        if (isset($data->token) && trim($data->token)) {
            $this->_token = trim($data->token);
        }

        if (isset($data->issued) && trim($data->issued)) {
            $this->_issued = intval(trim($data->issued));
        }

        if (isset($data->expires) && trim($data->expires)) {
            $this->_expires = intval(trim($data->expires));
        }

        if (isset($data->user) && trim($data->user)) {
            $this->_user = trim($data->user);
        }

        if (isset($data->service) && trim($data->service)) {
            $this->_service = trim($data->service);
        }

        if (isset($data->issuer) && trim($data->issuer)) {
            $this->_issuer = trim($data->issuer);
        }
    }

    /**
     * Checks if the data are valid.
     * 
     * @return boolean|string True if valid, else validation error message
     */
    public function validate() {
         if ($this->_type !== $this->getTokenType()) {
            return 'Not a ' . $this->getTokenType() . ' token type entry';
        }

        if (trim($this->_token) === '') {
            return 'No ' . $this->getTokenType() . ' token';
        }

        if (trim($this->_user) === '') {
            return 'No user for ' . $this->getTokenType() . ' token';
        }

        if (trim($this->_service) === '') {
            return 'No service for ' . $this->getTokenType() . ' token';
        }

        if ($this->_issued <= 0) {
            return 'No issue date for ' . $this->getTokenType() . ' token';
        }

        if ($this->_expires <= 0) {
            return 'No expiration date for ' . $this->getTokenType() . ' token';
        }

        if ($this->hasExpired()) {
            return 'Token has expired';
        }

        return true;
    }

    abstract public function getTokenType();

    /**
     * Returns raw token as returned from OIDC service.
     * 
     * @return string The raw token 
     */
    public function getToken() {
        return $this->_token;
    }

    /**
     * Returns the UID of the user this token was issued for.
     * 
     * @return string User's UID that this token was issued for.
     */
    public function getUser() {
        return $this->_user;
    }

    /**
     * Set the user for whom this token was issued for.
     * 
     * @param string $uid The user's UID
     * @return void 
     */
    public function setUser($uid) {
        $this->_user = $uid;
    }

    /**
     * Returns the name of the service for which this token was issued for.
     * 
     * @return string The name of the service this token was issued for.
     */
    public function getService() {
        return $this->_service;
    }

    /**
     * Returns the name or URL from where the access token was issued.
     * 
     * @return string The name of the service this token was issued from.
     */
    public function getIssuer() {
        return $this->_issuer;
    }

    /**
     * Returns timestamp when the token expires
     * 
     * @return int Timestamp when the token will expire
     */
    public function expiresOn() {
        return $this->_expires;
    }

    /**
     * Returns timestamp when the token was issued
     * 
     * @return int Timestamp when the token was issued
     */
    public function issuedOn() {
        return $this->_issued;
    }

    /**
     * Checks if token has expired
     * 
     * @return boolean
     */
    public function hasExpired() {
        $now = time();
        $then = $this->_expires;
        $expired = false;

        if($now > $then) {
            $expired = true;
        }

        return $expired;
    }

    /**
     * Helper function to retrieve specific property of the token information.
     * 
     * @param string                $property   Name of the property to retrieve.
     * @return string|int|timestamp             Value of the token information.
     */
    public function get($property) {
        $data = $this->toArray();

        if (isset($data[$property])) {
            return $data[$property];
        }

        return null;
    }

    /**
     * Returns a dictionary with token information
     * 
     * @return array
     */
    public function toArray() {
        return array(
            "type" => $this->getTokenType(),
            "token" => trim($this->_token),
            "issued" => $this->_issued,
            "expires" => $this->_expires,
            "service" => trim($this->_service),
            "user" => trim($this->_user),
            "issuer" => $this->_issuer,
        );
    }

    /**
     * Converts token information to JSON string
     * 
     * @return string Serialized JSON
     */
    public function serialize() {
        return json_encode((object) $this->toArray());
    }
}
