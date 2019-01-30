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
?>
<?php
// PLEASE DO NOT EDIT THIS FILE
// IT IS AUTOMATICALLY GENERATED BY THE MODELLER
// AND ANY CHANGES WILL BE OVERWRITTEN
namespace Application\Model;

class AppReleaseBase extends AROItem {
	public function __construct() {
		$this->_basename = 'AppReleases';
		$this->_baseitemname = 'AppRelease';
		parent::__construct();
		$this->_properties[] = new \Application\Model\AROProperty($this, 'id', 'id');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'appid', 'appID');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'appid', 'application', 'Application');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'release', 'release');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'series', 'series');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'state', 'state');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'addedon', 'addedon');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'publishedon', 'publishedon');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'manager', 'managerID');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'manager', 'manager', 'Researcher');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'lastupdated', 'lastupdated');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'releaseid', 'releaseID');
	}
}
?>