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

require_once(__DIR__ . '/AAIOIDCStorageType.php');
require_once(__DIR__ . '/AAIOIDCEncryption.php');

/**
 * Abstract class that handles the storage and retrieval of refresh/access tokens
 */
abstract class AAIOIDCStorage {
    protected $_service = null;
    private $_encryption = null;

    /**
     * @param string    $service    The name of the service this instance will handle.
     */
    public function __construct($service) {
           $this->_service = $service;
    }

    /**
     * Checks if an encryption handler is set for this instance.
     * 
     * @return boolean
     */
    public function hasEncryption() {
        $hasEncryption = false;

        if ($this->_encryption !== null && $this->_encryption instanceof AAIOIDCEncryption) {
            $hasEncryption = true;
        }

        return $hasEncryption;
    }

    /**
     * Sets an encryption instance to encrypt/decrypt the tokens
     * To disable the encryption, use this method with null as a parameter.
     * 
     * @param  AAIOIDCEncryption $encryption    The encryption handler instance.
     *                                          Must inherit from AAIOIDCEncryption.
     *                                          NULL value disables the encryption.
     */
    public function setEncryption(AAIOIDCEncryption $encryption) {
        $this->_encryption = $encryption;
    }

    /**
     * Retrieves the access token data from the storage, creates and returns an
     * AAI OIDC Access token instance.
     * 
     * @param  string               $uid    The owner user id of the token
     * @return AAIOIDCAcccessToken          The AAI OIDC AccessToken instance,
     *                                      or NULL if not content retrieved.
     */
    public function getUserAccessToken($uid) {
        $content = $this->get(AAIOIDCStorageType::ACCESS_TOKEN, $uid);

        if ($content) {
            if (is_string($content)) {
                $content = json_decode($content);
            } else {
                $content = (array) $content;
            }

            return new AAIOIDCAccessToken($content);
        }

        return null;
    }

    /**
     * Stores an AAI OIDC AccessToken instance for an user. The token data must 
     * be valid in order to be stored in the storage.
     *
     * @param  AAIOIDCAccessToken   $accessToken    An access token instance
     * @param  string               $uid            Optional. The owner id of the token.
     * @return boolean|string                       True on success, else error message
     */
    public function setUserAccessToken(AAIOIDCAccessToken $accessToken, $uid = null) {
        if(trim($uid) !== '') {
            $accessToken->setUser($uid);
        }

        $isValid = $accessToken->validate();
        if ($isValid !== true) {
            return $isValid;
        }

        return $this->set(AAIOIDCStorageType::ACCESS_TOKEN, $accessToken->getUser(), $accessToken->serialize());
    }

    /**
     * Retrieves the refresh token data from the storage, creates and returns an
     * AAI OIDC Refresh token instance.
     * 
     * @param  string               $uid    The owner user id of the token
     * @return AAIOIDCRefreshToken          The AAI OIDC RefreshToken instance,
     *                                      or NULL if not content retrieved.
     */
    public function getUserRefreshToken($uid) {
        $content = $this->get(AAIOIDCStorageType::REFRESH_TOKEN, $uid);

        if ($content) {
            if (is_string($content)) {
                $content = json_decode($content);
            } else {
                $content = (array) $content;
            }
            return new AAIOIDCRefreshToken($content);
        }

        return null;
    }

    /**
     * Stores an AAI OIDC RefreshToken instance for an user. The token data must 
     * be valid in order to be stored in the storage.
     *
     * @param  AAIOIDCRefreshToken  $refreshToken   An refresh token instance
     * @param  string               $uid            Optional. The owner id of the token.
     * @return boolean|string                       True on success, else error message
     */
    public function setUserRefreshToken(AAIOIDCRefreshToken $refreshToken, $uid = null) {
        if(trim($uid) !== '') {
            $refreshToken->setUser($uid);
        }

        $isValid = $refreshToken->validate();
        if ($isValid !== true) {
            return $isValid;
        }

        return $this->set(AAIOIDCStorageType::REFRESH_TOKEN, $refreshToken->getUser(), $refreshToken->serialize());
    }

    /**
     * Retrieves raw data from storage of the given storage type for the given user(uid)
     * 
     * @param  AAIOIDCStorageType    $storageType   The type of data to retrieve
     * @param  string                $uid           The id of the content owner
     * @return array                                Hash array of content data
     */
    protected function get($storageType, $uid) {
        $content = $this->_get($storageType, $uid);

        if ($content === null) {
            return null;
        }

        if ($this->hasEncryption()) {
            $content = $this->_encryption->decrypt($content);
        }

        return json_decode($content);
    }

    /**
     * Stores raw data to storage of the given storage type for the given owner(uid).
     * It prepares the content (encryption etc) before storing to the actual
     * storage.
     * 
     * @param  AAIOIDCStorageType    $storageType   The type of data to retrieve
     * @param  string                $uid           The id of the content owner
     * @param  string|array          $content       The raw data content or hash array with content
     * @return boolean|string                       True on success, else error message
     */
    protected function set($storageType, $uid, $content) {
        try {
            if (is_array($content)) {
                $content = json_encode($content);
            }

            if ($this->hasEncryption()) {
                $content = $this->_encryption->encrypt($content);
            }

            $this->_set($storageType, $uid, $content);
        } catch (Exception $ex) {
            return $ex->getMessage();
        }

        return true;
    }

    /**
     * The concrete sub classes must implement this method in order to
     * retrieve raw data from the actual storage.
     * 
     * This method is called only from the "get" method
     * 
     * @param   AAIOIDCStorageType  $storageType  The type of data to retrieve
     * @param   string              $uid          The id of the content owner
     * @returns string                            The raw data contents
     */
    abstract protected function _get($storageType, $uid);

    /**
     * The concrete sub classes must implement this method in order to
     * store raw data to the actual storage.
     * 
     * This method is called only from the "set" method
     * 
     * @param   AAIOIDCStorageType  $storageType  The type of data to store
     * @param   string              $uid          The related user id of the contents
     * @returns string                            The raw data contents
     */
    abstract protected function _set($storageType, $uid, $content);

    /**
     * The concrete sub classes must implement this method in order to
     * validate the functionality of the actual storage.
     * 
     * @returns boolean|string  True if the storage is configured and function
     *                          properly, else a related error message is returned.
     */
    abstract public function validateStorage();
}
