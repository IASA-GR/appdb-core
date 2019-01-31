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

class ProjectBase extends AROItem {
	public function __construct() {
		$this->_basename = 'Projects';
		$this->_baseitemname = 'Project';
		parent::__construct();
		$this->_properties[] = new \Application\Model\AROProperty($this, 'id', 'id');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'code', 'code');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'acronym', 'acronym');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'title', 'title');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'startdate', 'startDate');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'enddate', 'endDate');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'callidentifier', 'callIdentifier');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'websiteurl', 'websiteURL');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'keywords', 'keywords');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'duration', 'duration');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'contracttypeid', 'contractTypeID');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'contracttypeid', 'contractType', 'ContractType');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'fundingid', 'fundingID');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'fundingid', 'funding', 'Funding');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'addedon', 'addedOn');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'addedby', 'addedByID');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'addedby', 'addedBy', 'Researcher');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'guid', 'guid');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'identifier', 'identifier');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'sourceid', 'sourceID');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'sourceid', 'entitysource', 'Entitysource');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'deletedon', 'deletedon');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'deletedby', 'deletedby');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'deletedby', 'deletedBy', 'Researcher');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'ext_identifier', 'extIdentifier');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'moderated', 'moderated');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'deleted', 'deleted');
	}
}
?>