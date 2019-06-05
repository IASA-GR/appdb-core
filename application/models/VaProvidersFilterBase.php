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
class Default_Model_VaProvidersFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'sitename';
		$this->_fields[] = 'url';
		$this->_fields[] = 'gocdb_url';
		$this->_fields[] = 'hostname';
		$this->_fields[] = 'service_type';
		$this->_fields[] = 'serviceid';
		$this->_fields[] = 'service_status';
		$this->_fields[] = 'service_downtime';
		$this->_fields[] = 'service_downtime::int';
		$this->_fields[] = 'service_downtime::int::boolean';
		$this->_fields[] = 'host_dn';
		$this->_fields[] = 'host_ip';
		$this->_fields[] = 'host_os_id';
		$this->_fields[] = 'host_arch_id';
		$this->_fields[] = 'beta';
		$this->_fields[] = 'in_production';
		$this->_fields[] = 'node_monitored';
		$this->_fields[] = 'country_id';
		$this->_fields[] = 'ngi';
		$this->_fields[] = 'guid';
		$this->_fieldTypes['id'] = 'string';
		$this->_fieldTypes['sitename'] = 'string';
		$this->_fieldTypes['url'] = 'string';
		$this->_fieldTypes['gocdb_url'] = 'string';
		$this->_fieldTypes['hostname'] = 'string';
		$this->_fieldTypes['service_type'] = 'string';
		$this->_fieldTypes['serviceid'] = 'string';
		$this->_fieldTypes['service_status'] = 'boolean';
		$this->_fieldTypes['service_downtime'] = 'string';
		$this->_fieldTypes['service_downtime::int'] = 'integer';
		$this->_fieldTypes['service_downtime::int::boolean'] = 'boolean';
		$this->_fieldTypes['host_dn'] = 'string';
		$this->_fieldTypes['host_ip'] = 'string';
		$this->_fieldTypes['host_os_id'] = 'integer';
		$this->_fieldTypes['host_arch_id'] = 'integer';
		$this->_fieldTypes['beta'] = 'boolean';
		$this->_fieldTypes['in_production'] = 'boolean';
		$this->_fieldTypes['node_monitored'] = 'boolean';
		$this->_fieldTypes['country_id'] = 'integer';
		$this->_fieldTypes['ngi'] = 'string';
		$this->_fieldTypes['guid'] = 'uuid';
		$this->_table = 'va_providers';
	}
}
