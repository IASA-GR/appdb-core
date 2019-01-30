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

class PplDelInfosFilterBase extends Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'researcherid';
		$this->_fields[] = 'deletedby';
		$this->_fields[] = 'deletedon';
		$this->_fields[] = 'roleid';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['researcherid'] = 'integer';
		$this->_fieldTypes['deletedby'] = 'integer';
		$this->_fieldTypes['deletedon'] = 'string';
		$this->_fieldTypes['roleid'] = 'integer';
		$this->_table = 'ppl_del_infos';
	}
}
