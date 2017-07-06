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
class Default_Model_VMINetworkTrafficFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'flow_bits';
		$this->_fields[] = 'net_protocol_bits';
		$this->_fields[] = 'ip_range';
		$this->_fields[] = 'ports';
		$this->_fields[] = 'vmiinstanceid';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['flow_bits'] = 'string';
		$this->_fieldTypes['net_protocol_bits'] = 'string';
		$this->_fieldTypes['ip_range'] = 'string';
		$this->_fieldTypes['ports'] = 'string';
		$this->_fieldTypes['vmiinstanceid'] = 'integer';		
		$this->_table = 'vmi_net_traffic';
	}
}
