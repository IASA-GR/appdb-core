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

class CountryRegionsFilterBase extends Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'regionid';
		$this->_fields[] = 'region';
		$this->_fields[] = 'countryid';
		$this->_fields[] = 'country';
		$this->_fields[] = 'isocode';
		$this->_fields[] = 'continent';
		$this->_fieldTypes['regionid'] = 'integer';
		$this->_fieldTypes['region'] = 'string';
		$this->_fieldTypes['countryid'] = 'integer';
		$this->_fieldTypes['country'] = 'string';
		$this->_fieldTypes['isocode'] = 'string';
		$this->_fieldTypes['continent'] = 'boolean';
		$this->_table = 'countryregions';
	}
}
