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

class EntityRelationsFilterBase extends Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'reltypeid';
		$this->_fields[] = 'verbid';
		$this->_fields[] = 'target_guid';
		$this->_fields[] = 'target_type';
		$this->_fields[] = 'actionid';
		$this->_fields[] = 'verb';
		$this->_fields[] = 'verbname';
		$this->_fields[] = 'verbrname';
		$this->_fields[] = 'subject_guid';
		$this->_fields[] = 'subject_type';
		$this->_fields[] = 'typeguid';
		$this->_fields[] = 'guid';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['reltypeid'] = 'integer';
		$this->_fieldTypes['verbid'] = 'integer';
		$this->_fieldTypes['target_guid'] = 'string';
		$this->_fieldTypes['target_type'] = 'e_entity';
		$this->_fieldTypes['actionid'] = 'integer';
		$this->_fieldTypes['verb'] = 'string';
		$this->_fieldTypes['verbname'] = 'string';
		$this->_fieldTypes['verbrname'] = 'string';
		$this->_fieldTypes['subject_guid'] = 'uuid';
		$this->_fieldTypes['subject_type'] = 'e_entity';
		$this->_fieldTypes['typeguid'] = 'uuid';
		$this->_fieldTypes['guid'] = 'uuid';
		$this->_table = 'entityrelations';
	}
}
