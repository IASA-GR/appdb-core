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

class VOsFilterBase extends Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'name';
		$this->_fields[] = 'scope';
		$this->_fields[] = 'validated';
		$this->_fields[] = 'description';
		$this->_fields[] = 'homepage';
		$this->_fields[] = 'enrollment';
		$this->_fields[] = 'aup';
		$this->_fields[] = 'domainid';
		$this->_fields[] = 'deleted';
		$this->_fields[] = 'deletedon';
		$this->_fields[] = 'alias';
		$this->_fields[] = 'status';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['name'] = 'string';
		$this->_fieldTypes['scope'] = 'string';
		$this->_fieldTypes['validated'] = 'string';
		$this->_fieldTypes['description'] = 'string';
		$this->_fieldTypes['homepage'] = 'string';
		$this->_fieldTypes['enrollment'] = 'string';
		$this->_fieldTypes['aup'] = 'string';
		$this->_fieldTypes['domainid'] = 'integer';
		$this->_fieldTypes['deleted'] = 'boolean';
		$this->_fieldTypes['deletedon'] = 'string';
		$this->_fieldTypes['alias'] = 'string';
		$this->_fieldTypes['status'] = 'string';
		$this->_table = 'vos';
	}
}
