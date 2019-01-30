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

class ResearchersFilter extends ResearchersFilterBase {
	public function __construct() {
		parent::__construct();
		$this->_fields[] = 'rank';
		$this->_fields[] = 'EXTRACT(YEAR FROM dateinclusion)';
		$this->_fields[] = 'EXTRACT(MONTH FROM dateinclusion)';
		$this->_fields[] = 'EXTRACT(DAY FROM dateinclusion)';
        $this->_fields[] = "SUBSTRING(researchers.name, 1, 1)";
		$this->_fields[] = 'any.any';
		$this->_fieldTypes['rank'] = 'integer';
		$this->_fieldTypes['EXTRACT(YEAR FROM dateinclusion)'] = 'integer';
		$this->_fieldTypes['EXTRACT(MONTH FROM dateinclusion)'] = 'integer';
		$this->_fieldTypes['EXTRACT(DAY FROM dateinclusion)'] = 'integer';
		$this->_fieldTypes['password'] = 'NULL';
        $this->_fieldTypes["SUBSTRING(researchers.name, 1, 1)"] = 'string';
		$this->_fieldTypes['any.any'] = 'string';
	}

	public function item($i) {
		if ($i !== 'password') {
			return parent::item($i); 
		} else {
			return parent::item('name');
		}
	}
}
