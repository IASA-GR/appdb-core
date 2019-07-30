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

class AAIOIDConfiguration {
    protected $_basePath;

    public function __construct() {
        $this->_basePath = AAIOIDConfiguration::getBasePath();
    }

    private function getPath($path) {
        $p = trim($path);

        return $this->_basePath . (($p) ? '.' . $p : $p);
    }

    public function getApplicationApiKey() {
        return ApplicationConfiguration::api('key');
    }

    public function isEnabled() {
        return ApplicationConfiguration::service($this->getPath('enabled'), false);
    }

    public function getItem($path, $default = NULL) {
        return ApplicationConfiguration::service($this->getPath($path), $default);
    }

    public function getArray($path, $default = array()) {
        $ret = $this->getItem($path, $default);

        if(is_array($ret)) {
            return $ret;
        } else if (is_string($ret)) {
            $ret = trim($ret);
            return explode(';', $ret);
        } else if(is_int($ret)) {
            return array($ret);
        } else if(is_bool($ret)) {
            return array($ret);
        } else if(is_float($ret)) {
            return array($ret);
        } else if (is_long($ret)) {
            return array($ret);
        }

        return $ret;
    }

    public static function getBasePath() {
        return 'aaioidc';
    }
}

class AAIOIDServiceConfiguration extends AAIOIDConfiguration {
    const OIDC_SCOPE_DEFINITIONS = array(
            'openid' => 'log in using your identity',
            'offline_access' => 'access your info while not being logged in',
            'email' => 'read your email address',
            'profile' => 'read your basic profile info'
        );

    public function __construct($service) {
        parent::__construct();
        $this->_basePath = AAIOIDConfiguration::getBasePath() . '.service.' . $service;
    }

    public function getScopesDescriptions($scopes = array()) {
        $OIDC_SCOPE_DEFINITIONS = self::OIDC_SCOPE_DEFINITIONS;

        $scopesDescription= array();

        foreach($scopes as $scope) {
            if (isset($OIDC_SCOPE_DEFINITIONS[$scope])) {
                $scopesDescription[$scope] = $OIDC_SCOPE_DEFINITIONS[$scope];
            } else {
                $scopesDescription[$scope] = $scope;
            }
        }

        return $scopesDescription;
    }

    public static function exists($service) {
        $conf = new AAIOIDConfiguration();

        if ($conf->getItem('service.' . $service)) {
            return true;
        }

        return false;
    }
}
