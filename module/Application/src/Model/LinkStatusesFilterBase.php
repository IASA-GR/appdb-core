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

class LinkStatusesFilterBase extends Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'appname';
		$this->_fields[] = 'ownerid';
		$this->_fields[] = 'ownername';
		$this->_fields[] = 'contact';
		$this->_fields[] = 'title';
		$this->_fields[] = 'linkid';
		$this->_fields[] = 'appid';
		$this->_fields[] = 'linktype';
		$this->_fields[] = 'urlname';
		$this->_fields[] = 'parentname';
		$this->_fields[] = 'baseref';
		$this->_fields[] = 'valid';
		$this->_fields[] = 'result';
		$this->_fields[] = 'warning';
		$this->_fields[] = 'info';
		$this->_fields[] = 'url';
		$this->_fields[] = 'line';
		$this->_fields[] = 'col';
		$this->_fields[] = 'name';
		$this->_fields[] = 'checktime';
		$this->_fields[] = 'dltime';
		$this->_fields[] = 'dlsize';
		$this->_fields[] = 'cached';
		$this->_fields[] = 'firstchecked';
		$this->_fields[] = 'lastchecked';
		$this->_fields[] = 'age';
		$this->_fields[] = 'whitelisted';
		$this->_fieldTypes['appname'] = 'string';
		$this->_fieldTypes['ownerid'] = 'integer';
		$this->_fieldTypes['ownername'] = 'string';
		$this->_fieldTypes['contact'] = 'string';
		$this->_fieldTypes['title'] = 'string';
		$this->_fieldTypes['linkid'] = 'string';
		$this->_fieldTypes['appid'] = 'integer';
		$this->_fieldTypes['linktype'] = 'string';
		$this->_fieldTypes['urlname'] = 'string';
		$this->_fieldTypes['parentname'] = 'string';
		$this->_fieldTypes['baseref'] = 'string';
		$this->_fieldTypes['valid'] = 'integer';
		$this->_fieldTypes['result'] = 'string';
		$this->_fieldTypes['warning'] = 'string';
		$this->_fieldTypes['info'] = 'string';
		$this->_fieldTypes['url'] = 'string';
		$this->_fieldTypes['line'] = 'integer';
		$this->_fieldTypes['col'] = 'integer';
		$this->_fieldTypes['name'] = 'string';
		$this->_fieldTypes['checktime'] = 'integer';
		$this->_fieldTypes['dltime'] = 'integer';
		$this->_fieldTypes['dlsize'] = 'integer';
		$this->_fieldTypes['cached'] = 'integer';
		$this->_fieldTypes['firstchecked'] = 'string';
		$this->_fieldTypes['lastchecked'] = 'string';
		$this->_fieldTypes['age'] = 'string';
		$this->_fieldTypes['whitelisted'] = 'boolean';
		$this->_table = 'linkstatuses';
	}
}
