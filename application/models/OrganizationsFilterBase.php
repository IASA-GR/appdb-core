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
class Default_Model_OrganizationsFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'name';
		$this->_fields[] = 'shortname';
		$this->_fields[] = 'websiteurl';
		$this->_fields[] = 'countryid';
		$this->_fields[] = 'addedon';
		$this->_fields[] = 'addedby';
		$this->_fields[] = 'guid';
		$this->_fields[] = 'identifier';
		$this->_fields[] = 'sourceid';
		$this->_fields[] = 'deletedon';
		$this->_fields[] = 'deletedby';
		$this->_fields[] = 'ext_identifier';
		$this->_fields[] = 'moderated';
		$this->_fields[] = 'deleted';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['name'] = 'string';
		$this->_fieldTypes['shortname'] = 'string';
		$this->_fieldTypes['websiteurl'] = 'string';
		$this->_fieldTypes['countryid'] = 'integer';
		$this->_fieldTypes['addedon'] = 'string';
		$this->_fieldTypes['addedby'] = 'integer';
		$this->_fieldTypes['guid'] = 'string';
		$this->_fieldTypes['identifier'] = 'string';
		$this->_fieldTypes['sourceid'] = 'integer';
		$this->_fieldTypes['deletedon'] = 'string';
		$this->_fieldTypes['deletedby'] = 'integer';
		$this->_fieldTypes['ext_identifier'] = 'string';
		$this->_fieldTypes['moderated'] = 'boolean';
		$this->_fieldTypes['deleted'] = 'boolean';
		$this->_table = 'organizations';
	}
}
