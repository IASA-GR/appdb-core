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

require_once(__DIR__ . '/../../vendor/autoload.php');

use Jumbojett\OpenIDConnectClient;

require_once(__DIR__ . '/AAIOIDCConfiguration.php');
require_once(__DIR__ . '/AAIOIDCFileSystemStorage.php');
require_once(__DIR__ . '/AAIOIDCEncryptionOpenSSL.php');
require_once(__DIR__ . '/base/AAIOIDCAccessToken.php');
require_once(__DIR__ . '/base/AAIOIDCRefreshToken.php');

/**
 * Issues and handles access/refresh tokens for a specific service
 * for a specific user.
 */
class AAIOpenIDConnect {
    private $_uid="";
    private $_enabled;
    private $_service;
    private $_serviceName;
    private $_serviceLogo;
    private $_scopes;
    private $_clientID;
    private $_clientSecret;
    private $_issuer;
    private $_redirectURL;
    private $_accessTokenExpiration;
    private $_refreshTokenExpiration;
    private $_actions;
    private $_allowedReferrers;
    private $_scopesDescription;
    private $_storage = null;
    private $_config = null;
    private $_serviceConfig = null;
    private $_error = null;
    private $_errorDescription = null;
    private $_errorDetails = null;

    public function __construct($service, $uid)  {
        //Get generic AAIOIDC configuration
        $this->_config = new AAIOIDConfiguration();

        if (!$this->_config->getItem('enabled', false)) {
            throw new RuntimeException('AAI OpenID Connect is not enabled');
        }

        if (trim($service) === '' || trim($uid) === '') {
            throw new InvalidArgumentException('Invalid service name provided');
        }

        if (trim($uid) === '') {
            throw new InvalidArgumentException('Invalid user uid provided');
        }

        $this->_service = trim($service);
        $this->_uid = trim($uid);

        //Create and configure storage and encryption
        $this->_storage = $this->createStorage($this->_service, $this->_config);

        //Check if current storage configuration is operatable
        $validStorage = $this->_storage->validateStorage();
        if ($validStorage !== true) {
            throw new RuntimeException($validStorage);
        }

        //Load specific service configuration
        $this->_serviceConfig = new AAIOIDServiceConfiguration($this->_service);
        if (!$this->_serviceConfig->getItem('enabled', false)) {
            throw new RuntimeException('Service ' . $this->_service . ' is not enabled for AAI OpendID Connect');
        }

        //Extract/preload configuration
        $this->_enabled                 = $this->_serviceConfig->getItem('enabled');
        $this->_serviceName             = $this->_serviceConfig->getItem('service_name');
        $this->_serviceLogo             = $this->_serviceConfig->getItem('service_logo');
        $this->_scopes                  = $this->_serviceConfig->getArray('scopes', '');
        $this->_clientID                = $this->_serviceConfig->getItem('client_id');
        $this->_clientSecret            = $this->_serviceConfig->getItem('client_secret');
        $this->_issuer                  = $this->_serviceConfig->getItem('issuer');
        $this->_redirectURL             = $this->_serviceConfig->getItem('redirect_url');
        $this->_tokenExpiration         = $this->_serviceConfig->getItem('token_expiration');
        $this->_refreshTokenExpiration  = $this->_serviceConfig->getItem('refresh_token_expiration');
        $this->_actions                 = $this->_serviceConfig->getArray('actions', '');
        $this->_allowedReferrers        = $this->_serviceConfig->getItem('allowed_referrers');
        $this->_scopesDescription       = $this->_serviceConfig->getScopesDescriptions($this->_scopes);
    }

