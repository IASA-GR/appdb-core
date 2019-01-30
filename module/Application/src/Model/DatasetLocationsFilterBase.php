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

class DatasetLocationsFilterBase extends Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'addedby';
		$this->_fields[] = 'addedon';
		$this->_fields[] = 'uri';
		$this->_fields[] = 'is_master';
		$this->_fields[] = 'exchange_fmt';
		$this->_fields[] = 'connection_type';
		$this->_fields[] = 'is_public';
		$this->_fields[] = 'organizationid';
		$this->_fields[] = 'notes';
		$this->_fields[] = 'dataset_version_id';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['addedby'] = 'integer';
		$this->_fieldTypes['addedon'] = 'string';
		$this->_fieldTypes['uri'] = 'string';
		$this->_fieldTypes['is_master'] = 'boolean';
		$this->_fieldTypes['exchange_fmt'] = 'integer';
		$this->_fieldTypes['connection_type'] = 'integer';
		$this->_fieldTypes['is_public'] = 'boolean';
		$this->_fieldTypes['organizationid'] = 'integer';
		$this->_fieldTypes['notes'] = 'string';
		$this->_fieldTypes['dataset_version_id'] = 'integer';
		$this->_table = 'dataset_locations';
	}
}
