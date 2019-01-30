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

class PplApiLogEntryBase extends AROItem {
	public function __construct() {
		$this->_basename = 'PplApiLog';
		$this->_baseitemname = 'PplApiLogEntry';
		parent::__construct();
		$this->_properties[] = new \Application\Model\AROProperty($this, 'id', 'id');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'pplid', 'pplID');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'pplid', 'person', 'Researcher');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'timestamp', 'timestamp');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'researcherid', 'researcherID');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'researcherid', 'researcher', 'Researcher');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'source', 'source');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'ip', 'ip');
	}
}
?>