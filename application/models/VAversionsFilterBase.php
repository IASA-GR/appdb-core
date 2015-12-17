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
class Default_Model_VAversionsFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'version';
		$this->_fields[] = 'guid';
		$this->_fields[] = 'notes';
		$this->_fields[] = 'vappid';
		$this->_fields[] = 'published';
		$this->_fields[] = 'createdon';
		$this->_fields[] = 'expireson';
		$this->_fields[] = 'enabled';
		$this->_fields[] = 'archived';
		$this->_fields[] = 'status';
		$this->_fields[] = 'archivedon';
		$this->_fields[] = 'submissionid';
		$this->_fields[] = 'isexternal';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['version'] = 'string';
		$this->_fieldTypes['guid'] = 'uuid';
		$this->_fieldTypes['notes'] = 'string';
		$this->_fieldTypes['vappid'] = 'integer';
		$this->_fieldTypes['published'] = 'boolean';
		$this->_fieldTypes['createdon'] = 'string';
		$this->_fieldTypes['expireson'] = 'string';
		$this->_fieldTypes['enabled'] = 'boolean';
		$this->_fieldTypes['archived'] = 'boolean';
		$this->_fieldTypes['status'] = 'string';
		$this->_fieldTypes['archivedon'] = 'string';
		$this->_fieldTypes['submissionid'] = 'integer';
		$this->_fieldTypes['isexternal'] = 'boolean';
		$this->_table = 'vapp_versions';
	}
}
