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
class Default_Model_ApplicationsFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'name';
		$this->_fields[] = 'description';
		$this->_fields[] = 'abstract';
		$this->_fields[] = 'statusid';
		$this->_fields[] = 'dateadded';
		$this->_fields[] = 'addedby';
		$this->_fields[] = 'respect';
		$this->_fields[] = 'tool';
		$this->_fields[] = 'guid';
		$this->_fields[] = 'keywords';
		$this->_fields[] = 'lastupdated';
		$this->_fields[] = 'rating';
		$this->_fields[] = 'ratingcount';
		$this->_fields[] = 'moderated';
		$this->_fields[] = 'tagpolicy';
		$this->_fields[] = 'deleted';
		$this->_fields[] = 'metatype';
		$this->_fields[] = 'disciplineid';
		$this->_fields[] = 'owner';
		$this->_fields[] = 'categoryid';
		$this->_fields[] = 'hitcount';
		$this->_fields[] = 'cname';
		$this->_fields[] = 'links';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['name'] = 'string';
		$this->_fieldTypes['description'] = 'string';
		$this->_fieldTypes['abstract'] = 'string';
		$this->_fieldTypes['statusid'] = 'integer';
		$this->_fieldTypes['dateadded'] = 'string';
		$this->_fieldTypes['addedby'] = 'integer';
		$this->_fieldTypes['respect'] = 'boolean';
		$this->_fieldTypes['tool'] = 'boolean';
		$this->_fieldTypes['guid'] = 'string';
		$this->_fieldTypes['keywords'] = 'string';
		$this->_fieldTypes['lastupdated'] = 'string';
		$this->_fieldTypes['rating'] = 'float';
		$this->_fieldTypes['ratingcount'] = 'integer';
		$this->_fieldTypes['moderated'] = 'boolean';
		$this->_fieldTypes['tagpolicy'] = 'integer';
		$this->_fieldTypes['deleted'] = 'boolean';
		$this->_fieldTypes['metatype'] = 'integer';
		$this->_fieldTypes['disciplineid'] = 'integer';
		$this->_fieldTypes['owner'] = 'integer';
		$this->_fieldTypes['categoryid'] = 'integer';
		$this->_fieldTypes['hitcount'] = 'integer';
		$this->_fieldTypes['cname'] = 'string';
		$this->_fieldTypes['links'] = 'string';
		$this->_table = 'applications';
	}
}
