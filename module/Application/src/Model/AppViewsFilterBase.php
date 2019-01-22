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
class Default_Model_AppViewsFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'name';
		$this->_fields[] = 'description';
		$this->_fields[] = 'abstract';
		$this->_fields[] = 'logo';
		$this->_fields[] = 'statusid';
		$this->_fields[] = 'middlewareid';
		$this->_fields[] = 'dateadded';
		$this->_fields[] = 'addedby';
		$this->_fields[] = 'tool';
		$this->_fields[] = 'respect';
		$this->_fields[] = 'countryid';
		$this->_fields[] = 'regionid';
		$this->_fields[] = 'void';
		$this->_fields[] = 'persondata';
		$this->_fields[] = 'hasdocs';
		$this->_fields[] = 'guid';
		$this->_fields[] = 'deleted';
		$this->_fields[] = 'moderated';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['name'] = 'string';
		$this->_fieldTypes['description'] = 'string';
		$this->_fieldTypes['abstract'] = 'string';
		$this->_fieldTypes['logo'] = 'string';
		$this->_fieldTypes['statusid'] = 'integer';
		$this->_fieldTypes['middlewareid'] = 'integer';
		$this->_fieldTypes['dateadded'] = 'string';
		$this->_fieldTypes['addedby'] = 'integer';
		$this->_fieldTypes['tool'] = 'boolean';
		$this->_fieldTypes['respect'] = 'boolean';
		$this->_fieldTypes['countryid'] = 'integer';
		$this->_fieldTypes['regionid'] = 'integer';
		$this->_fieldTypes['void'] = 'integer';
		$this->_fieldTypes['persondata'] = 'string';
		$this->_fieldTypes['hasdocs'] = 'boolean';
		$this->_fieldTypes['guid'] = 'string';
		$this->_fieldTypes['deleted'] = 'boolean';
		$this->_fieldTypes['moderated'] = 'boolean';
		$this->_table = 'appviews';
	}
}
