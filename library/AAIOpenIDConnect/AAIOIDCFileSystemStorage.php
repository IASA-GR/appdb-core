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

require_once(__DIR__ . '/base/AAIOIDCStorage.php');
require_once(__DIR__ . '/base/AAIOIDCStorageType.php');

/**
 * Stores tokens in the file system.
 */
class AAIOIDCFileSystemStorage extends AAIOIDCStorage{
    private $_storagePath;

    private static $_typeSuffixes = array(
        AAIOIDCStorageType::ACCESS_TOKEN => '.atoken',
        AAIOIDCStorageType::REFRESH_TOKEN => '.rtoken'
    );

    /**
     * @param string $service       The related service of the storage
     * @param string $storagePath   The file system path to store tokens
     */
    public function __construct($service, $storagePath) {
        parent::__construct($service);
        $this->_storagePath = $storagePath;        
    }

    /**
     * Retrieves the configure file path of the storage
     * 
     * @param   boolean $ensure Create file path if it does not exist
     * @return  string          The file system path to store tokens
     */
    private function getFilePath($ensure = false) {
        $path = $this->_storagePath . '/' . $this->_service . '/';
        if ($ensure === true) {
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
        }

        return $path;
    }

    /**
     * Implementation of AAIOIDCStorage::_get method.
     * 
     * @param   AAIOIDCStorageType  $storageType
     * @param   string              $uid
     * @return  string|null
     */
    protected function _get($storageType, $uid) {
        $path = $this->getFilePath(true) . $uid . AAIOIDCFileSystemStorage::$_typeSuffixes[$storageType];

        if (!file_exists($path)) {
            return null;
        }

        $fd = fopen($path, "r");
        $contents = fread($fd, filesize($path));
        fclose($fd);

        if (trim($contents) === '') {
            return null;
        } else {
            try {
                return trim($contents);
            } catch (Exception $ex) {
                debug_log('[AAIOIDCFileSystemStorage::_get][ERROR] Could not decode contents. Reason: ' . $ex->getMessage());
                return null;
            }
        }
    }

    protected function _set($storageType, $uid, $content) {
        $path = $this->getFilePath(true) . $uid . AAIOIDCFileSystemStorage::$_typeSuffixes[$storageType];
        $fd = fopen($path, "w");
        $data = $content;

        if (is_null($content)) {
            $data = '';
        }

        if (!is_string($content)) {
            $data = (array) $content;
            if (is_array($data) && count($data) === 0) {
                $data = '';
            } else {
                $data = json_encode($data);
                $data = trim($data);
            }
        }

        fwrite($fd, $data) or die('Could not write to storage');
        fflush($fd);
        fclose($fd);
    }

    public function validateStorage() {
        clearstatcache();
        if (trim($this->_storagePath) === '') {
            return 'Storage path is not configured';
        }

        if (!is_dir($this->_storagePath)) {
            mkdir($this->_storagePath, 0777, true);
            return 'Storage does not exist';
        }

        $iswritable = is_writable($this->_storagePath);
        if (!$iswritable) {
            return 'Storage is not writable';
        }

        return true;
    }
}