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
class Default_Model_VMcasterRequestsFilterBase extends Default_Model_Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'id';
		$this->_fields[] = 'status';
		$this->_fields[] = 'username';
		$this->_fields[] = 'password';
		$this->_fields[] = 'authtype';
		$this->_fields[] = 'errormessage';
		$this->_fields[] = 'insertedon';
		$this->_fields[] = 'lastsubmitted';
		$this->_fields[] = 'ip';
		$this->_fields[] = 'input_vmil';
		$this->_fields[] = 'produced_xml';
		$this->_fields[] = 'appid';
		$this->_fields[] = 'action';
		$this->_fields[] = 'entity';
		$this->_fields[] = 'ldap_sn';
		$this->_fields[] = 'ldap_dn';
		$this->_fields[] = 'ldap_email';
		$this->_fields[] = 'ldap_displayname';
		$this->_fields[] = 'ldap_cn';
		$this->_fields[] = 'ldap_usercertificatesubject';
		$this->_fields[] = 'ldap_givenname';
		$this->_fields[] = 'rid';
		$this->_fields[] = 'uid';
		$this->_fieldTypes['id'] = 'integer';
		$this->_fieldTypes['status'] = 'string';
		$this->_fieldTypes['username'] = 'string';
		$this->_fieldTypes['password'] = 'string';
		$this->_fieldTypes['authtype'] = 'string';
		$this->_fieldTypes['errormessage'] = 'string';
		$this->_fieldTypes['insertedon'] = 'string';
		$this->_fieldTypes['lastsubmitted'] = 'string';
		$this->_fieldTypes['ip'] = 'string';
		$this->_fieldTypes['input_vmil'] = 'string';
		$this->_fieldTypes['produced_xml'] = 'string';
		$this->_fieldTypes['appid'] = 'integer';
		$this->_fieldTypes['action'] = 'string';
		$this->_fieldTypes['entity'] = 'string';
		$this->_fieldTypes['ldap_sn'] = 'string';
		$this->_fieldTypes['ldap_dn'] = 'string';
		$this->_fieldTypes['ldap_email'] = 'string';
		$this->_fieldTypes['ldap_displayname'] = 'string';
		$this->_fieldTypes['ldap_cn'] = 'string';
		$this->_fieldTypes['ldap_usercertificatesubject'] = 'string';
		$this->_fieldTypes['ldap_givenname'] = 'string';
		$this->_fieldTypes['rid'] = 'integer';
		$this->_fieldTypes['uid'] = 'integer';
		$this->_table = 'vmcaster_requests';
	}
}
