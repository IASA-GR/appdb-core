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

class VMIsFilterBase extends Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'name';
		$this->_fields[] = 'description';
		$this->_fields[] = 'guid';
		$this->_fields[] = 'vappid';
		$this->_fields[] = 'notes';
		$this->_fields[] = 'groupname';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['name'] = 'string';
		$this->_fieldTypes['description'] = 'string';
		$this->_fieldTypes['guid'] = 'uuid';
		$this->_fieldTypes['vappid'] = 'integer';
		$this->_fieldTypes['notes'] = 'string';
		$this->_fieldTypes['groupname'] = 'string';
		$this->_table = 'vmis';
	}
}