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
namespace Application\Model;

class ApplicationBase extends AROItem {
	public function __construct() {
		$this->_basename = 'Applications';
		$this->_baseitemname = 'Application';
		parent::__construct();
		$this->_properties[] = new \Application\Model\AROProperty($this, 'id', 'id');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'name', 'name');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'description', 'description');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'abstract', 'abstract');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'statusid', 'statusid');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'dateadded', 'dateadded');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'addedby', 'addedby');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'respect', 'respect');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'tool', 'tool');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'guid', 'guid');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'keywords', 'keywords');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'lastupdated', 'lastupdated');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'rating', 'rating');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'ratingcount', 'ratingcount');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'moderated', 'moderated');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'tagpolicy', 'tagpolicy');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'deleted', 'deleted');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'metatype', 'metatype');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'disciplineid', 'disciplineid');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'owner', 'owner');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'categoryid', 'categoryid');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'hitcount', 'hitcount');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'cname', 'cname');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'links', 'links');
	}
}
