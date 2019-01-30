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

class APIKeyBase extends AROItem {
	public function __construct() {
		$this->_basename = 'APIKeys';
		$this->_baseitemname = 'APIKey';
		parent::__construct();
		$this->_properties[] = new \Application\Model\AROProperty($this, 'id', 'id');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'sysaccountid', 'sysaccountid');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'createdon', 'createdon');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'ownerid', 'ownerid');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'authmethods', 'authmethods');
		$this->_properties[] = new \Application\Model\AROProperty($this, 'key', 'key');
	}
}
