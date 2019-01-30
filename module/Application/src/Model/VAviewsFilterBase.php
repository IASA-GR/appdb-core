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

class VAviewsFilterBase extends Filter {
	public function __construct() {
		parent::__construct();
		$this->setDialect(0);
		$this->_fields[] = 'vapplistid';
		$this->_fields[] = 'vappversionid';
		$this->_fields[] = 'vmiinstanceid';
		$this->_fields[] = 'size';
		$this->_fields[] = 'uri';
		$this->_fields[] = 'vmiinstance_version';
		$this->_fields[] = 'checksum';
		$this->_fields[] = 'checksumfunc';
		$this->_fields[] = 'vmiinstance_notes';
		$this->_fields[] = 'vmiinstance_guid';
		$this->_fields[] = 'vmiinstance_addedon';
		$this->_fields[] = 'vmiinstance_addedby';
		$this->_fields[] = 'vmiflavourid';
		$this->_fields[] = 'autointegrity';
		$this->_fields[] = 'coreminimum';
		$this->_fields[] = 'ramminimum';
		$this->_fields[] = 'vmiinstance_lastupdatedby';
		$this->_fields[] = 'vmiinstance_lastupdatedon';
		$this->_fields[] = 'vmiinstance_description';
		$this->_fields[] = 'vmiinstance_title';
		$this->_fields[] = 'integrity_status';
		$this->_fields[] = 'integrity_message';
		$this->_fields[] = 'ramrecommend';
		$this->_fields[] = 'corerecommend';
		$this->_fields[] = 'accessinfo';
		$this->_fields[] = 'vmiinstance_enabled';
		$this->_fields[] = 'initialsize';
		$this->_fields[] = 'initialchecksum';
		$this->_fields[] = 'ovfurl';
		$this->_fields[] = 'default_access';
		$this->_fields[] = 'vmiid';
		$this->_fields[] = 'hypervisors';
		$this->_fields[] = 'archid';
		$this->_fields[] = 'osid';
		$this->_fields[] = 'osversion';
		$this->_fields[] = 'format';
		$this->_fields[] = 'vmi_name';
		$this->_fields[] = 'vmi_description';
		$this->_fields[] = 'vmi_guid';
		$this->_fields[] = 'va_id';
		$this->_fields[] = 'vmi_notes';
		$this->_fields[] = 'groupname';
		$this->_fields[] = 'va_name';
		$this->_fields[] = 'appid';
		$this->_fields[] = 'va_guid';
		$this->_fields[] = 'imglst_private';
		$this->_fields[] = 'va_version';
		$this->_fields[] = 'va_version_guid';
		$this->_fields[] = 'va_version_notes';
		$this->_fields[] = 'va_version_published';
		$this->_fields[] = 'va_version_createdon';
		$this->_fields[] = 'va_version_expireson';
		$this->_fields[] = 'va_version_enabled';
		$this->_fields[] = 'va_version_archived';
		$this->_fields[] = 'va_version_status';
		$this->_fields[] = 'va_version_archivedon';
		$this->_fields[] = 'submissionid';
		$this->_fields[] = 'va_version_isexternal';
		$this->_fieldTypes['vapplistid'] = 'integer';
		$this->_fieldTypes['vappversionid'] = 'integer';
		$this->_fieldTypes['vmiinstanceid'] = 'integer';
		$this->_fieldTypes['size'] = 'string';
		$this->_fieldTypes['uri'] = 'string';
		$this->_fieldTypes['vmiinstance_version'] = 'string';
		$this->_fieldTypes['checksum'] = 'string';
		$this->_fieldTypes['checksumfunc'] = 'e_hashfuncs';
		$this->_fieldTypes['vmiinstance_notes'] = 'string';
		$this->_fieldTypes['vmiinstance_guid'] = 'uuid';
		$this->_fieldTypes['vmiinstance_addedon'] = 'string';
		$this->_fieldTypes['vmiinstance_addedby'] = 'integer';
		$this->_fieldTypes['vmiflavourid'] = 'integer';
		$this->_fieldTypes['autointegrity'] = 'boolean';
		$this->_fieldTypes['coreminimum'] = 'integer';
		$this->_fieldTypes['ramminimum'] = 'string';
		$this->_fieldTypes['vmiinstance_lastupdatedby'] = 'integer';
		$this->_fieldTypes['vmiinstance_lastupdatedon'] = 'string';
		$this->_fieldTypes['vmiinstance_description'] = 'string';
		$this->_fieldTypes['vmiinstance_title'] = 'string';
		$this->_fieldTypes['integrity_status'] = 'string';
		$this->_fieldTypes['integrity_message'] = 'string';
		$this->_fieldTypes['ramrecommend'] = 'bigint';
		$this->_fieldTypes['corerecommend'] = 'integer';
		$this->_fieldTypes['accessinfo'] = 'string';
		$this->_fieldTypes['vmiinstance_enabled'] = 'boolean';
		$this->_fieldTypes['initialsize'] = 'bigint';
		$this->_fieldTypes['initialchecksum'] = 'string';
		$this->_fieldTypes['ovfurl'] = 'string';
		$this->_fieldTypes['default_access'] = 'string';
		$this->_fieldTypes['vmiid'] = 'integer';
		$this->_fieldTypes['hypervisors'] = 'e_hypervisors[]';
		$this->_fieldTypes['archid'] = 'integer';
		$this->_fieldTypes['osid'] = 'integer';
		$this->_fieldTypes['osversion'] = 'string';
		$this->_fieldTypes['format'] = 'string';
		$this->_fieldTypes['vmi_name'] = 'string';
		$this->_fieldTypes['vmi_description'] = 'string';
		$this->_fieldTypes['vmi_guid'] = 'string';
		$this->_fieldTypes['va_id'] = 'integer';
		$this->_fieldTypes['vmi_notes'] = 'string';
		$this->_fieldTypes['groupname'] = 'string';
		$this->_fieldTypes['va_name'] = 'string';
		$this->_fieldTypes['appid'] = 'integer';
		$this->_fieldTypes['va_guid'] = 'string';
		$this->_fieldTypes['imglst_private'] = 'boolean';
		$this->_fieldTypes['va_version'] = 'string';
		$this->_fieldTypes['va_version_guid'] = 'uuid';
		$this->_fieldTypes['va_version_notes'] = 'string';
		$this->_fieldTypes['va_version_published'] = 'boolean';
		$this->_fieldTypes['va_version_createdon'] = 'string';
		$this->_fieldTypes['va_version_expireson'] = 'string';
		$this->_fieldTypes['va_version_enabled'] = 'boolean';
		$this->_fieldTypes['va_version_archived'] = 'boolean';
		$this->_fieldTypes['va_version_status'] = 'string';
		$this->_fieldTypes['va_version_archivedon'] = 'string';
		$this->_fieldTypes['submissionid'] = 'integer';
		$this->_fieldTypes['va_version_isexternal'] = 'boolean';
		$this->_table = 'vaviews';
	}
}