    /**
     * Create and initialize token storage instance for the 
     * handled service and current AAIOIDC configuration.
     * 
     * @param string                        $service    The service name this instance handles.
     * @param \AAIOIDConfiguration          $config     The configuration instance.
     * @return \AAIOIDCStorage                
     */
    private function createStorage($service, $config) {
        $s = strtolower(trim($config->getItem('storage.type'), 'filesystem'));
        $storage = null;

        /*
         * Select configured storage handler.
         * 
         * This section must be updated accordinly if
         * we want to support more storage types.
         * 
         * The storage type instances must inherit and implement the
         * AAIOIDCStorage class located at ./base/AAIOIDCStorage.php 
         */
        switch($s) {
            case 'filesystem':
            case 'fs':
            default:
                $storagePath = $config->getItem('storage.path', '/storage/users/oidc');
                $storage = new AAIOIDCFileSystemStorage($service, $storagePath);
                break;
        }

        /*
         * Setup storage encryption functionality if such is configured.
         * 
         * This section must be updated accordinly if
         * we want to support more encyrption mechanisms.
         * 
         * The encryption type instances must inherit and implement the
         * AAIOIDCEncryption class located at ./base/AAIOIDCEncryption.php
         */
        $encryptionType = strtolower(trim($this->_config->getItem('storage.encryption', null)));
        $encryptionParameterGetter = trim($this->_config->getItem('storage.encryptionParameterGetter', null));
        $encryptionParameterGetterName = trim($this->_config->getItem('storage.encryptionParameterGetterName', null));
        switch ($encryptionType) {
            case 'openssl':
                if (!$encryptionParameterGetter) {
                    throw new Exception("OIODC client encryption is not configured properly");
                }

                require_once($encryptionParameterGetter);

                $params = $encryptionParameterGetterName($this);
                $encryption = new AAIOIDCEncryptionOpenSSL($params);
                $storage->setEncryption($encryption);
                break;
            default:
                break;
        }

        return $storage;
    }

    /**
     * Retrieves a new access token by using an existing stored refresh token
     * 
     * @return object
     */
    private function refreshAccessToken() {
        //curl with refresh token
        $result = array(
            'error' => null,
            'errorDescription' => null,
            'response' => null
        );

        $refreshToken = $this->_storage->getUserRefreshToken($this->_uid);

        //Check if no refresh token information is stored
        if (!$refreshToken) {
            $result['error'] = 'Not found';
            $result['errorDescription'] = 'No refresh token found';
            return (object) $result;
        }

        //Do not try to get a new access token if stored refresh token is invalid (expired etc)
        $refreshTokenValidation = $refreshToken->validate();
        if ($refreshTokenValidation !== true) {
            $result['error'] = 'Invalid refresh token.' . $refreshTokenValidation;
            $result['errorDescription'] = $refreshTokenValidation;
            return (object) $result;
        }

        try {
            $oidc = new OpenIDConnectClient(
                $this->_issuer,
                $this->_clientID,
                $this->_clientSecret
            );

            if (ApplicationConfiguration::isProductionInstance() === false) {
                //The certificate verification is disabled in development
                $oidc->setVerifyPeer(false);
                $oidc->setVerifyHost(false);
            }

            $result['response'] = $oidc->refreshToken($refreshToken->get('token'));
            $result['userid'] = $oidc->requestUserInfo('sub');

            if ($result['userid'] !== $this->getUserUID()) {
                throw new Exception('The authenticated account do not match with current user information');
            }
        } catch(Exception $error) {
            $result['error'] = 'Could not refresh access token';
            $result['errorDescription'] = $this->sanitize($error->getMessage());
        }

        return (object) $result;
        //"curl -X POST -u '${$this->_clientID}':'${$this->_clientSecret}'  -d 'client_id=${$this->_clientID}&client_secret=${$this->_clientSecret}&grant_type=refresh_token&refresh_token=${$refreshToken}&scope=openid%20email%20profile' '${$this->_tokenEndpoint}
    }
    
    private function authenticateUser($redirectUrl) {
        $result = array(
            'accessToken' => null,
            'refreshToken' => null,
            'tokenEndpoint' => null,
            'error' => null,
            'errorDescription' => null
        );

        $redirectUrl = trim($redirectUrl);

        if ($redirectUrl === '') {
            $redirectUrl = $this->_redirectURL;
        }

        try {
            $oidc = new OpenIDConnectClient(
                $this->_issuer,
                $this->_clientID,
                $this->_clientSecret
            );

            if (ApplicationConfiguration::isProductionInstance() === false) {
                //The certificate verification is disabled in development
                $oidc->setVerifyPeer(false);
                $oidc->setVerifyHost(false);
            }

            $scopes = $this->_scopes;
            $oidc->addScope($scopes);
            $oidc->setRedirectURL($redirectUrl);
            $response = array('code');
            $oidc->setResponseTypes($response);

            $oidc->authenticate();

            $result['refreshToken']  = $oidc->getRefreshToken();
            $result['accessToken']   = $oidc->getAccessToken();
            $result['tokenResponse'] = $oidc->getTokenResponse();
            $result['userid'] = $oidc->requestUserInfo('sub');

            if ($result['userid'] !== $this->getUserUID()) {
                $result['error'] = 'Could not retrieve refresh token';
                $result['errorDescription'] = 'The authenticated account do not match with current user information';
                $result['errorDetails'] = "You authenticated with an account of user ID \n\n" . $result['userid'] . "\n\nbut the request is made for user ID\n\n" . $this->getUserUID()  . "\n\nPlease retry with an account of the requested user ID.";
            }
        } catch(Exception $error) {
            $result['error'] = 'Could not retrieve refresh token';
            $result['errorDescription'] = $error->getMessage();
        }

        return (object) $result;
    }

