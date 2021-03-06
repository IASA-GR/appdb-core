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
class Default_Model_VMIflavoursFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'vmiid';
		$this->_fields[] = 'hypervisors';
		$this->_fields[] = 'archid';
		$this->_fields[] = 'osid';
		$this->_fields[] = 'osversion';
		$this->_fields[] = 'format';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['vmiid'] = 'integer';
		$this->_fieldTypes['hypervisors'] = 'string';
		$this->_fieldTypes['archid'] = 'integer';
		$this->_fieldTypes['osid'] = 'integer';
		$this->_fieldTypes['osversion'] = 'string';
		$this->_fieldTypes['format'] = 'string';
		$this->_table = 'vmiflavours';
	}
}
