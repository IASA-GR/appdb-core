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
class Default_Model_VappviewsFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'vapplicationid';
		$this->_fields[] = 'vappversionid';
		$this->_fields[] = 'vmiid';
		$this->_fields[] = 'vmiinstanceid';
		$this->_fields[] = 'vmiflavourid';
		$this->_fields[] = 'vappversionguid';
		$this->_fields[] = 'vmiguid';
		$this->_fields[] = 'vmiinstanceguid';
		$this->_fields[] = 'vapplicationname';
		$this->_fields[] = 'vappversionversion';
		$this->_fields[] = 'vmigroupname';
		$this->_fields[] = 'instanceversion';
		$this->_fieldTypes['vapplicationid'] = 'integer';
		$this->_fieldTypes['vappversionid'] = 'integer';
		$this->_fieldTypes['vmiid'] = 'integer';
		$this->_fieldTypes['vmiinstanceid'] = 'integer';
		$this->_fieldTypes['vmiflavourid'] = 'integer';
		$this->_fieldTypes['vappversionguid'] = 'string';
		$this->_fieldTypes['vmiguid'] = 'uuid';
		$this->_fieldTypes['vmiinstanceguid'] = 'uuid';
		$this->_fieldTypes['vapplicationname'] = 'string';
		$this->_fieldTypes['vappversionversion'] = 'string';
		$this->_fieldTypes['vmigroupname'] = 'string';
		$this->_fieldTypes['instanceversion'] = 'string';
		$this->_table = 'vappviews';
	}
}
