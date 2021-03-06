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
class Default_Model_DatasetsFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'name';
		$this->_fields[] = 'category';
		$this->_fields[] = 'description';
		$this->_fields[] = 'disciplineid';
		$this->_fields[] = 'addedby';
		$this->_fields[] = 'addedon';
		$this->_fields[] = 'tags';
		$this->_fields[] = 'guid';
		$this->_fields[] = 'homepage';
		$this->_fields[] = 'elixir_url';
		$this->_fields[] = 'parentid';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['name'] = 'string';
		$this->_fieldTypes['category'] = 'string';
		$this->_fieldTypes['description'] = 'string';
		$this->_fieldTypes['disciplineid'] = 'integer';
		$this->_fieldTypes['addedby'] = 'integer';
		$this->_fieldTypes['addedon'] = 'string';
		$this->_fieldTypes['tags'] = 'string';
		$this->_fieldTypes['guid'] = 'uuid';
		$this->_fieldTypes['homepage'] = 'string';
		$this->_fieldTypes['elixir_url'] = 'string';
		$this->_fieldTypes['parentid'] = 'integer';
		$this->_table = 'datasets';
	}
}
