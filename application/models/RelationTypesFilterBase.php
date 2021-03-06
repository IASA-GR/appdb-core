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
class Default_Model_RelationTypesFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'target_type';
		$this->_fields[] = 'verbid';
		$this->_fields[] = 'subject_type';
		$this->_fields[] = 'description';
		$this->_fields[] = 'actionid';
		$this->_fields[] = 'guid';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['target_type'] = 'string';
		$this->_fieldTypes['verbid'] = 'integer';
		$this->_fieldTypes['subject_type'] = 'string';
		$this->_fieldTypes['description'] = 'string';
		$this->_fieldTypes['actionid'] = 'integer';
		$this->_fieldTypes['guid'] = 'string';
		$this->_table = 'relationtypes';
	}
}
