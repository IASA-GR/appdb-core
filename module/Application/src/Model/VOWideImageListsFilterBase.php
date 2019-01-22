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
class Default_Model_VOWideImageListsFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'void';
		$this->_fields[] = 'guid';
		$this->_fields[] = 'state';
		$this->_fields[] = 'expires_on';
		$this->_fields[] = 'published_on';
		$this->_fields[] = 'notes';
		$this->_fields[] = 'title';
		$this->_fields[] = 'alteredby';
		$this->_fields[] = 'lastmodified';
		$this->_fields[] = 'publishedby';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['void'] = 'integer';
		$this->_fieldTypes['guid'] = 'string';
		$this->_fieldTypes['state'] = 'e_vowide_image_state';
		$this->_fieldTypes['expires_on'] = 'string';
		$this->_fieldTypes['published_on'] = 'string';
		$this->_fieldTypes['notes'] = 'string';
		$this->_fieldTypes['title'] = 'string';
		$this->_fieldTypes['alteredby'] = 'integer';
		$this->_fieldTypes['lastmodified'] = 'string';
		$this->_fieldTypes['publishedby'] = 'integer';
		$this->_table = 'vowide_image_lists';
	}
}
