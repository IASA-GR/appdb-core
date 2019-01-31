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

class UserRequestsFilterBase extends Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'typeid';
		$this->_fields[] = 'userguid';
		$this->_fields[] = 'userdata';
		$this->_fields[] = 'targetguid';
		$this->_fields[] = 'actorguid';
		$this->_fields[] = 'actordata';
		$this->_fields[] = 'stateid';
		$this->_fields[] = 'created';
		$this->_fields[] = 'lastupdated';
		$this->_fields[] = 'guid';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['typeid'] = 'integer';
		$this->_fieldTypes['userguid'] = 'string';
		$this->_fieldTypes['userdata'] = 'string';
		$this->_fieldTypes['targetguid'] = 'uuid';
		$this->_fieldTypes['actorguid'] = 'uuid';
		$this->_fieldTypes['actordata'] = 'string';
		$this->_fieldTypes['stateid'] = 'integer';
		$this->_fieldTypes['created'] = 'string';
		$this->_fieldTypes['lastupdated'] = 'string';
		$this->_fieldTypes['guid'] = 'uuid';
		$this->_table = 'userrequests';
	}
}