    /**
     * Check if OpenID connect is configured as enabled
     * 
     * @return boolean
     */
    public function isEnabled() {
        return $this->_enabled;
    }

    /**
     * Get last error type
     * 
     * @return string
     */
    public function getError() {
        return $this->_error;
    }

    /**
     * Get last error description
     * 
     * @return string
     */
    public function getErrorDescription() {
        return $this->_errorDescription;
    }

    /**
     * Get last error details
     *
     * @return string
     */
    public function getErrorDetails() {
        return $this->_errorDetails;
    }

    /**
     * Get the description of the scopes of user consent
     * 
     * @return string[]
     */
    public function getScopesDescription() {
        return $this->_scopesDescription;
    }

    public function getServiceCode() {
        return $this->_service;
    }

    /**
     * Get the name of the service this instance handles.
     * 
     * @return string
     */
    public function getServiceName() {
        return $this->_serviceName;
    }

    /**
     * Get the image URL of the service this instance handles.
     * 
     * @return string
     */
    public function getServiceLogo() {
        return $this->_serviceLogo;
    }

    /**
     * Get the UID of the user this instance handles
     * 
     * @return string
     */
    public function getUserUID() {
        return $this->_uid;
    }

    /**
     * Get configuration object of this instance
     *
     * @return AAIOIDConfiguration
     */
    public function getConfig() {
        return $this->_config;
    }
    /**
     * Get raw access token.
     * 
     * @param boolean $refreshToken If true, the method tries to issue a new one if no valid access token is found
     * @return string|null   Raw access token
     */
    public function getUserAccessTokenInfo($autoRefresh= false) {
        $accessToken = new AAIOIDCAccessToken($this->_storage->getUserAccessToken($this->_uid));
        $accessTokenValidation = $accessToken->validate();

        if ($accessTokenValidation === true) {
            debug_log('[AAIOpenIDConnect::getUserAccessToken] Found cached Access Token (service: ' . $this->getServiceCode() . ', UID: ' .$this->getUserUID() . ')');
            return $accessToken->toArray();
        }

        debug_log('[AAIOpenIDConnect::getUserAccessToken][WARN] Invalid Access Token: ' . $accessTokenValidation . ' (service: ' . $this->getServiceCode() . ', UID: ' .$this->getUserUID() . ')');

        if ($autoRefresh === true) {
            $response = $this->refreshAccessToken();

            if ($response->error) {
                debug_log('[AAIOpenIDConnect::getUserAccessToken][ERROR] Could not refresh access token. (service: ' . $this->getServiceCode() . ', UID: ' .$this->getUserUID() . ')\nReason: ' . $response->error . '.' . $response->errorDescription );
                return null;
            }

            $response = (isset($response->response) ? $response->response : null); 
            if ($response  && isset($response->access_token) && trim($response->access_token) !== '') {
                debug_log('[AAIOpenIDConnect::getUserAccessToken] Issued new access token. Trying to store it (service: ' . $this->getServiceCode() . ', UID: ' .$this->getUserUID() . ')');

                $accessTokenExpiration = $this->_accessTokenExpiration;
                if (isset($response->expires_in) && trim($response->expires_in) !== '') {
                    $accessTokenExpiration = trim($response->expires_in);
                }
                debug_log('[AAIOpenIDConnect::getUserAccessTokenInfo] ====> ' . var_export(array(
                    "type" => "access",
                    "token" => $response->access_token,
                    "issued" => time(),
                    "accessTokenExpiration" => $accessTokenExpiration,
                    "accessTokenExpirationIntVal" => intval($accessTokenExpiration),
                    "expires" => time() + intval($accessTokenExpiration),
                    "user" => $this->_uid,
                    "service" => $this->_service,
                    "issuer" => $this->_issuer
                ), true));
                $atoken = new AAIOIDCAccessToken(array(
                    "type" => "access",
                    "token" => $response->access_token,
                    "issued" => time(),
                    "expires" => time() + intval($accessTokenExpiration),
                    "user" => $this->_uid,
                    "service" => $this->_service,
                    "issuer" => $this->_issuer
                ));

                $success = $this->_storage->setUserAccessToken($atoken, $this->_uid);

                if ($success !== true) {
                    debug_log('[AAIOpenIDConnect::getUserAccessToken][ERROR] Could not store access token (service: ' . $this->getServiceCode() . ', UID: ' .$this->getUserUID() . ') ' .$success);
                    return null;
                } else {
                    return $this->getUserAccessTokenInfo(false);
                }
            }
        }
        
        return null;
    }

