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

class VMIinstancesFilterBase extends Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'size';
		$this->_fields[] = 'uri';
		$this->_fields[] = 'version';
		$this->_fields[] = 'checksum';
		$this->_fields[] = 'checksumfunc';
		$this->_fields[] = 'notes';
		$this->_fields[] = 'guid';
		$this->_fields[] = 'addedon';
		$this->_fields[] = 'addedby';
		$this->_fields[] = 'vmiflavourid';
		$this->_fields[] = 'autointegrity';
		$this->_fields[] = 'coreminimum';
		$this->_fields[] = 'ramminimum';
		$this->_fields[] = 'lastupdatedby';
		$this->_fields[] = 'lastupdatedon';
		$this->_fields[] = 'description';
		$this->_fields[] = 'title';
		$this->_fields[] = 'integrity_status';
		$this->_fields[] = 'integrity_message';
		$this->_fields[] = 'ramrecommend';
		$this->_fields[] = 'corerecommend';
		$this->_fields[] = 'accessinfo';
		$this->_fields[] = 'enabled';
		$this->_fields[] = 'initialsize';
		$this->_fields[] = 'initialchecksum';
		$this->_fields[] = 'ovfurl';
		$this->_fields[] = 'default_access';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['size'] = 'bigint';
		$this->_fieldTypes['uri'] = 'string';
		$this->_fieldTypes['version'] = 'string';
		$this->_fieldTypes['checksum'] = 'string';
		$this->_fieldTypes['checksumfunc'] = 'e_hashfuncs';
		$this->_fieldTypes['notes'] = 'string';
		$this->_fieldTypes['guid'] = 'uuid';
		$this->_fieldTypes['addedon'] = 'string';
		$this->_fieldTypes['addedby'] = 'integer';
		$this->_fieldTypes['vmiflavourid'] = 'integer';
		$this->_fieldTypes['autointegrity'] = 'boolean';
		$this->_fieldTypes['coreminimum'] = 'integer';
		$this->_fieldTypes['ramminimum'] = 'integer';
		$this->_fieldTypes['lastupdatedby'] = 'integer';
		$this->_fieldTypes['lastupdatedon'] = 'string';
		$this->_fieldTypes['description'] = 'string';
		$this->_fieldTypes['title'] = 'string';
		$this->_fieldTypes['integrity_status'] = 'string';
		$this->_fieldTypes['integrity_message'] = 'string';
		$this->_fieldTypes['ramrecommend'] = 'integer';
		$this->_fieldTypes['corerecommend'] = 'integer';
		$this->_fieldTypes['accessinfo'] = 'string';
		$this->_fieldTypes['enabled'] = 'boolean';
		$this->_fieldTypes['initialsize'] = 'string';
		$this->_fieldTypes['initialchecksum'] = 'string';
		$this->_fieldTypes['ovfurl'] = 'string';
		$this->_fieldTypes['default_access'] = 'string';
		$this->_table = 'vmiinstances';
	}
}
