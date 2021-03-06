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
class Default_Model_AppRatingsFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'appid';
		$this->_fields[] = 'rating';
		$this->_fields[] = 'comment';
		$this->_fields[] = 'submittedon';
		$this->_fields[] = 'submitterid';
		$this->_fields[] = 'submittername';
		$this->_fields[] = 'submitteremail';
		$this->_fields[] = 'guid';
		$this->_fields[] = 'moderated';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['appid'] = 'integer';
		$this->_fieldTypes['rating'] = 'integer';
		$this->_fieldTypes['comment'] = 'string';
		$this->_fieldTypes['submittedon'] = 'string';
		$this->_fieldTypes['submitterid'] = 'integer';
		$this->_fieldTypes['submittername'] = 'string';
		$this->_fieldTypes['submitteremail'] = 'string';
		$this->_fieldTypes['guid'] = 'uuid';
		$this->_fieldTypes['moderated'] = 'boolean';
		$this->_table = 'appratings';
	}
}