    /**
     * Get raw refresh token. If refresh 
     * @return type
     */
    public function getUserRefreshTokenInfo() {
        $refreshToken = new AAIOIDCRefreshToken($this->_storage->getUserRefreshToken($this->_uid));

        if ($refreshToken->validate() === true) {
            return $refreshToken->toArray();
        } else {
            debug_log('[AAIOIDConnect::getUserRefreshToken][ERROR] Could not get refresh token (service: ' . $this->getServiceCode() . ', UID: ' .$this->getUserUID() . ')\nReason:  ' . $refreshToken->validate());
        }

        return null;
    }

    /**
     * Perform the OpenID connect user authentication flow with the issuer.
     * 
     * @param   string  $redirectURL    Instruct issuer where to redirect.
     * @return  boolean                 True on successful authentication.
     */
    public function authenticate($redirectURL) {
        $response = $this->authenticateUser($redirectURL);

        if ($response->error) {
            debug_log('[AAIOpenIDConnect::authenticate][ERROR] Could not authenticate user ' . trim($response->error) . '(service: ' . $this->getServiceCode() . ', UID: ' .$this->getUserUID() . ')\nReason: ' . trim($response->errorDescription));

            $this->_error = trim($response->error);
            $this->_errorDescription = trim($response->errorDescription);
            $this->_errorDetails = $response->errorDetails;

            return false;
        }

        if (trim($response->refreshToken) !== '') {
            debug_log('[AAIOpenIDConnect::authenticate] Found refresh token. Trying to store it (service: ' . $this->getServiceCode() . ', UID: ' .$this->getUserUID() . ')');

            $rtoken = new AAIOIDCRefreshToken(array(
                "type" => "refresh",
                "token" => $response->refreshToken,
                "issued" => time(),
                "issuer" => $this->_issuer,
                "tokenExpiration" => intval($this->_refreshTokenExpiration),
                "issuedAt" => time(),
                "expires" => time() + intval($this->_refreshTokenExpiration),
                "user" => $this->getUserUID(),
                "service" => $this->_service
            ));

            $success = $this->_storage->setUserRefreshToken($rtoken, $this->_uid);

            if ($success !== true) {
                debug_log('[AAIOpenIDConnect::authenticate][ERROR] Could not store refresh token (service: ' . $this->getServiceCode() . ', UID: ' .$this->getUserUID() . ')\nReason: ' . $success);
            } else {
                debug_log('[AAIOpenIDConnect::authenticate] Stored refresh token (service: ' . $this->getServiceCode() . ', UID: ' .$this->getUserUID() . ')');
            }
        }

        if (trim($response->accessToken) !== '') {
            debug_log('[AAIOpenIDConnect::authenticate] Found access token. Trying to store it (service: ' . $this->getServiceCode() . ', UID: ' .$this->getUserUID() . ')');

            $accessTokenExpiration = $this->_accessTokenExpiration;

            if ($response->tokenResponse && trim($response->tokenResponse->expires_in) !== '') {
                $accessTokenExpiration = trim($response->tokenResponse->expires_in);
            }

            $atoken = new AAIOIDCAccessToken(array(
                "type" => "access",
                "token" => $response->accessToken,
                "issued" => time(),
                "issuer" => $this->_issuer,
                "tokenExpiration" => intval($accessTokenExpiration),
                "issuedAt" => time(),
                "expires" => time() + intval($accessTokenExpiration),
                "user" => $this->_uid,
                "service" => $this->_service
            ));

            $success = $this->_storage->setUserAccessToken($atoken, $this->_uid);

            if ($success !== true) {
                debug_log('[AAIOpenIDConnect::authenticate][ERROR] Could not store access token (service: ' . $this->getServiceCode() . ', UID: ' .$this->getUserUID() . ')\nReason: ' . $success);
                return false;
            }

            debug_log('[AAIOpenIDConnect::authenticate] Stored access token (service: ' . $this->getServiceCode() . ', UID: ' .$this->getUserUID() . ')');
        }

        return true;
    }
}