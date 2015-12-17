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
class Default_Model_AppDocumentsFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'appid';
		$this->_fields[] = 'title';
		$this->_fields[] = 'url';
		$this->_fields[] = 'conference';
		$this->_fields[] = 'proceedings';
		$this->_fields[] = 'isbn';
		$this->_fields[] = 'pagestart';
		$this->_fields[] = 'pageend';
		$this->_fields[] = 'volume';
		$this->_fields[] = 'publisher';
		$this->_fields[] = 'year';
		$this->_fields[] = 'mainauthor';
		$this->_fields[] = 'doctypeid';
		$this->_fields[] = 'journal';
		$this->_fields[] = 'guid';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['appid'] = 'integer';
		$this->_fieldTypes['title'] = 'string';
		$this->_fieldTypes['url'] = 'string';
		$this->_fieldTypes['conference'] = 'string';
		$this->_fieldTypes['proceedings'] = 'string';
		$this->_fieldTypes['isbn'] = 'string';
		$this->_fieldTypes['pagestart'] = 'integer';
		$this->_fieldTypes['pageend'] = 'integer';
		$this->_fieldTypes['volume'] = 'string';
		$this->_fieldTypes['publisher'] = 'string';
		$this->_fieldTypes['year'] = 'integer';
		$this->_fieldTypes['mainauthor'] = 'boolean';
		$this->_fieldTypes['doctypeid'] = 'integer';
		$this->_fieldTypes['journal'] = 'string';
		$this->_fieldTypes['guid'] = 'uuid';
		$this->_table = 'appdocuments';
	}
}
