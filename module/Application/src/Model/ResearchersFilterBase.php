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
class Default_Model_ResearchersFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'firstname';
		$this->_fields[] = 'lastname';
		$this->_fields[] = 'dateinclusion';
		$this->_fields[] = 'institution';
		$this->_fields[] = 'countryid';
		$this->_fields[] = 'positiontypeid';
		$this->_fields[] = 'guid';
		$this->_fields[] = 'lastupdated';
		$this->_fields[] = 'name';
		$this->_fields[] = 'mail_unsubscribe_pwd';
		$this->_fields[] = 'lastlogin';
		$this->_fields[] = 'nodissemination';
		$this->_fields[] = 'accounttype';
		$this->_fields[] = 'deleted';
		$this->_fields[] = 'hitcount';
		$this->_fields[] = 'cname';
		$this->_fields[] = 'addedby';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['firstname'] = 'string';
		$this->_fieldTypes['lastname'] = 'string';
		$this->_fieldTypes['dateinclusion'] = 'string';
		$this->_fieldTypes['institution'] = 'string';
		$this->_fieldTypes['countryid'] = 'integer';
		$this->_fieldTypes['positiontypeid'] = 'integer';
		$this->_fieldTypes['guid'] = 'string';
		$this->_fieldTypes['lastupdated'] = 'string';
		$this->_fieldTypes['name'] = 'string';
		$this->_fieldTypes['mail_unsubscribe_pwd'] = 'string';
		$this->_fieldTypes['lastlogin'] = 'string';
		$this->_fieldTypes['nodissemination'] = 'boolean';
		$this->_fieldTypes['accounttype'] = 'integer';
		$this->_fieldTypes['deleted'] = 'boolean';
		$this->_fieldTypes['hitcount'] = 'integer';
		$this->_fieldTypes['cname'] = 'string';
		$this->_fieldTypes['addedby'] = 'integer';
		$this->_table = 'researchers';
	}
}
